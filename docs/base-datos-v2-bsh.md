# BSH - Diagnostico y propuesta de base de datos V2

Fecha del analisis: 2026-05-14

Este documento resume el analisis del proyecto Laravel BSH y propone una arquitectura de datos V2 limpia para la base de datos vacia `bsh`. No se ejecutaron migraciones. No se borro ni modifico codigo funcional existente.

## 1. Alcance revisado

Se revisaron estas areas del codigo:

- Rutas principales: `routes/web.php`, `routes/api.php`, `routes/console.php`.
- Modelos Eloquent en `app/Models`.
- Controladores de ventas, recargas, premios, faltantes, asistencia, reportes, dashboards, comercial, gerencia, operaciones, RRHH, tecnologia, tareas, agencias, contabilidad e incentivos.
- Exportaciones Excel en `app/Exports`.
- Servicios y helpers: `app/Services/Etl`, `app/Services/Lotobet`, `app/Support`.
- Migraciones actuales en `database/migrations`.
- SQL heredado en `database/*.sql`.
- Vistas Blade relacionadas con dashboards, reportes, BI, incentivos, operaciones, comercial y carga de data.

No se encontro directorio `app/Livewire`; por tanto, no hay componentes Livewire propios visibles en el proyecto aunque `composer.json` incluye Livewire 3.

## 2. Diagnostico actual

El sistema actual mezcla cuatro responsabilidades en las mismas tablas:

- Tablas crudas importadas desde Lotobet/Lotonet.
- Tablas usadas directamente por dashboards.
- Tablas usadas por reporteria pesada.
- Tablas maestras/catalogos parcialmente normalizadas.

La mayor carga funcional esta en ventas, productos, premios, recargas, pagos, faltantes, asistencia, agencias, empleados, incentivos y reportes comparativos. La evidencia mas importante esta en:

- `VentasController`, `VentasProductosController`, `RecargasController`, `PremioController`, `FaltantesController`, `AsistenciaController`, `Pago*Controller`, `PaqueticoController`.
- `FinanceDashboardController`, `InicioController`, `KpiLotobetController`, `ComercialController`, `MetaIncentivoController`, `GerencialController`, `VentaGerencialController`.
- `ReporteController`, que concentra SQL pesado con `UNION`, subconsultas, `GROUP BY`, joins por cedula y fechas.
- `IncentivosController`, que usa tablas temporales/resultado y calculos por periodo, agencia, sistema, coordinador y tipo de producto.

## 3. Problemas encontrados

### 3.1 Inconsistencias de nombres

Hay tablas y modelos con nombres divergentes:

- `VtUsuarioBet` y `VtUsuarioNet` apuntan a `ventas_usuarios_bet` y `ventas_usuarios_net`.
- Muchos reportes y dashboards consultan `vt_usuarios_bet` y `vt_usuarios_net`.
- `LotobetIngestionService` guarda ventas de usuario en `ventas_usuarios_bet`.
- `ReporteController`, `InicioController`, `FinanceDashboardController`, `ComercialController`, `GerencialController`, `IncentivosController` y exports leen `vt_usuarios_bet`/`vt_usuarios_net`.

Esto debe resolverse antes de crear migraciones V2. Recomendacion: usar una sola tabla canonica V2 `fact_ventas_usuarios` y, durante transicion, crear vistas de compatibilidad si hace falta.

Tambien hay inconsistencia en coordinadores:

- Migraciones antiguas crean `coordinador_operador`.
- El modelo `CoordinadorOperador` usa `coordinadores_operador`.
- Algunos controladores usan `coordinadores_operador`, otros `coordinador_operador`.

Recomendacion: en V2 separar claramente `coordinadores`, `operadores_ruta` y relaciones pivote.

### 3.2 Joins no sargables

Son frecuentes expresiones como:

- `TRIM(CAST(a.terminal AS CHAR)) = TRIM(CAST(v.agencia_id AS CHAR))`
- `REPLACE(REPLACE(v.cedula,'-',''),' ','') = REPLACE(REPLACE(e.cedula,'-',''),' ','')`
- `DATE(v.fecha)` en `GROUP BY` y filtros.

Estas expresiones impiden usar indices de forma eficiente. V2 debe guardar claves normalizadas:

- `agencia_codigo_fuente` para codigo/terminal de origen.
- `agencia_id` como FK interna.
- `cedula_normalizada` char(11).
- `fecha` como `date`, y `fecha_hora_origen` cuando aplique.

### 3.3 Tablas crudas usadas como tablas analiticas

Los dashboards leen tablas de carga directamente (`vt_usuarios_*`, `ventas_producto_*`, `premios_*`, `faltantes_*`). Esto hace que cada pantalla recalcule totales desde el detalle.

V2 debe separar:

- `stg_*`: datos crudos por corrida ETL.
- `fact_*`: datos limpios transaccionales.
- `agg_*`: resumen diario/mensual para dashboard.
- `rpt_*`: tablas de reporte listas para Excel/PDF/Power BI.

### 3.4 Ausencia o insuficiencia de llaves externas reales

Las tablas operativas importantes se relacionan por textos o codigos:

