-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 20-01-2026 a las 15:46:02
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `crm`
--

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `CalculoIncentivo`$$
CREATE  PROCEDURE `CalculoIncentivo` (IN `_mes` INT, IN `_anio` INT, IN `_excluidos` VARCHAR(255))   BEGIN
    DECLARE _fecha_inicio DATE;
    DECLARE _fecha_fin DATE;
    DECLARE _anio_actual INT DEFAULT YEAR(CURDATE());
    DECLARE _mes_inicio INT;
    DECLARE _mes_fin INT;
    DECLARE _anio_inicio INT;
    DECLARE _anio_fin INT;

    SET _anio_actual = IF(_anio IS NULL OR _anio = 0, YEAR(CURDATE()), _anio);

    -- ==============================
    -- 3 MESES ANTERIORES
    -- ==============================
    SET _mes_inicio = _mes - 3;
    SET _mes_fin    = _mes - 1;

    IF _mes_inicio <= 0 THEN
        SET _mes_inicio = _mes_inicio + 12;
        SET _anio_inicio = _anio_actual - 1;
    ELSE
        SET _anio_inicio = _anio_actual;
    END IF;

    IF _mes_fin <= 0 THEN
        SET _mes_fin = _mes_fin + 12;
        SET _anio_fin = _anio_actual - 1;
    ELSE
        SET _anio_fin = _anio_actual;
    END IF;

    SET _fecha_inicio = STR_TO_DATE(CONCAT(_anio_inicio,'-',LPAD(_mes_inicio,2,'0'),'-01'), '%Y-%m-%d');
    SET _fecha_fin = LAST_DAY(STR_TO_DATE(CONCAT(_anio_fin,'-',LPAD(_mes_fin,2,'0'),'-01'), '%Y-%m-%d'));

    -- Si viene vacío, lo convertimos en NULL
    IF _excluidos = '' THEN 
        SET _excluidos = NULL;
    END IF;

    -- ===================================
    -- CTE + INSERT — UNA SOLA SENTENCIA
    -- ===================================
    WITH factor AS (
        SELECT factor_base
        FROM estacionalidad 
        WHERE vigente = 1 AND mes = _mes
        LIMIT 1
    ),

    ventas_base AS (
        SELECT 
            v.agencia_id,
            c.tipo AS tipo_producto,
            v.sistema,
            SUM(IFNULL(v.monto, 0)) AS total_trimestre
        FROM (
            SELECT agencia_id, producto_id, monto, fecha, 'Lotonet' AS sistema FROM ventas_producto_net WHERE (_excluidos IS NULL OR FIND_IN_SET(producto_id, _excluidos) = 0)
            UNION ALL
            SELECT agencia_id, producto_id, monto, fecha, 'Lotobet' AS sistema FROM ventas_producto_bet WHERE (_excluidos IS NULL OR FIND_IN_SET(producto_id, _excluidos) = 0)
            UNION ALL
            SELECT agencia_id, producto_id, monto, fecha, 'Lotonet' AS sistema FROM recargas_net
            UNION ALL
            SELECT agencia_id, producto_id, monto, fecha, 'Lotobet' AS sistema FROM recargas_bet
            UNION ALL
            SELECT agencia_id, producto_id, monto_pagado AS monto, fecha, 'Lotonet' AS sistema FROM paquetico_net
        ) v
        LEFT JOIN catalogo_juegos c ON CAST(v.producto_id AS SIGNED) = c.producto_id
        WHERE v.fecha BETWEEN _fecha_inicio AND _fecha_fin
        GROUP BY v.agencia_id, c.tipo, v.sistema
    ),

    ventas_mes AS (
        SELECT
            v.agencia_id,
            c.tipo AS tipo_producto,
            v.sistema,
            SUM(IFNULL(v.monto, 0)) AS total_mes,
            MONTH(v.fecha) AS mes
        FROM (
            SELECT agencia_id, producto_id, monto, fecha, 'Lotonet' AS sistema FROM ventas_producto_net WHERE (_excluidos IS NULL OR FIND_IN_SET(producto_id, _excluidos) = 0)
            UNION ALL
            SELECT agencia_id, producto_id, monto, fecha, 'Lotobet' AS sistema FROM ventas_producto_bet WHERE (_excluidos IS NULL OR FIND_IN_SET(producto_id, _excluidos) = 0)
            UNION ALL
            SELECT agencia_id, producto_id, monto, fecha, 'Lotonet' AS sistema FROM recargas_net
            UNION ALL
            SELECT agencia_id, producto_id, monto, fecha, 'Lotobet' AS sistema FROM recargas_bet
            UNION ALL
            SELECT agencia_id, producto_id, monto_pagado AS monto, fecha, 'Lotonet' AS sistema FROM paquetico_net
        ) v
        LEFT JOIN catalogo_juegos c ON CAST(v.producto_id AS SIGNED) = c.producto_id
        WHERE MONTH(v.fecha) = _mes AND YEAR(v.fecha) = _anio_actual
        GROUP BY
            v.agencia_id,
            c.tipo,
            v.sistema
    ),

    aplicacion AS (
        SELECT
            b.agencia_id,
            b.tipo_producto,
            b.sistema,
            b.total_trimestre,
            m.total_mes,
            f.factor_base,
            IFNULL((b.total_trimestre / 3), 0) AS promedio_mensual,
            IFNULL(((b.total_trimestre / 3) * f.factor_base), 0) AS venta_base
        FROM ventas_base b
        LEFT JOIN ventas_mes m ON b.agencia_id = m.agencia_id 
            AND b.tipo_producto = m.tipo_producto 
            AND b.sistema = m.sistema
        CROSS JOIN factor f
    ),

    nivel AS (
        SELECT
            a.agencia_id,
            a.tipo_producto,
            a.sistema,
            a.total_trimestre,
            a.promedio_mensual,
            IFNULL(a.total_mes, 0) AS total_mes,
            a.venta_base,
            IFNULL(cn.nivel, '') AS nivel,
            /* IFNULL(
                IFNULL(
                    cn.incremento_fijo,
                    a.promedio_mensual * (1 + cn.incremento_porcentaje)
                ), 0 
            ) AS meta_incremental,*/
            IFNULL(
                IFNULL(
                    cn.incremento_fijo,
                    ((a.promedio_mensual * (1 + cn.incremento_porcentaje)) - a.promedio_mensual)
                ), 0
            ) AS cumplimiento
        FROM aplicacion a
        LEFT JOIN niveles cn ON a.tipo_producto = cn.tipo_producto
            AND a.promedio_mensual BETWEEN cn.rango_min AND cn.rango_max
    )

    SELECT
        agencia_id,
        tipo_producto,
        sistema,
        FORMAT(total_trimestre, 2, 'en_US') AS total_trimestre,
        FORMAT(promedio_mensual, 2, 'en_US') AS promedio_mensual,
        FORMAT(venta_base, 2, 'en_US') AS venta_base,
        FORMAT(total_mes, 2, 'en_US') AS total_mes,
        nivel,
        FORMAT(cumplimiento, 2, 'en_US') AS cumplimiento,
        FORMAT(0, 2, 'en_US') AS meta_plan,
        FORMAT(IFNULL((venta_base + cumplimiento), 0), 2, 'en_US') AS meta_incremental
    FROM nivel;
