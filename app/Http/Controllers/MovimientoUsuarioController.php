<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarMovimientoUsuarioRequest;
use App\Models\Empleado;
use App\Models\MovimientoUsuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimientoUsuarioController extends Controller
{
    public function index(): View
    {
        return view('recursos_humanos.movimiento-usuario');
    }

    public function employee(Request $request): JsonResponse
    {
        $validated = $request->validate(['cedula' => ['required', 'string', 'max:30']]);
        $identity = preg_replace('/\D+/', '', $validated['cedula']) ?: $validated['cedula'];
        $employee = Empleado::query()
            ->whereRaw("REPLACE(REPLACE(cedula, '-', ''), ' ', '') = ?", [$identity])
            ->orderByDesc('estatus')
            ->orderByDesc('id')
            ->first();

        if (! $employee) {
            return response()->json(['message' => 'No se encontró un empleado con esa cédula.'], 404);
        }

        return response()->json(['empleado' => [
            'id' => $employee->id,
            'cedula' => $employee->cedula,
            'nombre' => trim($employee->nombres.' '.$employee->apellidos),
            'centro_costo' => $employee->idcentrocosto,
            'estatus' => $employee->estatus ? 'Activo' : 'Inactivo',
        ]]);
    }

    public function store(GuardarMovimientoUsuarioRequest $request): JsonResponse
    {
        $employee = Empleado::query()->findOrFail($request->validated('empleado_id'));
        $movement = MovimientoUsuario::query()->create([
            ...$request->safe()->except('empleado_id'),
            'empleado_id' => $employee->id,
            'cedula' => $employee->cedula,
            'nombre_empleado' => trim($employee->nombres.' '.$employee->apellidos),
            'registrado_por' => $request->user()?->id,
        ]);

        return response()->json(['message' => 'Movimiento registrado correctamente.', 'movimiento' => $movement], 201);
    }

    public function list(): JsonResponse
    {
        $movements = MovimientoUsuario::query()
            ->with('registradoPor:id,name,email')
            ->latest()
            ->limit(500)
            ->get()
            ->map(function (MovimientoUsuario $movement): array {
                return [
                    ...$movement->toArray(),
                    'usuario_registro' => $movement->registradoPor?->name
                        ?: $movement->registradoPor?->email
                        ?: 'Usuario no disponible',
                    'usuario_email' => $movement->registradoPor?->email,
                ];
            });

        return response()->json(['movimientos' => $movements]);
    }
}
