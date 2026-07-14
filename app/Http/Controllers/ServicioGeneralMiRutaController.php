<?php

namespace App\Http\Controllers;

use App\Models\CoordinadorOperador;
use App\Models\ServicioGeneralChecklistItem;
use App\Models\ServicioGeneralRutaInspeccion;
use App\Models\ServicioGeneralVisitaRespuesta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServicioGeneralMiRutaController extends Controller
{
    public function index(Request $request): View
    {
        $persona = $this->personaActual($request);
        $fecha = $request->input('fecha', now()->toDateString());
        $visitas = collect();

        if ($persona) {
            $visitas = ServicioGeneralRutaInspeccion::with(['agencia', 'respuestas.item', 'averiasDerivadas'])
                ->where('coordinador_operador_id', $persona->id)
                ->where('tipo', 'inspeccion')->where('generado_automaticamente', true)
                ->whereDate('fecha', $fecha)->orderBy('id')->get();
        }

        return view('servicios-generales.mi-ruta', [
            'persona' => $persona, 'fecha' => $fecha, 'visitas' => $visitas,
            'items' => ServicioGeneralChecklistItem::where('activo', true)->orderBy('orden')->get(),
        ]);
    }

    public function iniciar(Request $request, ServicioGeneralRutaInspeccion $rutaInspeccion): RedirectResponse
    {
        $this->autorizar($request, $rutaInspeccion);
        if (!$rutaInspeccion->check_in_at) {
            $validated = $request->validate(['latitud'=>'nullable|numeric|between:-90,90','longitud'=>'nullable|numeric|between:-180,180']);
            $anterior = $rutaInspeccion->estado;
            $rutaInspeccion->update([
                'check_in_at'=>now(), 'estado'=>'en_inspeccion',
                'check_in_latitud'=>$validated['latitud'] ?? null, 'check_in_longitud'=>$validated['longitud'] ?? null,
                'iniciado_at'=>$rutaInspeccion->iniciado_at ?: now(),
            ]);
            $this->historial($rutaInspeccion, 'visita_iniciada', $anterior, 'en_inspeccion', 'Check-in registrado desde el panel móvil.');
        }
        return back()->with('success', 'Visita iniciada. Completa todos los requisitos.');
    }

    public function guardarChecklist(Request $request, ServicioGeneralRutaInspeccion $rutaInspeccion): RedirectResponse
    {
        $this->autorizar($request, $rutaInspeccion);
        if (!$rutaInspeccion->check_in_at) return back()->with('error', 'Primero debes iniciar la visita.');
        $request->validate(['evidencias.*'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']]);

        $items = ServicioGeneralChecklistItem::where('activo', true)->orderBy('orden')->get();
        $respuestas = $request->input('respuestas', []);
        $errores = [];
        foreach ($items as $item) {
            $respuesta = $respuestas[$item->id] ?? [];
            $resultado = $respuesta['resultado'] ?? null;
            if ($item->requerido && !in_array($resultado, ['cumple','no_cumple','no_aplica'], true)) $errores["respuestas.{$item->id}.resultado"] = "Responde: {$item->nombre}.";
            if ($resultado === 'no_cumple' && trim((string)($respuesta['observacion'] ?? '')) === '') $errores["respuestas.{$item->id}.observacion"] = "Describe el fallo en: {$item->nombre}.";
            if ($resultado === 'no_cumple' && $item->requiere_evidencia_fallo) {
                $evidenciaAnterior = ServicioGeneralVisitaRespuesta::where('ruta_inspeccion_id',$rutaInspeccion->id)->where('checklist_item_id',$item->id)->value('evidencia_path');
                if (!$request->hasFile("evidencias.{$item->id}") && !$evidenciaAnterior) $errores["evidencias.{$item->id}"] = "Adjunta una fotografía del fallo en: {$item->nombre}.";
            }
        }
        if ($errores) throw ValidationException::withMessages($errores);

        DB::transaction(function () use ($request, $rutaInspeccion, $items, $respuestas) {
            $fallos = 0; $cumplen = 0;
            foreach ($items as $item) {
                $data = $respuestas[$item->id] ?? [];
                $resultado = $data['resultado'] ?? 'no_aplica';
                $archivo = $request->file("evidencias.{$item->id}");
                $existente = ServicioGeneralVisitaRespuesta::where('ruta_inspeccion_id',$rutaInspeccion->id)->where('checklist_item_id',$item->id)->first();
                $path = $archivo ? $archivo->store('visitas-checklist','public') : $existente?->evidencia_path;
                ServicioGeneralVisitaRespuesta::updateOrCreate(
                    ['ruta_inspeccion_id'=>$rutaInspeccion->id,'checklist_item_id'=>$item->id],
                    ['user_id'=>auth()->id(),'resultado'=>$resultado,'observacion'=>$data['observacion'] ?? null,'evidencia_path'=>$path]
                );
                if ($resultado === 'cumple' || $resultado === 'no_aplica') $cumplen++; else $fallos++;

                if ($resultado === 'no_cumple') $this->crearAveria($rutaInspeccion, $item, (string)($data['observacion'] ?? ''), $path);
            }

            $abiertas = ServicioGeneralRutaInspeccion::where('visita_origen_id',$rutaInspeccion->id)->whereNotIn('estado',['cerrada','cancelada'])->count();
            $conforme = $fallos === 0 && $abiertas === 0;
            $anterior = $rutaInspeccion->estado;
            $rutaInspeccion->update([
                'cumplimiento_porcentaje' => $items->count() ? (int) round(($cumplen / $items->count()) * 100) : 100,
                'conforme'=>$conforme, 'check_out_at'=>now(),
                'estado'=>$conforme ? 'solicitud_cierre' : 'pendiente_solucion',
                'cierre_solicitado_at'=>$conforme ? now() : null,
            ]);
            $this->historial($rutaInspeccion, $conforme ? 'visita_conforme' : 'visita_con_hallazgos', $anterior, $rutaInspeccion->estado, $conforme ? 'Checklist completo; cierre solicitado.' : "Checklist completo con {$fallos} hallazgo(s).", ['cumplimiento'=>$rutaInspeccion->cumplimiento_porcentaje]);
        });

        return back()->with('success', $rutaInspeccion->fresh()->conforme ? 'Agencia conforme. Se solicitó el cierre.' : 'Visita registrada con hallazgos. Se generaron las averías correspondientes.');
    }

    public function registrarResultado(Request $request, ServicioGeneralRutaInspeccion $rutaInspeccion): RedirectResponse
    {
        $this->autorizar($request, $rutaInspeccion);
        if (!$rutaInspeccion->check_in_at) return back()->with('error', 'Primero debes iniciar la visita.');
        if ($rutaInspeccion->check_out_at) return back()->with('error', 'Esta agencia ya fue visitada y su resultado quedó registrado.');

        $validated = $request->validate([
            'resultado' => ['required', 'in:orden,novedad'],
            'observacion' => ['nullable', 'string', 'max:3000', 'required_if:resultado,novedad'],
            'evidencia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'required_if:resultado,novedad'],
        ]);
        $enOrden = $validated['resultado'] === 'orden';
        $evidencia = $request->hasFile('evidencia') ? $request->file('evidencia')->store('visitas-ruta', 'public') : null;

        DB::transaction(function () use ($rutaInspeccion, $validated, $enOrden, $evidencia) {
            $anterior = $rutaInspeccion->estado;
            $items = ServicioGeneralChecklistItem::where('activo', true)->get();
            if ($enOrden) {
                foreach ($items as $item) {
                    ServicioGeneralVisitaRespuesta::updateOrCreate(
                        ['ruta_inspeccion_id'=>$rutaInspeccion->id, 'checklist_item_id'=>$item->id],
                        ['user_id'=>auth()->id(), 'resultado'=>'cumple', 'observacion'=>'Confirmado en ruta: todo en orden.']
                    );
                }
            }

            $metadata = $rutaInspeccion->metadata ?? [];
            $metadata['resultado_visita'] = $enOrden ? 'orden' : 'novedad';
            $metadata['observacion_visita'] = $validated['observacion'] ?? null;
            $metadata['evidencia_visita'] = $evidencia;
            $rutaInspeccion->update([
                'estado' => $enOrden ? 'cerrada' : 'pendiente_solucion',
                'conforme' => $enOrden,
                'cumplimiento_porcentaje' => $enOrden ? 100 : 0,
                'check_out_at' => now(),
                'cerrado_at' => $enOrden ? now() : null,
                'cerrado_por' => $enOrden ? auth()->id() : null,
                'metadata' => $metadata,
                'evidencia_path' => $evidencia ?: $rutaInspeccion->evidencia_path,
            ]);
            $this->historial(
                $rutaInspeccion,
                $enOrden ? 'visita_completada_en_orden' : 'visita_completada_con_novedad',
                $anterior,
                $rutaInspeccion->estado,
                $validated['observacion'] ?? 'El responsable confirmó que la agencia está en orden.'
            );
        });

        return back()->with('success', $enOrden ? 'Visita completada. Continúa con la siguiente agencia.' : 'Novedad registrada. La agencia quedó marcada para seguimiento.');
    }

    private function crearAveria(ServicioGeneralRutaInspeccion $visita, $item, string $observacion, ?string $evidencia): void
    {
        $averia = ServicioGeneralRutaInspeccion::firstOrCreate(
            ['visita_origen_id'=>$visita->id,'checklist_item_id'=>$item->id],
            ['user_id'=>auth()->id(),'agencia_id'=>$visita->agencia_id,'coordinador_operador_id'=>$visita->coordinador_operador_id,
             'responsable_nombre'=>$visita->responsable_nombre,'tipo'=>'averia','nombre'=>'Hallazgo - '.$item->nombre,'fecha'=>now()->toDateString(),
             'estado'=>'asignada','prioridad'=>'alta','descripcion'=>$item->nombre.': '.$observacion,'evidencia_path'=>$evidencia,
             'metadata'=>['origen'=>'checklist_visita','visita_id'=>$visita->id]]
        );
        if ($averia->wasRecentlyCreated) $this->historial($averia,'averia_generada_por_checklist',null,'asignada','Hallazgo detectado durante '.$visita->codigo.'.');
    }

    private function personaActual(Request $request): ?CoordinadorOperador
    {
        if ($request->filled('responsable_id') && auth()->user()?->can('servicios_generales.manage')) return CoordinadorOperador::find($request->integer('responsable_id'));
        if (CoordinadorOperador::hasResolvedColumn('user_id')) {
            $persona = CoordinadorOperador::where('user_id',auth()->id())->first();
            if ($persona) return $persona;
        }
        foreach (['email','correo'] as $columna) if (CoordinadorOperador::hasResolvedColumn($columna)) {
            $persona = CoordinadorOperador::whereRaw("LOWER({$columna}) = ?",[mb_strtolower((string)auth()->user()?->email)])->first();
            if ($persona) return $persona;
        }
        return null;
    }

    private function autorizar(Request $request, ServicioGeneralRutaInspeccion $visita): void
    {
        $persona = $this->personaActual($request);
        abort_unless(($persona && (int)$persona->id === (int)$visita->coordinador_operador_id) || auth()->user()?->can('servicios_generales.manage'), 403);
        abort_unless($visita->tipo === 'inspeccion' && $visita->generado_automaticamente, 422);
    }

    private function historial($trabajo, $accion, $anterior, $nuevo, $observacion, $cambios=[]): void
    {
        $trabajo->historial()->create(['user_id'=>auth()->id(),'accion'=>$accion,'estado_anterior'=>$anterior,'estado_nuevo'=>$nuevo,
            'responsable_id'=>$trabajo->coordinador_operador_id,'responsable_nombre'=>$trabajo->responsable_nombre,'observacion'=>$observacion,'cambios'=>$cambios ?: null]);
    }
}