- Ventas a agencias por `agencia_id` del proveedor, no necesariamente por `agencias.id`.
- Ventas a empleados por `cedula`, no por `empleado_id`.
- Productos por `producto_id` y `descripcion`, con catalogo separado `catalogo_juegos`.
- Pagos entre empresas/agencias por codigos origen.

V2 debe conservar el valor fuente, pero resolver FK interna cuando sea posible. Si no se resuelve, guardar el conflicto ETL.

### 3.5 Duplicidad funcional por sistema

Existen pares de tablas para Lotobet y Lotonet:

- `vt_usuarios_bet` / `vt_usuarios_net`
- `ventas_usuarios_bet` / `ventas_usuarios_net`
- `ventas_producto_bet` / `ventas_producto_net`
- `recargas_bet` / `recargas_net`
- `premios_bet` / `premios_net`
- `faltantes_bet` / `faltantes_net`
- `asistencias_bet` / `asistencias_net`
- `pagos_*_bet` / `pagos_*_net`

V2 debe unificar esas tablas con una columna `sistema_id` o `sistema_codigo`, manteniendo `source_record_hash` para idempotencia.

### 3.6 Reporteria pesada en tiempo real

Hay pantallas que calculan en vivo:

- ventas por tipo/producto/agencia/dia,
- agencias en cero,
- comparativos hoy/ayer/semana/mes/anio,
- rentabilidad ventas menos premios/pagos,
- cruce empleados vs cedulas de venta/faltantes/asistencia,
- incentivos por agencia/coordinador/operador/administrativo.

V2 debe precalcular en tablas agregadas por dia, mes, agencia, producto, empleado y sistema.

## 4. Tablas actuales detectadas y proposito

### 4.1 Seguridad y plataforma

| Tabla actual | Uso detectado | Observaciones |
|---|---|---|
| `users` | autenticacion, roles, asignaciones de tareas/tickets | Compatible con Spatie Permission. |
| `roles`, `permissions`, pivotes Spatie | permisos por modulo | Mantener con migraciones oficiales o equivalentes. |
| `sessions` | sesiones y reporte superadmin | Indexar `user_id`, `last_activity`. |
| `cache`, `cache_locks`, `jobs`, `failed_jobs`, `job_batches`, `notifications` | infraestructura Laravel | Mantener estandar Laravel 12. |
| `tokens` | token de integracion Lotobet | Debe evolucionar a `api_tokens_integracion`. |
| `auto_proceso_configs` | programacion de procesos automaticos | Debe enlazarse a jobs ETL. |

### 4.2 Maestros operativos

| Tabla actual | Uso detectado | Relaciones/filtros |
|---|---|---|
| `agencias` | catalogo de agencias/terminales, estado, empresa, sistema, horarios | Joins por `terminal`; filtros por `estatus`, `empresa`, `sistema`. |
| `consorcios` | catalogo de consorcios | Referenciado por transacciones de ventas/premios/pagos/faltantes. |
| `catalogo_juegos` | producto/tipo/sistema/activo | Join por `producto_id`; filtros por `tipo`, `sistema`, `activo`. |
| `empleados` | nomina/RRHH y cruce por cedula | Join por cedula normalizada; filtros por activo/salida, depto, posicion, ciudad. |
| `coordinador_operador` / `coordinadores_operador` | coordinadores asignados a agencias | Inconsistente; requiere normalizacion. |
| `operador_ruta`, `rutas`, `ruta_agencia`, `operador_ruta_agencia`, `coordinador_operador_agencia` | rutas, operadores y asignaciones | Filtros por ruta, agencia, operador, coordinador. |
| `bancos_operaciones` | bancos usados en reporte diario de rutas | Relacionable con operaciones/contabilidad. |

### 4.3 Transaccionales de ventas y juego

| Tabla actual | Uso detectado | Columnas mas usadas |
|---|---|---|
| `vt_usuarios_bet`, `vt_usuarios_net` | base real de dashboards/reportes de ventas por usuario | `fecha`, `agencia_id`, `cedula`, `producto_id`, `tipo`, `monto`. |
| `ventas_usuarios_bet`, `ventas_usuarios_net` | destino de algunos modelos/ingestion nueva | `fecha`, `agencia_id`, `cedula`, `monto`; riesgo por divergencia. |
| `ventas_producto_bet`, `ventas_producto_net` | ventas por producto y KPI de productos | `fecha`, `agencia_id`, `producto_id`, `descripcion`, `monto`, `sorteo_id`, `source_hash`. |
| `ventas_flash_bet` | ventas flash Lotobet | `fecha`, `numero_externo`, montos de loteria/recarga/no tradicional, comisiones, premios. |
| `mar_ventas` | ventas sistema MAR por SOAP | columnas propias `VentaID`, `Dia`, `BancaID`, quinielas/pales/tripletas/comisiones. |
| `recargas_bet`, `recargas_net` | recargas por agencia/cedula/producto | `fecha`, `agencia_id`, `cedula`, `monto`, `producto_id`. |
| `premios_bet`, `premios_net` | premios pagados | `fecha`, `agencia_id`, `producto_id`, `monto`, posiblemente `cedula`. |
| `paquetico_net` | paquetico Lotonet | `fecha`, `agencia_id`, `producto_id`, `monto`. |
| `pagos_misma_empresa_*`, `pagos_aotra_empresa_*`, `pagos_porotra_empresa_*` | pagos entre agencias/empresas | `fecha`, `agencia_id`, `producto_id`, `monto`, contraparte, plataforma. |
| `faltantes_bet`, `faltantes_net` | faltantes por agencia/cedula | `fecha`, `agencia_id`, `identificacion`, `monto`, `abono`, `balance`. |
| `asistencias_bet`, `asistencias_net` | asistencia/login/salida | `fecha`, `agencia_id`, `cedula/identificacion`, `primer_login/entrada`, `ultimo_login/salida`. |

