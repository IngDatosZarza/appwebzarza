-- ============================================
-- CONSULTAS ÚTILES - SISTEMA PUNTOS FIDELIDAD
-- ============================================

-- Configurar schema antes de ejecutar consultas
SET search_path TO appweb, public;

-- ============================================
-- CONSULTAS DE VERIFICACIÓN
-- ============================================

-- 1. Verificar todas las tablas creadas
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'appweb' 
ORDER BY table_name;

-- 2. Contar registros en cada tabla
SELECT 
    'usuarios' as tabla, COUNT(*) as registros FROM usuarios
UNION ALL SELECT 'direcciones', COUNT(*) FROM direcciones
UNION ALL SELECT 'sucursales', COUNT(*) FROM sucursales
UNION ALL SELECT 'puntos', COUNT(*) FROM puntos
UNION ALL SELECT 'compras', COUNT(*) FROM compras
UNION ALL SELECT 'transacciones_puntos', COUNT(*) FROM transacciones_puntos
UNION ALL SELECT 'cupones', COUNT(*) FROM cupones
UNION ALL SELECT 'cupones_asignados', COUNT(*) FROM cupones_asignados
UNION ALL SELECT 'redenciones', COUNT(*) FROM redenciones
UNION ALL SELECT 'auditoria', COUNT(*) FROM auditoria
ORDER BY tabla;

-- ============================================
-- CONSULTAS DE NEGOCIO
-- ============================================

-- 3. Usuarios con sus puntos actuales
SELECT 
    u.id,
    u.nombres || ' ' || u.apellido_paterno || ' ' || COALESCE(u.apellido_materno, '') as nombre_completo,
    u.email,
    u.rol,
    COALESCE(p.saldo, 0) as puntos_actuales
FROM usuarios u
LEFT JOIN puntos p ON u.id = p.usuario_id
ORDER BY u.rol, u.nombres;

-- 4. Compras por usuario con puntos generados
SELECT 
    u.nombres || ' ' || u.apellido_paterno as cliente,
    s.nombre as sucursal,
    c.monto,
    c.puntos_generados,
    c.created_at::date as fecha_compra
FROM compras c
JOIN usuarios u ON c.usuario_id = u.id
JOIN sucursales s ON c.sucursal_id = s.id
ORDER BY c.created_at DESC;

-- 5. Historial de transacciones de puntos
SELECT 
    u.nombres || ' ' || u.apellido_paterno as usuario,
    tp.tipo,
    tp.puntos,o
    tp.descripcion,
    tp.created_at::date as fecha
FROM transacciones_puntos tp
JOIN usuarios u ON tp.usuario_id = u.id
ORDER BY tp.created_at DESC;

-- 6. Cupones disponibles con puntos requeridos
SELECT 
    id,
    nombre,
    descripcion,
    puntos_requeridos,
    fecha_inicio,
    fecha_fin,
    activo,
    CASE 
        WHEN fecha_fin < CURRENT_DATE THEN 'Vencido'
        WHEN fecha_inicio > CURRENT_DATE THEN 'Futuro'
        ELSE 'Vigente'
    END as estado_vigencia
FROM cupones
ORDER BY puntos_requeridos;

-- 7. Cupones que puede canjear cada usuario
SELECT 
    u.nombres || ' ' || u.apellido_paterno as usuario,
    u.email,
    p.saldo as puntos_disponibles,
    COUNT(cu.id) as cupones_disponibles
FROM usuarios u
LEFT JOIN puntos p ON u.id = p.usuario_id
LEFT JOIN cupones cu ON cu.puntos_requeridos <= COALESCE(p.saldo, 0) 
    AND cu.activo = true 
    AND cu.fecha_inicio <= CURRENT_DATE 
    AND cu.fecha_fin >= CURRENT_DATE
WHERE u.rol = 'cliente'
GROUP BY u.id, u.nombres, u.apellido_paterno, u.email, p.saldo
ORDER BY p.saldo DESC;

-- ============================================
-- CONSULTAS DE AUDITORÍA
-- ============================================

-- 8. Últimas acciones registradas en auditoría
SELECT 
    u.nombres || ' ' || u.apellido_paterno as usuario,
    a.tabla,
    a.registro_id,
    a.accion,
    a.fecha::date as fecha
FROM auditoria a
JOIN usuarios u ON a.usuario_id = u.id
ORDER BY a.fecha DESC
LIMIT 10;

-- ============================================
-- CONSULTAS DE REPORTES
-- ============================================

-- 9. Resumen de puntos por usuario
SELECT 
    u.rol,
    COUNT(*) as total_usuarios,
    SUM(COALESCE(p.saldo, 0)) as total_puntos,
    AVG(COALESCE(p.saldo, 0))::INTEGER as promedio_puntos
FROM usuarios u
LEFT JOIN puntos p ON u.id = p.usuario_id
GROUP BY u.rol;

-- 10. Ventas por sucursal
SELECT 
    s.codigo,
    s.nombre,
    COUNT(c.id) as total_compras,
    SUM(c.monto) as total_ventas,
    SUM(c.puntos_generados) as total_puntos_otorgados
FROM sucursales s
LEFT JOIN compras c ON s.id = c.sucursal_id
GROUP BY s.id, s.codigo, s.nombre
ORDER BY total_ventas DESC NULLS LAST;

-- ============================================
-- FUNCIONES ÚTILES
-- ============================================

-- Función para calcular puntos por monto
-- SELECT FLOOR(150.75 * 1) as puntos_por_compra;

-- Verificar usuarios que pueden canjear un cupón específico
-- SELECT * FROM usuarios u
-- JOIN puntos p ON u.id = p.usuario_id
-- WHERE p.saldo >= (SELECT puntos_requeridos FROM cupones WHERE id = 1);