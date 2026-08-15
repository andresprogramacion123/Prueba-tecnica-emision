-- =========================================================
-- Datos de prueba - Bufete de abogados
-- =========================================================

INSERT INTO clientes (cedula, nombre, telefono, email, direccion) VALUES
('1001234567', 'Laura Gómez Restrepo',   '3001234567', 'laura.gomez@example.com',   'Cra 45 #12-30, Medellín'),
('1002345678', 'Carlos Andrés Muñoz',    '3012345678', 'carlos.munoz@example.com',  'Cll 10 #5-20, Bogotá'),
('1003456789', 'María Fernanda López',   '3023456789', 'maria.lopez@example.com',   'Av. Siempre Viva 742, Cali');

INSERT INTO abogados (cedula, nombre, telefono, email, tarjeta_profesional) VALUES
('700111222', 'Julián Ríos Pérez',       '3101112222', 'julian.rios@bufete.com',   'TP-100234'),
('700222333', 'Ana María Castaño',       '3102223333', 'ana.castano@bufete.com',   'TP-100987'),
('700333444', 'Diego Fernando Salazar',  '3103334444', 'diego.salazar@bufete.com', 'TP-101456');

INSERT INTO casos (numero_expediente, cliente_id, fecha_inicio, fecha_archivo, estado) VALUES
('EXP-2024-001', 1, '2024-02-10', NULL,         'en_tramite'),
('EXP-2024-002', 1, '2023-05-15', '2024-01-20', 'archivado'),
('EXP-2024-003', 2, '2024-06-01', NULL,         'en_tramite'),
('EXP-2024-004', 3, '2023-11-03', '2024-03-10', 'finalizado'),
('EXP-2024-005', 2, '2024-07-22', NULL,         'suspendido'),
('EXP-2024-006', 3, '2024-08-01', NULL,         'en_tramite');

INSERT INTO caso_abogado (caso_id, abogado_id) VALUES
(1, 1), (1, 2), (2, 1), (3, 3), (4, 2), (4, 3), (5, 1), (6, 3);