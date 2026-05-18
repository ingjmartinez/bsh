<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::unprepared(<<<'SQL'
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `paises` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paises_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `provincias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pais_id` bigint unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provincias_pais_id_index` (`pais_id`),
  KEY `provincias_pais_id_idx` (`pais_id`),
  CONSTRAINT `provincias_pais_id_fk` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ciudades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia_id` bigint unsigned DEFAULT NULL,
  `pais_id` bigint unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ciudades_nombre_pais_id_unique` (`nombre`,`pais_id`),
  KEY `ciudades_pais_id_index` (`pais_id`),
  KEY `ciudades_provincia_id_index` (`provincia_id`),
  KEY `ciudades_provincia_id_idx` (`provincia_id`),
  CONSTRAINT `ciudades_provincia_id_fk` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agencias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `terminal` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sistema` enum('lotobet','lotonet','ambos') COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad_id` bigint unsigned DEFAULT NULL,
  `horario_am` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_pm` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT '1',
  `aplica_incentivo` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agencias_codigo_sistema_unique` (`codigo`,`sistema`),
  KEY `agencias_estatus_index` (`estatus`),
  KEY `agencias_sistema_estatus_index` (`sistema`,`estatus`),
  KEY `agencias_ciudad_id_index` (`ciudad_id`),
  KEY `agencias_deleted_at_index` (`deleted_at`),
  KEY `agencias_ciudad_id_idx` (`ciudad_id`),
  CONSTRAINT `agencias_ciudad_id_fk` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asistencias_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `consorcio_id` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primer_login` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asistencias_bet_fecha_index` (`fecha`),
  KEY `asistencias_bet_cedula_index` (`cedula`),
  KEY `asistencias_bet_fecha_agencia_id_index` (`fecha`,`agencia_id`),
  KEY `asistencias_bet_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asistencias_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `consorcio` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrada` datetime DEFAULT NULL,
  `salida` datetime DEFAULT NULL,
  `identificacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terminal` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salida_inactividad` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turno` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asistencias_net_fecha_index` (`fecha`),
  KEY `asistencias_net_identificacion_index` (`identificacion`),
  KEY `asistencias_net_terminal_index` (`terminal`)
) ENGINE=InnoDB AUTO_INCREMENT=512 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auto_proceso_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sistema` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `hora` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_seconds` smallint unsigned NOT NULL DEFAULT '1800' COMMENT 'Tiempo maximo de ejecucion del auto proceso.',
  `process_date` date DEFAULT NULL,
  `process_day_offset` tinyint DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `last_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_summary` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auto_proceso_configs_sistema_unique` (`sistema`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bancos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bancos_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bancos_operaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bancos_operaciones_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `catalogo_juegos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sistema` enum('lotobet','lotonet','ambos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ambos',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalogo_juegos_producto_id_unique` (`producto_id`),
  KEY `catalogo_juegos_tipo_sistema_index` (`tipo`,`sistema`),
  KEY `catalogo_juegos_sistema_index` (`sistema`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `centros_de_costo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_centro_costo` int unsigned NOT NULL,
  `company_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuenta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inactivo` tinyint(1) NOT NULL DEFAULT '0',
  `id_grupo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_sub_grupo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_division` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_sociedad` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_viejo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_centro_costo_resumir_en` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocultar` tinyint(1) NOT NULL DEFAULT '0',
  `com_recarga` tinyint(1) NOT NULL DEFAULT '0',
  `gasto_vta_tradicional` tinyint(1) NOT NULL DEFAULT '0',
  `varios_locales` tinyint(1) NOT NULL DEFAULT '0',
  `aplica_para_ponderar` tinyint(1) NOT NULL DEFAULT '0',
  `valor_ponderar` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `creado_por` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_grabado` datetime DEFAULT NULL,
  `modificado_por` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_modificado` datetime DEFAULT NULL,
  `atributos` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `centros_de_costo_id_centro_costo_unique` (`id_centro_costo`),
  KEY `centros_de_costo_id_grupo_index` (`id_grupo`),
  KEY `centros_de_costo_inactivo_index` (`inactivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `consorcios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consorcios_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contabilidad_electricidad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha_factura` date NOT NULL,
  `empresa` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sucursal` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrato` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medidor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lectura_anterior` decimal(14,3) NOT NULL DEFAULT '0.000',
  `lectura_actual` decimal(14,3) NOT NULL DEFAULT '0.000',
  `ajuste_kwh` decimal(14,3) NOT NULL DEFAULT '0.000',
  `tarifa_kwh` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `otros_cargos` decimal(14,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(14,2) NOT NULL DEFAULT '0.00',
  `pagado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_pago` date DEFAULT NULL,
  `referencia_pago` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contabilidad_electricidad_fecha_factura_empresa_index` (`fecha_factura`,`empresa`),
  KEY `contabilidad_electricidad_empresa_sucursal_index` (`empresa`,`sucursal`),
  KEY `contabilidad_electricidad_pagado_index` (`pagado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contabilidad_electricidad_averia_dia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha_reporte` date NOT NULL,
  `reporte` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `distribuidora` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nic` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agencia` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinadores` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agente_venta_am` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agente_venta_pm` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estatus` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ave_fecha_dist` (`fecha_reporte`,`distribuidora`),
  KEY `idx_ave_nic` (`nic`),
  KEY `contabilidad_electricidad_averia_dia_estatus_index` (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contabilidad_electricidad_seguimiento_dia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha_solicitud` date NOT NULL,
  `distribuidora` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nic` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agencia` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estatus` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seg_fecha_dist` (`fecha_solicitud`,`distribuidora`),
  KEY `idx_seg_nic` (`nic`),
  KEY `contabilidad_electricidad_seguimiento_dia_estatus_index` (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coordinadores_operador` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coordinadores_operador_cedula_unique` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coordinador_operador_agencia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coordinador_operador_id` bigint unsigned NOT NULL,
  `agencia_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coa_coord_agencia_unique` (`coordinador_operador_id`,`agencia_id`),
  UNIQUE KEY `coordinador_operador_agencia_unique` (`coordinador_operador_id`,`agencia_id`),
  KEY `coordinador_operador_agencia_agencia_id_index` (`agencia_id`),
  KEY `coordinador_operador_agencia_coordinador_operador_id_idx` (`coordinador_operador_id`),
  KEY `coordinador_operador_agencia_agencia_id_idx` (`agencia_id`),
  CONSTRAINT `coordinador_operador_agencia_agencia_id_fk` FOREIGN KEY (`agencia_id`) REFERENCES `agencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coordinador_operador_agencia_coordinador_operador_id_fk` FOREIGN KEY (`coordinador_operador_id`) REFERENCES `coordinadores_operador` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cuentas_contables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cuenta` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ctacontrol` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuentas_contables_cuenta_unique` (`cuenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#405189',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departamentos_nombre_unique` (`nombre`),
  UNIQUE KEY `departamentos_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `detalle_cuentas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `external_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuenta` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_asiento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_raw` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_ref` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debito` decimal(18,2) DEFAULT NULL,
  `credito` decimal(18,2) DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `grupo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_grupo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `centro_costo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conciliado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_grabado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_modificado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modificado_por` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sociedad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detalle_cuentas_external_key_unique` (`external_key`),
  KEY `detalle_cuentas_cuenta_fecha_index` (`cuenta`,`fecha`),
  KEY `detalle_cuentas_cuenta_index` (`cuenta`),
  KEY `detalle_cuentas_fecha_index` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `distribucion_porcentajes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `departamento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porcentaje` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dist_pct_dept_tipo_unique` (`departamento`,`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `efectividad_usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `agencia_id` int unsigned NOT NULL,
  `tipo_producto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sistema` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venta_mes` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `empleadoid_bet` int unsigned DEFAULT NULL,
  `cedula_bet` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_cedula_bet` decimal(18,4) DEFAULT NULL,
  `porcentaje_cedula_bet` decimal(10,4) DEFAULT NULL,
  `empleadoid_net` int unsigned DEFAULT NULL,
  `cedula_net` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_cedula_net` decimal(18,4) DEFAULT NULL,
  `porcentaje_cedula_net` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `efectividad_usuarios_job_id_index` (`job_id`),
  KEY `efectividad_usuarios_agencia_id_index` (`agencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estados_civiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estados_civiles_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posiciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posiciones_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tipos_documento` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_documento_nombre_unique` (`nombre`),
  UNIQUE KEY `tipos_documento_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `turnos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `turnos_nombre_unique` (`nombre`),
  UNIQUE KEY `turnos_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `empleados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `companyid` int NOT NULL,
  `empleadoid` int NOT NULL,
  `cedula` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_documento_id` bigint unsigned DEFAULT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento_id` bigint unsigned DEFAULT NULL,
  `posicion_id` bigint unsigned DEFAULT NULL,
  `ciudad_id` bigint unsigned DEFAULT NULL,
  `estado_civil_id` bigint unsigned DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_egreso` date DEFAULT NULL,
  `turno_id` bigint unsigned DEFAULT NULL,
  `tipo_contrato` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT '1',
  `salario` decimal(14,2) DEFAULT NULL,
  `banco_id` bigint unsigned DEFAULT NULL,
  `numero_cuenta` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_cuenta` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aplica_incentivo` tinyint(1) NOT NULL DEFAULT '0',
  `porcentaje_incentivo` decimal(5,2) DEFAULT NULL,
  `tipo_empleado_incentivo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fuente_sync` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ultima_sync_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleados_empleadoid_companyid_unique` (`empleadoid`,`companyid`),
  UNIQUE KEY `empleados_cedula_unique` (`cedula`),
  KEY `empleados_departamento_id_index` (`departamento_id`),
  KEY `empleados_posicion_id_index` (`posicion_id`),
  KEY `empleados_estatus_index` (`estatus`),
  KEY `empleados_aplica_incentivo_index` (`aplica_incentivo`),
  KEY `empleados_tipo_documento_id_idx` (`tipo_documento_id`),
  KEY `empleados_departamento_id_idx` (`departamento_id`),
  KEY `empleados_posicion_id_idx` (`posicion_id`),
  KEY `empleados_ciudad_id_idx` (`ciudad_id`),
  KEY `empleados_estado_civil_id_idx` (`estado_civil_id`),
  KEY `empleados_turno_id_idx` (`turno_id`),
  KEY `empleados_banco_id_idx` (`banco_id`),
  CONSTRAINT `empleados_banco_id_fk` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_ciudad_id_fk` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_departamento_id_fk` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_estado_civil_id_fk` FOREIGN KEY (`estado_civil_id`) REFERENCES `estados_civiles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_posicion_id_fk` FOREIGN KEY (`posicion_id`) REFERENCES `posiciones` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_tipo_documento_id_fk` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `empleados_turno_id_fk` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `entrevistas_online` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `puesto_id` bigint unsigned DEFAULT NULL,
  `nombre_completo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `edad` tinyint unsigned DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hijos` tinyint unsigned DEFAULT NULL,
  `estudia_actualmente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `licencia_vehiculo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laborando_actualmente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ultimo_empleo_posicion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiempo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salario` decimal(12,2) DEFAULT NULL,
  `fecha_salida_motivo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comentarios` text COLLATE utf8mb4_unicode_ci,
  `fecha_llamada` date DEFAULT NULL,
  `entrevistado_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experiencia_demostrable` text COLLATE utf8mb4_unicode_ci,
  `conoce_del_area` text COLLATE utf8mb4_unicode_ci,
  `fortalezas` text COLLATE utf8mb4_unicode_ci,
  `debilidades` text COLLATE utf8mb4_unicode_ci,
  `manejo_excel` tinyint(1) NOT NULL DEFAULT '0',
  `manejo_excel_nivel` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entrevistas_online_user_id_foreign` (`user_id`),
  KEY `entrevistas_online_fecha_llamada_index` (`fecha_llamada`),
  KEY `entrevistas_online_puesto_id_index` (`puesto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `entrevistas_online_puestos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estacionalidad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` smallint unsigned NOT NULL,
  `mes` tinyint unsigned NOT NULL,
  `factor_base` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `vigente` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estacionalidad_year_mes_unique` (`year`,`mes`),
  KEY `estacionalidad_vigente_index` (`vigente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `etl_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tabla` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `fecha_ini` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `dry_run` tinyint(1) NOT NULL DEFAULT '0',
  `chunk_size` int unsigned NOT NULL DEFAULT '50000',
  `rows_expected` bigint unsigned DEFAULT NULL,
  `rows_migrated` bigint unsigned NOT NULL DEFAULT '0',
  `rows_failed` bigint unsigned NOT NULL DEFAULT '0',
  `rows_skipped` bigint unsigned NOT NULL DEFAULT '0',
  `last_offset` bigint unsigned NOT NULL DEFAULT '0',
  `error` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etl_runs_tabla_index` (`tabla`),
  KEY `etl_runs_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `etl_conflictos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `etl_run_id` bigint unsigned NOT NULL,
  `tabla` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legacy_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etl_conflictos_resolved_by_foreign` (`resolved_by`),
  KEY `etl_conflictos_tabla_index` (`tabla`),
  KEY `etl_conflictos_etl_run_id_idx` (`etl_run_id`),
  CONSTRAINT `etl_conflictos_etl_run_id_fk` FOREIGN KEY (`etl_run_id`) REFERENCES `etl_runs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `etl_run_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `etl_run_id` bigint unsigned NOT NULL,
  `batch_num` int unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'done',
  `rows_processed` int unsigned NOT NULL DEFAULT '0',
  `rows_inserted` int unsigned NOT NULL DEFAULT '0',
  `rows_skipped` int unsigned NOT NULL DEFAULT '0',
  `error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eri_run_batch_idx` (`etl_run_id`,`batch_num`),
  KEY `etl_run_items_etl_run_id_idx` (`etl_run_id`),
  CONSTRAINT `etl_run_items_etl_run_id_fk` FOREIGN KEY (`etl_run_id`) REFERENCES `etl_runs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2020 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faltantes_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(18,2) DEFAULT '0.00',
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `faltantes_bet_agencia_id_index` (`agencia_id`),
  KEY `faltantes_bet_fecha_index` (`fecha`),
  KEY `faltantes_bet_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=588 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faltantes_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(18,2) DEFAULT '0.00',
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fn_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `faltantes_net_agencia_id_index` (`agencia_id`),
  KEY `faltantes_net_fecha_index` (`fecha`),
  KEY `faltantes_net_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `importaciones_diarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sistema` enum('lotobet','lotonet','ambos') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('pendiente','ejecutando','completado','fallido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `filas_importadas` bigint unsigned NOT NULL DEFAULT '0',
  `checksum` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci,
  `iniciado_at` timestamp NULL DEFAULT NULL,
  `finalizado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `importaciones_diarias_sistema_modulo_fecha_unique` (`sistema`,`modulo`,`fecha`),
  KEY `importaciones_diarias_estado_index` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `incentivo_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mes` smallint unsigned NOT NULL,
  `anio` smallint unsigned NOT NULL,
  `excluidos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incentivo_jobs_anio_mes_index` (`anio`,`mes`),
  KEY `incentivo_jobs_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `incentivo_resultados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `agencia_id` int unsigned NOT NULL,
  `tipo_producto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sistema` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_trimestre` decimal(18,2) NOT NULL DEFAULT '0.00',
  `promedio_mensual` decimal(18,2) NOT NULL DEFAULT '0.00',
  `venta_base` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_mes` decimal(18,2) NOT NULL DEFAULT '0.00',
  `cumplimiento` decimal(18,2) NOT NULL DEFAULT '0.00',
  `meta_plan` decimal(18,2) NOT NULL DEFAULT '0.00',
  `meta_incremental` decimal(18,2) NOT NULL DEFAULT '0.00',
  `nivel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incentivo_resultados_job_id_index` (`job_id`),
  KEY `incentivo_resultados_agencia_id_index` (`agencia_id`),
  KEY `incentivo_resultados_sistema_index` (`sistema`),
  KEY `incentivo_resultados_tipo_producto_index` (`tipo_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `niveles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_producto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` smallint unsigned NOT NULL,
  `rango_min` decimal(18,2) NOT NULL DEFAULT '0.00',
  `rango_max` decimal(18,2) NOT NULL DEFAULT '0.00',
  `incremento_porcentaje` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `incremento_fijo` decimal(18,2) NOT NULL DEFAULT '0.00',
  `prioridad` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `niveles_tipo_producto_index` (`tipo_producto`),
  KEY `niveles_tipo_producto_nivel_index` (`tipo_producto`,`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `novedades_horario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `terminal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_agencia` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruta` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_empleado` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cedula` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `primer_login` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `horas_acumuladas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `novedades_horario_fecha_terminal_index` (`fecha`,`terminal`),
  KEY `novedades_horario_cedula_index` (`cedula`),
  KEY `novedades_horario_ruta_index` (`ruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `operadores_ruta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operadores_ruta_cedula_unique` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `operador_ruta_agencia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operador_ruta_id` bigint unsigned NOT NULL,
  `agencia_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operador_ruta_agencia_operador_ruta_id_agencia_id_unique` (`operador_ruta_id`,`agencia_id`),
  UNIQUE KEY `operador_ruta_agencia_unique` (`operador_ruta_id`,`agencia_id`),
  KEY `operador_ruta_agencia_agencia_id_index` (`agencia_id`),
  KEY `operador_ruta_agencia_operador_ruta_id_idx` (`operador_ruta_id`),
  KEY `operador_ruta_agencia_agencia_id_idx` (`agencia_id`),
  CONSTRAINT `operador_ruta_agencia_agencia_id_fk` FOREIGN KEY (`agencia_id`) REFERENCES `agencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `operador_ruta_agencia_operador_ruta_id_fk` FOREIGN KEY (`operador_ruta_id`) REFERENCES `operadores_ruta` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pago_incentivos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `agencia_id` int unsigned NOT NULL,
  `tipo_producto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sistema` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empleadoid` int unsigned NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porcentaje_cedula` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `monto_agente` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `monto_incentivo` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pago_incentivos_job_id_index` (`job_id`),
  KEY `pago_incentivos_cedula_index` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pago_incentivos_admin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `companyid` int unsigned NOT NULL,
  `empleadoid` int unsigned NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porcentaje` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `tradicional` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `no_tradicional` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `recarga` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `paquetico` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pago_incentivos_admin_job_id_index` (`job_id`),
  KEY `pago_incentivos_admin_cedula_index` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pago_incentivos_coordinador` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `companyid` int unsigned NOT NULL,
  `empleadoid` int unsigned NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porcentaje` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pago_incentivos_coordinador_job_id_index` (`job_id`),
  KEY `pago_incentivos_coordinador_cedula_index` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_aotra_empresa_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paeb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `pagos_aotra_empresa_bet_agencia_id_index` (`agencia_id`),
  KEY `pagos_aotra_empresa_bet_fecha_index` (`fecha`),
  KEY `pagos_aotra_empresa_bet_cedula_index` (`cedula`),
  KEY `pagos_aotra_empresa_bet_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_aotra_empresa_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paen_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `pagos_aotra_empresa_net_agencia_id_index` (`agencia_id`),
  KEY `pagos_aotra_empresa_net_fecha_index` (`fecha`),
  KEY `pagos_aotra_empresa_net_cedula_index` (`cedula`),
  KEY `pagos_aotra_empresa_net_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_misma_empresa_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pmeb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `pagos_misma_empresa_bet_agencia_id_index` (`agencia_id`),
  KEY `pagos_misma_empresa_bet_fecha_index` (`fecha`),
  KEY `pagos_misma_empresa_bet_cedula_index` (`cedula`),
  KEY `pagos_misma_empresa_bet_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=102606 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_misma_empresa_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pmen_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `pagos_misma_empresa_net_agencia_id_index` (`agencia_id`),
  KEY `pagos_misma_empresa_net_fecha_index` (`fecha`),
  KEY `pagos_misma_empresa_net_cedula_index` (`cedula`),
  KEY `pagos_misma_empresa_net_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1068 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_porotra_empresa_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ppeb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `pagos_porotra_empresa_bet_agencia_id_index` (`agencia_id`),
  KEY `pagos_porotra_empresa_bet_fecha_index` (`fecha`),
  KEY `pagos_porotra_empresa_bet_cedula_index` (`cedula`),
  KEY `pagos_porotra_empresa_bet_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_porotra_empresa_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ppen_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `pagos_porotra_empresa_net_agencia_id_index` (`agencia_id`),
  KEY `pagos_porotra_empresa_net_fecha_index` (`fecha`),
  KEY `pagos_porotra_empresa_net_cedula_index` (`cedula`),
  KEY `pagos_porotra_empresa_net_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `paquetico_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pn_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `paquetico_net_agencia_id_index` (`agencia_id`),
  KEY `paquetico_net_fecha_index` (`fecha`),
  KEY `paquetico_net_cedula_index` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=355 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plan_agencias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` int unsigned NOT NULL,
  `nombre_agencia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_agencias_agencia_id_unique` (`agencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plan_agencias_distribucion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `agencia_id` int unsigned NOT NULL,
  `tipo_producto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sistema` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venta_mes` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `venta_base` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `excedente` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `porcentaje_agente` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `porcentaje_coordinador` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `porcentaje_administrativo` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `monto_agente` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `monto_coordinador` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `monto_administrativo` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_distribucion` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_agencias_distribucion_job_id_index` (`job_id`),
  KEY `plan_agencias_distribucion_agencia_id_index` (`agencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `porcentaje_administrativo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `empleado_id` int unsigned NOT NULL,
  `porcentaje` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pct_admin_co_emp_unique` (`company_id`,`empleado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `premios_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_id` bigint unsigned DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorteo_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `premios_bet_agencia_id_index` (`agencia_id`),
  KEY `premios_bet_producto_id_index` (`producto_id`),
  KEY `premios_bet_fecha_index` (`fecha`),
  KEY `premios_bet_cedula_index` (`cedula`),
  KEY `premios_bet_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `premios_bet_fecha_producto_idx` (`fecha`,`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=801777 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `premios_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_id` bigint unsigned DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorteo_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prn_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `premios_net_agencia_id_index` (`agencia_id`),
  KEY `premios_net_producto_id_index` (`producto_id`),
  KEY `premios_net_fecha_index` (`fecha`),
  KEY `premios_net_cedula_index` (`cedula`),
  KEY `premios_net_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `premios_net_fecha_producto_idx` (`fecha`,`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1014 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `procesos_departamento` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `departamento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proceso_base` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ri-file-list-3-line',
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `protocolo` longtext COLLATE utf8mb4_unicode_ci,
  `es_personalizado` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procesos_departamento_departamento_index` (`departamento`),
  KEY `procesos_departamento_base_idx` (`departamento`,`proceso_base`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recargas_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `recargas_bet_agencia_id_index` (`agencia_id`),
  KEY `recargas_bet_fecha_index` (`fecha`),
  KEY `recargas_bet_cedula_index` (`cedula`),
  KEY `recargas_bet_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44338 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recargas_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rn_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `recargas_net_agencia_id_index` (`agencia_id`),
  KEY `recargas_net_fecha_index` (`fecha`),
  KEY `recargas_net_cedula_index` (`cedula`),
  KEY `recargas_net_fecha_agencia_idx` (`fecha`,`agencia_id`)
) ENGINE=InnoDB AUTO_INCREMENT=448 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rutas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `serial` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rutas_serial_unique` (`serial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reporte_diario_rutas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `serial_ruta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruta_id` bigint unsigned NOT NULL,
  `operador_ruta_id` bigint unsigned NOT NULL,
  `banco_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entregado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `procesado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gasto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `correo_destino` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `comprobante_entregado_path` longtext COLLATE utf8mb4_unicode_ci,
  `comprobante_diferencia_path` longtext COLLATE utf8mb4_unicode_ci,
  `enviado_operador_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdr_fecha_ruta_idx` (`fecha`,`ruta_id`),
  KEY `reporte_diario_rutas_fecha_index` (`fecha`),
  KEY `reporte_diario_rutas_ruta_id_idx` (`ruta_id`),
  KEY `reporte_diario_rutas_operador_ruta_id_idx` (`operador_ruta_id`),
  CONSTRAINT `reporte_diario_rutas_operador_ruta_id_fk` FOREIGN KEY (`operador_ruta_id`) REFERENCES `operadores_ruta` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reporte_diario_rutas_ruta_id_fk` FOREIGN KEY (`ruta_id`) REFERENCES `rutas` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ruta_agencia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ruta_id` bigint unsigned NOT NULL,
  `agencia_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruta_agencia_ruta_id_agencia_id_unique` (`ruta_id`,`agencia_id`),
  UNIQUE KEY `ruta_agencia_unique` (`ruta_id`,`agencia_id`),
  KEY `ruta_agencia_agencia_id_index` (`agencia_id`),
  KEY `ruta_agencia_ruta_id_idx` (`ruta_id`),
  KEY `ruta_agencia_agencia_id_idx` (`agencia_id`),
  CONSTRAINT `ruta_agencia_agencia_id_fk` FOREIGN KEY (`agencia_id`) REFERENCES `agencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ruta_agencia_ruta_id_fk` FOREIGN KEY (`ruta_id`) REFERENCES `rutas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_login_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tipos_requerimiento_sg` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_requerimiento_sg_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `servicios_generales_requerimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitante_id` bigint unsigned NOT NULL,
  `asignado_a_id` bigint unsigned DEFAULT NULL,
  `tipo_requerimiento_id` bigint unsigned NOT NULL,
  `titulo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `cierre_solicitado_at` timestamp NULL DEFAULT NULL,
  `cierre_solicitado_por_id` bigint unsigned DEFAULT NULL,
  `fecha_completada` date DEFAULT NULL,
  `adjunto_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adjunto_nombre` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sgr_estado_idx` (`estado`),
  KEY `sgr_solicitante_idx` (`solicitante_id`),
  KEY `sgr_asignado_idx` (`asignado_a_id`),
  KEY `servicios_generales_requerimientos_solicitante_id_idx` (`solicitante_id`),
  KEY `servicios_generales_requerimientos_asignado_a_id_idx` (`asignado_a_id`),
  KEY `servicios_generales_requerimientos_cierre_solicitado_por_id_idx` (`cierre_solicitado_por_id`),
  KEY `servicios_generales_requerimientos_tipo_requerimiento_id_idx` (`tipo_requerimiento_id`),
  CONSTRAINT `servicios_generales_requerimientos_asignado_a_id_fk` FOREIGN KEY (`asignado_a_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `servicios_generales_requerimientos_cierre_solicitado_por_id_` FOREIGN KEY (`cierre_solicitado_por_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `servicios_generales_requerimientos_solicitante_id_fk` FOREIGN KEY (`solicitante_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `servicios_generales_requerimientos_tipo_requerimiento_id_fk` FOREIGN KEY (`tipo_requerimiento_id`) REFERENCES `tipos_requerimiento_sg` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_empleo_educacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_empleo_id` bigint unsigned NOT NULL,
  `nivel` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `centro_docente` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_termino` date DEFAULT NULL,
  `nivel_alcanzado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitud_empleo_educacion_solicitud_empleo_id_foreign` (`solicitud_empleo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_empleo_empleos_previos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_empleo_id` bigint unsigned NOT NULL,
  `empresa_nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `puesto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiempo_en_puesto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_desde` date DEFAULT NULL,
  `fecha_hasta` date DEFAULT NULL,
  `ultimo_sueldo` decimal(12,2) DEFAULT NULL,
  `funciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_salida` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisor_inmediato` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitud_empleo_empleos_previos_solicitud_empleo_id_foreign` (`solicitud_empleo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_empleo_familiares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_empleo_id` bigint unsigned NOT NULL,
  `parentesco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `edad` tinyint unsigned DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocupacion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_trabajo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitud_empleo_familiares_solicitud_empleo_id_foreign` (`solicitud_empleo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_empleo_referencias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_empleo_id` bigint unsigned NOT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocupacion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_trabajo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector_residencia` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitud_empleo_referencias_solicitud_empleo_id_foreign` (`solicitud_empleo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitudes_empleo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `registrado_por_id` bigint unsigned DEFAULT NULL,
  `apellidos` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apodo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cedula_pasaporte` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `lugar_nacimiento` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nacionalidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edad` tinyint unsigned DEFAULT NULL,
  `direccion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_residencial` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_sangre` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estudia_actualmente` tinyint(1) NOT NULL DEFAULT '0',
  `que_estudia` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_estudio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domina_computadora` tinyint(1) NOT NULL DEFAULT '0',
  `domina_fax` tinyint(1) NOT NULL DEFAULT '0',
  `domina_impresora` tinyint(1) NOT NULL DEFAULT '0',
  `domina_scanner` tinyint(1) NOT NULL DEFAULT '0',
  `domina_maquinas_elec` tinyint(1) NOT NULL DEFAULT '0',
  `domina_calculadoras` tinyint(1) NOT NULL DEFAULT '0',
  `ha_trabajado_antes_en_empresa` tinyint(1) NOT NULL DEFAULT '0',
  `familiares_en_empresa` tinyint(1) NOT NULL DEFAULT '0',
  `competencias_laborales` text COLLATE utf8mb4_unicode_ci,
  `fortalezas_profesionales` text COLLATE utf8mb4_unicode_ci,
  `problemas_salud_detalle` text COLLATE utf8mb4_unicode_ci,
  `impedimento_sab_dom_fer` tinyint(1) NOT NULL DEFAULT '0',
  `afp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ars` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sabe_conducir` tinyint(1) NOT NULL DEFAULT '0',
  `licencia_categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `licencia_vencimiento` date DEFAULT NULL,
  `fecha_disponible` date DEFAULT NULL,
  `acepta_cambio_horario` tinyint(1) NOT NULL DEFAULT '0',
  `acepta_cambio_lugar` tinyint(1) NOT NULL DEFAULT '0',
  `disp_diurno` tinyint(1) NOT NULL DEFAULT '0',
  `disp_nocturno` tinyint(1) NOT NULL DEFAULT '0',
  `disp_rotativo` tinyint(1) NOT NULL DEFAULT '0',
  `disp_domingos` tinyint(1) NOT NULL DEFAULT '0',
  `disp_feriados` tinyint(1) NOT NULL DEFAULT '0',
  `cuenta_banco_caribe_bhd` tinyint(1) NOT NULL DEFAULT '0',
  `incluido_buro_credito` tinyint(1) NOT NULL DEFAULT '0',
  `referido_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referido_parentesco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergencia_contacto_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergencia_parentesco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergencia_telefonos` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medio_informo_vacante` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_firma` date DEFAULT NULL,
  `seleccionado` tinyint(1) NOT NULL DEFAULT '0',
  `puesto_aplicado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_trabajo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `salario` decimal(12,2) DEFAULT NULL,
  `aprobado_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solicitudes_empleo_cedula_pasaporte_unique` (`cedula_pasaporte`),
  KEY `solicitudes_empleo_estado_index` (`estado`),
  KEY `solicitudes_empleo_cedula_pasaporte_index` (`cedula_pasaporte`),
  KEY `solicitudes_empleo_registrado_por_id_idx` (`registrado_por_id`),
  CONSTRAINT `solicitudes_empleo_registrado_por_id_fk` FOREIGN KEY (`registrado_por_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tareas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `adjunto_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adjunto_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `asignado_id` bigint unsigned DEFAULT NULL,
  `tarea_padre_id` bigint unsigned DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta','critica') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `progreso` tinyint unsigned NOT NULL DEFAULT '0',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_completada` date DEFAULT NULL,
  `cierre_solicitado_at` timestamp NULL DEFAULT NULL,
  `cierre_solicitado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tareas_estado_fecha_fin_index` (`estado`,`fecha_fin`),
  KEY `tareas_asignado_id_estado_index` (`asignado_id`,`estado`),
  KEY `tareas_departamento_id_estado_index` (`departamento_id`,`estado`),
  KEY `tareas_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `tareas_departamento_id_idx` (`departamento_id`),
  KEY `tareas_user_id_idx` (`user_id`),
  KEY `tareas_asignado_id_idx` (`asignado_id`),
  KEY `tareas_tarea_padre_id_idx` (`tarea_padre_id`),
  KEY `tareas_cierre_solicitado_por_idx` (`cierre_solicitado_por`),
  CONSTRAINT `tareas_asignado_id_fk` FOREIGN KEY (`asignado_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tareas_cierre_solicitado_por_fk` FOREIGN KEY (`cierre_solicitado_por`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tareas_departamento_id_fk` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tareas_tarea_padre_id_fk` FOREIGN KEY (`tarea_padre_id`) REFERENCES `tareas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tareas_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tarea_comentarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tarea_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tarea_comentarios_tarea_id_created_at_index` (`tarea_id`,`created_at`),
  KEY `tarea_comentarios_tarea_id_idx` (`tarea_id`),
  KEY `tarea_comentarios_user_id_idx` (`user_id`),
  CONSTRAINT `tarea_comentarios_tarea_id_fk` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tarea_comentarios_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tipos_solicitud_tecnologia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `requiere_progreso` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_solicitud_tecnologia_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tecnologia_solicitudes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitante_id` bigint unsigned NOT NULL,
  `asignado_a_id` bigint unsigned DEFAULT NULL,
  `asignado_at` timestamp NULL DEFAULT NULL,
  `tipo_solicitud_id` bigint unsigned NOT NULL,
  `titulo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `prioridad` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `progreso` tinyint unsigned NOT NULL DEFAULT '0',
  `detalle_solucion` text COLLATE utf8mb4_unicode_ci,
  `cierre_solicitado_at` timestamp NULL DEFAULT NULL,
  `cierre_solicitado_por_id` bigint unsigned DEFAULT NULL,
  `fecha_completada` date DEFAULT NULL,
  `cerrado_por_id` bigint unsigned DEFAULT NULL,
  `adjunto_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adjunto_nombre` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tecnologia_solicitudes_estado_index` (`estado`),
  KEY `tecnologia_solicitudes_solicitante_id_index` (`solicitante_id`),
  KEY `tecnologia_solicitudes_asignado_a_id_index` (`asignado_a_id`),
  KEY `tecnologia_solicitudes_solicitante_id_idx` (`solicitante_id`),
  KEY `tecnologia_solicitudes_asignado_a_id_idx` (`asignado_a_id`),
  KEY `tecnologia_solicitudes_cierre_solicitado_por_id_idx` (`cierre_solicitado_por_id`),
  KEY `tecnologia_solicitudes_tipo_solicitud_id_idx` (`tipo_solicitud_id`),
  KEY `tecnologia_solicitudes_cerrado_por_id_foreign` (`cerrado_por_id`),
  CONSTRAINT `tecnologia_solicitudes_asignado_a_id_fk` FOREIGN KEY (`asignado_a_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tecnologia_solicitudes_cerrado_por_id_foreign` FOREIGN KEY (`cerrado_por_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tecnologia_solicitudes_cierre_solicitado_por_id_fk` FOREIGN KEY (`cierre_solicitado_por_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tecnologia_solicitudes_solicitante_id_fk` FOREIGN KEY (`solicitante_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tecnologia_solicitudes_tipo_solicitud_id_fk` FOREIGN KEY (`tipo_solicitud_id`) REFERENCES `tipos_solicitud_tecnologia` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tokens` (
  `id` tinyint unsigned NOT NULL,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ventas_producto_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consorcio_id` bigint unsigned DEFAULT NULL,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `descripcion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `comision` decimal(18,2) DEFAULT NULL,
  `comision_supervisor` decimal(18,2) DEFAULT NULL,
  `fecha` date NOT NULL,
  `sorteo_id` bigint unsigned DEFAULT NULL,
  `fecha_sorteo` datetime DEFAULT NULL,
  `source_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_producto_bet_source_hash_unique` (`source_hash`),
  KEY `vpb_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `vpb_fecha_producto_idx` (`fecha`,`producto_id`),
  KEY `ventas_producto_bet_agencia_id_index` (`agencia_id`),
  KEY `ventas_producto_bet_producto_id_index` (`producto_id`),
  KEY `ventas_producto_bet_fecha_index` (`fecha`),
  KEY `ventas_producto_bet_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `ventas_producto_bet_fecha_producto_idx` (`fecha`,`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1922666 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ventas_producto_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `sorteo_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vpn_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `vpn_fecha_producto_idx` (`fecha`,`producto_id`),
  KEY `ventas_producto_net_agencia_id_index` (`agencia_id`),
  KEY `ventas_producto_net_producto_id_index` (`producto_id`),
  KEY `ventas_producto_net_fecha_index` (`fecha`),
  KEY `ventas_producto_net_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `ventas_producto_net_fecha_producto_idx` (`fecha`,`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6445 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ventas_usuarios_bet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vub_fecha_cedula_idx` (`fecha`,`cedula`),
  KEY `ventas_usuarios_bet_cedula_index` (`cedula`),
  KEY `ventas_usuarios_bet_agencia_id_index` (`agencia_id`),
  KEY `ventas_usuarios_bet_fecha_index` (`fecha`),
  KEY `ventas_usuarios_bet_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `ventas_usuarios_bet_fecha_cedula_idx` (`fecha`,`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=954176 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ventas_usuarios_net` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cedula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(18,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vun_fecha_cedula_idx` (`fecha`,`cedula`),
  KEY `ventas_usuarios_net_cedula_index` (`cedula`),
  KEY `ventas_usuarios_net_agencia_id_index` (`agencia_id`),
  KEY `ventas_usuarios_net_fecha_index` (`fecha`),
  KEY `ventas_usuarios_net_fecha_agencia_idx` (`fecha`,`agencia_id`),
  KEY `ventas_usuarios_net_fecha_cedula_idx` (`fecha`,`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=14279 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE OR REPLACE VIEW `vw_ventas_por_producto` AS select `v`.`producto_id` AS `producto_id`,coalesce(`c`.`descripcion`,concat('Producto ',`v`.`producto_id`)) AS `descripcion_producto`,coalesce(`c`.`tipo`,'Sin tipo') AS `tipo_producto`,`v`.`sistema` AS `sistema`,sum(`v`.`monto`) AS `total_ventas`,count(0) AS `total_registros` from ((select `ventas_producto_bet`.`producto_id` AS `producto_id`,`ventas_producto_bet`.`monto` AS `monto`,'lotobet' AS `sistema` from `ventas_producto_bet` union all select `ventas_producto_net`.`producto_id` AS `producto_id`,`ventas_producto_net`.`monto` AS `monto`,'lotonet' AS `sistema` from `ventas_producto_net`) `v` left join `catalogo_juegos` `c` on((`c`.`producto_id` = `v`.`producto_id`))) group by `v`.`producto_id`,`c`.`descripcion`,`c`.`tipo`,`v`.`sistema`;

SQL);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `vw_ventas_por_producto`;
DROP TABLE IF EXISTS `ventas_usuarios_net`;
DROP TABLE IF EXISTS `ventas_usuarios_bet`;
DROP TABLE IF EXISTS `ventas_producto_net`;
DROP TABLE IF EXISTS `ventas_producto_bet`;
DROP TABLE IF EXISTS `tokens`;
DROP TABLE IF EXISTS `tecnologia_solicitudes`;
DROP TABLE IF EXISTS `tipos_solicitud_tecnologia`;
DROP TABLE IF EXISTS `tarea_comentarios`;
DROP TABLE IF EXISTS `tareas`;
DROP TABLE IF EXISTS `solicitudes_empleo`;
DROP TABLE IF EXISTS `solicitud_empleo_referencias`;
DROP TABLE IF EXISTS `solicitud_empleo_familiares`;
DROP TABLE IF EXISTS `solicitud_empleo_empleos_previos`;
DROP TABLE IF EXISTS `solicitud_empleo_educacion`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `servicios_generales_requerimientos`;
DROP TABLE IF EXISTS `tipos_requerimiento_sg`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `ruta_agencia`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `role_has_permissions`;
DROP TABLE IF EXISTS `reporte_diario_rutas`;
DROP TABLE IF EXISTS `rutas`;
DROP TABLE IF EXISTS `recargas_net`;
DROP TABLE IF EXISTS `recargas_bet`;
DROP TABLE IF EXISTS `procesos_departamento`;
DROP TABLE IF EXISTS `premios_net`;
DROP TABLE IF EXISTS `premios_bet`;
DROP TABLE IF EXISTS `porcentaje_administrativo`;
DROP TABLE IF EXISTS `plan_agencias_distribucion`;
DROP TABLE IF EXISTS `plan_agencias`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `paquetico_net`;
DROP TABLE IF EXISTS `pagos_porotra_empresa_net`;
DROP TABLE IF EXISTS `pagos_porotra_empresa_bet`;
DROP TABLE IF EXISTS `pagos_misma_empresa_net`;
DROP TABLE IF EXISTS `pagos_misma_empresa_bet`;
DROP TABLE IF EXISTS `pagos_aotra_empresa_net`;
DROP TABLE IF EXISTS `pagos_aotra_empresa_bet`;
DROP TABLE IF EXISTS `pago_incentivos_coordinador`;
DROP TABLE IF EXISTS `pago_incentivos_admin`;
DROP TABLE IF EXISTS `pago_incentivos`;
DROP TABLE IF EXISTS `operador_ruta_agencia`;
DROP TABLE IF EXISTS `operadores_ruta`;
DROP TABLE IF EXISTS `novedades_horario`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `niveles`;
DROP TABLE IF EXISTS `model_has_roles`;
DROP TABLE IF EXISTS `model_has_permissions`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `incentivo_resultados`;
DROP TABLE IF EXISTS `incentivo_jobs`;
DROP TABLE IF EXISTS `importaciones_diarias`;
DROP TABLE IF EXISTS `faltantes_net`;
DROP TABLE IF EXISTS `faltantes_bet`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `etl_run_items`;
DROP TABLE IF EXISTS `etl_conflictos`;
DROP TABLE IF EXISTS `etl_runs`;
DROP TABLE IF EXISTS `estacionalidad`;
DROP TABLE IF EXISTS `entrevistas_online_puestos`;
DROP TABLE IF EXISTS `entrevistas_online`;
DROP TABLE IF EXISTS `empleados`;
DROP TABLE IF EXISTS `turnos`;
DROP TABLE IF EXISTS `tipos_documento`;
DROP TABLE IF EXISTS `posiciones`;
DROP TABLE IF EXISTS `estados_civiles`;
DROP TABLE IF EXISTS `efectividad_usuarios`;
DROP TABLE IF EXISTS `distribucion_porcentajes`;
DROP TABLE IF EXISTS `detalle_cuentas`;
DROP TABLE IF EXISTS `departamentos`;
DROP TABLE IF EXISTS `cuentas_contables`;
DROP TABLE IF EXISTS `coordinador_operador_agencia`;
DROP TABLE IF EXISTS `coordinadores_operador`;
DROP TABLE IF EXISTS `contabilidad_electricidad_seguimiento_dia`;
DROP TABLE IF EXISTS `contabilidad_electricidad_averia_dia`;
DROP TABLE IF EXISTS `contabilidad_electricidad`;
DROP TABLE IF EXISTS `consorcios`;
DROP TABLE IF EXISTS `centros_de_costo`;
DROP TABLE IF EXISTS `catalogo_juegos`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `bancos_operaciones`;
DROP TABLE IF EXISTS `bancos`;
DROP TABLE IF EXISTS `auto_proceso_configs`;
DROP TABLE IF EXISTS `asistencias_net`;
DROP TABLE IF EXISTS `asistencias_bet`;
DROP TABLE IF EXISTS `agencias`;
DROP TABLE IF EXISTS `ciudades`;
DROP TABLE IF EXISTS `provincias`;
DROP TABLE IF EXISTS `paises`;
DROP TABLE IF EXISTS `activity_log`;

SQL);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
