<?php

return [
    [
        'nombre' => 'Solicitudes de Empleo',
        'descripcion' => 'Gestiona solicitudes, formularios y expedientes de candidatos.',
        'url' => '/registro-empleados',
        'icono' => 'ri-file-user-line',
        'categoria' => 'Reclutamiento',
        'tags' => ['solicitudes', 'registro', 'candidatos', 'empleo'],
        'activo' => true,
    ],
    [
        'nombre' => 'Empleados',
        'descripcion' => 'Consulta empleados, estatus, dashboard y masa salarial por empresa.',
        'url' => '/empleados',
        'icono' => 'ri-team-line',
        'categoria' => 'Personal',
        'tags' => ['empleados', 'dashboard', 'estatus', 'salarios'],
        'activo' => true,
    ],
    [
        'nombre' => 'Novedades de Horario',
        'descripcion' => 'Revisa novedades, marcas y variaciones de horario del personal.',
        'url' => '/recursos-humanos/novedades-horario',
        'icono' => 'ri-time-line',
        'categoria' => 'Asistencia',
        'tags' => ['horario', 'novedades', 'asistencia', 'marcas'],
        'activo' => true,
    ],
    [
        'nombre' => 'Entrevista Online',
        'descripcion' => 'Registra entrevistas telefónicas u online realizadas a candidatos.',
        'url' => '/entrevistas-online',
        'icono' => 'ri-video-chat-line',
        'categoria' => 'Reclutamiento',
        'tags' => ['entrevista', 'candidatos', 'vacantes', 'online'],
        // Vista ocultada del hub de Recursos Humanos; las rutas se conservan para no perder historial ni datos.
        'activo' => false,
    ],
    [
        'nombre' => 'Empleados No Regularizados',
        'descripcion' => 'Da seguimiento a empleados pendientes de regularización.',
        'url' => '/empleados-no-regularizados',
        'icono' => 'ri-user-warning-line',
        'categoria' => 'Control',
        'tags' => ['regularizados', 'pendientes', 'empleados'],
        'activo' => false,
    ],
    [
        'nombre' => 'Ventas Sin Empleado',
        'descripcion' => 'Identifica ventas que necesitan asociación o validación de empleado.',
        'url' => '/ventas-sin-empleado',
        'icono' => 'ri-user-search-line',
        'categoria' => 'Control',
        'tags' => ['ventas', 'sin empleado', 'cédula', 'validación'],
        'activo' => false,
    ],
];
