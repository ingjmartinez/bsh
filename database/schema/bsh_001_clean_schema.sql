SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE bsh;

CREATE TABLE paises (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_iso2 CHAR(2) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provincias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pais_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY provincias_pais_nombre_unique (pais_id, nombre),
  CONSTRAINT provincias_pais_id_fk FOREIGN KEY (pais_id) REFERENCES paises(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ciudades (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provincia_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(100) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY ciudades_provincia_id_index (provincia_id),
  CONSTRAINT ciudades_provincia_id_fk FOREIGN KEY (provincia_id) REFERENCES provincias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departamentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE posiciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  departamento_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(120) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY posiciones_departamento_nombre_unique (departamento_id, nombre),
  CONSTRAINT posiciones_departamento_id_fk FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipos_documento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(80) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bancos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(100) NULL,
  last_login_at TIMESTAMP NULL,
  last_activity_at TIMESTAMP NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(125) NOT NULL,
  guard_name VARCHAR(125) NOT NULL DEFAULT 'web',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY roles_name_guard_unique (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(125) NOT NULL,
  guard_name VARCHAR(125) NOT NULL DEFAULT 'web',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY permissions_name_guard_unique (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE model_has_roles (
  role_id BIGINT UNSIGNED NOT NULL,
  model_type VARCHAR(255) NOT NULL,
  model_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, model_id, model_type),
  KEY model_has_roles_model_index (model_id, model_type),
  CONSTRAINT model_has_roles_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE model_has_permissions (
  permission_id BIGINT UNSIGNED NOT NULL,
  model_type VARCHAR(255) NOT NULL,
  model_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (permission_id, model_id, model_type),
  KEY model_has_permissions_model_index (model_id, model_type),
  CONSTRAINT model_has_permissions_permission_id_fk FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_has_permissions (
  permission_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (permission_id, role_id),
  CONSTRAINT role_has_permissions_permission_id_fk FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
  CONSTRAINT role_has_permissions_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE consorcios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agencias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  consorcio_id BIGINT UNSIGNED NULL,
  ciudad_id BIGINT UNSIGNED NULL,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  terminal VARCHAR(50) NULL UNIQUE,
  nombre VARCHAR(150) NOT NULL,
  sistema ENUM('lotobet','lotonet','ambos') NOT NULL DEFAULT 'ambos',
  empresa VARCHAR(120) NULL,
  horario_am VARCHAR(40) NULL,
  horario_pm VARCHAR(40) NULL,
  activa TINYINT(1) NOT NULL DEFAULT 1,
  aplica_incentivo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  KEY agencias_sistema_activa_index (sistema, activa),
  KEY agencias_consorcio_id_index (consorcio_id),
  KEY agencias_ciudad_id_index (ciudad_id),
  CONSTRAINT agencias_consorcio_id_fk FOREIGN KEY (consorcio_id) REFERENCES consorcios(id),
  CONSTRAINT agencias_ciudad_id_fk FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE empleados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_externo VARCHAR(50) NULL,
  company_id INT NULL,
  empleado_id INT NULL,
  tipo_documento_id BIGINT UNSIGNED NULL,
  cedula VARCHAR(30) NULL UNIQUE,
  nombres VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL,
  departamento_id BIGINT UNSIGNED NULL,
  posicion_id BIGINT UNSIGNED NULL,
  ciudad_id BIGINT UNSIGNED NULL,
  banco_id BIGINT UNSIGNED NULL,
  fecha_nacimiento DATE NULL,
  fecha_ingreso DATE NULL,
  fecha_salida DATE NULL,
  salario_mensual DECIMAL(14,2) NULL,
  telefono VARCHAR(40) NULL,
  email VARCHAR(150) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  aplica_incentivo TINYINT(1) NOT NULL DEFAULT 0,
  fuente_sync VARCHAR(50) NULL,
  ultima_sync_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY empleados_company_empleado_unique (company_id, empleado_id),
  KEY empleados_departamento_id_index (departamento_id),
  KEY empleados_posicion_id_index (posicion_id),
  KEY empleados_ciudad_id_index (ciudad_id),
  KEY empleados_activo_index (activo),
  CONSTRAINT empleados_tipo_documento_id_fk FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento(id),
  CONSTRAINT empleados_departamento_id_fk FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
  CONSTRAINT empleados_posicion_id_fk FOREIGN KEY (posicion_id) REFERENCES posiciones(id),
  CONSTRAINT empleados_ciudad_id_fk FOREIGN KEY (ciudad_id) REFERENCES ciudades(id),
  CONSTRAINT empleados_banco_id_fk FOREIGN KEY (banco_id) REFERENCES bancos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coordinadores (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empleado_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(150) NOT NULL,
  cedula VARCHAR(30) NULL UNIQUE,
  telefono VARCHAR(40) NULL,
  email VARCHAR(150) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  CONSTRAINT coordinadores_empleado_id_fk FOREIGN KEY (empleado_id) REFERENCES empleados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operadores_ruta (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empleado_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(150) NOT NULL,
  cedula VARCHAR(30) NULL UNIQUE,
  telefono VARCHAR(40) NULL,
  email VARCHAR(150) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  CONSTRAINT operadores_ruta_empleado_id_fk FOREIGN KEY (empleado_id) REFERENCES empleados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rutas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  operador_ruta_id BIGINT UNSIGNED NULL,
  serial VARCHAR(60) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  empresa VARCHAR(120) NULL,
  activa TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  KEY rutas_operador_ruta_id_index (operador_ruta_id),
  CONSTRAINT rutas_operador_ruta_id_fk FOREIGN KEY (operador_ruta_id) REFERENCES operadores_ruta(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agencia_coordinador (
  agencia_id BIGINT UNSIGNED NOT NULL,
  coordinador_id BIGINT UNSIGNED NOT NULL,
  desde DATE NULL,
  hasta DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (agencia_id, coordinador_id),
  KEY agencia_coordinador_coordinador_id_fk (coordinador_id),
  CONSTRAINT agencia_coordinador_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id) ON DELETE CASCADE,
  CONSTRAINT agencia_coordinador_coordinador_id_fk FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agencia_operador_ruta (
  agencia_id BIGINT UNSIGNED NOT NULL,
  operador_ruta_id BIGINT UNSIGNED NOT NULL,
  desde DATE NULL,
  hasta DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (agencia_id, operador_ruta_id),
  KEY agencia_operador_ruta_operador_id_fk (operador_ruta_id),
  CONSTRAINT agencia_operador_ruta_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id) ON DELETE CASCADE,
  CONSTRAINT agencia_operador_ruta_operador_id_fk FOREIGN KEY (operador_ruta_id) REFERENCES operadores_ruta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agencia_ruta (
  agencia_id BIGINT UNSIGNED NOT NULL,
  ruta_id BIGINT UNSIGNED NOT NULL,
  desde DATE NULL,
  hasta DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (agencia_id, ruta_id),
  KEY agencia_ruta_ruta_id_fk (ruta_id),
  CONSTRAINT agencia_ruta_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id) ON DELETE CASCADE,
  CONSTRAINT agencia_ruta_ruta_id_fk FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_externo VARCHAR(60) NOT NULL UNIQUE,
  tipo VARCHAR(80) NULL,
  descripcion VARCHAR(180) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  aplica_incentivo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY productos_tipo_index (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ventas_usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  fecha DATE NOT NULL,
  agencia_id BIGINT UNSIGNED NULL,
  empleado_id BIGINT UNSIGNED NULL,
  producto_id BIGINT UNSIGNED NULL,
  agencia_codigo VARCHAR(50) NULL,
  cedula VARCHAR(30) NULL,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  fuente_archivo VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY ventas_usuarios_fecha_sistema_index (fecha, sistema),
  KEY ventas_usuarios_agencia_fecha_index (agencia_id, fecha),
  KEY ventas_usuarios_empleado_fecha_index (empleado_id, fecha),
  KEY ventas_usuarios_producto_fecha_index (producto_id, fecha),
  KEY ventas_usuarios_cedula_fecha_index (cedula, fecha),
  CONSTRAINT ventas_usuarios_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT ventas_usuarios_empleado_id_fk FOREIGN KEY (empleado_id) REFERENCES empleados(id),
  CONSTRAINT ventas_usuarios_producto_id_fk FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ventas_productos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  fecha DATE NOT NULL,
  agencia_id BIGINT UNSIGNED NULL,
  producto_id BIGINT UNSIGNED NULL,
  agencia_codigo VARCHAR(50) NULL,
  producto_codigo VARCHAR(60) NULL,
  descripcion VARCHAR(180) NULL,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  comision DECIMAL(18,2) NULL,
  comision_supervisor DECIMAL(18,2) NULL,
  sorteo_numero VARCHAR(60) NULL,
  sorteo_fecha DATE NULL,
  fuente_archivo VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY ventas_productos_fecha_sistema_index (fecha, sistema),
  KEY ventas_productos_agencia_fecha_index (agencia_id, fecha),
  KEY ventas_productos_producto_fecha_index (producto_id, fecha),
  CONSTRAINT ventas_productos_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT ventas_productos_producto_id_fk FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE premios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  fecha DATE NOT NULL,
  consorcio_id BIGINT UNSIGNED NULL,
  agencia_id BIGINT UNSIGNED NULL,
  producto_id BIGINT UNSIGNED NULL,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  descripcion VARCHAR(180) NULL,
  fuente_archivo VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY premios_fecha_sistema_index (fecha, sistema),
  KEY premios_agencia_fecha_index (agencia_id, fecha),
  KEY premios_producto_fecha_index (producto_id, fecha),
  CONSTRAINT premios_consorcio_id_fk FOREIGN KEY (consorcio_id) REFERENCES consorcios(id),
  CONSTRAINT premios_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT premios_producto_id_fk FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pagos_empresas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  tipo ENUM('a_otra_empresa','misma_empresa','por_otra_empresa') NOT NULL,
  fecha DATE NOT NULL,
  consorcio_id BIGINT UNSIGNED NULL,
  agencia_id BIGINT UNSIGNED NULL,
  producto_id BIGINT UNSIGNED NULL,
  contraparte_consorcio_id BIGINT UNSIGNED NULL,
  contraparte_agencia_id BIGINT UNSIGNED NULL,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  descripcion VARCHAR(180) NULL,
  plataforma VARCHAR(80) NULL,
  fuente_archivo VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY pagos_empresas_fecha_tipo_index (fecha, tipo, sistema),
  KEY pagos_empresas_agencia_fecha_index (agencia_id, fecha),
  KEY pagos_empresas_producto_fecha_index (producto_id, fecha),
  CONSTRAINT pagos_empresas_consorcio_id_fk FOREIGN KEY (consorcio_id) REFERENCES consorcios(id),
  CONSTRAINT pagos_empresas_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT pagos_empresas_producto_id_fk FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT pagos_empresas_contraparte_consorcio_fk FOREIGN KEY (contraparte_consorcio_id) REFERENCES consorcios(id),
  CONSTRAINT pagos_empresas_contraparte_agencia_fk FOREIGN KEY (contraparte_agencia_id) REFERENCES agencias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recargas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  fecha DATE NOT NULL,
  consorcio_id BIGINT UNSIGNED NULL,
  agencia_id BIGINT UNSIGNED NULL,
  producto_id BIGINT UNSIGNED NULL,
  proveedor_id VARCHAR(60) NULL,
  proveedor_nombre VARCHAR(150) NULL,
  distribuidora_id VARCHAR(60) NULL,
  distribuidora_nombre VARCHAR(150) NULL,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  comision DECIMAL(18,2) NULL,
  fuente_archivo VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY recargas_fecha_sistema_index (fecha, sistema),
  KEY recargas_agencia_fecha_index (agencia_id, fecha),
  KEY recargas_producto_fecha_index (producto_id, fecha),
  CONSTRAINT recargas_consorcio_id_fk FOREIGN KEY (consorcio_id) REFERENCES consorcios(id),
  CONSTRAINT recargas_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT recargas_producto_id_fk FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE asistencias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  fecha DATE NOT NULL,
  agencia_id BIGINT UNSIGNED NULL,
  empleado_id BIGINT UNSIGNED NULL,
  cedula VARCHAR(30) NULL,
  usuario VARCHAR(120) NULL,
  primer_login DATETIME NULL,
  ultimo_logout DATETIME NULL,
  turno VARCHAR(80) NULL,
  fuente_archivo VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY asistencias_fecha_sistema_index (fecha, sistema),
  KEY asistencias_agencia_fecha_index (agencia_id, fecha),
  KEY asistencias_empleado_fecha_index (empleado_id, fecha),
  KEY asistencias_cedula_fecha_index (cedula, fecha),
  CONSTRAINT asistencias_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT asistencias_empleado_id_fk FOREIGN KEY (empleado_id) REFERENCES empleados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faltantes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sistema ENUM('lotobet','lotonet') NOT NULL,
  fecha DATE NOT NULL,
  consorcio_id BIGINT UNSIGNED NULL,
  agencia_id BIGINT UNSIGNED NULL,
  empleado_id BIGINT UNSIGNED NULL,
  identificacion VARCHAR(50) NULL,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  abono DECIMAL(18,2) NOT NULL DEFAULT 0,
  balance DECIMAL(18,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY faltantes_fecha_sistema_index (fecha, sistema),
  KEY faltantes_agencia_fecha_index (agencia_id, fecha),
  KEY faltantes_empleado_fecha_index (empleado_id, fecha),
  CONSTRAINT faltantes_consorcio_id_fk FOREIGN KEY (consorcio_id) REFERENCES consorcios(id),
  CONSTRAINT faltantes_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT faltantes_empleado_id_fk FOREIGN KEY (empleado_id) REFERENCES empleados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tareas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT NULL,
  departamento_id BIGINT UNSIGNED NULL,
  creado_por_id BIGINT UNSIGNED NOT NULL,
  asignado_a_id BIGINT UNSIGNED NULL,
  tarea_padre_id BIGINT UNSIGNED NULL,
  estado ENUM('pendiente','en_progreso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  prioridad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
  progreso TINYINT UNSIGNED NOT NULL DEFAULT 0,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  fecha_completada DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  KEY tareas_estado_prioridad_index (estado, prioridad),
  KEY tareas_departamento_id_index (departamento_id),
  KEY tareas_asignado_a_id_index (asignado_a_id),
  CONSTRAINT tareas_departamento_id_fk FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
  CONSTRAINT tareas_creado_por_id_fk FOREIGN KEY (creado_por_id) REFERENCES users(id),
  CONSTRAINT tareas_asignado_a_id_fk FOREIGN KEY (asignado_a_id) REFERENCES users(id),
  CONSTRAINT tareas_tarea_padre_id_fk FOREIGN KEY (tarea_padre_id) REFERENCES tareas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tarea_comentarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tarea_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  comentario TEXT NOT NULL,
  tipo VARCHAR(40) NOT NULL DEFAULT 'comentario',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY tarea_comentarios_tarea_id_index (tarea_id),
  CONSTRAINT tarea_comentarios_tarea_id_fk FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
  CONSTRAINT tarea_comentarios_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE solicitudes_empleo (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registrado_por_id BIGINT UNSIGNED NULL,
  estado ENUM('nueva','en_revision','aprobada','rechazada','contratada') NOT NULL DEFAULT 'nueva',
  nombres VARCHAR(150) NOT NULL,
  apellidos VARCHAR(150) NOT NULL,
  cedula_pasaporte VARCHAR(40) NOT NULL UNIQUE,
  fecha_nacimiento DATE NULL,
  telefono VARCHAR(60) NULL,
  celular VARCHAR(60) NULL,
  email VARCHAR(150) NULL,
  direccion VARCHAR(500) NULL,
  puesto_aplicado VARCHAR(150) NULL,
  salario_aspirado DECIMAL(14,2) NULL,
  fecha_disponible DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  KEY solicitudes_empleo_estado_index (estado),
  CONSTRAINT solicitudes_empleo_registrado_por_id_fk FOREIGN KEY (registrado_por_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cuentas_contables (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cuenta VARCHAR(60) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NOT NULL,
  cuenta_control VARCHAR(60) NULL,
  tipo VARCHAR(80) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE centros_costo (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_externo VARCHAR(60) NULL UNIQUE,
  descripcion VARCHAR(255) NOT NULL,
  cuenta_id BIGINT UNSIGNED NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT centros_costo_cuenta_id_fk FOREIGN KEY (cuenta_id) REFERENCES cuentas_contables(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detalle_cuentas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cuenta_id BIGINT UNSIGNED NULL,
  centro_costo_id BIGINT UNSIGNED NULL,
  fecha DATE NOT NULL,
  no_asiento VARCHAR(80) NULL,
  referencia VARCHAR(120) NULL,
  descripcion VARCHAR(500) NULL,
  debito DECIMAL(18,2) NOT NULL DEFAULT 0,
  credito DECIMAL(18,2) NOT NULL DEFAULT 0,
  conciliado TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY detalle_cuentas_fecha_index (fecha),
  KEY detalle_cuentas_cuenta_fecha_index (cuenta_id, fecha),
  KEY detalle_cuentas_centro_fecha_index (centro_costo_id, fecha),
  CONSTRAINT detalle_cuentas_cuenta_id_fk FOREIGN KEY (cuenta_id) REFERENCES cuentas_contables(id),
  CONSTRAINT detalle_cuentas_centro_costo_id_fk FOREIGN KEY (centro_costo_id) REFERENCES centros_costo(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bancos_operaciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  banco_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(120) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT bancos_operaciones_banco_id_fk FOREIGN KEY (banco_id) REFERENCES bancos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reporte_diario_rutas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fecha DATE NOT NULL,
  ruta_id BIGINT UNSIGNED NULL,
  operador_ruta_id BIGINT UNSIGNED NULL,
  banco_operacion_id BIGINT UNSIGNED NULL,
  entregado DECIMAL(18,2) NOT NULL DEFAULT 0,
  procesado DECIMAL(18,2) NOT NULL DEFAULT 0,
  gasto DECIMAL(18,2) NOT NULL DEFAULT 0,
  diferencia DECIMAL(18,2) NOT NULL DEFAULT 0,
  observacion TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY reporte_diario_rutas_fecha_ruta_unique (fecha, ruta_id),
  KEY reporte_diario_rutas_operador_fecha_index (operador_ruta_id, fecha),
  CONSTRAINT reporte_diario_rutas_ruta_id_fk FOREIGN KEY (ruta_id) REFERENCES rutas(id),
  CONSTRAINT reporte_diario_rutas_operador_id_fk FOREIGN KEY (operador_ruta_id) REFERENCES operadores_ruta(id),
  CONSTRAINT reporte_diario_rutas_banco_id_fk FOREIGN KEY (banco_operacion_id) REFERENCES bancos_operaciones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incentivo_periodos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  periodo CHAR(7) NOT NULL UNIQUE,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado ENUM('borrador','calculado','aprobado','pagado','cerrado') NOT NULL DEFAULT 'borrador',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incentivo_resultados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incentivo_periodo_id BIGINT UNSIGNED NOT NULL,
  empleado_id BIGINT UNSIGNED NULL,
  agencia_id BIGINT UNSIGNED NULL,
  coordinador_id BIGINT UNSIGNED NULL,
  tipo VARCHAR(60) NOT NULL,
  base_calculo DECIMAL(18,2) NOT NULL DEFAULT 0,
  porcentaje DECIMAL(8,4) NOT NULL DEFAULT 0,
  monto DECIMAL(18,2) NOT NULL DEFAULT 0,
  detalle JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY incentivo_resultados_periodo_tipo_index (incentivo_periodo_id, tipo),
  KEY incentivo_resultados_empleado_index (empleado_id),
  KEY incentivo_resultados_agencia_index (agencia_id),
  CONSTRAINT incentivo_resultados_periodo_id_fk FOREIGN KEY (incentivo_periodo_id) REFERENCES incentivo_periodos(id) ON DELETE CASCADE,
  CONSTRAINT incentivo_resultados_empleado_id_fk FOREIGN KEY (empleado_id) REFERENCES empleados(id),
  CONSTRAINT incentivo_resultados_agencia_id_fk FOREIGN KEY (agencia_id) REFERENCES agencias(id),
  CONSTRAINT incentivo_resultados_coordinador_id_fk FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE etl_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  origen_schema VARCHAR(80) NOT NULL DEFAULT 'crm',
  destino_schema VARCHAR(80) NOT NULL DEFAULT 'bsh',
  estado ENUM('pendiente','ejecutando','completado','fallido','cancelado') NOT NULL DEFAULT 'pendiente',
  iniciado_at TIMESTAMP NULL,
  finalizado_at TIMESTAMP NULL,
  total_origen BIGINT UNSIGNED NOT NULL DEFAULT 0,
  total_destino BIGINT UNSIGNED NOT NULL DEFAULT 0,
  total_conflictos BIGINT UNSIGNED NOT NULL DEFAULT 0,
  resumen JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY etl_runs_estado_index (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE etl_run_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etl_run_id BIGINT UNSIGNED NOT NULL,
  tabla_origen VARCHAR(120) NOT NULL,
  tabla_destino VARCHAR(120) NOT NULL,
  estado ENUM('pendiente','ejecutando','completado','fallido','omitido') NOT NULL DEFAULT 'pendiente',
  filas_origen BIGINT UNSIGNED NOT NULL DEFAULT 0,
  filas_insertadas BIGINT UNSIGNED NOT NULL DEFAULT 0,
  filas_actualizadas BIGINT UNSIGNED NOT NULL DEFAULT 0,
  filas_conflicto BIGINT UNSIGNED NOT NULL DEFAULT 0,
  checksum_origen VARCHAR(128) NULL,
  checksum_destino VARCHAR(128) NULL,
  mensaje TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY etl_run_items_run_estado_index (etl_run_id, estado),
  CONSTRAINT etl_run_items_run_id_fk FOREIGN KEY (etl_run_id) REFERENCES etl_runs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE etl_conflictos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etl_run_item_id BIGINT UNSIGNED NULL,
  tabla_origen VARCHAR(120) NOT NULL,
  llave_origen VARCHAR(180) NULL,
  tipo VARCHAR(80) NOT NULL,
  severidad ENUM('info','warning','error','critical') NOT NULL DEFAULT 'warning',
  payload JSON NULL,
  resuelto TINYINT(1) NOT NULL DEFAULT 0,
  resuelto_por_id BIGINT UNSIGNED NULL,
  resuelto_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY etl_conflictos_tabla_tipo_index (tabla_origen, tipo),
  KEY etl_conflictos_resuelto_index (resuelto),
  CONSTRAINT etl_conflictos_run_item_id_fk FOREIGN KEY (etl_run_item_id) REFERENCES etl_run_items(id) ON DELETE SET NULL,
  CONSTRAINT etl_conflictos_resuelto_por_id_fk FOREIGN KEY (resuelto_por_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE VIEW vw_ventas_por_producto AS
SELECT
  p.id AS producto_id,
  p.codigo_externo,
  p.descripcion,
  p.tipo,
  v.sistema,
  v.fecha,
  SUM(v.monto) AS total_ventas
FROM ventas_productos v
LEFT JOIN productos p ON p.id = v.producto_id
GROUP BY p.id, p.codigo_externo, p.descripcion, p.tipo, v.sistema, v.fecha;

SET FOREIGN_KEY_CHECKS = 1;