END$$

DROP PROCEDURE IF EXISTS `sp_ventas_unificadas`$$
CREATE  PROCEDURE `sp_ventas_unificadas` (IN `_ini` DATE, IN `_fin` DATE)   BEGIN

    SELECT 
        v.agencia_id,
        v.consorcio_id,
        v.cedula,
        v.tipo,
        v.producto_id,
        cj.tipo AS tipo_juego,
        cj.descripcion AS descripcion_juego,
        v.monto,
        v.fecha,
        v.sistema
    FROM (

        -- =============================
        -- NET
        -- =============================
        SELECT 
            agencia_id,
            consorcio_id,
            cedula,
            tipo,
            producto_id,
            descripcion,
            monto,
            fecha,
            'NET' AS sistema
        FROM vt_usuarios_net
        WHERE fecha BETWEEN _ini AND _fin
        
        UNION ALL
        
        -- =============================
        -- BET
        -- =============================
        SELECT 
            agencia_id,
            consorcio_id,
            cedula,
            tipo,
            producto_id,
            descripcion,
            monto,
            fecha,
            'BET' AS sistema
        FROM vt_usuarios_bet
        WHERE fecha BETWEEN _ini AND _fin

    ) AS v

    LEFT JOIN catalogo_juegos AS cj
        ON cj.producto_id = v.producto_id;

END$$

DROP PROCEDURE IF EXISTS `ventas`$$
CREATE  PROCEDURE `ventas` (IN `_ini` DATE, IN `_fin` DATE)   BEGIN

    SELECT 
        v.agencia_id,
        v.consorcio_id,
        v.cedula,
        v.tipo,
        v.producto_id,
        cj.tipo AS tipo_juego,
        cj.descripcion AS descripcion_juego,
        v.monto,
        v.fecha,
        v.sistema
    FROM (

        -- =============================
        -- NET
        -- =============================
        SELECT 
            agencia_id,
            consorcio_id,
            cedula,
            tipo,
            producto_id,
            descripcion,
            monto,
            fecha,
            'NET' AS sistema
        FROM vt_usuarios_net
        WHERE 
            -- Si viene NULL devuelve todo
            (_ini IS NULL OR _fin IS NULL OR fecha BETWEEN _ini AND _fin)

        
        UNION ALL
        
        -- =============================
        -- BET
        -- =============================
        SELECT 
            agencia_id,
            consorcio_id,
            cedula,
            tipo,
            producto_id,
            descripcion,
            monto,
            fecha,
            'BET' AS sistema
        FROM vt_usuarios_bet
        WHERE 
            -- Si viene NULL devuelve todo
            (_ini IS NULL OR _fin IS NULL OR fecha BETWEEN _ini AND _fin)

    ) AS v

    LEFT JOIN catalogo_juegos AS cj
        ON cj.producto_id = v.producto_id;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias_bet`
--

DROP TABLE IF EXISTS `asistencias_bet`;
CREATE TABLE IF NOT EXISTS `asistencias_bet` (
  `asistencia_id` int NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `primer_login` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`asistencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias_net`
--

DROP TABLE IF EXISTS `asistencias_net`;
CREATE TABLE IF NOT EXISTS `asistencias_net` (
  `asistencia_id` int NOT NULL AUTO_INCREMENT,
  `consorcio` varchar(150) DEFAULT NULL,
  `agencia` varchar(25) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `entrada` datetime DEFAULT NULL,
  `salida` datetime DEFAULT NULL,
  `identificacion` varchar(100) DEFAULT NULL,
  `username` varchar(250) DEFAULT NULL,
  `banca` varchar(250) DEFAULT NULL,
  `terminal` varchar(250) DEFAULT NULL,
  `salida_inactividad` varchar(250) DEFAULT NULL,
  `turno` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`asistencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo_juegos`
--

DROP TABLE IF EXISTS `catalogo_juegos`;
CREATE TABLE IF NOT EXISTS `catalogo_juegos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` varchar(12) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordinador`
--

