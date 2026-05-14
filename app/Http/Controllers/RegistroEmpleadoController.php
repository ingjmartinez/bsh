<?php

namespace App\Http\Controllers;

use App\Models\RegistroEmpleado;
use Illuminate\Http\Request;

class RegistroEmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empleados = RegistroEmpleado::all();
        return view('registro_empleado.index', compact('empleados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('registro_empleado.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apellidos' => 'required',
            'nombres' => 'required',
            'cedula_pasaporte' => 'required|unique:solicitudes_empleo,cedula_pasaporte',
            'email' => 'nullable|email',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_disponible' => 'nullable|date',
            'licencia_vencimiento' => 'nullable|date',
            'fecha_firma' => 'nullable|date',
            'fecha_ingreso' => 'nullable|date',
            'salario' => 'nullable|numeric',
        ]);

        $solicitud = RegistroEmpleado::create($request->except([
            'familiares', 'educacion', 'empleos', 'referencias_laborales', 'referencias_personales'
        ]));

        // Familiares
        if ($request->has('familiares')) {
            foreach ($request->familiares as $familiar) {
                $solicitud->familiares()->create($familiar);
            }
        }

        // Educación
        if ($request->has('educacion')) {
            foreach ($request->educacion as $edu) {
                $solicitud->educacion()->create($edu);
            }
        }

        // Empleos
        if ($request->has('empleos')) {
            foreach ($request->empleos as $emp) {
                $solicitud->empleos()->create($emp);
            }
        }

        // Referencias Laborales
        if ($request->has('referencias_laborales')) {
            foreach ($request->referencias_laborales as $ref) {
                $solicitud->referenciasLaborales()->create($ref);
            }
        }

        // Referencias Personales
        if ($request->has('referencias_personales')) {
            foreach ($request->referencias_personales as $ref) {
                $solicitud->referenciasPersonales()->create($ref);
            }
        }

        return redirect()->route('registro-empleados.index')->with('success', 'Solicitud de empleo registrada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $empleado = RegistroEmpleado::findOrFail($id);
        return view('registro_empleado.show', compact('empleado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $empleado = RegistroEmpleado::findOrFail($id);
        return view('registro_empleado.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'apellidos' => 'required',
            'nombres' => 'required',
            'cedula_pasaporte' => 'required|unique:solicitudes_empleo,cedula_pasaporte,' . $id . ',solicitud_id',
            'email' => 'nullable|email',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_disponible' => 'nullable|date',
            'licencia_vencimiento' => 'nullable|date',
            'fecha_firma' => 'nullable|date',
            'fecha_ingreso' => 'nullable|date',
            'salario' => 'nullable|numeric',
        ]);

        $solicitud = RegistroEmpleado::findOrFail($id);
        $solicitud->update($request->except([
            'familiares', 'educacion', 'empleos', 'referencias_laborales', 'referencias_personales'
        ]));

        // Familiares
        $solicitud->familiares()->delete();
        if ($request->has('familiares')) {
            foreach ($request->familiares as $familiar) {
                $solicitud->familiares()->create($familiar);
            }
        }

        // Educación
        $solicitud->educacion()->delete();
        if ($request->has('educacion')) {
            foreach ($request->educacion as $edu) {
                $solicitud->educacion()->create($edu);
            }
        }

        // Empleos
        $solicitud->empleos()->delete();
        if ($request->has('empleos')) {
            foreach ($request->empleos as $emp) {
                $solicitud->empleos()->create($emp);
            }
        }

        // Referencias Laborales
        $solicitud->referenciasLaborales()->delete();
        if ($request->has('referencias_laborales')) {
            foreach ($request->referencias_laborales as $ref) {
                $solicitud->referenciasLaborales()->create($ref);
            }
        }

        // Referencias Personales
        $solicitud->referenciasPersonales()->delete();
        if ($request->has('referencias_personales')) {
            foreach ($request->referencias_personales as $ref) {
                $solicitud->referenciasPersonales()->create($ref);
            }
        }

        return redirect()->route('registro-empleados.index')->with('success', 'Solicitud de empleo actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $empleado = RegistroEmpleado::findOrFail($id);
        $empleado->delete();

        return redirect()->route('registro-empleados.index')->with('success', 'Empleado eliminado exitosamente.');
    }
}
