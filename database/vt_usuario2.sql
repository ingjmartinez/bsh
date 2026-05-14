-- Active: 1761167754786@@127.0.0.1@3306@crm
-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 21-10-2025 a las 01:02:03
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Base de datos: `u359284306_joselito`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vt_usuario2`
--

CREATE TABLE `vt_usuario_bet` (
    `vt_usuario_id` int(11) NOT NULL DEFAULT 0,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `cedula` varchar(50) DEFAULT NULL,
    `tipo` varchar(45) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

CREATE TABLE `vt_usuario_net` (
    `vt_usuario_id` int(11) NOT NULL DEFAULT 0,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `cedula` varchar(50) DEFAULT NULL,
    `tipo` varchar(45) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

COMMIT;

CREATE TABLE `faltantes_bet` (
    `faltante_id` int(11) NOT NULL DEFAULT 0,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `identificacion` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `abono` DECIMAL(12, 2) DEFAULT NULL,
    `balance` DECIMAL(12, 2) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `faltantes_net` (
    `faltante_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `identificacion` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `abono` DECIMAL(12, 2) DEFAULT NULL,
    `balance` DECIMAL(12, 2) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `ventas_producto_bet` (
    `venta_id` int(11) NOT NULL DEFAULT 0,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `comision` decimal(12, 2) DEFAULT NULL,
    `comision_supervisor` decimal(12, 2) DEFAULT NULL,
    `numero_sorteo` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `ventas_producto_net` (
    `venta_id` int(11) NOT NULL DEFAULT 0,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `recargas_bet` (
    `recarga_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `distribuidora_id` varchar(25) DEFAULT NULL,
    `distribuidora_nombre` varchar(25) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `proveedor_id` varchar(25) DEFAULT NULL,
    `proveedor_nombre` varchar(25) DEFAULT NULL,
    `comision` decimal(12, 2) DEFAULT NULL,
    `comision_supervisor` decimal(12, 2) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `premios_bet` (
    `premio_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `pagos_misma_empresa_bet` (
    `pago_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `pagado_agencia_id` varchar(25) DEFAULT NULL,
    `plataforma_pago` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `pagos_aotra_empresa_bet` (
    `pago_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `importe` decimal(12, 2) DEFAULT NULL,
    `pagado_consorcio_id` varchar(25) DEFAULT NULL,
    `plataforma_pago` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `pagos_porotra_empresa_bet` (
    `pago_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `fecha` DATE DEFAULT NULL,
    `pagado_consorcio_id` varchar(25) DEFAULT NULL,
    `plataforma_pago` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `mar_ventas` (
    `VentaID` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `Dia` varchar(20) DEFAULT NULL,
    `EDiFecha` DATE DEFAULT NULL,
    `GrupoID` int(11) DEFAULT NULL,
    `GruNombre` varchar(100) DEFAULT NULL,
    `RiferoID` int(11) DEFAULT NULL,
    `RifNombre` varchar(100) DEFAULT NULL,
    `BancaID` int(11) DEFAULT NULL,
    `BanNombre` varchar(100) DEFAULT NULL,
    `BanContacto` varchar(100) DEFAULT NULL,
    `BanComisionQ` decimal(12, 2) DEFAULT NULL,
    `BanComisionP` decimal(12, 2) DEFAULT NULL,
    `BanComisionT` decimal(12, 2) DEFAULT NULL,
    `BanVComision` decimal(12, 2) DEFAULT NULL,
    `PagoDeOtra` decimal(12, 2) DEFAULT NULL,
    `PagoEnOtra` decimal(12, 2) DEFAULT NULL,
    `PagosPendiente` decimal(12, 2) DEFAULT NULL,
    `DiasPendiente` int(11) DEFAULT NULL,
    `VTarjComisionBanca` decimal(12, 2) DEFAULT NULL,
    `VTarjComision` decimal(12, 2) DEFAULT NULL,
    `VTarjetas` decimal(12, 2) DEFAULT NULL,
    `CVQuinielas` int(11) DEFAULT NULL,
    `VQuinielas` decimal(12, 2) DEFAULT NULL,
    `CVPales` int(11) DEFAULT NULL,
    `CVTripletas` int(11) DEFAULT NULL,
    `VPales` decimal(12, 2) DEFAULT NULL,
    `VTripletas` decimal(12, 2) DEFAULT NULL,
    `CPrimero` int(11) DEFAULT NULL,
    `CSegundo` int(11) DEFAULT NULL,
    `CTercero` int(11) DEFAULT NULL,
    `CPales` int(11) DEFAULT NULL,
    `CTripletas` int(11) DEFAULT NULL,
    `MPrimero` decimal(12, 2) DEFAULT NULL,
    `MSegundo` decimal(12, 2) DEFAULT NULL,
    `MTercero` decimal(12, 2) DEFAULT NULL,
    `MPales` decimal(12, 2) DEFAULT NULL,
    `MTripletas` decimal(12, 2) DEFAULT NULL,
    `RifDescuento` decimal(12, 2) DEFAULT NULL,
    `ISRRetenido` decimal(12, 2) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `paquetico_net` (
  `paquetico_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `fecha` DATE DEFAULT NULL,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `monto_pagado` decimal(12, 2) DEFAULT NULL,
  `cargo_servicio` decimal(12, 2) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `proveedor_nombre` varchar(250) DEFAULT NULL,
  `proveedor_id` varchar(25) DEFAULT NULL,
  `distribuidora_id` varchar(25) DEFAULT NULL,
  `distribuidora_nombre` varchar(250) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `recargas_net` (
  `recarga_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `fecha` DATE DEFAULT NULL,
  `consorcio_id` varchar(5) DEFAULT NULL,
  `producto_id` varchar(50) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `agencia_id` varchar(25) DEFAULT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `monto` decimal(12, 2) DEFAULT NULL,
  `proveedor_nombre` varchar(250) DEFAULT NULL,
  `proveedor_id` varchar(25) DEFAULT NULL,
  `distribuidora_id` varchar(25) DEFAULT NULL,
  `distribuidora_nombre` varchar(250) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE empleados (
    companyid INT,
    empleadoid INT PRIMARY KEY,
    nombres VARCHAR(100),
    apellidos VARCHAR(100),
    idposicion INT,
    posicion VARCHAR(100),
    salariomensual DECIMAL(10, 2),
    iddepto INT,
    depto VARCHAR(100),
    idciudad INT,
    ciudad VARCHAR(100),
    idpais INT,
    pais VARCHAR(100),
    ctabanco VARCHAR(50),
    tipodocidentidad VARCHAR(10),
    cedula VARCHAR(20),
    sexo VARCHAR(10),
    estadocivil VARCHAR(20),
    nohijos INT,
    direccion VARCHAR(255),
    tel1 VARCHAR(20),
    tel2 VARCHAR(20),
    email VARCHAR(100),
    profesion1 VARCHAR(100),
    profesion2 VARCHAR(100),
    fechanacimiento DATE,
    fechaingreso DATE,
    fechasalida DATE,
    iniciovacaciones DATE,
    finalvacaciones DATE,
    clienteid INT,
    codigovendedor VARCHAR(50),
    chofer BOOLEAN,
    bombero BOOLEAN,
    creadopor VARCHAR(50),
    modificadopor VARCHAR(50),
    fechagrabado DATETIME,
    fechamodificado DATETIME,
    atributoprn VARCHAR(100),
    idsucursalturno INT,
    moduloturno VARCHAR(100),
    idturno INT,
    nocalcularsalario BOOLEAN,
    turnorotativo BOOLEAN,
    porcientocomision DECIMAL(5, 2),
    enporciento BOOLEAN,
    cuenta VARCHAR(50),
    cobrador BOOLEAN,
    mozo BOOLEAN,
    clavemozo VARCHAR(50),
    lavador BOOLEAN,
    idsistemaviejo VARCHAR(50),
    viapago VARCHAR(50),
    idcentrocosto INT,
    cuentanav VARCHAR(50),
    idbanco INT,
    viapago_banco VARCHAR(50),
    idcalendario INT,
    preaviso BOOLEAN,
    cesantia BOOLEAN,
    vacaciones BOOLEAN,
    navidad BOOLEAN,
    viapago_bancoemp VARCHAR(50),
    tipocuenta VARCHAR(10),
    cuentagastoinfotep VARCHAR(50),
    cuentagastoriesgolaboral VARCHAR(50),
    rutafoto VARCHAR(255),
    enperiodo_prepost_natal BOOLEAN,
    en_licencia_medica BOOLEAN,
    tipo_empleado VARCHAR(50),
    idplaza INT,
    doctor BOOLEAN
);

CREATE TABLE `asistencias_bet` (
    `asistencia_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `fecha` DATE DEFAULT NULL,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `usuario` varchar(50) DEFAULT NULL,
    `cedula` varchar(50) DEFAULT NULL,
    `primer_login` DATETIME DEFAULT NULL,
    `ultimo_login` DATETIME DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `asistencias_net` (
    `asistencia_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `consorcio` varchar(150) DEFAULT NULL,
    `agencia` varchar(25) DEFAULT NULL,
    `usuario` varchar(50) DEFAULT NULL,
    `entrada` DATETIME DEFAULT NULL,
    `salida` DATETIME DEFAULT NULL,
    `identificacion` varchar(100) DEFAULT NULL,
    `username` varchar(250) DEFAULT NULL,
    `banca` varchar(250) DEFAULT NULL,
    `terminal` varchar(250) DEFAULT NULL,
    `salida_inactividad` varchar(250) DEFAULT NULL,
    `turno` varchar(250) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS estacionalidad;
CREATE TABLE estacionalidad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mes INT NOT NULL,
    factor_base DECIMAL(10, 4) NOT NULL DEFAULT 1.01,
    vigente TINYINT(1) DEFAULT 1
);
INSERT INTO estacionalidad (mes, factor_base) 
VALUES 
 (1, 0.83),
 (2, 0.94),
 (3, 1.03),
 (4, 0.91),
 (5, 0.99),
 (6, 0.99),
 (7, 1.07),
 (8, 1.01),
 (9, 1.01),
 (10, 1.08),
 (11, 0.97),
 (12, 1.22);

DROP TABLE IF EXISTS niveles;
CREATE TABLE niveles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_producto VARCHAR(50),
    nivel INT NOT NULL,
    rango_min DECIMAL(18,2),
    rango_max DECIMAL(18,2),
    incremento_porcentaje DECIMAL(10,4),   -- Ej: 0.20 = 20%
    incremento_fijo DECIMAL(18,2),         -- Ej: 50000 para tradicional
    prioridad INT DEFAULT 1                 -- Para romper empates
);
INSERT INTO niveles (tipo_producto, nivel, rango_min, rango_max, incremento_porcentaje, incremento_fijo)
VALUES
('Tradicional', 1, 1, 600000, NULL, 50000),
('Tradicional', 2, 600001, 100000, NULL, 50000),
('Tradicional', 3, 100001, 200000, NULL, 50000),
('Tradicional', 4, 200001, 300000, 0.20, NULL),
('Tradicional', 5, 300001, 400000, 0.15, NULL),
('Tradicional', 6, 400001, 500000, 0.15, NULL),
('Tradicional', 7, 500001, 700000, 0.12, NULL),
('Tradicional', 8, 700001, 900000, 0.10, NULL),
('Tradicional', 9, 900001, 1000000, 0.10, NULL),
('Tradicional', 10, 1000001, 1200000, 0.08, NULL),
('Tradicional', 11, 1200001, 1400000, 0.08, NULL),
('Tradicional', 12, 1400001, 1600000, 0.07, NULL),
('Tradicional', 13, 1600001, 1800000, 0.06, NULL),
('Tradicional', 14, 1800001, 2000000, 0.05, NULL),
('Tradicional', 15, 2000001, 2200000, 0.05, NULL),
('Tradicional', 16, 2201001, 2600000, 0.05, NULL),
('Tradicional', 17, 2601001, 3000000, 0.04, NULL),
('Tradicional', 18, 3001001, 999999999, 0.04, NULL);

/*
1			1			16000
2			16001		30000
3			30001		60000
4			60001		100000
5			100001		200000
6			200001		300000
7			300001		400000
8			400001		500000
9			500001		600000
10			600001		más
*/
INSERT INTO niveles (tipo_producto, nivel, rango_min, rango_max, incremento_porcentaje, incremento_fijo)
VALUES
('No Tradicional', 1, 1, 16000, NULL, 15000),
('No Tradicional', 2, 16001, 30000, NULL, 15000),
('No Tradicional', 3, 30001, 60000, NULL, 15000),
('No Tradicional', 4, 60001, 100000, 0.25, NULL),
('No Tradicional', 5, 100001, 200000, 0.20, NULL),
('No Tradicional', 6, 200001, 300000, 0.15, NULL),
('No Tradicional', 7, 300001, 400000, 0.15, NULL),
('No Tradicional', 8, 400001, 500000, 0.10, NULL),
('No Tradicional', 9, 500001, 600000, 0.09, NULL),
('No Tradicional', 10, 600001, 999999999, 0.09, NULL);

/*
1				1			10000
2				10001		20000
3				20001		30000
4				30001		60000
5				60001		100000
6				100001		Mas
*/
INSERT INTO niveles (tipo_producto, nivel, rango_min, rango_max, incremento_porcentaje, incremento_fijo)
VALUES
('Recargas', 1, 1, 10000, NULL, 10000),
('Recargas', 2, 10001, 20000, NULL, 10000),
('Recargas', 3, 20001, 30000, NULL, 10000),
('Recargas', 4, 30001, 60000, 0.15, NULL),
('Recargas', 5, 60001, 100000, 0.10, NULL),
('Recargas', 6, 100001, 999999999, 0.08, NULL);

INSERT INTO niveles (tipo_producto, nivel, rango_min, rango_max, incremento_porcentaje, incremento_fijo)
VALUES
('Paquetico', 1, 1, 10000, NULL, 10000),
('Paquetico', 2, 10001, 20000, NULL, 10000),
('Paquetico', 3, 20001, 30000, NULL, 10000),
('Paquetico', 4, 30001, 60000, 0.15, NULL),
('Paquetico', 5, 60001, 100000, 0.10, NULL),
('Paquetico', 6, 100001, 999999999, 0.08, NULL);


CREATE TABLE incentivo_temporal_c (
    incentivo_id INT PRIMARY KEY AUTO_INCREMENT,
    anio INT,
    mes INT
);
DROP TABLE IF EXISTS incentivo_temporal;
CREATE TABLE incentivo_temporal (
    incentivo_id INT,
    agencia_id VARCHAR(25),
    tipo_producto VARCHAR(50),
    sistema VARCHAR(50),
    total_trimestre DECIMAL(18,2),
    promedio_mensual DECIMAL(18,2),
    venta_base DECIMAL(18,2),
    venta_mes DECIMAL(18,2),
    nivel INT,
    meta_incremental DECIMAL(18,2)
);

CREATE TABLE Distribucion_Administrativa (
    Departamento VARCHAR(50),
    Nombre VARCHAR(50),
    Apellido VARCHAR(50),
    Cedula INT,
    promedio_mensual DECIMAL(18,2),
    venta_base DECIMAL(18,2),
    venta_mes_anterior DECIMAL(18,2),
    ID_Empleado INT,
    meta_incremental DECIMAL(18,2)
);

create table distribucion_incentivos(
    companyid varchar(5),
    empleadoid int,
    nombres varchar(100),
    apellidos varchar(100),
    cedula varchar(20),
    depto varchar(100),
    ctabanco varchar(50),
    factor_base_incentivos decimal(10,4)
);

CREATE TABLE plan_agencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agencia_id VARCHAR(25),
    nombre_agencia VARCHAR(100)
);

SELECT 
    it.agencia_id,
    it.tipo_producto,
    it.sistema,
    FORMAT(it.venta_mes, 2, 'en_US') AS venta_mes,
    FORMAT(it.venta_base, 2, 'en_US') AS venta_base,
    CASE 
        WHEN it.venta_mes >= it.venta_base THEN 
            FORMAT(it.venta_mes - it.venta_base, 2, 'en_US')
        ELSE 
            CONCAT(
                'FALTA ',
                FORMAT( ((it.venta_base - it.venta_mes) / it.venta_base) * 100 , 2),
                '%'
            )
    END AS excedente,
    pa_agente.porcentaje AS porcentaje_agente,
    pa_coord.porcentaje AS porcentaje_coordinador,
    pa_admin.porcentaje AS porcentaje_Admin,
    CASE 
        WHEN it.venta_mes > it.venta_base THEN 
            FORMAT((it.venta_mes - it.venta_base) * pa_agente.porcentaje, 2, 'en_US')
        ELSE ''
    END AS monto_agente,
    CASE 
        WHEN it.venta_mes > it.venta_base THEN 
            FORMAT((it.venta_mes - it.venta_base) * pa_coord.porcentaje, 2, 'en_US')
        ELSE ''
    END AS monto_coordinador,
    CASE 
        WHEN it.venta_mes > it.venta_base THEN 
            FORMAT((it.venta_mes - it.venta_base) * pa_admin.porcentaje, 2, 'en_US')
        ELSE ''
    END AS monto_Admin
FROM incentivo_temporal it
LEFT JOIN distribucion_porcentajes pa_agente
       ON pa_agente.departamento = 'Agente'
      AND pa_agente.tipo = it.tipo_producto
LEFT JOIN distribucion_porcentajes pa_coord
       ON pa_coord.departamento = 'Coordinador'
      AND pa_coord.tipo = it.tipo_producto
LEFT JOIN distribucion_porcentajes pa_admin
       ON pa_admin.departamento = 'Admin'
      AND pa_admin.tipo = it.tipo_producto
INNER JOIN plan_agencia pa 
        ON CAST(TRIM(it.agencia_id) AS UNSIGNED) = pa.agencia_id
WHERE it.incentivo_id = 1
  AND it.venta_mes > 0;

  SELECT * FROM incentivo_temporal WHERE incentivo_id = 1

SELECT * FROM plan_agencias_distribucion

SELECT
    it.agencia_id,
    it.tipo_producto,
    it.venta_mes,
    -- Totales NET
    net.cedula_net,
    net.monto_net,
    ROUND((net.monto_net / it.venta_mes) * 100, 2) AS porcentaje_participacion_net,
    -- Totales BET
    bet.cedula_bet,
    bet.monto_bet,
    ROUND((bet.monto_bet / it.venta_mes) * 100, 2) AS porcentaje_participacion_bet
FROM incentivo_temporal it
INNER JOIN plan_agencia pa ON CAST(TRIM(it.agencia_id) AS UNSIGNED) = pa.agencia_id
-- AGRUPAMOS NET
LEFT JOIN (
    SELECT 
        agencia_id,
        MAX(cedula) AS cedula_net,
        SUM(monto) AS monto_net
    FROM vt_usuarios_net n
    INNER JOIN incentivo_temporal_c itc ON 1 = 1
    WHERE MONTH(n.fecha) = itc.mes AND YEAR(n.fecha) = itc.anio
    GROUP BY agencia_id
) net ON net.agencia_id = it.agencia_id
-- AGRUPAMOS BET
LEFT JOIN (
    SELECT 
        agencia_id,
        MAX(cedula) AS cedula_bet,
        SUM(monto) AS monto_bet
    FROM vt_usuarios_bet b
    INNER JOIN incentivo_temporal_c itc ON 1=1
    WHERE MONTH(b.fecha) = itc.mes AND YEAR(b.fecha) = itc.anio
    GROUP BY agencia_id
) bet ON bet.agencia_id = it.agencia_id
WHERE it.incentivo_id = 1
  AND it.venta_mes >= it.meta_plan
  AND it.agencia_id = '050007';


SELECT
    it.agencia_id,
    it.tipo_producto,
    it.sistema,
    it.venta_mes,
    -- === VENTAS NET AGRUPADAS ===
    net.cedula_net,
    net.monto_net,
    ROUND((net.monto_net / it.venta_mes) * 100, 2) AS porc_net,
    -- === VENTAS BET AGRUPADAS ===
    bet.cedula_bet,
    bet.monto_bet,
    ROUND((bet.monto_bet / it.venta_mes) * 100, 2) AS porc_bet
FROM incentivo_temporal it
-- NET AGRUPADO POR AGENCIA Y MES
LEFT JOIN (
    SELECT agencia_id, MAX(cedula) AS cedula_net, SUM(monto) AS monto_net
    FROM vt_usuarios_net n
    WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025
    GROUP BY agencia_id, cedula
) net ON net.agencia_id = it.agencia_id
-- BET AGRUPADO POR AGENCIA Y MES
LEFT JOIN (
    SELECT agencia_id, MAX(cedula) AS cedula_bet, SUM(monto) AS monto_bet
    FROM vt_usuarios_bet b
    WHERE MONTH(b.fecha) = 10 AND YEAR(b.fecha) = 2025
    GROUP BY agencia_id, cedula
) bet ON bet.agencia_id = it.agencia_id
WHERE it.incentivo_id = 1
    AND it.venta_mes >= it.meta_plan
    AND it.agencia_id = '050007';



 SELECT agencia_id, cedula, SUM(monto), tipo
    FROM vt_usuarios_bet n
    WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025
    AND agencia_id = '050007'
GROUP BY agencia_id, cedula, tipo;

SELECT 
    agencia_id,
    tipo,
    cedula,
    SUM(monto) AS monto_total_cedula,
    -- TOTAL GENERAL POR AGENCIA + TIPO
    SUM(SUM(monto)) OVER (PARTITION BY agencia_id, tipo) AS monto_total_agencia_tipo,
    -- PORCENTAJE DE LA CÉDULA
    ROUND(
        (SUM(monto) / SUM(SUM(monto)) OVER (PARTITION BY agencia_id, tipo)) * 100,2
    ) AS porcentaje_cedula
FROM vt_usuarios_bet
WHERE MONTH(fecha) = 10 
  AND YEAR(fecha) = 2025
  AND agencia_id = '050007'
GROUP BY agencia_id, tipo, cedula;


SELECT
    it.agencia_id,
    it.sistema,
    it.tipo_producto,
    FORMAT(it.venta_mes, 2, 'en_US') AS venta_mes,
    -- BET
    bet.cedula AS cedula_bet,
    FORMAT(bet.monto_cedula, 2, 'en_US') AS monto_bet_cedula,
    ROUND((bet.monto_cedula / it.venta_mes) * 100, 2) AS porc_bet,
    -- NET
    net.cedula AS cedula_net,
    FORMAT(net.monto_cedula, 2, 'en_US') AS monto_net_cedula,
    ROUND((net.monto_cedula / it.venta_mes) * 100, 2) AS porc_net
FROM incentivo_temporal it
LEFT JOIN (
    SELECT agencia_id, cedula, SUM(monto) AS monto_cedula, tipo, 'Lotobet' AS sistema
    FROM vt_usuarios_bet
    WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025 AND agencia_id IN ('050007', '050245')
    GROUP BY agencia_id, cedula, tipo
) bet ON bet.agencia_id = it.agencia_id AND bet.tipo = it.tipo_producto AND it.sistema = bet.sistema
LEFT JOIN (
    SELECT agencia_id, cedula, SUM(monto) AS monto_cedula, c.tipo, 'Lotonet' AS sistema
    FROM vt_usuarios_net n
    LEFT JOIN catalogo_juegos c ON n.producto_id = c.producto_id
    WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025 AND agencia_id IN ('050007', '050245')
    GROUP BY agencia_id, cedula, c.tipo
) net ON net.agencia_id = it.agencia_id AND net.tipo = it.tipo_producto AND it.sistema = net.sistema
WHERE it.incentivo_id = 1 AND it.agencia_id IN ('050007', '050245')
ORDER BY it.agencia_id;

SELECT * FROM incentivo_temporal_c


/* {
    "companyid": 168,
    "empleadoid": 7058,
    "nombres": "Prueba",
    "apellidos": "Prueba 1",
    "idposicion": 55,
    "posicion": "AGENTE DE VENTAS",
    "salariomensual": 0.0,
    "iddepto": 10,
    "depto": "VENTAS",
    "idciudad": 1,
    "ciudad": "0001-Santo Domingo.",
    "idpais": 1,
    "pais": "0001-RD",
    "ctabanco": "",
    "tipodocidentidad": "CE",
    "cedula": "00227608805",
    "sexo": "Femenino",
    "estadocivil": "Union Libre",
    "nohijos": 0,
    "direccion": "0",
    "tel1": "(0__) ___-____",
    "tel2": "(___) ___-____",
    "email": "NOTIENE@MAIL.COM",
    "profesion1": "",
    "profesion2": "",
    "fechanacimiento": null,
    "fechaingreso": "2025-11-03T00:00:00",
    "fechasalida": null,
    "iniciovacaciones": null,
    "finalvacaciones": null,
    "clienteid": 6661,
    "codigovendedor": null,
    "chofer": false,
    "bombero": false,
    "creadopor": "PBELLO",
    "modificadopor": "",
    "fechagrabado": "2025-11-03T16:09:23",
    "fechamodificado": null,
    "atributoprn": "",
    "idsucursalturno": 0,
    "moduloturno": "Recursos Humanos",
    "idturno": 7,
    "nocalcularsalario": false,
    "turnorotativo": false,
    "porcientocomision": 0.0,
    "enporciento": false,
    "cuenta": "600110001",
    "cobrador": true,
    "mozo": false,
    "clavemozo": "",
    "lavador": false,
    "idsistemaviejo": "",
    "viapago": "Efectivo",
    "idcentrocosto": 3689,
    "cuentanav": "600110006",
    "idbanco": null,
    "viapago_banco": "--No Aplica--",
    "idcalendario": 1,
    "preaviso": false,
    "cesantia": false,
    "vacaciones": false,
    "navidad": false,
    "viapago_bancoemp": "--No Aplica--",
    "tipocuenta": "CA",
    "cuentagastoinfotep": null,
    "cuentagastoriesgolaboral": null,
    "rutafoto": "168/Empleado/168-0-7058-1",
    "enperiodo_prepost_natal": false,
    "en_licencia_medica": false,
    "tipo_empleado": "Empleado",
    "idplaza": null,
    "doctor": false
  } */

SELECT
    v.agencia_id,
    c.tipo AS tipo_producto,
    v.sistema,
    SUM(IFNULL(v.monto, 0)) AS total_mes,
    MONTH(v.fecha) AS mes
FROM (
    SELECT agencia_id, producto_id, monto, fecha, 'Lotobet' AS sistema FROM ventas_producto_bet
    UNION ALL
    SELECT agencia_id, producto_id, monto, fecha, 'Lotobet' AS sistema FROM recargas_bet
) v
LEFT JOIN catalogo_juegos c ON CAST(v.producto_id AS SIGNED) = c.producto_id
WHERE MONTH(v.fecha) = 10 AND YEAR(v.fecha) = 2025 AND v.agencia_id = '050007'
GROUP BY
    v.agencia_id,
    c.tipo,
    v.sistema;

SELECT SUM(monto) AS total_mes, cedula, tipo
FROM vt_usuarios_bet
WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025 AND agencia_id = '050007'
GROUP BY agencia_id, tipo, cedula
ORDER BY tipo;

SELECT agencia_id, cedula, SUM(monto) AS monto_cedula, c.tipo
FROM vt_usuarios_net n
LEFT JOIN catalogo_juegos c ON n.producto_id = c.producto_id
WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025 AND agencia_id IN ('050245')
GROUP BY agencia_id, cedula, n.tipo
ORDER BY n.tipo

SELECT * FROM vt_usuarios_net WHERE MONTH(fecha) = 10 AND YEAR(fecha) = 2025;

SELECT * FROM vt_usuarios_net WHERE agencia_id = '05348' AND MONTH(fecha) = 10 AND YEAR(fecha) = 2025
    AND producto_id is null

SELECT DISTINCT v.producto_id
FROM (
    SELECT producto_id FROM vt_usuarios_net
    UNION 
    SELECT producto_id FROM vt_usuarios_bet
) v
LEFT JOIN catalogo_juegos c
    ON CAST(v.producto_id AS UNSIGNED) = c.producto_id
WHERE c.producto_id IS NULL;




SELECT * FROM vt_usuarios_bet 
WHERE agencia_id = '05346' AND MONTH(fecha) = 10 AND YEAR(fecha) = 2025
    AND tipo = 'No Tradicional' AND cedula = '' OR cedula is NULL




SELECT
    eu.agencia_id,
    eu.tipo_producto,
    CASE WHEN '$sistema' = 'Lotobet'
        THEN eu.cedula_bet
        ELSE eu.cedula_net
    END AS cedula,
    CASE WHEN '$sistema' = 'Lotobet'
        THEN FORMAT(eu.porcentaje_cedula_bet, 2, 'en_US')
        ELSE FORMAT(eu.porcentaje_cedula_net, 2, 'en_US')
    END AS porcentaje_cedula,
    FORMAT(pad.monto_agente, 2, 'en_US') AS monto_agente,
    FORMAT(pad.monto_coordinador, 2, 'en_US') AS monto_coordinador,
    FORMAT(pad.monto_administrativo, 2, 'en_US') AS monto_administrativo,
    FORMAT(pad.total_distribucion, 2, 'en_US') AS total_distribucion,
    CASE WHEN '$sistema' = 'Lotobet'
        THEN ROUND((pad.total_distribucion / eu.porcentaje_cedula_bet) * 100, 2)
        ELSE ROUND((pad.total_distribucion / eu.porcentaje_cedula_net) * 100, 2)
    END AS monto_incentivo
FROM efectividad_usuarios eu
INNER JOIN plan_agencias_distribucion pad ON eu.incentivo_id = pad.incentivo_id
    AND eu.agencia_id = pad.agencia_id
    AND eu.tipo_producto = pad.tipo_producto
INNER JOIN incentivo_temporal it on eu.incentivo_id = it.incentivo_id
    AND eu.agencia_id = it.agencia_id
    AND eu.tipo_producto = it.tipo_producto
WHERE eu.incentivo_id = 1 AND eu.agencia_id = '050074'
    AND it.venta_mes >= it.venta_base;

SELECT SUM(monto_administrativo) AS total_agencia
FROM plan_agencias_distribucion
WHERE incentivo_id = 1 AND excedente > 0
GROUP BY tipo_producto


CREATE TABLE pagos_porotra_empresa_net (
    pago_id INT PRIMARY KEY AUTO_INCREMENT,
    consorcio_id VARCHAR(50),
    producto_id VARCHAR(50),
    monto DECIMAL(18,4),
    agencia_id VARCHAR(25),
    descripcion VARCHAR(50),
    fecha DATE,
    pagado_consorcio_id VARCHAR(25),
    plataforma VARCHAR(50)
);

CREATE TABLE porcentaje_administrativo (
    empleado_id INT,
    porcentaje DECIMAL(8, 4)
);


CREATE TABLE `premios_net` (
    `premio_id` int NOT NULL AUTO_INCREMENT,
    `consorcio_id` varchar(5) DEFAULT NULL,
    `producto_id` varchar(50) DEFAULT NULL,
    `monto` decimal(12, 2) DEFAULT NULL,
    `agencia_id` varchar(25) DEFAULT NULL,
    `descripcion` varchar(50) DEFAULT NULL,
    `fecha` date DEFAULT NULL,
    PRIMARY KEY (`premio_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci



SELECT empleadoid, porcentaje_incentivo
FROM empleados
WHERE porcentaje_incentivo IS NOT NULL
  AND porcentaje_incentivo <> ''

INSERT INTO porcentaje_administrativo (empleado_id, porcentaje)
SELECT empleadoid, porcentaje_incentivo
FROM empleados
WHERE porcentaje_incentivo IS NOT NULL
  AND porcentaje_incentivo <> ''
ON DUPLICATE KEY UPDATE 
    porcentaje = VALUES(porcentaje);


SELECT * 
FROM porcentaje_administrativo
WHERE empleado_id IN (
    3788, 3265
);

DELETE FROM porcentaje_administrativo
WHERE empleado_id = 5471 limit 1;

UPDATE empleados e
LEFT JOIN (
    SELECT 
        empleadoid,
        -- prioridad: admin > coordinador > agente
        CASE
            WHEN empleadoid IN (SELECT empleadoid FROM pago_incentivos_admin) THEN 3
            WHEN empleadoid IN (SELECT empleadoid FROM pago_incentivos_coordinador) THEN 2
            WHEN empleadoid IN (SELECT empleadoid FROM pago_incentivos) THEN 1
            ELSE NULL
        END AS nuevo_tipo
    FROM empleados
) x ON x.empleadoid = e.empleadoid
SET e.tipo_empleado_incentivo = x.nuevo_tipo
WHERE x.nuevo_tipo IS NOT NULL;


CREATE TABLE incentivo_resultados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mes INT NOT NULL,
  anio INT NOT NULL,
  excluidos VARCHAR(255) NULL,

  agencia_id INT NOT NULL,
  tipo_producto VARCHAR(120) NULL,
  sistema VARCHAR(20) NOT NULL,

  total_trimestre DECIMAL(18,2) NOT NULL,
  promedio_mensual DECIMAL(18,2) NOT NULL,
  venta_base DECIMAL(18,2) NOT NULL,
  total_mes DECIMAL(18,2) NOT NULL,

  nivel VARCHAR(50) NULL,
  cumplimiento DECIMAL(18,2) NOT NULL,
  meta_plan DECIMAL(18,2) NOT NULL,
  meta_incremental DECIMAL(18,2) NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  KEY idx_periodo (anio, mes),
  KEY idx_agencia (agencia_id),
  KEY idx_sistema (sistema),
  KEY idx_tipo (tipo_producto)
);

CREATE TABLE incentivo_jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mes INT NOT NULL,
  anio INT NOT NULL,
  excluidos VARCHAR(255) NULL,
  status ENUM('pending','running','done','failed') NOT NULL DEFAULT 'pending',
  error TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  started_at DATETIME NULL,
  finished_at DATETIME NULL
);

