-- ============================================
-- Script SQL para PostgreSQL
-- Sistema de Tracking de Ubicación para Marketing
-- Tabla separada: ubicaciones_usuarios
-- ============================================

-- PASO 1: Crear tabla de ubicaciones de usuarios
CREATE TABLE IF NOT EXISTS appweb.ubicaciones_usuarios (
    id BIGSERIAL PRIMARY KEY,
    
    -- Relación con usuario (nullable para visitantes anónimos)
    usuario_id BIGINT,
    
    -- Coordenadas GPS
    latitud NUMERIC(10, 7) NOT NULL,
    longitud NUMERIC(10, 7) NOT NULL,
    precision NUMERIC(10, 2),
    
    -- Información geográfica (reverse geocoding)
    ciudad VARCHAR(100),
    estado VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'México',
    codigo_postal VARCHAR(10),
    
    -- Información del dispositivo y navegador (para marketing)
    dispositivo VARCHAR(50), -- 'mobile', 'tablet', 'desktop'
    navegador VARCHAR(100),
    sistema_operativo VARCHAR(100),
    user_agent VARCHAR(500),
    ip_address INET,
    
    -- Contexto de captura
    pagina_origen VARCHAR(255), -- URL donde se capturó
    evento VARCHAR(100), -- 'registro', 'compra', 'navegacion', etc.
    session_id VARCHAR(255), -- ID de sesión del navegador
    
    -- Metadata para análisis
    es_primera_visita BOOLEAN DEFAULT FALSE,
    metadata JSONB,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key
    CONSTRAINT fk_ubicaciones_usuario FOREIGN KEY (usuario_id) 
        REFERENCES appweb.usuarios(id) ON DELETE SET NULL
);

-- PASO 2: Agregar comentarios a las columnas (documentación)
COMMENT ON TABLE appweb.ubicaciones_usuarios IS 'Registro de ubicaciones de usuarios para análisis de marketing';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.usuario_id IS 'ID del usuario (NULL para visitantes anónimos)';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.latitud IS 'Latitud GPS (-90 a 90)';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.longitud IS 'Longitud GPS (-180 a 180)';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.precision IS 'Precisión de la ubicación en metros';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.dispositivo IS 'Tipo de dispositivo: mobile, tablet, desktop';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.evento IS 'Evento que disparó la captura: registro, compra, navegacion, etc.';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.session_id IS 'ID de sesión del navegador';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.es_primera_visita IS 'Indica si es la primera visita de esta sesión';
COMMENT ON COLUMN appweb.ubicaciones_usuarios.metadata IS 'Datos adicionales en formato JSON';

-- PASO 3: Crear índices para optimizar búsquedas de marketing
CREATE INDEX IF NOT EXISTS idx_ubicaciones_usuario_id ON appweb.ubicaciones_usuarios (usuario_id);
CREATE INDEX IF NOT EXISTS idx_ubicaciones_ciudad_estado ON appweb.ubicaciones_usuarios (ciudad, estado) WHERE ciudad IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_ubicaciones_pais ON appweb.ubicaciones_usuarios (pais) WHERE pais IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_ubicaciones_evento ON appweb.ubicaciones_usuarios (evento) WHERE evento IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_ubicaciones_created_at ON appweb.ubicaciones_usuarios (created_at DESC);
CREATE INDEX IF NOT EXISTS idx_ubicaciones_session_id ON appweb.ubicaciones_usuarios (session_id);

-- Índice compuesto para análisis temporal por usuario
CREATE INDEX IF NOT EXISTS idx_ubicaciones_usuario_fecha ON appweb.ubicaciones_usuarios (usuario_id, created_at DESC) WHERE usuario_id IS NOT NULL;

-- Índice para búsquedas de primeras visitas
CREATE INDEX IF NOT EXISTS idx_ubicaciones_primera_visita ON appweb.ubicaciones_usuarios (es_primera_visita, created_at DESC) WHERE es_primera_visita = TRUE;

-- PASO 4: Crear función para actualizar timestamp automáticamente
CREATE OR REPLACE FUNCTION appweb.actualizar_updated_at_ubicaciones()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- PASO 5: Crear trigger para updated_at
CREATE TRIGGER trigger_actualizar_updated_at_ubicaciones
BEFORE UPDATE ON appweb.ubicaciones_usuarios
FOR EACH ROW
EXECUTE FUNCTION appweb.actualizar_updated_at_ubicaciones();

-- PASO 6: Validar que la tabla se creó correctamente
SELECT 
    column_name,
    data_type,
    character_maximum_length,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_schema = 'appweb'
AND table_name = 'ubicaciones_usuarios'
ORDER BY ordinal_position;

-- ============================================
-- CONSULTAS ÚTILES PARA MARKETING
-- ============================================

-- 1. Ver últimas ubicaciones capturadas
SELECT 
    id,
    usuario_id,
    ciudad,
    estado,
    dispositivo,
    navegador,
    evento,
    es_primera_visita,
    created_at
FROM appweb.ubicaciones_usuarios
ORDER BY created_at DESC
LIMIT 20;

-- 2. Contar ubicaciones por ciudad (Top 10)
SELECT 
    ciudad,
    estado,
    COUNT(*) as total_registros,
    COUNT(DISTINCT usuario_id) as usuarios_unicos,
    COUNT(*) FILTER (WHERE es_primera_visita) as primeras_visitas