### 4.4 Incentivos

| Tabla actual | Uso detectado | Observaciones |
|---|---|---|
| `incentivo_temporal_c` | cabecera de cargas/configuracion temporal | Usada para mes/anio/sistema. |
| `incentivo_temporal` | distribucion o detalle temporal | Reemplazable por configuraciones versionadas. |
| `plan_agencias_distribucion` | planes por agencia | Debe convertirse en metas por periodo/agencia/producto. |
| `efectividad_usuarios` | efectividad por usuario | Debe enlazar empleado/agencia/periodo. |
| `pago_incentivos`, `pago_incentivos_coordinador`, `pago_incentivos_admin` | pagos calculados | Deben ser resultados versionados/reproducibles. |
| `incentivo_administrativos`, `porcentaje_incentivos` | configuracion administrativa reciente | Mantener como catalogo/configuracion. |
| `incentivo_jobs`, `incentivo_resultados` | jobs/resultados en SQL heredado | Debe integrarse a jobs Laravel/ETL. |
| Procedimiento `CalculoIncentivo` | calculo pesado por mes/anio | Conviene migrarlo a jobs idempotentes y tablas de resultados. |

### 4.5 CRM, tareas, solicitudes y RRHH

| Tabla actual | Uso detectado | Relaciones |
|---|---|---|
| `departamentos` | departamentos CRM/tareas | `tareas.departamento_id`. |
| `tareas`, `tarea_comentarios` | tareas, subtareas, adjuntos, cierre | usuarios, departamentos, tarea padre. |
| `tecnologia_solicitudes`, `tipos_solicitud_tecnologia` | tickets tecnologia | solicitante, asignado, tipo, cierre. |
| `servicios_generales_requerimientos` | tickets servicios generales | creador/asignado/cierre. |
| `procesos_departamento` | protocolos/procesos por departamento | filtros por departamento, personalizado. |
| `entrevistas_online` | entrevistas RRHH | user_id. |
| `solicitudes_empleo` y tablas hijas `solicitud_*` | postulaciones | FK por `solicitud_id` en SQL heredado. |
| `novedades_horario` | novedades de horario/terminal | cedula, terminal, fecha. |

### 4.6 Contabilidad y operaciones

| Tabla actual | Uso detectado | Relaciones/filtros |
|---|---|---|
| `cuentas_contables` | catalogo contable | join por `cuenta`. |
| `detalle_cuentas` | detalle contable importado | filtros por fecha, cuenta, centro_costo. |
| `centros_de_costo` | catalogo centro de costo | externa/contable; incluye flags de ocultar y ponderacion. |
| `contabilidad_electricidad` | facturas electricidad | filtros por fecha, empresa, sucursal, pagado. |
| `contabilidad_electricidad_seguimiento_dia` | seguimiento diario electricidad | fecha, estatus, ruta/agencia. |
| `contabilidad_electricidad_averia_dia` | averias electricidad | fecha, estatus, agencia/ruta. |
| `reporte_diario_rutas` | cuadre operativo por ruta | fecha, ruta, operador, banco, entregado/procesado/gasto/diferencia, comprobantes. |

## 5. Joins y filtros frecuentes

Joins frecuentes:

- Ventas -> agencias por `ventas.agencia_id` contra `agencias.terminal`.
- Ventas/faltantes/asistencias -> empleados por cedula.
- Ventas producto -> `catalogo_juegos.producto_id`.
- Agencias -> rutas por `ruta_agencia`.
- Agencias -> coordinadores por `coordinador_operador_agencia`.
- Rutas -> operador por `rutas.operador_ruta_id`.
- Reporte diario rutas -> rutas/operadores/bancos.
- Tecnologia/tareas/servicios -> `users`.

Filtros frecuentes:

- `fecha` o rango `fecha_inicio`/`fecha_fin`.
- `agencia_id`/terminal.
- `cedula`/`identificacion`.
- `producto_id`.
- `consorcio_id`.
- `tipo` de juego/producto.
- `sistema` (`Lotobet`, `Lotonet`, `todos`).
- `empresa` (`Negosur`, `Joselito`, todas).
- `estatus`/activo.
- `mes`, `anio`.
- `coordinador`, `ruta`, `operador`.

## 6. Arquitectura V2 propuesta

La V2 debe usar una arquitectura por capas:

1. Catalogos/maestras (`cat_*`, tablas de dimensiones).
2. Staging ETL (`stg_*`) para datos crudos.
3. Transaccional limpio (`fact_*`) para detalle normalizado.
4. Historico/auditoria (`hist_*`, `etl_*`).
5. Agregados/materializaciones (`agg_*`) para dashboards.
6. Reporteria (`rpt_*`) para Excel/PDF/Power BI.

### 6.1 Catalogos y maestras

Tablas sugeridas:

- `sistemas`
  - `id`, `codigo` (`lotobet`, `lotonet`, `mar`), `nombre`, `activo`.
- `empresas`
  - `id`, `codigo`, `nombre`, `activo`.
- `consorcios`
  - `id`, `codigo_fuente`, `nombre`, `sistema_id`, `activo`.
- `ciudades`
  - `id`, `nombre`, `provincia`, `activo`.
- `agencias`
  - `id`, `empresa_id`, `sistema_id`, `consorcio_id`, `ciudad_id`, `codigo`, `terminal`, `nombre`, `estatus`, `aplica_incentivo`, `horario_am`, `horario_pm`, timestamps, softDeletes.
  - Pendiente de validacion: si una agencia puede operar en mas de un sistema o si se duplica por sistema.
- `agencia_identificadores`
  - `id`, `agencia_id`, `sistema_id`, `tipo` (`terminal`, `codigo`, `numero_externo`, `banca_id`), `valor`, `activo`.
  - Evita joins con `TRIM(CAST(...))`.
- `productos`
  - `id`, `sistema_id`, `producto_codigo`, `descripcion`, `tipo_producto_id`, `activo`.
- `tipos_producto`
  - `id`, `codigo` (`tradicional`, `no_tradicional`, `recarga`, `paquetico`, `sin_tipo`), `nombre`.
- `empleados`
  - `id`, `company_id`, `empleado_codigo`, `cedula`, `nombres`, `apellidos`, `departamento_id`, `posicion_id`, `ciudad_id`, `fecha_ingreso`, `fecha_salida`, `activo`, campos RRHH necesarios.
- `departamentos`
  - unificar departamentos CRM/RRHH si aplica, o separar `departamentos_crm` y `departamentos_rrhh`.
- `posiciones`, `bancos`, `tipos_documento`
  - catalogos normalizados desde empleados.
- `coordinadores`
  - `id`, `empleado_id` nullable, `cedula`, `nombre`, `email`, `telefono`, `activo`.
- `operadores_ruta`
  - `id`, `empleado_id` nullable, `cedula`, `nombre`, `apellido`, `email`, `telefono`, `activo`.
- `rutas`
  - `id`, `empresa_id`, `operador_ruta_id`, `nombre`, `serial`, `activo`.
- `agencia_ruta`
  - `agencia_id`, `ruta_id`, `fecha_desde`, `fecha_hasta`, timestamps.
- `agencia_coordinador`
  - `agencia_id`, `coordinador_id`, `fecha_desde`, `fecha_hasta`, timestamps.
- `agencia_operador_ruta`
  - `agencia_id`, `operador_ruta_id`, `fecha_desde`, `fecha_hasta`, timestamps.

### 6.2 Staging ETL

Tablas sugeridas:

- `etl_runs`
  - `id`, `sistema_id`, `modulo`, `fecha_inicio`, `fecha_fin`, `status`, `dry_run`, `rows_expected`, `rows_read`, `rows_inserted`, `rows_updated`, `rows_failed`, `source`, `started_at`, `finished_at`, `error`, timestamps.
- `etl_run_items`
  - `id`, `etl_run_id`, `batch_num`, `status`, contadores, `error`.
- `etl_conflictos`
  - `id`, `etl_run_id`, `tabla_destino`, `tipo`, `clave_fuente`, `motivo`, `payload`, `resuelto`, `resuelto_por_id`, timestamps.
- `stg_lotobet_ventas_usuarios`
- `stg_lotonet_ventas_usuarios`
- `stg_lotobet_ventas_productos`
- `stg_lotonet_ventas_productos`
- `stg_lotobet_recargas`
- `stg_lotonet_recargas`
- `stg_lotobet_premios`
- `stg_lotonet_premios`
- `stg_lotobet_faltantes`
- `stg_lotonet_faltantes`
- `stg_lotobet_asistencias`
- `stg_lotonet_asistencias`
- `stg_lotobet_pagos_empresas`
- `stg_lotonet_pagos_empresas`
- `stg_mar_ventas`

Cada `stg_*` debe incluir:

- `id`, `etl_run_id`, `fecha_corte`, `payload` JSON, `source_hash`, `processed_at`, `status`, `error`, timestamps.
- Columnas extra indexables cuando se usan para deduplicar: `agencia_codigo_fuente`, `cedula`, `producto_codigo`, `fecha`.

### 6.3 Fact tables transaccionales

Tablas sugeridas:

- `fact_ventas_usuarios`
  - `id`, `sistema_id`, `agencia_id`, `empleado_id` nullable, `producto_id` nullable, `tipo_producto_id` nullable, `fecha`, `cedula`, `agencia_codigo_fuente`, `monto`, `source_hash`, `etl_run_id`, timestamps.
  - Sustituye `vt_usuarios_*` y `ventas_usuarios_*`.
