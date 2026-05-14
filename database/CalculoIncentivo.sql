DROP PROCEDURE CalculoIncentivo;

CREATE PROCEDURE `CalculoIncentivo`(IN _mes INT, IN _anio INT, IN _excluidos VARCHAR(255))
BEGIN
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
        WHERE vigente = 1 AND mes = _mes AND year = _anio
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
END