FROM appweb.ubicaciones_usuarios
WHERE ciudad IS NOT NULL
GROUP BY ciudad, estado
ORDER BY total_registros DESC
LIMIT 10;

-- 3. Análisis por dispositivo
SELECT 
    dispositivo,
    COUNT(*) as total,
    COUNT(DISTINCT usuario_id) as usuarios_unicos,
    ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) as porcentaje
FROM appweb.ubicaciones_usuarios
WHERE dispositivo IS NOT NULL
GROUP BY dispositivo
ORDER BY total DESC;

-- 4. Análisis temporal (últimos 7 días)
SELECT 
    DATE(created_at) as fecha,
    COUNT(*) as total_ubicaciones,
    COUNT(DISTINCT usuario_id) as usuarios_unicos,
    COUNT(*) FILTER (WHERE es_primera_visita) as primeras_visitas,
    COUNT(*) FILTER (WHERE usuario_id IS NULL) as visitas_anonimas
FROM appweb.ubicaciones_usuarios
WHERE created_at >= NOW() - INTERVAL '7 days'
GROUP BY DATE(created_at)
ORDER BY fecha DESC;

-- 5. Mapa de calor por evento
SELECT 
    evento,
    COUNT(*) as total,
    COUNT(DISTINCT session_id) as sesiones_unicas,
    AVG(CASE WHEN precision IS NOT NULL THEN precision END) as precision_promedio_metros
FROM appweb.ubicaciones_usuarios
WHERE evento IS NOT NULL
GROUP BY evento
ORDER BY total DESC;

-- 6. Usuarios con más ubicaciones registradas
SELECT 
    u.usuario_id,
    CONCAT(us.nombres, ' ', us.apellido_paterno) as nombre,
    us.email,
    COUNT(*) as ubicaciones_registradas,
    MIN(u.created_at) as primera_ubicacion,
    MAX(u.created_at) as ultima_ubicacion
FROM appweb.ubicaciones_usuarios u
LEFT JOIN appweb.usuarios us ON u.usuario_id = us.id
WHERE u.usuario_id IS NOT NULL
GROUP BY u.usuario_id, us.nombres, us.apellido_paterno, us.email
ORDER BY ubicaciones_registradas DESC
LIMIT 10;

-- 7. Análisis de visitantes anónimos vs registrados
SELECT 
    CASE 
        WHEN usuario_id IS NULL THEN 'Anónimo'
        ELSE 'Registrado'
    END as tipo_usuario,
    COUNT(*) as total_ubicaciones,
    COUNT(DISTINCT session_id) as sesiones_unicas,
    COUNT(DISTINCT DATE(created_at)) as dias_activos
FROM appweb.ubicaciones_usuarios
GROUP BY CASE WHEN usuario_id IS NULL THEN 'Anónimo' ELSE 'Registrado' END;

-- 8. Estadísticas generales
SELECT 
    COUNT(*) as total_ubicaciones,
    COUNT(DISTINCT usuario_id) as usuarios_distintos,
    COUNT(*) FILTER (WHERE usuario_id IS NULL) as ubicaciones_anonimas,
    COUNT(DISTINCT ciudad) as ciudades_distintas,
    COUNT(DISTINCT estado) as estados_distintos,
    COUNT(*) FILTER (WHERE created_at >= NOW() - INTERVAL '24 hours') as ultimas_24h,
    COUNT(*) FILTER (WHERE created_at >= NOW() - INTERVAL '7 days') as ultimos_7d,
    COUNT(*) FILTER (WHERE es_primera_visita) as primeras_visitas
FROM appweb.ubicaciones_usuarios;

-- 9. Rutas más comunes (páginas de origen)
SELECT 
    pagina_origen,
    COUNT(*) as visitas,
    COUNT(DISTINCT session_id) as sesiones_unicas
FROM appweb.ubicaciones_usuarios
WHERE pagina_origen IS NOT NULL
GROUP BY pagina_origen
ORDER BY visitas DESC
LIMIT 15;

-- 10. Análisis de navegadores más usados
SELECT 
    navegador,
    COUNT(*) as total,
    ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) as porcentaje
FROM appweb.ubicaciones_usuarios
WHERE navegador IS NOT NULL
AND navegador != 'Desconocido'
GROUP BY navegador
ORDER BY total DESC;

-- ============================================
-- SCRIPT DE ROLLBACK (en caso de necesitar revertir)
-- ============================================
/*
-- Eliminar trigger
DROP TRIGGER IF EXISTS trigger_actualizar_updated_at_ubicaciones ON appweb.ubicaciones_usuarios;

-- Eliminar función
DROP FUNCTION IF EXISTS appweb.actualizar_updated_at_ubicaciones();

-- Eliminar índices
DROP INDEX IF EXISTS appweb.idx_ubicaciones_usuario_id;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_ciudad_estado;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_pais;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_evento;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_created_at;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_session_id;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_usuario_fecha;
DROP INDEX IF EXISTS appweb.idx_ubicaciones_primera_visita;

-- Eliminar tabla
DROP TABLE IF EXISTS appweb.ubicaciones_usuarios;
*/

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