- `fact_ventas_productos`
  - `id`, `sistema_id`, `consorcio_id` nullable, `agencia_id`, `producto_id`, `tipo_producto_id`, `fecha`, `fecha_sorteo`, `sorteo_id`, `agencia_codigo_fuente`, `producto_codigo_fuente`, `descripcion_fuente`, `monto`, `comision`, `comision_supervisor`, `source_hash`, `etl_run_id`, timestamps.
  - Sustituye `ventas_producto_*`.
- `fact_recargas`
  - `id`, `sistema_id`, `consorcio_id` nullable, `agencia_id`, `empleado_id` nullable, `producto_id` nullable, `fecha`, `cedula`, `monto`, `source_hash`, `etl_run_id`.
- `fact_premios`
  - `id`, `sistema_id`, `consorcio_id`, `agencia_id`, `empleado_id` nullable, `producto_id`, `fecha`, `sorteo_id`, `cedula`, `monto`, `source_hash`, `etl_run_id`.
- `fact_pagos_empresas`
  - `id`, `sistema_id`, `tipo_pago_empresa` (`misma_empresa`, `a_otra_empresa`, `por_otra_empresa`), `consorcio_id`, `agencia_id`, `producto_id`, `contraparte_consorcio_id` nullable, `contraparte_agencia_id` nullable, `fecha`, `monto`, `importe`, `plataforma`, `source_hash`, `etl_run_id`.
- `fact_faltantes`
  - `id`, `sistema_id`, `consorcio_id`, `agencia_id`, `empleado_id` nullable, `fecha`, `cedula`, `monto`, `abono`, `balance`, `motivo`, `observacion`, `source_hash`, `etl_run_id`.
- `fact_asistencias`
  - `id`, `sistema_id`, `agencia_id`, `empleado_id` nullable, `fecha`, `cedula`, `usuario`, `entrada_at`, `salida_at`, `salida_inactividad_at`, `turno`, `horas_trabajadas`, `source_hash`, `etl_run_id`.
- `fact_ventas_flash`
  - `id`, `sistema_id`, `agencia_id`, `fecha`, `venta_loteria`, `venta_recarga`, `venta_no_tradicional`, `premios_pagado`, `premios_pagados_no_tradicional`, `comision_loteria`, `comision_recarga`, `comision_gobierno`, `source_hash`.
- `fact_mar_ventas`
  - `id`, `venta_id_fuente`, `fecha`, `grupo_id`, `rifero_id`, `agencia_id` nullable, `banca_codigo_fuente`, montos por quiniela/pale/tripleta/comisiones/pagos, `source_hash`.

### 6.4 Operaciones, CRM y contabilidad

Tablas sugeridas:

- `crm_tareas`, `crm_tarea_comentarios`, `crm_departamentos`
  - O mantener `tareas`, `tarea_comentarios`, `departamentos` si se desea compatibilidad inmediata.
- `tickets_tecnologia`, `tipos_ticket_tecnologia`
- `tickets_servicios_generales`
- `procesos_departamento`
- `solicitudes_empleo`, `solicitud_educacion`, `solicitud_empleos`, `solicitud_familiares`, `solicitud_referencias_laborales`, `solicitud_referencias_personales`
- `entrevistas_online`
- `novedades_horario`
- `cuentas_contables`, `centros_de_costo`, `detalle_cuentas`
- `facturas_electricidad`, `electricidad_seguimiento_diario`, `electricidad_averias_diarias`
- `reporte_diario_rutas`

### 6.5 Incentivos V2

Tablas sugeridas:

- `incentivo_periodos`
  - `id`, `anio`, `mes`, `fecha_inicio`, `fecha_fin`, `sistema_id`, `estado`, `cerrado_at`, unique por periodo/sistema.
- `incentivo_configuraciones`
  - `id`, `periodo_id`, `tipo`, `nombre`, `version`, `payload`, `activo`.
- `incentivo_metas_agencia`
  - `id`, `periodo_id`, `agencia_id`, `tipo_producto_id`, `meta_monto`, `meta_diaria`, `origen`.
- `incentivo_efectividad_usuarios`
  - `id`, `periodo_id`, `empleado_id`, `agencia_id`, `dias_cumplidos`, `monto_venta`, `porcentaje`.
- `incentivo_resultados`
  - `id`, `periodo_id`, `tipo_beneficiario` (`agente`, `coordinador`, `administrativo`), `empleado_id` nullable, `coordinador_id` nullable, `agencia_id` nullable, `monto_base`, `porcentaje`, `monto_incentivo`, `estado`, `etl_run_id`.
- `incentivo_resultado_detalles`
  - detalle explicable de cada calculo.
- `incentivo_jobs`
  - opcional, si se quiere tracking especifico ademas de `etl_runs`.

## 7. Tablas agregadas/materializadas para dashboards

Para evitar consultas gigantes en tiempo real:

- `agg_ventas_diarias_agencia`
  - `fecha`, `sistema_id`, `empresa_id`, `agencia_id`, `total_ventas`, `total_tradicional`, `total_no_tradicional`, `total_recargas`, `transacciones`, `ticket_promedio`, `updated_at`.
