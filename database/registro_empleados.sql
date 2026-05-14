-- =========================
-- 1) CABECERA: Solicitud
-- =========================
CREATE TABLE solicitudes_empleo (
  solicitud_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Datos personales (pág. 1)
  apellidos           VARCHAR(120) NULL,
  nombres             VARCHAR(120) NULL,
  apodo               VARCHAR(120) NULL,
  cedula_pasaporte    VARCHAR(20)  NULL,
  fecha_nacimiento    DATE NULL,
  lugar_nacimiento    VARCHAR(150) NULL,
  nacionalidad        VARCHAR(80)  NULL,
  edad                SMALLINT UNSIGNED NULL,

  direccion           VARCHAR(255) NULL, -- Calle y número
  sector              VARCHAR(120) NULL,
  ciudad              VARCHAR(120) NULL,

  telefono_residencial VARCHAR(30) NULL,
  celular              VARCHAR(30) NULL,
  email                VARCHAR(180) NULL,

  estado_civil ENUM('SOLTERO','CASADO','DIVORCIADO','UNION_LIBRE') NULL,
  tipo_sangre         VARCHAR(10) NULL,
  instagram           VARCHAR(120) NULL,
  facebook            VARCHAR(120) NULL,
  -- Educación / estudio actual (pág. 1)
  estudia_actualmente TINYINT(1) NULL,
  que_estudia         VARCHAR(255) NULL,
  horario_estudio     VARCHAR(120) NULL,
  -- Herramientas que domina (pág. 1)
  domina_computadora        TINYINT(1) NOT NULL DEFAULT 0,
  domina_fax               TINYINT(1) NOT NULL DEFAULT 0,
  domina_impresora         TINYINT(1) NOT NULL DEFAULT 0,
  domina_scanner           TINYINT(1) NOT NULL DEFAULT 0,
  domina_maquinas_elec     TINYINT(1) NOT NULL DEFAULT 0,
  domina_calculadoras      TINYINT(1) NOT NULL DEFAULT 0,
  -- Preguntas finales pág. 1
  ha_trabajado_antes_en_empresa TINYINT(1) NULL,
  familiares_en_empresa         TINYINT(1) NULL,

  competencias_laborales TEXT NULL,
  fortalezas_profesionales TEXT NULL,
  -- Otras informaciones (pág. 2)
  impedimento_sab_dom_fer TINYINT(1) NULL,
  problemas_salud_detalle VARCHAR(255) NULL,

  afp VARCHAR(120) NULL,
  ars VARCHAR(120) NULL,

  sabe_conducir TINYINT(1) NULL,
  licencia_categoria VARCHAR(20) NULL,
  licencia_vencimiento DATE NULL,

  fecha_disponible DATE NULL,

  acepta_cambio_horario TINYINT(1) NULL,
  acepta_cambio_lugar   TINYINT(1) NULL,
  -- Disponibilidad de horario (pág. 2, “encierre en un círculo”)
  disp_diurno     TINYINT(1) NOT NULL DEFAULT 0,
  disp_nocturno   TINYINT(1) NOT NULL DEFAULT 0,
  disp_rotativo   TINYINT(1) NOT NULL DEFAULT 0,
  disp_domingos   TINYINT(1) NOT NULL DEFAULT 0,
  disp_feriados   TINYINT(1) NOT NULL DEFAULT 0,

  cuenta_banco_caribe_bhd TINYINT(1) NULL,
  incluido_buro_credito   TINYINT(1) NULL,

  referido_por VARCHAR(150) NULL,
  referido_parentesco VARCHAR(80) NULL,

  emergencia_contacto_nombre VARCHAR(150) NULL,
  emergencia_parentesco      VARCHAR(80) NULL,
  emergencia_telefonos       VARCHAR(80) NULL,

  medio_informo_vacante VARCHAR(255) NULL,
  -- Firma / fecha (pág. 2)
  firma_nombre VARCHAR(150) NULL,
  fecha_firma DATE NULL,
  -- Uso exclusivo Gestión Humana (pág. 2)
  seleccionado TINYINT(1) NULL,
  puesto_aplicado VARCHAR(150) NULL,
  banca VARCHAR(120) NULL,
  horario_trabajo VARCHAR(120) NULL,
  fecha_ingreso DATE NULL,
  salario DECIMAL(12,2) NULL,
  aprobado_por VARCHAR(150) NULL,

  PRIMARY KEY (solicitud_id),
  KEY idx_solicitud_cedula (cedula_pasaporte),
  KEY idx_solicitud_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- 2) ESTRUCTURA FAMILIAR (pág. 1)
