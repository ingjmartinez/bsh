<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentasDsVirtualDateRequest;
use App\Services\Lotobet\VentasDsVirtualService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class VentasDsVirtualController extends Controller
{
    public function __construct(private readonly VentasDsVirtualService $ventasDsVirtual) {}

    public function index(): View
    {
        return view('lotobet.ventas-ds-virtual');
    }

    public function data(VentasDsVirtualDateRequest $request): JsonResponse
    {
        try {
            $ventas = $this->ventasDsVirtual->fetch($request->validated('fecha'));

            return response()->json([
                'ventas' => $ventas,
                'total' => $ventas->count(),
            ]);
        } catch (Throwable $exception) {
            return $this->externalApiError($exception, $request->validated('fecha'));
        }
    }

    public function sync(VentasDsVirtualDateRequest $request): JsonResponse
    {
        try {
            $result = $this->ventasDsVirtual->sync($request->validated('fecha'));

            return response()->json([
                'message' => 'Ventas DS Virtual sincronizadas correctamente.',
                ...$result,
            ]);
        } catch (Throwable $exception) {
            return $this->externalApiError($exception, $request->validated('fecha'));
        }
    }

    public function destroy(VentasDsVirtualDateRequest $request): JsonResponse
    {
        $deleted = $this->ventasDsVirtual->delete($request->validated('fecha'));

        return response()->json([
            'message' => 'Ventas DS Virtual eliminadas correctamente.',
            'deleted' => $deleted,
        ]);
    }

    private function externalApiError(Throwable $exception, string $fecha): JsonResponse
    {
        Log::error('Error procesando Ventas DS Virtual', [
            'fecha' => $fecha,
            'error' => $exception->getMessage(),
        ]);

        return response()->json([
            'message' => $exception->getMessage(),
        ], 502);
    }
}