- `agg_ventas_diarias_producto`
  - `fecha`, `sistema_id`, `agencia_id`, `producto_id`, `tipo_producto_id`, `total_monto`, `transacciones`.
- `agg_ventas_mensuales_agencia`
  - `anio`, `mes`, `sistema_id`, `agencia_id`, totales por tipo, dias_con_venta, promedio_diario.
- `agg_agencias_cero_diarias`
  - `fecha`, `sistema_id`, `empresa_id`, `total_agencias_catalogo`, `agencias_en_cero`.
- `agg_rentabilidad_diaria_agencia`
  - `fecha`, `sistema_id`, `agencia_id`, `ventas`, `premios`, `pagos_misma_empresa`, `pagos_a_otra_empresa`, `pagos_por_otra_empresa`, `utilidad_bruta`.
- `agg_asistencia_diaria_empleado`
  - `fecha`, `sistema_id`, `empleado_id`, `cedula`, `agencia_id`, `horas_trabajadas`, `primer_login`, `ultimo_logout`.
- `agg_faltantes_mensuales_empleado`
  - `anio`, `mes`, `sistema_id`, `empleado_id`, `cedula`, `cantidad`, `monto`, `abono`, `balance`.
- `rpt_verificador_usuarios`
  - materializacion por rango/periodo para Excel/Power BI.
- `rpt_meta_incentivo`
  - resultado ya calculado para la vista comercial.
- `rpt_venta_gerencial_diaria`
  - vista/tablas listas para gerencia.

## 8. Indices sugeridos

### 8.1 Catalogos

- `agencias`
  - unique `(sistema_id, terminal)` cuando aplique.
  - index `(empresa_id, estatus)`.
  - index `(sistema_id, estatus)`.
  - index `(consorcio_id)`, `(ciudad_id)`.
- `agencia_identificadores`
  - unique `(sistema_id, tipo, valor)`.
  - index `(agencia_id, activo)`.
- `empleados`
  - unique `(company_id, empleado_codigo)`.
  - index/unique segun negocio `(cedula)`.
  - index `(activo)`, `(departamento_id)`, `(posicion_id)`, `(fecha_salida)`.
- `productos`
  - unique `(sistema_id, producto_codigo)`.
  - index `(tipo_producto_id, activo)`.

### 8.2 Fact tables

- `fact_ventas_usuarios`
  - unique `(source_hash)`.
  - index `(fecha, sistema_id)`.
  - index `(agencia_id, fecha)`.
  - index `(cedula, fecha)`.
  - index `(empleado_id, fecha)`.
  - index `(producto_id, fecha)`.
  - index `(tipo_producto_id, fecha)`.
- `fact_ventas_productos`
  - unique `(source_hash)`.
  - index `(fecha, sistema_id)`.
  - index `(agencia_id, fecha)`.
  - index `(producto_id, fecha)`.
  - index `(consorcio_id, fecha)`.
  - index `(tipo_producto_id, fecha)`.
- `fact_recargas`, `fact_premios`, `fact_faltantes`
  - unique `(source_hash)`.
  - index `(fecha, sistema_id)`.
  - index `(agencia_id, fecha)`.
  - index `(cedula, fecha)`.
  - index `(producto_id, fecha)` donde aplique.
  - index `(consorcio_id, fecha)` donde aplique.
- `fact_asistencias`
  - unique `(source_hash)`.
  - index `(fecha, sistema_id)`.
  - index `(agencia_id, fecha)`.
  - index `(cedula, fecha)`.
  - index `(empleado_id, fecha)`.
  - index `(entrada_at)`.
- `fact_pagos_empresas`
  - index `(fecha, sistema_id, tipo_pago_empresa)`.
  - index `(agencia_id, fecha)`.
  - index `(producto_id, fecha)`.
  - index `(contraparte_agencia_id, fecha)`.
  - index `(contraparte_consorcio_id, fecha)`.

### 8.3 Agregados

- `agg_ventas_diarias_agencia`
  - unique `(fecha, sistema_id, agencia_id)`.
  - index `(fecha, empresa_id)`.
  - index `(agencia_id, fecha)`.
- `agg_ventas_diarias_producto`
  - unique `(fecha, sistema_id, agencia_id, producto_id)`.
  - index `(producto_id, fecha)`.
  - index `(tipo_producto_id, fecha)`.
- `agg_ventas_mensuales_agencia`
  - unique `(anio, mes, sistema_id, agencia_id)`.
- `agg_rentabilidad_diaria_agencia`
  - unique `(fecha, sistema_id, agencia_id)`.

## 9. Flujo ETL profesional propuesto

### 9.1 Extraccion

- Cada integracion dispara un `etl_run`.
- Se consulta API/SOAP/archivo por sistema, modulo y fecha/rango.
- El payload se guarda primero en `stg_*` con `source_hash`.
- No se escribe directamente en `fact_*`.

### 9.2 Limpieza

- Normalizar cedulas a `char(11)`.
- Normalizar agencia/terminal con tabla `agencia_identificadores`.
- Normalizar producto por `(sistema_id, producto_codigo)`.
- Normalizar tipo de producto usando `productos.tipo_producto_id`.
- Validar fechas y montos.
- Guardar payload invalido en `etl_conflictos`.