-- =========================
CREATE TABLE solicitud_familiares (
  familiar_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id BIGINT UNSIGNED NOT NULL,

  parentesco ENUM('PADRE','MADRE','CONYUGE','HIJO','OTRO') NOT NULL DEFAULT 'OTRO',
  nombre      VARCHAR(150) NULL,
  edad        SMALLINT UNSIGNED NULL,
  telefono    VARCHAR(30) NULL,
  ocupacion   VARCHAR(120) NULL,
  lugar_trabajo VARCHAR(150) NULL,

  PRIMARY KEY (familiar_id),
  KEY idx_fam_solicitud (solicitud_id),
  CONSTRAINT fk_fam_solicitud
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_empleo(solicitud_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- 3) EDUCACION (pág. 1)
-- =========================
CREATE TABLE solicitud_educacion (
  educacion_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id BIGINT UNSIGNED NOT NULL,

  nivel ENUM('PRIMARIA','SECUNDARIA','UNIVERSITARIO','TECNICO','POST_GRADO','MAESTRIA','OTRO') NOT NULL DEFAULT 'OTRO',
  centro_docente VARCHAR(180) NULL,
  lugar          VARCHAR(150) NULL,
  fecha_termino  DATE NULL,
  nivel_alcanzado VARCHAR(120) NULL,

  PRIMARY KEY (educacion_id),
  KEY idx_edu_solicitud (solicitud_id),
  CONSTRAINT fk_edu_solicitud
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_empleo(solicitud_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- 4) ULTIMOS EMPLEOS (pág. 2) - lista (hasta 3 en el formulario)
-- =========================
CREATE TABLE solicitud_empleos (
  empleo_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id BIGINT UNSIGNED NOT NULL,

  empresa_nombre VARCHAR(180) NULL,
  telefono       VARCHAR(30) NULL,
  puesto         VARCHAR(150) NULL,
  tiempo_en_puesto VARCHAR(80) NULL,

  fecha_desde DATE NULL,
  fecha_hasta DATE NULL,
  ultimo_sueldo DECIMAL(12,2) NULL,

  funciones TEXT NULL,
  motivo_salida VARCHAR(255) NULL,
  supervisor_inmediato VARCHAR(150) NULL,

  PRIMARY KEY (empleo_id),
  KEY idx_emp_solicitud (solicitud_id),
  CONSTRAINT fk_emp_solicitud
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_empleo(solicitud_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- 5) REFERENCIAS LABORALES (pág. 2) - indica 3
-- =========================
CREATE TABLE solicitud_referencias_laborales (
  ref_lab_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id BIGINT UNSIGNED NOT NULL,

  nombre VARCHAR(150) NULL,
  ocupacion VARCHAR(120) NULL,
  lugar_trabajo VARCHAR(180) NULL,
  telefono VARCHAR(30) NULL,

  PRIMARY KEY (ref_lab_id),
  KEY idx_reflab_solicitud (solicitud_id),
  CONSTRAINT fk_reflab_solicitud
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_empleo(solicitud_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- 6) REFERENCIAS PERSONALES (pág. 2) - indica 2 (no familiares)
-- =========================
CREATE TABLE solicitud_referencias_personales (
  ref_per_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id BIGINT UNSIGNED NOT NULL,

  nombre VARCHAR(150) NULL,
  ocupacion VARCHAR(120) NULL,
  lugar_trabajo VARCHAR(180) NULL,
  sector_residencia VARCHAR(120) NULL,
  telefono VARCHAR(30) NULL,

  PRIMARY KEY (ref_per_id),
  KEY idx_refper_solicitud (solicitud_id),
  CONSTRAINT fk_refper_solicitud
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_empleo(solicitud_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
