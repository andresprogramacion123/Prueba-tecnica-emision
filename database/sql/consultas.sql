-- =========================================================
-- Consultas pedidas - Punto 1
-- =========================================================

-- 1) Casos asociados a un cliente, dado su número de cédula
SELECT
    c.id,
    c.numero_expediente,
    c.fecha_inicio,
    c.fecha_archivo,
    c.estado,
    cli.cedula AS cliente_cedula,
    cli.nombre AS cliente_nombre
FROM casos c
INNER JOIN clientes cli ON cli.id = c.cliente_id
WHERE cli.cedula = '1001234567'
  AND c.deleted_at IS NULL;

-- 2) Todos los casos, en orden ascendente (por fecha de inicio)
SELECT
    c.id,
    c.numero_expediente,
    c.fecha_inicio,
    c.fecha_archivo,
    c.estado,
    cli.nombre AS cliente_nombre
FROM casos c
INNER JOIN clientes cli ON cli.id = c.cliente_id
WHERE c.deleted_at IS NULL
ORDER BY c.fecha_inicio ASC;

-- 3) Los primeros 5 registros
SELECT
    c.id,
    c.numero_expediente,
    c.fecha_inicio,
    c.fecha_archivo,
    c.estado,
    cli.nombre AS cliente_nombre
FROM casos c
INNER JOIN clientes cli ON cli.id = c.cliente_id
WHERE c.deleted_at IS NULL
ORDER BY c.id ASC
LIMIT 5;