### 9.3 Transformacion

- Resolver `agencia_id`, `empleado_id`, `producto_id`, `consorcio_id`.
- Calcular `horas_trabajadas` en asistencias.
- Clasificar pagos empresa.
- Calcular `source_hash` estable por sistema/modulo/fecha/clave/monto.
- Marcar registros sin agencia/empleado/producto como pendientes, no descartarlos.

### 9.4 Carga

- Carga idempotente por `source_hash`.
- Usar `upsert` por lotes.
- No hacer `delete where fecha` salvo reproceso controlado con `etl_run`.
- Registrar conteos leidos/insertados/actualizados/fallidos.

### 9.5 Consolidacion

- Actualizar fact tables.
- Refrescar agregados impactados por fecha/sistema.
- Recalcular reportes materializados de la ventana afectada.
- Invalidar caches de dashboards (`InicioVentasCache` o reemplazo versionado).

### 9.6 Metricas historicas

- Jobs programados nocturnos:
  - `RefreshAggVentasDiarias`
  - `RefreshAggRentabilidad`
  - `RefreshAggAsistencia`
  - `RefreshIncentivosPeriodo`
  - `RefreshReportesPowerBi`
- Permitir reproceso por fecha/rango sin duplicados.

## 10. Flujo de reporteria V2

### Dashboards administrativos

- Deben leer `agg_*`, no `fact_*`, salvo drill-down.
- Filtros: fecha, sistema, empresa, agencia, tipo_producto.
- Comparativos historicos deben usar `agg_ventas_diarias_agencia` y `agg_ventas_mensuales_agencia`.

### Excel/PDF

- Exportaciones pequenas pueden leer agregados.
- Exportaciones pesadas deben leer `rpt_*` ya materializadas.
- Para reportes por rango grande, crear job y descargar archivo cuando termine.

### Power BI

- Exponer vistas estables:
  - `bi_dim_agencias`
  - `bi_dim_empleados`
  - `bi_dim_productos`
  - `bi_fact_ventas_diarias`
  - `bi_fact_rentabilidad_diaria`
  - `bi_fact_faltantes`
  - `bi_fact_asistencia`
  - `bi_incentivos_resultados`
- Evitar campos JSON en vistas BI.
- Usar claves enteras internas y columnas descriptivas limpias.

## 11. Propuesta de migraciones Laravel 12

No se deben reutilizar las migraciones antiguas danadas. Crear una nueva carpeta/serie V2 organizada por modulo. Ejemplo:

### 11.1 Core

- `2026_05_14_000001_create_core_catalog_tables.php`
  - `empresas`, `sistemas`, `ciudades`, `tipos_documento`, `bancos`.
- `2026_05_14_000002_create_security_tables.php`
  - usar migraciones Laravel/Spatie, o mantener oficiales separadas.

### 11.2 Maestros

- `2026_05_14_010001_create_agencias_tables.php`
  - `consorcios`, `agencias`, `agencia_identificadores`.
- `2026_05_14_010002_create_empleados_tables.php`
  - `departamentos`, `posiciones`, `empleados`.
- `2026_05_14_010003_create_rutas_tables.php`
  - `coordinadores`, `operadores_ruta`, `rutas`, `agencia_ruta`, `agencia_coordinador`, `agencia_operador_ruta`.
- `2026_05_14_010004_create_productos_tables.php`
  - `tipos_producto`, `productos`.

### 11.3 ETL

- `2026_05_14_020001_create_etl_control_tables.php`
  - `etl_runs`, `etl_run_items`, `etl_conflictos`.
- `2026_05_14_020002_create_staging_lotobet_tables.php`
- `2026_05_14_020003_create_staging_lotonet_tables.php`
- `2026_05_14_020004_create_staging_mar_tables.php`

### 11.4 Facts

- `2026_05_14_030001_create_fact_ventas_tables.php`
  - `fact_ventas_usuarios`, `fact_ventas_productos`, `fact_ventas_flash`, `fact_mar_ventas`.
- `2026_05_14_030002_create_fact_movimientos_tables.php`
  - `fact_recargas`, `fact_premios`, `fact_pagos_empresas`, `fact_faltantes`, `fact_asistencias`.

### 11.5 Agregados y reportes

- `2026_05_14_040001_create_agg_ventas_tables.php`
- `2026_05_14_040002_create_agg_operativos_tables.php`
- `2026_05_14_040003_create_report_materialized_tables.php`
- `2026_05_14_040004_create_power_bi_views.php`

### 11.6 CRM, operaciones, contabilidad e incentivos

- `2026_05_14_050001_create_crm_tables.php`
- `2026_05_14_050002_create_tickets_tables.php`
- `2026_05_14_050003_create_operaciones_tables.php`
- `2026_05_14_050004_create_contabilidad_tables.php`
- `2026_05_14_050005_create_incentivos_tables.php`

## 12. Reglas de diseno para migraciones

