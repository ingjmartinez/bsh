<?php

namespace App\Http\Controllers;

use App\Exports\ReporteDiarioRutaExport;
use App\Models\BancoOperacion;
use App\Models\OperadorRuta;
use App\Models\ReporteDiarioRuta;
use App\Models\Ruta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class OperacionesReporteDiarioController extends Controller
{
    public function index(Request $request)
    {
        $fechaFiltro = (string) $request->input('fecha', now()->toDateString());

        $rutas = Ruta::query()
            ->with('operadorAsignado:id,nombre,apellido,correo')
            ->orderBy('nombre_ruta')
            ->get();

        $ultimosReportes = $this->obtenerReportesPorFecha($fechaFiltro);
        $bancos = BancoOperacion::query()->orderBy('nombre')->get();

        return view('operaciones.reportes.diario', compact('rutas', 'ultimosReportes', 'fechaFiltro', 'bancos'));
    }

    public function exportExcel(Request $request)
    {
        $fechaFiltro = (string) $request->input('fecha', now()->toDateString());
        $reportes = $this->obtenerReportesPorFecha($fechaFiltro);

        $fileName = 'reporte_diario_operaciones_' . str_replace('-', '', $fechaFiltro) . '_' . now()->format('His') . '.xlsx';

        return Excel::download(new ReporteDiarioRutaExport($reportes), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $fechaFiltro = (string) $request->input('fecha', now()->toDateString());
        $reportes = $this->obtenerReportesPorFecha($fechaFiltro);

        $pdf = Pdf::loadView('operaciones.reportes.diario_pdf', [
            'reportes' => $reportes,
            'fechaFiltro' => $fechaFiltro,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('reporte_diario_operaciones_' . str_replace('-', '', $fechaFiltro) . '.pdf');
    }

    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:guardar,guardar_enviar'],
            'fecha' => ['required', 'date'],
            'serial_ruta' => ['required', 'string', 'max:20', 'regex:/^\d{1,20}$/'],
            'ruta_id' => ['required', 'integer', 'exists:rutas,id'],
            'operador_ruta_id' => ['required', 'integer', 'exists:operador_ruta,id'],
            'banco_nombre' => ['nullable', 'string', 'max:150'],
            'entregado' => ['nullable', 'numeric', 'min:0'],
            'procesado' => ['required', 'numeric', 'min:0'],
            'gasto' => ['nullable', 'numeric', 'min:0'],
            'comprobante_entregado_path' => ['nullable', 'string', 'max:500'],
            'comprobante_diferencia_path' => ['nullable', 'string', 'max:500'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $ruta = Ruta::query()
            ->with('operadorAsignado:id,nombre,apellido')
            ->findOrFail((int) $validated['ruta_id']);

        $operador = OperadorRuta::query()
            ->select('id', 'nombre', 'apellido', 'correo')
            ->findOrFail((int) $validated['operador_ruta_id']);

        if ((int) $ruta->operador_ruta_id !== (int) $operador->id) {
            return redirect()->back()->withInput()->with('error', 'El operador seleccionado no coincide con la ruta indicada.');
        }

        $entregado = (float) ($validated['entregado'] ?? 0);
        $procesado = (float) $validated['procesado'];
        $gasto = (float) ($validated['gasto'] ?? 0);
        $diferencia = $procesado - ($entregado + $gasto);

        $reporte = ReporteDiarioRuta::create([
            'fecha' => $validated['fecha'],
            'serial_ruta' => $validated['serial_ruta'],
            'ruta_id' => $ruta->id,
            'operador_ruta_id' => $operador->id,
            'banco_nombre' => $validated['banco_nombre'] ?? null,
            'entregado' => $entregado,
            'procesado' => $procesado,
            'gasto' => $gasto,
            'diferencia' => $diferencia,
            'correo_destino' => (string) ($operador->correo ?? ''),
            'observacion' => $validated['observacion'] ?? null,
            'comprobante_entregado_path' => $validated['comprobante_entregado_path'],
            'comprobante_diferencia_path' => $validated['comprobante_diferencia_path'],
        ]);

        if ($validated['accion'] === 'guardar') {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => $validated['fecha']])
                ->with('success', 'Informe guardado correctamente.');
        }

        if (empty($operador->correo)) {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => $validated['fecha']])
                ->with('error', 'El informe se guardo, pero el operador no tiene correo configurado.');
        }

        return $this->enviarCorreoInforme($reporte, true);
    }

    public function guardarBanco(Request $request)
    {
        $validated = $request->validateWithBag('guardarBanco', [
            'nombre_banco' => ['required', 'string', 'max:150', 'unique:bancos_operaciones,nombre'],
            'fecha' => ['nullable', 'date'],
        ]);

        BancoOperacion::create([
            'nombre' => trim((string) $validated['nombre_banco']),
        ]);

        $fecha = (string) ($validated['fecha'] ?? now()->toDateString());

        return redirect()->route('operaciones.reporte.diario', ['fecha' => $fecha])
            ->with('success', 'Banco guardado correctamente.');
    }

    public function enviarInformePorCorreo(ReporteDiarioRuta $reporte_diario_ruta)
    {
        if (empty(optional($reporte_diario_ruta->operador)->correo)) {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => optional($reporte_diario_ruta->fecha)->toDateString()])
                ->with('error', 'El operador asignado no tiene correo configurado.');
        }

        return $this->enviarCorreoInforme($reporte_diario_ruta, false);
    }

    public function enviarTodoPorCorreo(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'correo_destino' => ['required', 'email', 'max:150'],
        ]);

        $fechaFiltro = (string) $validated['fecha'];
        $correoDestino = (string) $validated['correo_destino'];
        $reportes = $this->obtenerReportesPorFecha($fechaFiltro);

        if ($reportes->isEmpty()) {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => $fechaFiltro])
                ->with('error', 'No hay reportes para enviar en la fecha seleccionada.');
        }

        try {
            Mail::send('emails.operaciones.reporte_diario_ruta_general', [
                'fecha' => \Carbon\Carbon::parse($fechaFiltro)->format('d/m/Y'),
                'reportes' => $reportes,
            ], function ($message) use ($correoDestino, $fechaFiltro) {
                $message->to($correoDestino)
                    ->subject('Reporte Diario de Operaciones - ' . $fechaFiltro);
            });
        } catch (Throwable $e) {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => $fechaFiltro])
                ->with('error', 'No se pudo enviar el correo masivo: ' . $e->getMessage());
        }

        return redirect()->route('operaciones.reporte.diario', ['fecha' => $fechaFiltro])
            ->with('success', 'Reporte diario enviado correctamente a ' . $correoDestino . '.');
    }

    public function actualizarGasto(Request $request, ReporteDiarioRuta $reporte_diario_ruta)
    {
        $validated = $request->validate([
            'entregado' => ['nullable', 'numeric', 'min:0'],
            'gasto' => ['required', 'numeric', 'min:0'],
        ]);

        $entregado = (float) ($validated['entregado'] ?? 0);
        $gasto = (float) $validated['gasto'];
        $procesado = (float) $reporte_diario_ruta->procesado;

        $reporte_diario_ruta->entregado = $entregado;
        $reporte_diario_ruta->gasto = $gasto;
        $reporte_diario_ruta->diferencia = $procesado - ($entregado + $gasto);
        $reporte_diario_ruta->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Depositos y gastos actualizados correctamente.',
                'entregado' => number_format((float) $reporte_diario_ruta->entregado, 2, '.', ''),
                'procesado' => number_format((float) $reporte_diario_ruta->procesado, 2, '.', ''),
                'gasto' => number_format((float) ($reporte_diario_ruta->gasto ?? 0), 2, '.', ''),
                'diferencia' => number_format((float) $reporte_diario_ruta->diferencia, 2, '.', ''),
                'estatus' => abs((float) $reporte_diario_ruta->diferencia) > 0.00001 ? 'Pendiente' : 'Completada',
            ]);
        }

        return redirect()->route('operaciones.reporte.diario', [
            'fecha' => optional($reporte_diario_ruta->fecha)->toDateString(),
        ])->with('success', 'Depositos y gastos actualizados correctamente.');
    }

    public function verComprobante(ReporteDiarioRuta $reporte_diario_ruta, string $tipo)
    {
        if (!in_array($tipo, ['entregado', 'diferencia'], true)) {
            abort(404);
        }

        $rutaComprobanteRaw = $tipo === 'entregado'
            ? $reporte_diario_ruta->comprobante_entregado_path
            : $reporte_diario_ruta->comprobante_diferencia_path;
        $rutaComprobante = $this->normalizarRutaStorage($rutaComprobanteRaw);

        if (empty($rutaComprobante) || !Storage::disk('local')->exists($rutaComprobante)) {
            abort(404, 'Comprobante no encontrado.');
        }

        return response()->file(Storage::disk('local')->path($rutaComprobante));
    }

    public function obtenerComprobantes(ReporteDiarioRuta $reporte_diario_ruta)
    {
        $entregado = $this->normalizarRutaStorage($reporte_diario_ruta->comprobante_entregado_path);
        $diferencia = $this->normalizarRutaStorage($reporte_diario_ruta->comprobante_diferencia_path);

        $urlEntregado = null;
        $urlDiferencia = null;

        if (!empty($entregado) && Storage::disk('local')->exists($entregado)) {
            $urlEntregado = route('operaciones.reporte.diario.comprobante', [
                'reporte_diario_ruta' => $reporte_diario_ruta->id,
                'tipo' => 'entregado',
            ]);
        }

        if (!empty($diferencia) && Storage::disk('local')->exists($diferencia)) {
            $urlDiferencia = route('operaciones.reporte.diario.comprobante', [
                'reporte_diario_ruta' => $reporte_diario_ruta->id,
                'tipo' => 'diferencia',
            ]);
        }

        return response()->json([
            'id' => $reporte_diario_ruta->id,
            'banco_nombre' => $reporte_diario_ruta->banco_nombre,
            'entregado' => number_format((float) $reporte_diario_ruta->entregado, 2, '.', ''),
            'procesado' => number_format((float) $reporte_diario_ruta->procesado, 2, '.', ''),
            'gasto' => number_format((float) ($reporte_diario_ruta->gasto ?? 0), 2, '.', ''),
            'diferencia' => number_format((float) $reporte_diario_ruta->diferencia, 2, '.', ''),
            'entregado_url' => $urlEntregado,
            'diferencia_url' => $urlDiferencia,
        ]);
    }

    public function actualizarBanco(Request $request, ReporteDiarioRuta $reporte_diario_ruta)
    {
        $validated = $request->validate([
            'banco_nombre' => ['nullable', 'string', 'max:150', 'exists:bancos_operaciones,nombre'],
        ]);

        $reporte_diario_ruta->banco_nombre = $validated['banco_nombre'] ?? null;
        $reporte_diario_ruta->save();

        return response()->json([
            'message' => 'Banco actualizado correctamente.',
            'banco_nombre' => $reporte_diario_ruta->banco_nombre,
        ]);
    }

    public function uploadComprobante(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:5120'],
            'tipo' => ['required', 'in:entregado,diferencia'],
        ]);

        $file = $validated['file'];
        $tipo = (string) $validated['tipo'];
        $nombre = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $ruta = 'operaciones/reportes_diarios/' . $tipo . '/' . now()->format('Y/m') . '/' . $nombre;

        Storage::disk('local')->putFileAs(
            dirname($ruta),
            $file,
            basename($ruta)
        );

        return response()->json([
            'message' => 'Archivo cargado correctamente.',
            'path' => $ruta,
        ]);
    }

    public function uploadComprobanteEnReporte(Request $request, ReporteDiarioRuta $reporte_diario_ruta)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:5120'],
            'tipo' => ['required', 'in:entregado,diferencia'],
        ]);

        $file = $validated['file'];
        $tipo = (string) $validated['tipo'];
        $nombre = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $ruta = 'operaciones/reportes_diarios/' . $tipo . '/' . now()->format('Y/m') . '/' . $nombre;

        Storage::disk('local')->putFileAs(
            dirname($ruta),
            $file,
            basename($ruta)
        );

        $campo = $tipo === 'entregado' ? 'comprobante_entregado_path' : 'comprobante_diferencia_path';
        $anterior = $this->normalizarRutaStorage($reporte_diario_ruta->{$campo});

        if (!empty($anterior) && Storage::disk('local')->exists($anterior)) {
            Storage::disk('local')->delete($anterior);
        }

        $reporte_diario_ruta->{$campo} = $ruta;
        $reporte_diario_ruta->save();

        return response()->json([
            'message' => 'Comprobante actualizado correctamente.',
            'path' => $ruta,
            'url' => route('operaciones.reporte.diario.comprobante', [
                'reporte_diario_ruta' => $reporte_diario_ruta->id,
                'tipo' => $tipo,
            ]),
        ]);
    }

    private function enviarCorreoInforme(ReporteDiarioRuta $reporte, bool $informeRecienCreado)
    {
        $reporte->loadMissing([
            'ruta:id,nombre_ruta',
            'operador:id,nombre,apellido,correo',
        ]);

        $operador = $reporte->operador;
        $correo = (string) ($operador->correo ?? $reporte->correo_destino ?? '');

        if ($correo === '') {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => optional($reporte->fecha)->toDateString()])
                ->with('error', 'No se encontro correo destino para el operador.');
        }

        $payload = [
            'fecha' => optional($reporte->fecha)->format('d/m/Y') ?? now()->format('d/m/Y'),
            'ruta' => $reporte->ruta->nombre_ruta ?? '-',
            'empresa' => $reporte->ruta->empresa ?? '-',
            'operador' => trim((($operador->nombre ?? '') . ' ' . ($operador->apellido ?? ''))),
            'entregado' => number_format((float) $reporte->entregado, 2),
            'procesado' => number_format((float) $reporte->procesado, 2),
            'gasto' => number_format((float) $reporte->gasto, 2),
            'diferencia' => number_format((float) $reporte->diferencia, 2),
            'observacion' => (string) ($reporte->observacion ?? ''),
        ];

        try {
            Mail::send('emails.operaciones.reporte_diario_ruta', $payload, function ($message) use ($correo, $payload) {
                $message->to($correo)
                    ->subject('Cuadre Diario de Ruta - ' . $payload['ruta'] . ' - ' . $payload['fecha']);
            });
        } catch (Throwable $e) {
            return redirect()->route('operaciones.reporte.diario', ['fecha' => optional($reporte->fecha)->toDateString()])
                ->with('error', 'No se pudo enviar el correo: ' . $e->getMessage());
        }

        $reporte->enviado_operador_at = now();
        $reporte->save();

        $mensaje = $informeRecienCreado
            ? 'Cuadre de ruta guardado y enviado al operador correctamente.'
            : 'Informe enviado por correo correctamente.';

        return redirect()->route('operaciones.reporte.diario')
            ->with('success', $mensaje);
    }

    private function obtenerReportesPorFecha(string $fechaFiltro)
    {
        return ReporteDiarioRuta::query()
            ->with([
                'ruta:id,nombre_ruta,empresa',
                'operador:id,nombre,apellido,correo',
            ])
            ->whereDate('fecha', $fechaFiltro)
            ->orderByDesc('id')
            ->get();
    }

    private function normalizarRutaStorage(?string $ruta): ?string
    {
        if ($ruta === null || trim($ruta) === '') {
            return null;
        }

        return ltrim(str_replace('\\', '/', trim($ruta)), '/');
    }
}