DROP TABLE IF EXISTS `coordinador`;
CREATE TABLE IF NOT EXISTS `coordinador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agencia_id` int DEFAULT NULL,
  `empleado_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `departamento` varchar(20) DEFAULT 'Coordinador',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
CREATE TABLE IF NOT EXISTS `departamentos` (
  `depto_id` int DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `porcentaje` decimal(8,4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distribucion_incentivos`
--

DROP TABLE IF EXISTS `distribucion_incentivos`;
CREATE TABLE IF NOT EXISTS `distribucion_incentivos` (
  `companyid` varchar(5) DEFAULT NULL,
  `empleadoid` int DEFAULT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `depto` varchar(100) DEFAULT NULL,
  `ctabanco` varchar(50) DEFAULT NULL,
  `factor_base_incentivos` decimal(10,4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distribucion_porcentajes`
--

DROP TABLE IF EXISTS `distribucion_porcentajes`;
CREATE TABLE IF NOT EXISTS `distribucion_porcentajes` (
  `id` int NOT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `porcentaje` decimal(10,3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `efectividad_usuarios`
--

DROP TABLE IF EXISTS `efectividad_usuarios`;
CREATE TABLE IF NOT EXISTS `efectividad_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incentivo_id` int DEFAULT NULL,
  `agencia_id` int DEFAULT NULL,
  `tipo_producto` varchar(20) DEFAULT NULL,
  `sistema` varchar(20) DEFAULT NULL,
  `venta_mes` decimal(18,4) DEFAULT NULL,
  `empleadoid_bet` int DEFAULT NULL,
  `cedula_bet` varchar(20) DEFAULT NULL,
  `monto_cedula_bet` decimal(18,4) DEFAULT NULL,
  `porcentaje_cedula_bet` decimal(18,4) DEFAULT NULL,
  `empleadoid_net` int DEFAULT NULL,
  `cedula_net` varchar(20) DEFAULT NULL,
  `monto_cedula_net` decimal(18,4) DEFAULT NULL,
  `porcentaje_cedula_net` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

DROP TABLE IF EXISTS `empleados`;
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `companyid` int NOT NULL,
  `empleadoid` int NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `idposicion` int DEFAULT NULL,
  `posicion` varchar(100) DEFAULT NULL,
  `salariomensual` decimal(10,2) DEFAULT NULL,
  `iddepto` int DEFAULT NULL,
  `depto` varchar(100) DEFAULT NULL,
  `idciudad` int DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `idpais` int DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `ctabanco` varchar(50) DEFAULT NULL,
  `tipodocidentidad` varchar(10) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `sexo` varchar(10) DEFAULT NULL,
  `estadocivil` varchar(20) DEFAULT NULL,
  `nohijos` int DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `tel1` varchar(20) DEFAULT NULL,
  `tel2` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profesion1` varchar(100) DEFAULT NULL,
  `profesion2` varchar(100) DEFAULT NULL,
  `fechanacimiento` date DEFAULT NULL,
  `fechaingreso` date DEFAULT NULL,
  `fechasalida` date DEFAULT NULL,
  `iniciovacaciones` date DEFAULT NULL,
  `finalvacaciones` date DEFAULT NULL,
  `clienteid` int DEFAULT NULL,
  `codigovendedor` varchar(50) DEFAULT NULL,
  `chofer` tinyint(1) DEFAULT NULL,
  `bombero` tinyint(1) DEFAULT NULL,
  `creadopor` varchar(50) DEFAULT NULL,
  `modificadopor` varchar(50) DEFAULT NULL,
  `fechagrabado` datetime DEFAULT NULL,
  `fechamodificado` datetime DEFAULT NULL,
  `atributoprn` varchar(100) DEFAULT NULL,
  `idsucursalturno` int DEFAULT NULL,
  `moduloturno` varchar(100) DEFAULT NULL,
  `idturno` int DEFAULT NULL,
  `nocalcularsalario` tinyint(1) DEFAULT NULL,
  `turnorotativo` tinyint(1) DEFAULT NULL,
  `porcientocomision` decimal(5,2) DEFAULT NULL,
  `enporciento` tinyint(1) DEFAULT NULL,
  `cuenta` varchar(50) DEFAULT NULL,
  `cobrador` tinyint(1) DEFAULT NULL,
  `mozo` tinyint(1) DEFAULT NULL,
  `clavemozo` varchar(50) DEFAULT NULL,
  `lavador` tinyint(1) DEFAULT NULL,
  `idsistemaviejo` varchar(50) DEFAULT NULL,
  `viapago` varchar(50) DEFAULT NULL,
  `idcentrocosto` int DEFAULT NULL,
  `cuentanav` varchar(50) DEFAULT NULL,
  `idbanco` int DEFAULT NULL,
  `viapago_banco` varchar(50) DEFAULT NULL,
  `idcalendario` int DEFAULT NULL,
  `preaviso` tinyint(1) DEFAULT NULL,
  `cesantia` tinyint(1) DEFAULT NULL,
  `vacaciones` tinyint(1) DEFAULT NULL,
  `navidad` tinyint(1) DEFAULT NULL,
  `viapago_bancoemp` varchar(50) DEFAULT NULL,
  `tipocuenta` varchar(10) DEFAULT NULL,
  `cuentagastoinfotep` varchar(50) DEFAULT NULL,
  `cuentagastoriesgolaboral` varchar(50) DEFAULT NULL,
  `rutafoto` varchar(255) DEFAULT NULL,
  `enperiodo_prepost_natal` tinyint(1) DEFAULT NULL,
  `en_licencia_medica` tinyint(1) DEFAULT NULL,
  `tipo_empleado` varchar(50) DEFAULT NULL,
  `idplaza` int DEFAULT NULL,
  `doctor` tinyint(1) DEFAULT NULL,
  `distribucion_departamento` varchar(25) DEFAULT NULL,
  `aplica_incentivo` varchar(2) DEFAULT 'NO',
  `porcentaje_incentivo` decimal(6,4) DEFAULT NULL,
  `tipo_empleado_incentivo` enum('1','2','3','4') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `empleados_no_regularizados`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `empleados_no_regularizados`;
CREATE TABLE IF NOT EXISTS `empleados_no_regularizados` (
`cedula` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estacionalidad`
--

DROP TABLE IF EXISTS `estacionalidad`;
CREATE TABLE IF NOT EXISTS `estacionalidad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mes` int NOT NULL,
  `factor_base` decimal(10,4) NOT NULL DEFAULT '1.0100',
  `vigente` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `faltantes_bet`
--

DROP TABLE IF EXISTS `faltantes_bet`;
CREATE TABLE IF NOT EXISTS `faltantes_bet` (
  `faltante_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `abono` decimal(12,2) DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`faltante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `faltantes_net`
--

DROP TABLE IF EXISTS `faltantes_net`;
CREATE TABLE IF NOT EXISTS `faltantes_net` (
  `faltante_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `abono` decimal(12,2) DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`faltante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incentivo_temporal`
--

DROP TABLE IF EXISTS `incentivo_temporal`;
CREATE TABLE IF NOT EXISTS `incentivo_temporal` (
  `incentivo_id` int DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `tipo_producto` varchar(50) DEFAULT NULL,
  `sistema` varchar(50) DEFAULT NULL,
  `total_trimestre` decimal(18,2) DEFAULT NULL,
  `promedio_mensual` decimal(18,2) DEFAULT NULL,
  `venta_base` decimal(18,2) DEFAULT NULL,
  `venta_mes` decimal(18,2) DEFAULT NULL,
  `nivel` int DEFAULT NULL,
  `meta_incremental` decimal(18,2) DEFAULT NULL,
  `meta_plan` decimal(18,2) DEFAULT NULL,
  `cumplimiento` decimal(10,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incentivo_temporal_c`
--

DROP TABLE IF EXISTS `incentivo_temporal_c`;
CREATE TABLE IF NOT EXISTS `incentivo_temporal_c` (
  `incentivo_id` int NOT NULL AUTO_INCREMENT,
  `anio` int DEFAULT NULL,
  `mes` int DEFAULT NULL,
  PRIMARY KEY (`incentivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mar_ventas`
--

DROP TABLE IF EXISTS `mar_ventas`;
CREATE TABLE IF NOT EXISTS `mar_ventas` (
  `VentaID` int NOT NULL AUTO_INCREMENT,
  `Dia` varchar(20) DEFAULT NULL,
  `EDiFecha` date DEFAULT NULL,
  `GrupoID` int DEFAULT NULL,
  `GruNombre` varchar(100) DEFAULT NULL,
  `RiferoID` int DEFAULT NULL,
  `RifNombre` varchar(100) DEFAULT NULL,
  `BancaID` int DEFAULT NULL,
  `BanNombre` varchar(100) DEFAULT NULL,
  `BanContacto` varchar(100) DEFAULT NULL,
  `BanComisionQ` decimal(12,2) DEFAULT NULL,
  `BanComisionP` decimal(12,2) DEFAULT NULL,
  `BanComisionT` decimal(12,2) DEFAULT NULL,
  `BanVComision` decimal(12,2) DEFAULT NULL,
  `PagoDeOtra` decimal(12,2) DEFAULT NULL,
  `PagoEnOtra` decimal(12,2) DEFAULT NULL,
  `PagosPendiente` decimal(12,2) DEFAULT NULL,
  `DiasPendiente` int DEFAULT NULL,
  `VTarjComisionBanca` decimal(12,2) DEFAULT NULL,
  `VTarjComision` decimal(12,2) DEFAULT NULL,
  `VTarjetas` decimal(12,2) DEFAULT NULL,
  `CVQuinielas` int DEFAULT NULL,
  `VQuinielas` decimal(12,2) DEFAULT NULL,
  `CVPales` int DEFAULT NULL,
  `CVTripletas` int DEFAULT NULL,
  `VPales` decimal(12,2) DEFAULT NULL,
  `VTripletas` decimal(12,2) DEFAULT NULL,
  `CPrimero` int DEFAULT NULL,
  `CSegundo` int DEFAULT NULL,
  `CTercero` int DEFAULT NULL,
  `CPales` int DEFAULT NULL,
  `CTripletas` int DEFAULT NULL,
  `MPrimero` decimal(12,2) DEFAULT NULL,
  `MSegundo` decimal(12,2) DEFAULT NULL,
  `MTercero` decimal(12,2) DEFAULT NULL,
  `MPales` decimal(12,2) DEFAULT NULL,
  `MTripletas` decimal(12,2) DEFAULT NULL,
  `RifDescuento` decimal(12,2) DEFAULT NULL,
  `ISRRetenido` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`VentaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `niveles`
--

DROP TABLE IF EXISTS `niveles`;
CREATE TABLE IF NOT EXISTS `niveles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_producto` varchar(50) DEFAULT NULL,
  `nivel` int NOT NULL,
  `rango_min` decimal(18,2) DEFAULT NULL,
  `rango_max` decimal(18,2) DEFAULT NULL,
  `incremento_porcentaje` decimal(10,4) DEFAULT NULL,
  `incremento_fijo` decimal(18,2) DEFAULT NULL,
  `prioridad` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_aotra_empresa_bet`
--

DROP TABLE IF EXISTS `pagos_aotra_empresa_bet`;
CREATE TABLE IF NOT EXISTS `pagos_aotra_empresa_bet` (
  `pago_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `importe` decimal(12,2) DEFAULT NULL,
  `pagado_consorcio_id` varchar(25) DEFAULT NULL,
  `plataforma_pago` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pago_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_aotra_empresa_net`
--

DROP TABLE IF EXISTS `pagos_aotra_empresa_net`;
CREATE TABLE IF NOT EXISTS `pagos_aotra_empresa_net` (
  `pago_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(50) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(18,4) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pagado_a_consorcio_id` varchar(25) DEFAULT NULL,
  `plataforma` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pago_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_misma_empresa_bet`
--

DROP TABLE IF EXISTS `pagos_misma_empresa_bet`;
CREATE TABLE IF NOT EXISTS `pagos_misma_empresa_bet` (
  `pago_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pagado_agencia_id` varchar(25) DEFAULT NULL,
  `plataforma_pago` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pago_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_misma_empresa_net`
--

DROP TABLE IF EXISTS `pagos_misma_empresa_net`;
CREATE TABLE IF NOT EXISTS `pagos_misma_empresa_net` (
  `pago_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(50) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(18,4) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pagado_agencia_id` varchar(25) DEFAULT NULL,
  `plataforma` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pago_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_porotra_empresa_bet`
--

DROP TABLE IF EXISTS `pagos_porotra_empresa_bet`;
CREATE TABLE IF NOT EXISTS `pagos_porotra_empresa_bet` (
  `pago_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pagado_consorcio_id` varchar(25) DEFAULT NULL,
  `plataforma_pago` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pago_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_porotra_empresa_net`
--

DROP TABLE IF EXISTS `pagos_porotra_empresa_net`;
CREATE TABLE IF NOT EXISTS `pagos_porotra_empresa_net` (
  `pago_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(50) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(18,4) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pagado_consorcio_id` varchar(25) DEFAULT NULL,
  `plataforma` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pago_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_incentivos`
--

DROP TABLE IF EXISTS `pago_incentivos`;
CREATE TABLE IF NOT EXISTS `pago_incentivos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incentivo_id` int DEFAULT NULL,
  `agencia_id` int DEFAULT NULL,
  `tipo_producto` varchar(20) DEFAULT NULL,
  `sistema` varchar(20) DEFAULT NULL,
  `empleadoid` int DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `porcentaje_cedula` decimal(18,4) DEFAULT NULL,
  `monto_agente` decimal(18,4) DEFAULT NULL,
  `monto_incentivo` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_incentivos_admin`
--

DROP TABLE IF EXISTS `pago_incentivos_admin`;
CREATE TABLE IF NOT EXISTS `pago_incentivos_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incentivo_id` int DEFAULT NULL,
  `companyid` int DEFAULT NULL,
  `empleadoid` int DEFAULT NULL,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `porcentaje` decimal(18,4) DEFAULT NULL,
  `tradicional` decimal(18,4) DEFAULT NULL,
  `no_tradicional` decimal(18,4) DEFAULT NULL,
  `recarga` decimal(18,4) DEFAULT NULL,
  `paquetico` decimal(18,4) DEFAULT NULL,
  `total` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_incentivos_coordinador`
--

DROP TABLE IF EXISTS `pago_incentivos_coordinador`;
CREATE TABLE IF NOT EXISTS `pago_incentivos_coordinador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incentivo_id` int DEFAULT NULL,
  `companyid` int DEFAULT NULL,
  `empleadoid` int DEFAULT NULL,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `porcentaje` decimal(10,4) DEFAULT NULL,
  `total` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetico_net`
--

DROP TABLE IF EXISTS `paquetico_net`;
CREATE TABLE IF NOT EXISTS `paquetico_net` (
  `paquetico_id` int NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `monto_pagado` decimal(12,2) DEFAULT NULL,
  `cargo_servicio` decimal(12,2) DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `proveedor_nombre` varchar(250) DEFAULT NULL,
  `proveedor_id` varchar(25) DEFAULT NULL,
  `distribuidora_id` varchar(25) DEFAULT NULL,
  `distribuidora_nombre` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`paquetico_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_agencia`
--

DROP TABLE IF EXISTS `plan_agencia`;
CREATE TABLE IF NOT EXISTS `plan_agencia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agencia_id` varchar(25) DEFAULT NULL,
  `nombre_agencia` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_agencias_distribucion`
--

DROP TABLE IF EXISTS `plan_agencias_distribucion`;
CREATE TABLE IF NOT EXISTS `plan_agencias_distribucion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incentivo_id` int DEFAULT NULL,
  `agencia_id` int DEFAULT NULL,
  `tipo_producto` varchar(20) DEFAULT NULL,
  `sistema` varchar(20) DEFAULT NULL,
  `venta_mes` decimal(18,4) DEFAULT NULL,
  `venta_base` decimal(18,4) DEFAULT NULL,
  `excedente` decimal(18,4) DEFAULT NULL,
  `porcentaje_agente` decimal(18,4) DEFAULT NULL,
  `porcentaje_coordinador` decimal(18,4) DEFAULT NULL,
  `porcentaje_administrativo` decimal(18,4) DEFAULT NULL,
  `monto_agente` decimal(18,4) DEFAULT NULL,
  `monto_coordinador` decimal(18,4) DEFAULT NULL,
  `monto_administrativo` decimal(18,4) DEFAULT NULL,
  `total_distribucion` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `porcentaje_administrativo`
--

DROP TABLE IF EXISTS `porcentaje_administrativo`;
CREATE TABLE IF NOT EXISTS `porcentaje_administrativo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT NULL,
  `empleado_id` int DEFAULT NULL,
  `porcentaje` decimal(8,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `premios_bet`
--

DROP TABLE IF EXISTS `premios_bet`;
CREATE TABLE IF NOT EXISTS `premios_bet` (
  `premio_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`premio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `premios_net`
--

DROP TABLE IF EXISTS `premios_net`;
CREATE TABLE IF NOT EXISTS `premios_net` (
  `premio_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`premio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recargas_bet`
--

DROP TABLE IF EXISTS `recargas_bet`;
CREATE TABLE IF NOT EXISTS `recargas_bet` (
  `recarga_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `distribuidora_id` varchar(25) DEFAULT NULL,
  `distribuidora_nombre` varchar(25) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `proveedor_id` varchar(25) DEFAULT NULL,
  `proveedor_nombre` varchar(25) DEFAULT NULL,
  `comision` decimal(12,2) DEFAULT NULL,
  `comision_supervisor` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`recarga_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recargas_net`
--

DROP TABLE IF EXISTS `recargas_net`;
CREATE TABLE IF NOT EXISTS `recargas_net` (
  `recarga_id` int NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `proveedor_nombre` varchar(250) DEFAULT NULL,
  `proveedor_id` varchar(25) DEFAULT NULL,
  `distribuidora_id` varchar(25) DEFAULT NULL,
  `distribuidora_nombre` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`recarga_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_empleo`
--

DROP TABLE IF EXISTS `solicitudes_empleo`;
CREATE TABLE IF NOT EXISTS `solicitudes_empleo` (
  `solicitud_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `apellidos` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombres` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apodo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cedula_pasaporte` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `lugar_nacimiento` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nacionalidad` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edad` smallint UNSIGNED DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_residencial` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` enum('SOLTERO','CASADO','DIVORCIADO','UNION_LIBRE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_sangre` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estudia_actualmente` tinyint(1) DEFAULT NULL,
  `que_estudia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_estudio` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domina_computadora` tinyint(1) NOT NULL DEFAULT '0',
  `domina_fax` tinyint(1) NOT NULL DEFAULT '0',
  `domina_impresora` tinyint(1) NOT NULL DEFAULT '0',
  `domina_scanner` tinyint(1) NOT NULL DEFAULT '0',
  `domina_maquinas_elec` tinyint(1) NOT NULL DEFAULT '0',
  `domina_calculadoras` tinyint(1) NOT NULL DEFAULT '0',
  `ha_trabajado_antes_en_empresa` tinyint(1) DEFAULT NULL,
  `familiares_en_empresa` tinyint(1) DEFAULT NULL,
  `competencias_laborales` text COLLATE utf8mb4_unicode_ci,
  `fortalezas_profesionales` text COLLATE utf8mb4_unicode_ci,
  `impedimento_sab_dom_fer` tinyint(1) DEFAULT NULL,
  `problemas_salud_detalle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `afp` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ars` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sabe_conducir` tinyint(1) DEFAULT NULL,
  `licencia_categoria` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `licencia_vencimiento` date DEFAULT NULL,
  `fecha_disponible` date DEFAULT NULL,
  `acepta_cambio_horario` tinyint(1) DEFAULT NULL,
  `acepta_cambio_lugar` tinyint(1) DEFAULT NULL,
  `disp_diurno` tinyint(1) NOT NULL DEFAULT '0',
  `disp_nocturno` tinyint(1) NOT NULL DEFAULT '0',
  `disp_rotativo` tinyint(1) NOT NULL DEFAULT '0',
  `disp_domingos` tinyint(1) NOT NULL DEFAULT '0',
  `disp_feriados` tinyint(1) NOT NULL DEFAULT '0',
  `cuenta_banco_caribe_bhd` tinyint(1) DEFAULT NULL,
  `incluido_buro_credito` tinyint(1) DEFAULT NULL,
  `referido_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referido_parentesco` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergencia_contacto_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergencia_parentesco` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergencia_telefonos` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medio_informo_vacante` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_firma` date DEFAULT NULL,
  `seleccionado` tinyint(1) DEFAULT NULL,
  `puesto_aplicado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_trabajo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `salario` decimal(12,2) DEFAULT NULL,
  `aprobado_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`solicitud_id`),
  KEY `idx_solicitud_cedula` (`cedula_pasaporte`),
  KEY `idx_solicitud_fecha` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_educacion`
--

DROP TABLE IF EXISTS `solicitud_educacion`;
CREATE TABLE IF NOT EXISTS `solicitud_educacion` (
  `educacion_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `solicitud_id` bigint UNSIGNED NOT NULL,
  `nivel` enum('PRIMARIA','SECUNDARIA','UNIVERSITARIO','TECNICO','POST_GRADO','MAESTRIA','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OTRO',
  `centro_docente` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_termino` date DEFAULT NULL,
  `nivel_alcanzado` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`educacion_id`),
  KEY `idx_edu_solicitud` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_empleos`
--

DROP TABLE IF EXISTS `solicitud_empleos`;
CREATE TABLE IF NOT EXISTS `solicitud_empleos` (
  `empleo_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `solicitud_id` bigint UNSIGNED NOT NULL,
  `empresa_nombre` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `puesto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiempo_en_puesto` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_desde` date DEFAULT NULL,
  `fecha_hasta` date DEFAULT NULL,
  `ultimo_sueldo` decimal(12,2) DEFAULT NULL,
  `funciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_salida` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisor_inmediato` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`empleo_id`),
  KEY `idx_emp_solicitud` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_familiares`
--

DROP TABLE IF EXISTS `solicitud_familiares`;
CREATE TABLE IF NOT EXISTS `solicitud_familiares` (
  `familiar_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `solicitud_id` bigint UNSIGNED NOT NULL,
  `parentesco` enum('PADRE','MADRE','CONYUGE','HIJO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OTRO',
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edad` smallint UNSIGNED DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocupacion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_trabajo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`familiar_id`),
  KEY `idx_fam_solicitud` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_referencias_laborales`
--

DROP TABLE IF EXISTS `solicitud_referencias_laborales`;
CREATE TABLE IF NOT EXISTS `solicitud_referencias_laborales` (
  `ref_lab_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `solicitud_id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocupacion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_trabajo` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ref_lab_id`),
  KEY `idx_reflab_solicitud` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_referencias_personales`
--

DROP TABLE IF EXISTS `solicitud_referencias_personales`;
CREATE TABLE IF NOT EXISTS `solicitud_referencias_personales` (
  `ref_per_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `solicitud_id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocupacion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_trabajo` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector_residencia` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ref_per_id`),
  KEY `idx_refper_solicitud` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens`
--

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int NOT NULL,
  `token` varchar(100) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_flash_bet`
--

DROP TABLE IF EXISTS `ventas_flash_bet`;
CREATE TABLE IF NOT EXISTS `ventas_flash_bet` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo` varchar(50) DEFAULT NULL,
  `banca` varchar(100) DEFAULT NULL,
  `numero_externo` int DEFAULT NULL,
  `venta_loteria` decimal(18,4) DEFAULT NULL,
  `comision_loteria` decimal(18,6) DEFAULT NULL,
  `premios_pagado` decimal(18,4) DEFAULT NULL,
  `venta_recarga` decimal(18,4) DEFAULT NULL,
  `comision_recarga` decimal(18,6) DEFAULT NULL,
  `ventas_no_tradicional` decimal(18,4) DEFAULT NULL,
  `premios_pagados_no_tradicional` decimal(18,4) DEFAULT NULL,
  `comision_loterias_lot_no_tradicional` decimal(18,6) DEFAULT NULL,
  `comision_gobierno` decimal(18,6) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_producto_bet`
--

DROP TABLE IF EXISTS `ventas_producto_bet`;
CREATE TABLE IF NOT EXISTS `ventas_producto_bet` (
  `venta_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `comision` decimal(12,2) DEFAULT NULL,
  `comision_supervisor` decimal(12,2) DEFAULT NULL,
  `numero_sorteo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`venta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_producto_net`
--

DROP TABLE IF EXISTS `ventas_producto_net`;
CREATE TABLE IF NOT EXISTS `ventas_producto_net` (
  `venta_id` int NOT NULL DEFAULT '0',
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vt_usuarios_bet`
--

DROP TABLE IF EXISTS `vt_usuarios_bet`;
CREATE TABLE IF NOT EXISTS `vt_usuarios_bet` (
  `vt_usuario_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`vt_usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vt_usuarios_net`
--

DROP TABLE IF EXISTS `vt_usuarios_net`;
CREATE TABLE IF NOT EXISTS `vt_usuarios_net` (
  `vt_usuario_id` int NOT NULL AUTO_INCREMENT,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`vt_usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_usuarios_union`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_usuarios_union`;
CREATE TABLE IF NOT EXISTS `vw_usuarios_union` (
`consorcio_id` varchar(5)
,`agencia_id` varchar(25)
,`cedula` varchar(50)
,`tipo` varchar(45)
,`producto_id` varchar(50)
,`monto` decimal(34,2)
,`fecha` date
,`origen` varchar(3)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_ventas_por_producto`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vw_ventas_por_producto`;
CREATE TABLE IF NOT EXISTS `vw_ventas_por_producto` (
`producto_id` varchar(12)
,`descripcion_producto` varchar(255)
,`tipo_producto` varchar(255)
,`total_ventas` decimal(12,2)
,`a` double
);

-- --------------------------------------------------------

--
-- Estructura para la vista `empleados_no_regularizados`
--
DROP TABLE IF EXISTS `empleados_no_regularizados`;

DROP VIEW IF EXISTS `empleados_no_regularizados`;
CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `empleados_no_regularizados`  AS SELECT DISTINCT `vt_usuarios_bet`.`cedula` AS `cedula` FROM `vt_usuarios_bet` WHERE (`vt_usuarios_bet`.`cedula` in (select `empleados`.`cedula` from `empleados` where (length(`empleados`.`cedula`) = 11)) is false AND (`vt_usuarios_bet`.`cedula` <> ''))union all select distinct `vt_usuarios_net`.`cedula` AS `cedula` from `vt_usuarios_net` where (`vt_usuarios_net`.`cedula` in (select `empleados`.`cedula` from `empleados` where (length(`empleados`.`cedula`) = 11)) is false and (`vt_usuarios_net`.`cedula` <> ''))  ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_usuarios_union`
--
DROP TABLE IF EXISTS `vw_usuarios_union`;

DROP VIEW IF EXISTS `vw_usuarios_union`;
CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vw_usuarios_union`  AS SELECT max(`vt_usuarios_bet`.`consorcio_id`) AS `consorcio_id`, max(`vt_usuarios_bet`.`agencia_id`) AS `agencia_id`, `vt_usuarios_bet`.`cedula` AS `cedula`, `vt_usuarios_bet`.`tipo` AS `tipo`, `vt_usuarios_bet`.`producto_id` AS `producto_id`, sum(`vt_usuarios_bet`.`monto`) AS `monto`, max(`vt_usuarios_bet`.`fecha`) AS `fecha`, 'BET' AS `origen` FROM `vt_usuarios_bet` WHERE ((`vt_usuarios_bet`.`cedula` is null) OR (trim(`vt_usuarios_bet`.`cedula`) = '')) GROUP BY `vt_usuarios_bet`.`producto_id`, `vt_usuarios_bet`.`tipo`, `vt_usuarios_bet`.`cedula`union all select max(`vt_usuarios_net`.`consorcio_id`) AS `consorcio_id`,max(`vt_usuarios_net`.`agencia_id`) AS `agencia_id`,`vt_usuarios_net`.`cedula` AS `cedula`,`vt_usuarios_net`.`tipo` AS `tipo`,`vt_usuarios_net`.`producto_id` AS `producto_id`,sum(`vt_usuarios_net`.`monto`) AS `monto`,max(`vt_usuarios_net`.`fecha`) AS `fecha`,'NET' AS `origen` from `vt_usuarios_net` where ((`vt_usuarios_net`.`cedula` is null) or (trim(`vt_usuarios_net`.`cedula`) = '')) group by `vt_usuarios_net`.`producto_id`,`vt_usuarios_net`.`tipo`,`vt_usuarios_net`.`cedula`  ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_ventas_por_producto`
--
DROP TABLE IF EXISTS `vw_ventas_por_producto`;

DROP VIEW IF EXISTS `vw_ventas_por_producto`;
CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vw_ventas_por_producto`  AS SELECT `c`.`producto_id` AS `producto_id`, `c`.`descripcion` AS `descripcion_producto`, `c`.`tipo` AS `tipo_producto`, `v`.`monto` AS `total_ventas`, sum(`c`.`tipo`) AS `a` FROM ((select `vt_usuarios_net`.`producto_id` AS `producto_id`,`vt_usuarios_net`.`monto` AS `monto` from `vt_usuarios_net` union all select `vt_usuarios_bet`.`producto_id` AS `producto_id`,`vt_usuarios_bet`.`monto` AS `monto` from `vt_usuarios_bet`) `v` left join `catalogo_juegos` `c` on((`c`.`producto_id` = `v`.`producto_id`))) GROUP BY `c`.`producto_id`, `c`.`descripcion`, `c`.`tipo` ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `solicitud_educacion`
--
ALTER TABLE `solicitud_educacion`
  ADD CONSTRAINT `fk_edu_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_empleo` (`solicitud_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitud_empleos`
--
ALTER TABLE `solicitud_empleos`
  ADD CONSTRAINT `fk_emp_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_empleo` (`solicitud_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitud_familiares`
--
ALTER TABLE `solicitud_familiares`
  ADD CONSTRAINT `fk_fam_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_empleo` (`solicitud_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitud_referencias_laborales`
--
ALTER TABLE `solicitud_referencias_laborales`
  ADD CONSTRAINT `fk_reflab_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_empleo` (`solicitud_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitud_referencias_personales`
--
ALTER TABLE `solicitud_referencias_personales`
  ADD CONSTRAINT `fk_refper_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_empleo` (`solicitud_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