- Nombres en `snake_case`.
- Usar `foreignId()->constrained()` cuando la relacion sea firme.
- Usar `nullable()->constrained()->nullOnDelete()` cuando el origen pueda no resolverse.
- Guardar claves fuente (`*_codigo_fuente`) aunque exista FK interna.
- Usar `timestamps()` en tablas operativas, ETL, facts y reportes.
- Usar `softDeletes()` solo en maestras administrables: `agencias`, `empleados`, `productos`, `rutas`, `coordinadores`, `operadores_ruta`, tickets si el negocio lo pide.
- No usar softDeletes en facts masivos; usar `etl_run_id`, `source_hash`, estado y auditoria.
- Montos con `decimal(18,2)`; porcentajes con `decimal(8,4)` o `decimal(10,6)`.
- Fechas analiticas como `date`; fecha/hora original como `dateTime`.
- JSON solo en staging/auditoria, no en facts principales ni vistas BI.

## 13. Relaciones pendientes de validacion

Estas relaciones aparecen en codigo, pero necesitan confirmacion de negocio o limpieza previa:

- Si `agencias.terminal` es unico global o solo por sistema/empresa.
- Si `agencia_id` en ventas representa siempre terminal, codigo de agencia o numero externo segun sistema.
- Si `consorcio_id` puede mapearse a una sola tabla para Lotobet y Lotonet.
- Si `catalogo_juegos.producto_id` es unico global o por sistema.
- Si las cedulas de ventas siempre identifican empleados o tambien clientes/usuarios externos.
- Si `coordinador_operador` y `coordinadores_operador` son la misma entidad o tablas distintas en alguna version.
- Si `ciudades` debe existir como catalogo propio; el codigo la referencia, pero no se vio una migracion actual clara en la base vacia.
- Si `ventas_usuarios_bet/net` deben tener columna `producto_id`/`tipo`; muchos reportes la esperan en `vt_usuarios_*`.
- Si `faltantes` deben enlazar por `cedula` a empleado siempre o aceptar responsables externos.
- Si los pagos entre empresas deben considerarse premios, egresos, transferencias o todos segun contexto de reporte.

## 14. Estrategia de migracion futura

1. Congelar nombres canonicos V2.
2. Crear migraciones V2 en base vacia `bsh`.
3. Crear seeders de catalogos base: sistemas, empresas, tipos producto, roles/permisos.
4. Crear jobs ETL por modulo y sistema.
5. Cargar maestros primero: agencias, empleados, productos, consorcios, rutas/coordinadores.
6. Ejecutar ETL historico hacia `stg_*`.
7. Transformar a `fact_*` con conflictos auditados.
8. Refrescar `agg_*` y `rpt_*`.
9. Adaptar controladores gradualmente:
   - primero dashboards a agregados,
   - luego reportes,
   - luego pantallas de carga,
   - finalmente incentivos.
10. Crear vistas de compatibilidad temporal si el codigo actual debe seguir funcionando:
   - `vt_usuarios_bet` como vista filtrada de `fact_ventas_usuarios` para Lotobet.
   - `vt_usuarios_net` como vista filtrada para Lotonet.
   - `ventas_producto_bet/net` como vistas o aliases controlados.
11. Retirar compatibilidad cuando todos los controladores apunten a V2.

## 15. Recomendaciones de rendimiento

- Evitar `whereDate(fecha)` en tablas grandes; usar rangos `fecha >= inicio` y `fecha < fin`.
- Evitar `DATE(fecha)` en agrupaciones de facts; tener `fecha` tipo `date`.
- Evitar `TRIM`, `CAST`, `REPLACE` en joins; almacenar columnas normalizadas.
- Usar `source_hash` unico para idempotencia.
- Usar `upsert` por lotes, no inserts fila a fila.
- Materializar KPIs diarios/mensuales.
- Separar reportes de larga duracion en jobs con descarga posterior.
- Mantener agregados por dia y mes; no recalcular historicos completos si solo cambio una fecha.
- Para Power BI, preferir vistas planas sobre facts/agregados con indices.
- Particionar logicamente por fecha mediante indices compuestos; si el volumen crece mucho, evaluar particiones MySQL/MariaDB por rango de fecha en facts masivos.
- Registrar conflictos ETL en vez de descartar datos.

## 16. Prioridad recomendada

1. Resolver nombres canonicos: `vt_usuarios_*` vs `ventas_usuarios_*`, `coordinador_operador` vs `coordinadores_operador`.
2. Crear catalogos V2: sistemas, empresas, agencias, identificadores, productos, empleados.
3. Crear control ETL y staging.
4. Crear facts unificadas.
5. Crear agregados principales de ventas/rentabilidad/agencias cero.
6. Adaptar dashboards mas usados.
7. Adaptar reporteria pesada y exportaciones.
8. Migrar incentivos a resultados versionados.

## 17. Conclusion

La V2 debe abandonar la estructura duplicada por sistema y pasar a un modelo analitico-relacional con facts unificadas, claves internas, claves fuente conservadas, ETL auditable y agregados de dashboard. El codigo actual ofrece evidencia suficiente para disenar las entidades principales, pero varias relaciones deben validarse antes de escribir migraciones definitivas, especialmente agencias/terminales, productos por sistema, coordinadores y nombres de tablas de ventas de usuario.


