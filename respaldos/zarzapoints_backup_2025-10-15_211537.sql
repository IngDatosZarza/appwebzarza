-- ZarzaPoints Database Backup
-- Generated: 2025-10-15 21:15:37
-- Schema: appweb
-- Tables: 13

SET search_path TO appweb, public;

-- ============================================
-- Tabla: auditoria
-- ============================================

DROP TABLE IF EXISTS appweb.auditoria CASCADE;
CREATE TABLE appweb.auditoria (
    id bigint DEFAULT nextval('auditoria_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    tabla character varying NOT NULL,
    registro_id bigint NOT NULL,
    accion character varying NOT NULL,
    cambios jsonb NOT NULL,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de auditoria
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (6, 27, 'usuarios', 27, 'create', '{"ip": "::1", "email": "web_test_1759953313@example.com", "accion": "registro_usuario", "user_agent": null}', '2025-10-08 13:55:13.593147');
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (7, 28, 'usuarios', 28, 'create', '{"ip": "::1", "email": "test1759953321@example.com", "accion": "registro_usuario", "user_agent": null}', '2025-10-08 13:55:22.165453');
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (8, 29, 'usuarios', 29, 'create', '{"ip": "::1", "email": "ingdatos@lazarza.com.mx", "accion": "registro_usuario", "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36"}', '2025-10-08 13:57:43.657591');
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (9, 5, 'usuarios', 5, 'update', '{"campo_actualizado": "perfil_usuario", "datos_modificados": ["nombres", "apellido_paterno", "apellido_materno", "email", "telefono", "fecha_nacimiento", "updated_at"]}', '2025-10-08 15:50:51.718733');
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (10, 5, 'usuarios', 5, 'update', '{"campo_actualizado": "perfil_usuario", "datos_modificados": ["nombres", "apellido_paterno", "apellido_materno", "email", "telefono", "fecha_nacimiento", "updated_at"]}', '2025-10-08 16:06:05.323609');
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (22, 5, 'cupones', 9, 'delete', '{"cupon_eliminado": {"id": 9, "activo": true, "nombre": "Cupón de Prueba Directo", "fecha_fin": "2025-11-09", "created_at": "2025-10-09 15:19:25.860282", "updated_at": "2025-10-09 15:19:25.860282", "descripcion": "Este cupón fue creado directamente para probar la funcionalidad", "fecha_inicio": "2025-10-09", "actualizado_por": 37, "puntos_requeridos": 75}}', '2025-10-10 09:49:49.805936');
INSERT INTO appweb.auditoria (id, usuario_id, tabla, registro_id, accion, cambios, fecha) VALUES (23, 5, 'cupones', 11, 'create', '{"activo": true, "nombre": "Banderillas 20 %", "descripcion": "términos y condiciones", "puntos_requeridos": 20}', '2025-10-10 09:53:48.266042');

-- ============================================
-- Tabla: compras
-- ============================================

DROP TABLE IF EXISTS appweb.compras CASCADE;
CREATE TABLE appweb.compras (
    id bigint DEFAULT nextval('compras_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    monto numeric NOT NULL,
    puntos_generados integer,
    creado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    numero_ticket character varying,
    descripcion text,
    metodo_pago character varying DEFAULT 'efectivo'::character varying NOT NULL,
    fecha_compra timestamp without time zone
);

-- Datos de compras
INSERT INTO appweb.compras (id, usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at, numero_ticket, descripcion, metodo_pago, fecha_compra) VALUES (1, 2, 1, 150.00, 150, 1, '2025-10-07 11:45:23.87692', '2025-10-07 11:45:23.87692', NULL, NULL, 'efectivo', NULL);
INSERT INTO appweb.compras (id, usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at, numero_ticket, descripcion, metodo_pago, fecha_compra) VALUES (3, 4, 1, 100.50, 100, 4, '2025-10-07 16:01:35.65', '2025-10-07 16:01:35.65', NULL, NULL, 'efectivo', NULL);
INSERT INTO appweb.compras (id, usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at, numero_ticket, descripcion, metodo_pago, fecha_compra) VALUES (4, 4, 1, 100.50, 100, 4, '2025-10-07 16:02:18.434593', '2025-10-07 16:02:18.434593', NULL, NULL, 'efectivo', NULL);
INSERT INTO appweb.compras (id, usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at, numero_ticket, descripcion, metodo_pago, fecha_compra) VALUES (6, 4, 1, 250.75, 100, 4, '2025-10-13 09:59:28.055992', '2025-10-13 09:59:28.055992', 'TEST-20251013175928-654', 'Compra de prueba - Ticket #TEST-20251013175928-654', 'tarjeta', '2025-10-13 17:59:28');
INSERT INTO appweb.compras (id, usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at, numero_ticket, descripcion, metodo_pago, fecha_compra) VALUES (8, 5, 1, 200.00, 100, 5, '2025-10-13 19:06:04', '2025-10-13 19:06:04', 1212123123113, 'Ticket #1212123123113', 'tarjeta', '2025-10-13 00:00:00');

-- ============================================
-- Tabla: cupones
-- ============================================

DROP TABLE IF EXISTS appweb.cupones CASCADE;
CREATE TABLE appweb.cupones (
    id bigint DEFAULT nextval('cupones_id_seq'::regclass) NOT NULL,
    nombre character varying NOT NULL,
    descripcion text NOT NULL,
    puntos_requeridos integer NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date NOT NULL,
    activo boolean DEFAULT true,
    actualizado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    codigo character varying
);

-- Datos de cupones
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (1, 'Descuento 10%', 'Obtén un 10% de descuento en tu próxima compra', 100, '2024-10-07', '2025-01-07', true, 1, '2025-10-07 11:45:23.870278', '2025-10-07 11:45:23.870278', 'D1045');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (2, 'Producto Gratis', 'Obtén un producto gratis de la selección especial', 500, '2024-10-07', '2025-04-07', true, 1, '2025-10-07 11:45:23.87196', '2025-10-07 11:45:23.87196', 'PG80');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (3, 'Descuento 25%', 'Descuento del 25% en toda la tienda', 1000, '2024-10-07', '2025-10-07', true, 1, '2025-10-07 11:45:23.872322', '2025-10-07 11:45:23.872322', 'D2596');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (4, 'Cupón de Prueba - 50 pts', 'Cupón especial para testing con solo 50 puntos requeridos', 50, '2025-10-07', '2025-11-06', true, NULL, '2025-10-07 16:02:06.042393', '2025-10-07 16:02:06.042393', 'CP34');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (5, 'Descuento 10% Compras', '10% de descuento en compras superiores a $500 pesos mexicanos', 100, '2025-10-09', '2025-12-31', true, 37, '2025-10-09 12:42:27.552142', '2025-10-09 12:42:27.552142', 'D10C11');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (6, 'Envío Gratis Premium', 'Envío gratuito en tu próxima compra sin mínimo de compra', 150, '2025-10-09', '2025-11-30', true, 37, '2025-10-09 12:42:27.552142', '2025-10-09 12:42:27.552142', 'EGP71');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (7, 'Descuento VIP 20%', '20% de descuento exclusivo para clientes VIP en toda la tienda', 500, '2025-10-15', '2026-01-15', true, 37, '2025-10-09 12:42:27.552142', '2025-10-09 12:42:27.552142', 'DVIP291');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (8, 'Cupón Vencido - Ejemplo', 'Este es un ejemplo de cupón vencido para mostrar estados', 75, '2025-09-01', '2025-10-08', true, 37, '2025-10-09 12:42:27.552142', '2025-10-09 12:42:27.552142', 'CV40');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (11, 'Banderillas 20 %', 'términos y condiciones', 20, '2025-10-10', '2025-10-31', true, 5, '2025-10-10 09:53:48.266042', '2025-10-10 09:53:48.266042', 'B2028');
INSERT INTO appweb.cupones (id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at, codigo) VALUES (12, 'Cupón de Prueba Gamificación', 'Cupón para probar el sistema de gamificación', 500, '2025-10-10', '2025-11-09', true, NULL, '2025-10-10 10:04:42.811895', '2025-10-10 10:04:42.811895', 'CP44');

-- ============================================
-- Tabla: cupones_asignados
-- ============================================

DROP TABLE IF EXISTS appweb.cupones_asignados CASCADE;
CREATE TABLE appweb.cupones_asignados (
    id bigint DEFAULT nextval('cupones_asignados_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    cupon_id bigint NOT NULL,
    estado character varying DEFAULT 'pendiente'::character varying,
    codigo_qr character varying NOT NULL,
    asignado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    qr_code character varying,
    fecha_uso timestamp without time zone,
    validado_por integer
);

-- Datos de cupones_asignados
INSERT INTO appweb.cupones_asignados (id, usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at, qr_code, fecha_uso, validado_por) VALUES (1, 4, 4, 'asignado', 'QR-TEST-68e58dea6d3dd-4', 4, '2025-10-07 16:02:18.447253', '2025-10-07 16:02:18.447253', 'QR_1_20251010222047_f7177163', NULL, NULL);
INSERT INTO appweb.cupones_asignados (id, usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at, qr_code, fecha_uso, validado_por) VALUES (10, 36, 12, 'asignado', 'ZP3D5F8EAF8832', 5, '2025-10-10 10:17:26.264785', '2025-10-10 10:17:26.264785', 'QR_10_20251010222047_d87ca511', NULL, NULL);
INSERT INTO appweb.cupones_asignados (id, usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at, qr_code, fecha_uso, validado_por) VALUES (11, 2, 11, 'asignado', 'ZPC86726A5D2F3', 2, '2025-10-10 14:08:06.262211', '2025-10-10 14:08:06.262211', 'QR_11_20251010222047_eb163727', NULL, NULL);
INSERT INTO appweb.cupones_asignados (id, usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at, qr_code, fecha_uso, validado_por) VALUES (13, 4, 6, 'asignado', 'EGP71-EA3F4', 4, '2025-10-13 13:50:07.858896', '2025-10-13 13:50:07.858896', NULL, NULL, NULL);
INSERT INTO appweb.cupones_asignados (id, usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at, qr_code, fecha_uso, validado_por) VALUES (12, 4, 11, 'usado', 'B2028-E0C2B', 4, '2025-10-13 13:47:38.035271', '2025-10-13 14:07:11.443963', NULL, '2025-10-13 14:07:11.443963', 5);

-- ============================================
-- Tabla: direcciones
-- ============================================

DROP TABLE IF EXISTS appweb.direcciones CASCADE;
CREATE TABLE appweb.direcciones (
    id bigint DEFAULT nextval('direcciones_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    calle character varying NOT NULL,
    numero character varying NOT NULL,
    colonia character varying NOT NULL,
    codigo_postal character varying NOT NULL,
    estado character varying NOT NULL,
    ciudad character varying,
    pais character varying DEFAULT 'México'::character varying,
    referencias text,
    tipo character varying DEFAULT 'casa'::character varying,
    principal boolean DEFAULT false,
    actualizado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de direcciones
INSERT INTO appweb.direcciones (id, usuario_id, calle, numero, colonia, codigo_postal, estado, ciudad, pais, referencias, tipo, principal, actualizado_por, created_at, updated_at) VALUES (1, 2, 'Calle Ejemplo', 123, 'Colonia Centro', 12345, 'Ciudad de México', 'Ciudad de México', 'México', 'Casa azul con portón blanco', 'casa', true, 1, '2025-10-07 11:45:23.872942', '2025-10-07 11:45:23.872942');

-- ============================================
-- Tabla: migrations
-- ============================================

DROP TABLE IF EXISTS appweb.migrations CASCADE;
CREATE TABLE appweb.migrations (
    id integer DEFAULT nextval('migrations_id_seq'::regclass) NOT NULL,
    migration character varying NOT NULL,
    batch integer NOT NULL
);

-- Datos de migrations
INSERT INTO appweb.migrations (id, migration, batch) VALUES (1, '2024_01_01_000001_create_usuarios_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (2, '2024_01_01_000002_create_sucursales_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (3, '2024_01_01_000003_create_direcciones_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (4, '2024_01_01_000004_create_puntos_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (5, '2024_01_01_000005_create_compras_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (6, '2024_01_01_000006_create_transacciones_puntos_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (7, '2024_01_01_000007_create_cupones_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (8, '2024_01_01_000008_create_cupones_asignados_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (9, '2024_01_01_000009_create_redenciones_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (10, '2024_01_01_000010_create_auditoria_table', 1);
INSERT INTO appweb.migrations (id, migration, batch) VALUES (11, '2024_01_01_000008_add_ticket_fields_to_compras_table', 2);

-- ============================================
-- Tabla: notificaciones
-- ============================================

DROP TABLE IF EXISTS appweb.notificaciones CASCADE;
CREATE TABLE appweb.notificaciones (
    id bigint DEFAULT nextval('notificaciones_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    tipo character varying NOT NULL,
    titulo character varying NOT NULL,
    mensaje text NOT NULL,
    datos jsonb,
    leida boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de notificaciones
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (1, 4, 'welcome', '¡Bienvenido a FidelityPoints!', 'Gracias por unirte a nuestro programa de puntos. ¡Empieza a ganar puntos con cada compra!', '{"type": "welcome"}', false, '2025-10-07 06:13:00.49354', '2025-10-07 16:13:00.49354');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (2, 4, 'purchase', '¡Puntos ganados!', 'Has ganado 100 puntos por tu compra de $100.50', '{"type": "purchase", "amount": 100.5, "points": 100}', false, '2025-10-07 11:13:00.502615', '2025-10-07 16:13:00.502615');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (3, 4, 'coupon', 'Cupón canjeado', 'Has canjeado exitosamente el cupón "Cupón de Prueba - 50 pts" por 50 puntos', '{"type": "coupon_redeemed", "coupon_name": "Cupón de Prueba - 50 pts", "points_used": 50}', false, '2025-10-05 16:13:00.503312', '2025-10-07 16:13:00.503312');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (4, 4, 'promotion', '¡Oferta especial!', 'Este fin de semana gana puntos dobles en todas tus compras. ¡No te lo pierdas!', '{"type": "promotion", "multiplier": 2}', false, '2025-10-06 08:13:00.503747', '2025-10-07 16:13:00.503747');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (5, 5, 'system', 'Sistema de notificaciones activado', 'El sistema de notificaciones ha sido activado exitosamente para todos los usuarios.', '{"type": "system", "feature": "notifications"}', false, '2025-10-07 16:13:00.504322', '2025-10-07 16:13:00.504322');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (7, 4, 'purchase', '¡Puntos ganados!', 'Has ganado 150 puntos por tu compra de $150.75', '{"type": "purchase", "amount": 150.75, "points": 150}', false, '2025-10-07 16:18:55.537679', '2025-10-07 16:18:55.537679');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (8, 4, 'coupon', 'Cupón canjeado exitosamente', 'Has canjeado el cupón ''Cupón de Prueba'' por 100 puntos. Código: QR-TEST-12345', '{"type": "coupon_redeemed", "qr_code": "QR-TEST-12345", "coupon_name": "Cupón de Prueba", "points_used": 100}', false, '2025-10-07 16:18:55.539135', '2025-10-07 16:18:55.539135');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (9, 5, 'welcome', '¡Bienvenido a FidelityPoints!', 'Hola Admin, gracias por unirte a nuestro programa de puntos. ¡Comienza a ganar puntos con tus compras!', '{"type": "welcome", "user_name": "Admin"}', false, '2025-10-07 16:18:55.541576', '2025-10-07 16:18:55.541576');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (11, 4, 'purchase', '¡Puntos ganados!', 'Has ganado 150 puntos por tu compra de $150.75', '{"type": "purchase", "amount": 150.75, "points": 150}', false, '2025-10-07 16:19:13.276035', '2025-10-07 16:19:13.276035');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (12, 4, 'coupon', 'Cupón canjeado exitosamente', 'Has canjeado el cupón ''Cupón de Prueba'' por 100 puntos. Código: QR-TEST-12345', '{"type": "coupon_redeemed", "qr_code": "QR-TEST-12345", "coupon_name": "Cupón de Prueba", "points_used": 100}', false, '2025-10-07 16:19:13.277991', '2025-10-07 16:19:13.277991');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (13, 5, 'welcome', '¡Bienvenido a FidelityPoints!', 'Hola Admin, gracias por unirte a nuestro programa de puntos. ¡Comienza a ganar puntos con tus compras!', '{"type": "welcome", "user_name": "Admin"}', false, '2025-10-07 16:19:13.279618', '2025-10-07 16:19:13.279618');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (14, 27, 'welcome', '¡Bienvenido a FidelityPoints!', 'Hola WebTest, gracias por unirte a nuestro programa de puntos. ¡Comienza a ganar puntos con tus compras!', '{"type": "welcome", "user_name": "WebTest"}', false, '2025-10-08 13:55:13.886268', '2025-10-08 13:55:13.886268');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (15, 28, 'welcome', '¡Bienvenido a FidelityPoints!', 'Hola TestUser, gracias por unirte a nuestro programa de puntos. ¡Comienza a ganar puntos con tus compras!', '{"type": "welcome", "user_name": "TestUser"}', false, '2025-10-08 13:55:22.446883', '2025-10-08 13:55:22.446883');
INSERT INTO appweb.notificaciones (id, usuario_id, tipo, titulo, mensaje, datos, leida, created_at, updated_at) VALUES (16, 29, 'welcome', '¡Bienvenido a FidelityPoints!', 'Hola Alejandro, gracias por unirte a nuestro programa de puntos. ¡Comienza a ganar puntos con tus compras!', '{"type": "welcome", "user_name": "Alejandro"}', false, '2025-10-08 13:57:43.972796', '2025-10-08 13:57:43.972796');

-- ============================================
-- Tabla: puntos
-- ============================================

DROP TABLE IF EXISTS appweb.puntos CASCADE;
CREATE TABLE appweb.puntos (
    id bigint DEFAULT nextval('puntos_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    saldo integer,
    actualizado_por bigint,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de puntos
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (7, 11, 0, NULL, '2025-10-08 13:19:10.550856');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (23, 27, 0, NULL, '2025-10-08 13:55:13.593147');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (24, 28, 0, NULL, '2025-10-08 13:55:22.165453');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (25, 29, 0, NULL, '2025-10-08 13:57:43.657591');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (26, 32, 150, NULL, '2025-10-08 15:14:17.51731');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (29, 36, 0, 36, '2025-10-09 12:07:50.809781');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (1, 2, 130, 1, '2025-10-07 11:45:23.874607');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (3, 5, 1100, NULL, '2025-10-13 19:06:04');
INSERT INTO appweb.puntos (id, usuario_id, saldo, actualizado_por, updated_at) VALUES (2, 4, 330, NULL, '2025-10-13 09:59:28.055992');

-- ============================================
-- Tabla: redenciones
-- ============================================

DROP TABLE IF EXISTS appweb.redenciones CASCADE;
CREATE TABLE appweb.redenciones (
    id bigint DEFAULT nextval('redenciones_id_seq'::regclass) NOT NULL,
    cupon_asignado_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    fecha_redencion timestamp without time zone NOT NULL,
    observaciones text,
    realizado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Tabla: sessions
-- ============================================

DROP TABLE IF EXISTS appweb.sessions CASCADE;
CREATE TABLE appweb.sessions (
    id character varying NOT NULL,
    user_id bigint,
    ip_address character varying,
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);

-- Datos de sessions
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('tsHk0h734H3pKMEhUHoFoVDNWwqu5jC8iacuKF6u', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWpvTHBZdGU2eVBPNGhEQ3NOUklneU1udDR4Wm11bDBxOFhOM2VnNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC8/aWQ9MjExMGEyZWMtZTBhMS00NDJiLTg5YzQtNWQyOWJlNDYxMDQzJnZzY29kZUJyb3dzZXJSZXFJZD0xNzYwMDMxOTI3MjI0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1760031927);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('SOEtLgDoAjPemVr0z3EwNSIaoBjJdTO1krTSXwBC', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiazRIREE0MTJBSTB2QXE1RTV6bXhnTG9WT2Q5bnIwcjJyeGplWU41RCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1760032961);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('esG6LrRwOAMzy1H7D1Vn8Rems55aJgEs3SFVAFwG', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6ImNCYzI3dDRLTDc0dUVPOGtrdUlsZWhQV1VPb1BPenJHZ1YwREgydFIiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjIxOiJodHRwOi8vbG9jYWxob3N0OjgwMDAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjE4OiJ1c2VyX2F1dGhlbnRpY2F0ZWQiO2I6MTtzOjc6InVzZXJfaWQiO2k6Mjk7czoxMDoidXNlcl9lbWFpbCI7czoyMzoiaW5nZGF0b3NAbGF6YXJ6YS5jb20ubXgiO3M6MTE6InVzZXJfbm9tYnJlIjtzOjk6IkFsZWphbmRybyI7czoxMzoidXNlcl9hcGVsbGlkbyI7czo3OiJQYXJlZGVzIjtzOjg6InVzZXJfcm9sIjtzOjc6ImNsaWVudGUiO3M6MTE6InVzZXJfcHVudG9zIjtpOjA7fQ==', 1760037084);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('E6IOAFZ8S3fG6Y7HF1DPXXr8HjSKhvaisCY2SEoI', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6IlM4SXVGNVFHZm9TUDdMSUl1SVlzVGxBd0xaR2ZFWENXRWxCRjByVkQiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQxOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWRtaW4vY3Vwb25lcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MTg6InVzZXJfYXV0aGVudGljYXRlZCI7YjoxO3M6NzoidXNlcl9pZCI7aTo1O3M6MTA6InVzZXJfZW1haWwiO3M6MTQ6ImFkbWluQHRlc3QuY29tIjtzOjExOiJ1c2VyX25vbWJyZSI7czo1OiJBZG1pbiI7czoxMzoidXNlcl9hcGVsbGlkbyI7czo3OiJTaXN0ZW1hIjtzOjg6InVzZXJfcm9sIjtzOjU6ImFkbWluIjtzOjExOiJ1c2VyX3B1bnRvcyI7aToxMDAwO30=', 1760037120);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('nv0WoLcLhugWLWnlvJpbbmgtSwj1I1Hl3r5Jdbqe', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaWhCbFFiclFqSENRMXVVRGdEVUxHMXAyT2J5RzFTbXJtcWUwSkhEbyI7czo1OiJlcnJvciI7czoxNToiQWNjZXNvIGRlbmVnYWRvIjtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MTp7aTowO3M6NToiZXJyb3IiO319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTA4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWRtaW4vY3Vwb25lcz9pZD1kN2NlZWVjYS01YjdiLTQzOTgtYmUzNS01ZjM1MzhhZjgyN2MmdnNjb2RlQnJvd3NlclJlcUlkPTE3NjAwMzUyOTY1NzkiO319', 1760035296);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('jayEUUIum7pgGk68fmypqJ2XMMMXoZ6fcAoRnoUI', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoibmY2VlZYbkRnU3B0N21nZlhkMW9ibU02T3F3ZTlZNmNVdHJ1cGlsdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1760044459);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('n8iS53WNmrJayNLQXjnhiWhHYhCy6oIWaOAjPTcv', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic0kyTEN4dW5ESWFhNmx3VERmRkRXeFJkT1hoblpaNzExcFNpcFZLbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9Mjk1YWRmZTItNTBhMy00MDVkLWEwN2ItMTVhYzY1ZDVhMjZlJnZzY29kZUJyb3dzZXJSZXFJZD0xNzYwMDMzMjc1NTE5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1760033275);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('bdKWQex5y4r6h3muX6X3DJ77mHNRcoRmndaOPPHT', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6ImgxdWJxNnE3VU80d3RQRVFQMVFqN1ZtczFvWWRidldTZHdOVERyNW4iO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvY3Vwb25lcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MTg6InVzZXJfYXV0aGVudGljYXRlZCI7YjoxO3M6NzoidXNlcl9pZCI7aTo1O3M6MTA6InVzZXJfZW1haWwiO3M6MTQ6ImFkbWluQHRlc3QuY29tIjtzOjExOiJ1c2VyX25vbWJyZSI7czo1OiJBZG1pbiI7czoxMzoidXNlcl9hcGVsbGlkbyI7czo3OiJTaXN0ZW1hIjtzOjg6InVzZXJfcm9sIjtzOjU6ImFkbWluIjtzOjExOiJ1c2VyX3B1bnRvcyI7aToxMDAwO30=', 1760032506);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('uSG6zmn6RSluerV9pONW4srtWKcRH3qePrpdC6g5', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOFJpYlY4dXR2WHFMOVcwNHpJZ1k0dGhLcnFEZlNVWWJVRlFJM3VJMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1760035297);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('CyVVCC1SPZEsFXBUe0QvZayPlrPlnE8oeLNFpjSx', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6ImhvbUlrMXl3OUMxQzAyUUZpWjE0SlR2STEyaWJnOGFWTk04R2FRSloiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvY3Vwb25lcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MTg6InVzZXJfYXV0aGVudGljYXRlZCI7YjoxO3M6NzoidXNlcl9pZCI7aToyOTtzOjEwOiJ1c2VyX2VtYWlsIjtzOjIzOiJpbmdkYXRvc0BsYXphcnphLmNvbS5teCI7czoxMToidXNlcl9ub21icmUiO3M6OToiQWxlamFuZHJvIjtzOjEzOiJ1c2VyX2FwZWxsaWRvIjtzOjc6IlBhcmVkZXMiO3M6ODoidXNlcl9yb2wiO3M6NzoiY2xpZW50ZSI7czoxMToidXNlcl9wdW50b3MiO2k6MDt9', 1760033442);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('aZbyaQRIl3VrZvJZRzkFPlUa0miDtIomUAEweetA', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6IjEzWDhFZndXQnBrV0dOSmtrUUxra0pCcllFT2U5bXRhUVk4VEt5cnciO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvY3Vwb25lcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MTg6InVzZXJfYXV0aGVudGljYXRlZCI7YjoxO3M6NzoidXNlcl9pZCI7aToyOTtzOjEwOiJ1c2VyX2VtYWlsIjtzOjIzOiJpbmdkYXRvc0BsYXphcnphLmNvbS5teCI7czoxMToidXNlcl9ub21icmUiO3M6OToiQWxlamFuZHJvIjtzOjEzOiJ1c2VyX2FwZWxsaWRvIjtzOjc6IlBhcmVkZXMiO3M6ODoidXNlcl9yb2wiO3M6NzoiY2xpZW50ZSI7czoxMToidXNlcl9wdW50b3MiO2k6MDt9', 1760033785);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('cuckNC3jXKdjk7A9jVXjykAezLrYhHACCMlW5w7N', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSndpbjgwMUg4UXRLTHRRVUtMNnFPZkxJRmxaZnpRc21hQ2x1N2ZIdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9NDNiNzFmMjMtZTMzNi00MDA2LWIyZDgtYmEzYmRlODA5MjJiJnZzY29kZUJyb3dzZXJSZXFJZD0xNzYwMDM1MzUxNTE1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1760035351);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('WJ1mNZFYYvUGJe8YbEdgWbuyG1jed0QW3V2ogIjd', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.104.3 Chrome/138.0.7204.235 Electron/37.3.1 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSmdxMWhWNkljWWNaTHdwRGJHS20ycEZUN2RIRGxDa21hMTg1TXZtZSI7czo1OiJlcnJvciI7czoyMToiRGViZXMgaW5pY2lhciBzZXNpw7NuIjtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MTp7aTowO3M6NToiZXJyb3IiO319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTAyOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvY3Vwb25lcz9pZD00NzFmZWMyMS0yNzY5LTRmYTQtYWY2Ni1kNWNmZWJhMjg2YmImdnNjb2RlQnJvd3NlclJlcUlkPTE3NjAwMzI5NjEwNTAiO319', 1760032961);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('oaQZczDI3mme0NCHUnhve3kAgZw1BBvD1ZsxUcpz', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6Imh0cWduSDBhRW5Fd2ZzeXN3M3F2WHhhT2s2b3dCMnlpdmxxSUpYajUiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvY3Vwb25lcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MTg6InVzZXJfYXV0aGVudGljYXRlZCI7YjoxO3M6NzoidXNlcl9pZCI7aToyOTtzOjEwOiJ1c2VyX2VtYWlsIjtzOjIzOiJpbmdkYXRvc0BsYXphcnphLmNvbS5teCI7czoxMToidXNlcl9ub21icmUiO3M6OToiQWxlamFuZHJvIjtzOjEzOiJ1c2VyX2FwZWxsaWRvIjtzOjc6IlBhcmVkZXMiO3M6ODoidXNlcl9yb2wiO3M6NzoiY2xpZW50ZSI7czoxMToidXNlcl9wdW50b3MiO2k6MDt9', 1760032225);
INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES ('vxL6OL9v3QKk3iYra5gv4KJATyNpyhESEhKtxaOG', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6IjdCS0E3ZHJCRlQ0WkR1cmxZdHM3bVcwZHB6S1gwc0xXRWR2eEFwNFMiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM0OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWRtaW4vcG9pbnRzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxODoidXNlcl9hdXRoZW50aWNhdGVkIjtiOjE7czo3OiJ1c2VyX2lkIjtpOjU7czoxMDoidXNlcl9lbWFpbCI7czoxNDoiYWRtaW5AdGVzdC5jb20iO3M6MTE6InVzZXJfbm9tYnJlIjtzOjU6IkFkbWluIjtzOjEzOiJ1c2VyX2FwZWxsaWRvIjtzOjc6IlNpc3RlbWEiO3M6ODoidXNlcl9yb2wiO3M6NToiYWRtaW4iO3M6MTE6InVzZXJfcHVudG9zIjtpOjEwMDA7fQ==', 1760033821);

-- ============================================
-- Tabla: sucursales
-- ============================================

DROP TABLE IF EXISTS appweb.sucursales CASCADE;
CREATE TABLE appweb.sucursales (
    id bigint DEFAULT nextval('sucursales_id_seq'::regclass) NOT NULL,
    codigo character varying NOT NULL,
    nombre character varying NOT NULL,
    direccion text NOT NULL,
    telefono character varying,
    actualizado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de sucursales
INSERT INTO appweb.sucursales (id, codigo, nombre, direccion, telefono, actualizado_por, created_at, updated_at) VALUES (1, 'SUC001', 'Sucursal Centro', 'Av. Juárez 123, Centro, Ciudad de México', '555-1001', 1, '2025-10-07 11:45:23.865549', '2025-10-07 11:45:23.865549');
INSERT INTO appweb.sucursales (id, codigo, nombre, direccion, telefono, actualizado_por, created_at, updated_at) VALUES (2, 'SUC002', 'Sucursal Norte', 'Blvd. Norte 456, Colonia Norte, Ciudad de México', '555-1002', 1, '2025-10-07 11:45:23.868339', '2025-10-07 11:45:23.868339');
INSERT INTO appweb.sucursales (id, codigo, nombre, direccion, telefono, actualizado_por, created_at, updated_at) VALUES (3, 'SUC003', 'Sucursal Sur', 'Calzada del Sur 789, Colonia Sur, Ciudad de México', '555-1003', 1, '2025-10-07 11:45:23.869373', '2025-10-07 11:45:23.869373');

-- ============================================
-- Tabla: transacciones_puntos
-- ============================================

DROP TABLE IF EXISTS appweb.transacciones_puntos CASCADE;
CREATE TABLE appweb.transacciones_puntos (
    id bigint DEFAULT nextval('transacciones_puntos_id_seq'::regclass) NOT NULL,
    usuario_id bigint NOT NULL,
    tipo character varying NOT NULL,
    puntos integer NOT NULL,
    descripcion text NOT NULL,
    registrado_por bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de transacciones_puntos
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (1, 2, 'compra', 150, 'Puntos ganados por compra #1', 1, '2025-10-07 11:45:23.880812');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (18, 4, 'compra', 100, 'Compra de prueba - $100.5', 4, '2025-10-07 16:01:35.65');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (19, 4, 'compra', 100, 'Compra de prueba - $100.5', 4, '2025-10-07 16:02:18.434593');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (20, 4, 'canje', 50, 'Canje de cupón: Cupón de Prueba - 50 pts', 4, '2025-10-07 16:02:18.447253');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (26, 36, 'canje', 500, 'Canje por cupón: Cupón de Prueba Gamificación', 5, '2025-10-10 10:17:26.264785');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (27, 2, 'canje', 20, 'Canje por cupón: Banderillas 20 %', 2, '2025-10-10 14:08:06.262211');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (28, 4, 'compra', 100, 'Puntos por ticket #TEST-20251013175928-654 - Sucursal Centro', 4, '2025-10-13 09:59:28.055992');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (29, 5, 'compra', 100, 'Puntos por ticket #1212123123113 - Sucursal Centro', 5, '2025-10-13 19:06:04');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (30, 4, 'canje', 20, 'Canje por cupón: Banderillas 20 %', 4, '2025-10-13 13:47:38.035271');
INSERT INTO appweb.transacciones_puntos (id, usuario_id, tipo, puntos, descripcion, registrado_por, created_at) VALUES (31, 4, 'canje', 150, 'Canje por cupón: Envío Gratis Premium', 4, '2025-10-13 13:50:07.858896');

-- ============================================
-- Tabla: usuarios
-- ============================================

DROP TABLE IF EXISTS appweb.usuarios CASCADE;
CREATE TABLE appweb.usuarios (
    id bigint DEFAULT nextval('usuarios_id_seq'::regclass) NOT NULL,
    nombres character varying NOT NULL,
    apellido_paterno character varying NOT NULL,
    apellido_materno character varying,
    email character varying NOT NULL,
    password character varying NOT NULL,
    telefono character varying,
    fecha_nacimiento date,
    genero character varying,
    rol character varying DEFAULT 'cliente'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Datos de usuarios
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (1, 'Admin', 'Sistema', 'Principal', 'admin@puntosfidelidad.com', '$2y$10$kg3yUgkeId56pvzBh.gB.OFyr8hFRnLi8Naah6j3YLfuYcP6bbNka', '555-0001', NULL, 'masculino', 'admin', '2025-10-07 11:45:23.798987', '2025-10-07 11:45:23.798987');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (2, 'Juan Carlos', 'Pérez', 'González', 'cliente@example.com', '$2y$10$KJnFsoHblXZQwkwz.oEN1OxFz.bAmt4qc7/4LP9C4f4WzQjOYSWVi', '555-0002', '1990-05-15', 'masculino', 'cliente', '2025-10-07 11:45:23.863091', '2025-10-07 11:45:23.863091');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (32, 'María', 'González', 'López', 'cliente.prueba@test.com', '$2y$10$6SxqX7mBs4Y9bc.PH9JojudXxXdCOK4qi7i7nbxR13.5Zx/kt9p3G', 5551234567, '1990-05-15', NULL, 'cliente', '2025-10-08 15:14:17.514419', '2025-10-08 15:45:58.3764');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (11, 'Carlos', 'Test', 'Usuario', 'carlos.test@email.com', '$2y$10$rlAi/Sq/iRpX/yaZ0EPTOO/dGJ1Ku4Gu3OKV5xXzWk5Bu.mARC0Hm', 555123456, '1980-03-20', NULL, 'cliente', '2025-10-08 13:19:10.550856', '2025-10-08 13:19:10.550856');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (27, 'WebTest', 'Usuario', 'Web', 'web_test_1759953313@example.com', '$2y$12$eHpRsfmExOqy7RFXEbSKqetIdDhppv6C7xsRaMkwLrnM9IarDdNcq', 1234567890, NULL, NULL, 'cliente', '2025-10-08 13:55:13.593147', '2025-10-08 13:55:13.593147');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (28, 'TestUser', 'Apellido', 'Materno', 'test1759953321@example.com', '$2y$12$/mhk1fHvbsHcsPf4LFUwbeWATnBMkIJAp917WUgyfhg63h99QZQfe', 1234567890, NULL, NULL, 'cliente', '2025-10-08 13:55:22.165453', '2025-10-08 13:55:22.165453');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (36, 'Cliente', 'De Prueba', 'Cupones', 'cliente.test@zarzapoints.com', '$2y$10$FWCSM8cllRnyourX0LFPyuUBmNA.IT0GTYYToVX4kTNwBy13u6k9G', 1234567890, '1990-01-01', NULL, 'cliente', '2025-10-09 12:07:50.809781', '2025-10-09 12:07:50.809781');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (37, 'Admin', 'ZarzaPoints', 'Sistema', 'admin.test@zarzapoints.com', '$2y$10$F9ORBMroqqhtQB197/EBQeQfnRT/jjMZV.zTTf4VP.oG1vqRB2gIy', 9876543210, '1985-01-01', NULL, 'admin', '2025-10-09 12:41:58.593483', '2025-10-09 12:41:58.593483');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (29, 'Alejandro', 'Paredes', 'Galvan', 'ingdatos@lazarza.com.mx', '$2y$12$POSiY/eJtGMc9fKfKzwJx.4wCYYsiBkqahUxiJqSLiRKlBlu2CZmG', 2226702175, '1992-09-16', NULL, 'cliente', '2025-10-08 13:57:43.657591', '2025-10-13 11:05:37.015064');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (5, 'Admin', 'Sistema', 'BI', 'admin@test.com', '$2y$10$YI1awNhq6vYbKD19jRqro.M3RzB4/SvrCubs/cNuK/jf8nLq3PQIC', NULL, '1992-09-16', NULL, 'admin', '2025-10-07 15:40:27.468269', '2025-10-15 08:28:46.501582');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (6, 'Admin', 'Sistema', NULL, 'admin@zarza.com', '$2y$10$NkGN9H6QE/Iojx8ktg1UVegI.EP/v7UsBgqd4PcqmcEeTZrMZXkCW', NULL, NULL, NULL, 'admin', '2025-10-08 12:31:44.913155', '2025-10-08 14:53:37.243512');
INSERT INTO appweb.usuarios (id, nombres, apellido_paterno, apellido_materno, email, password, telefono, fecha_nacimiento, genero, rol, created_at, updated_at) VALUES (4, 'Cliente', 'Prueba', NULL, 'cliente@test.com', '$2y$10$YI1awNhq6vYbKD19jRqro.M3RzB4/SvrCubs/cNuK/jf8nLq3PQIC', NULL, NULL, NULL, 'cliente', '2025-10-07 15:40:27.409208', '2025-10-15 08:30:45.362052');

