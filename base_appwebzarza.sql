-- ============================================
-- Base de Datos: appwebzarza
-- Sistema de Gestión de Clientes y Fidelización
-- Fecha de creación: 2026-01-08
-- Adaptado para: PostgreSQL
-- ============================================

-- Crear esquema si no existe
CREATE SCHEMA IF NOT EXISTS appwebzarza;

-- Usar el esquema
SET search_path TO appwebzarza;

-- ============================================
-- Tabla: clientes_api_lazarza
-- Descripción: Almacena los datos de clientes sincronizados con la API de Fidelización de Lazarza
-- Fecha de creación: 2026-01-06
-- ============================================

CREATE TABLE IF NOT EXISTS clientes_api_lazarza (
    -- Identificador único local
    id BIGSERIAL PRIMARY KEY,
    
    -- Campos de la API (sincronizados)
    code VARCHAR(50) UNIQUE NOT NULL,
    person_customer BOOLEAN DEFAULT TRUE,
    person_name VARCHAR(100) NOT NULL,
    person_last_name VARCHAR(100) NOT NULL,
    person_last_name2 VARCHAR(100),
    person_birth_date DATE,
    tax_reg_nr VARCHAR(13) UNIQUE,
    person_gender VARCHAR(1),
    email_club_lazarza VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    province VARCHAR(100),
    city_name VARCHAR(100),
    district_name VARCHAR(100),
    
    -- Campos de estado
    closed BOOLEAN DEFAULT FALSE,
    
    -- Campos de registro y auditoría
    registration_channel VARCHAR(50) DEFAULT 'WEB',
    registered_by_user_id BIGINT,
    registration_ip VARCHAR(45),
    registration_user_agent TEXT,
    
    -- Timestamps de auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_sync_at TIMESTAMP
);

-- Índices para clientes_api_lazarza
CREATE INDEX IF NOT EXISTS idx_code ON clientes_api_lazarza(code);
CREATE INDEX IF NOT EXISTS idx_tax_reg_nr ON clientes_api_lazarza(tax_reg_nr);
CREATE INDEX IF NOT EXISTS idx_email ON clientes_api_lazarza(email_club_lazarza);
CREATE INDEX IF NOT EXISTS idx_closed ON clientes_api_lazarza(closed);
CREATE INDEX IF NOT EXISTS idx_last_sync ON clientes_api_lazarza(last_sync_at);
CREATE INDEX IF NOT EXISTS idx_full_name ON clientes_api_lazarza(person_name, person_last_name, person_last_name2);
CREATE INDEX IF NOT EXISTS idx_location ON clientes_api_lazarza(province, city_name, district_name);

-- ============================================
-- Comentarios para la tabla clientes_api_lazarza
-- ============================================

COMMENT ON TABLE clientes_api_lazarza IS 'Tabla de clientes sincronizados con API de Fidelización Lazarza';
COMMENT ON COLUMN clientes_api_lazarza.code IS 'Código único del cliente en la API';
COMMENT ON COLUMN clientes_api_lazarza.person_customer IS 'Indica si es una persona física';
COMMENT ON COLUMN clientes_api_lazarza.person_name IS 'Nombre(s) del cliente';
COMMENT ON COLUMN clientes_api_lazarza.person_last_name IS 'Apellido paterno';
COMMENT ON COLUMN clientes_api_lazarza.person_last_name2 IS 'Apellido materno';
COMMENT ON COLUMN clientes_api_lazarza.person_birth_date IS 'Fecha de nacimiento';
COMMENT ON COLUMN clientes_api_lazarza.tax_reg_nr IS 'RFC del cliente (13 caracteres)';
COMMENT ON COLUMN clientes_api_lazarza.person_gender IS 'Género: M=Masculino, F=Femenino, O=Otro/Prefiero no especificar';
COMMENT ON COLUMN clientes_api_lazarza.email_club_lazarza IS 'Email para el programa de fidelización';
COMMENT ON COLUMN clientes_api_lazarza.phone IS 'Teléfono de contacto';
COMMENT ON COLUMN clientes_api_lazarza.province IS 'Estado/Provincia de residencia';
COMMENT ON COLUMN clientes_api_lazarza.city_name IS 'Ciudad de residencia';
COMMENT ON COLUMN clientes_api_lazarza.district_name IS 'Municipio o distrito';
COMMENT ON COLUMN clientes_api_lazarza.closed IS 'Cliente cerrado/inactivo en la API';
COMMENT ON COLUMN clientes_api_lazarza.registration_channel IS 'Canal de registro: WEB, POS (Punto de Venta), PHONE (Telefónico), API, MANUAL';
COMMENT ON COLUMN clientes_api_lazarza.registered_by_user_id IS 'ID del usuario administrador que realizó el registro';
COMMENT ON COLUMN clientes_api_lazarza.registration_ip IS 'Dirección IP desde donde se registró el cliente';
COMMENT ON COLUMN clientes_api_lazarza.registration_user_agent IS 'User-Agent del navegador o dispositivo (para análisis de dispositivos)';
COMMENT ON COLUMN clientes_api_lazarza.created_at IS 'Fecha de creación del registro';
COMMENT ON COLUMN clientes_api_lazarza.updated_at IS 'Fecha de última actualización';
COMMENT ON COLUMN clientes_api_lazarza.last_sync_at IS 'Fecha de última sincronización con la API';

-- ============================================
-- Función para actualizar updated_at automáticamente
-- ============================================

CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- Trigger para actualizar updated_at en clientes_api_lazarza
-- ============================================

DROP TRIGGER IF EXISTS trigger_update_clientes_api_lazarza ON clientes_api_lazarza;
CREATE TRIGGER trigger_update_clientes_api_lazarza
BEFORE UPDATE ON clientes_api_lazarza
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Tabla: clientes_credenciales
-- Descripción: Almacena las credenciales de acceso (contraseñas hasheadas)
-- Nota: Tabla separada por seguridad y privacidad
-- ============================================

CREATE TABLE IF NOT EXISTS clientes_credenciales (
    -- Identificador único local
    id BIGSERIAL PRIMARY KEY,
    
    -- Relación con cliente
    cliente_id BIGINT NOT NULL UNIQUE,
    
    -- Credenciales
    password_hash VARCHAR(255) NOT NULL,
    password_algorithm VARCHAR(50) DEFAULT 'bcrypt',
    
    -- Control de acceso
    activo BOOLEAN DEFAULT TRUE,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    
    -- Auditoría de contraseña
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    password_changed_at TIMESTAMP NULL,
    
    -- Clave foránea
    CONSTRAINT fk_cliente_credencial FOREIGN KEY (cliente_id) 
        REFERENCES clientes_api_lazarza(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

-- Índices para clientes_credenciales
CREATE INDEX IF NOT EXISTS idx_cliente_id ON clientes_credenciales(cliente_id);
CREATE INDEX IF NOT EXISTS idx_activo ON clientes_credenciales(activo);
CREATE INDEX IF NOT EXISTS idx_bloqueado ON clientes_credenciales(bloqueado_hasta);

-- ============================================
-- Comentarios para la tabla clientes_credenciales
-- ============================================

COMMENT ON TABLE clientes_credenciales IS 'Tabla de credenciales de acceso (contraseñas hasheadas)';
COMMENT ON COLUMN clientes_credenciales.cliente_id IS 'ID del cliente (relación uno a uno)';
COMMENT ON COLUMN clientes_credenciales.password_hash IS 'Contraseña hasheada con bcrypt/Argon2';
COMMENT ON COLUMN clientes_credenciales.password_algorithm IS 'Algoritmo de hash usado (bcrypt, argon2id, etc)';
COMMENT ON COLUMN clientes_credenciales.activo IS 'Credencial activa/habilitada';
COMMENT ON COLUMN clientes_credenciales.intentos_fallidos IS 'Número de intentos fallidos de login';
COMMENT ON COLUMN clientes_credenciales.bloqueado_hasta IS 'Fecha hasta la cual está bloqueado por intentos fallidos';
COMMENT ON COLUMN clientes_credenciales.created_at IS 'Fecha de creación de credencial';
COMMENT ON COLUMN clientes_credenciales.updated_at IS 'Fecha de último cambio';
COMMENT ON COLUMN clientes_credenciales.last_login_at IS 'Último acceso exitoso';
COMMENT ON COLUMN clientes_credenciales.password_changed_at IS 'Última vez que se cambió la contraseña';

-- ============================================
-- Trigger para actualizar updated_at en clientes_credenciales
-- ============================================

DROP TRIGGER IF EXISTS trigger_update_clientes_credenciales ON clientes_credenciales;
CREATE TRIGGER trigger_update_clientes_credenciales
BEFORE UPDATE ON clientes_credenciales
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Tabla: auditoria_registros
-- Descripción: Registra cada evento de registro, actualización y acceso de clientes
-- ============================================

CREATE TABLE IF NOT EXISTS auditoria_registros (
    -- Identificador único local
    id BIGSERIAL PRIMARY KEY,
    
    -- Relación con cliente
    cliente_id BIGINT NOT NULL,
    
    -- Tipo de evento
    evento_tipo VARCHAR(50) NOT NULL,
    evento_descripcion TEXT,
    
    -- Canal e información de origen
    canal VARCHAR(50),
    usuario_id BIGINT,
    usuario_email VARCHAR(255),
    
    -- Información técnica
    ip_address VARCHAR(45),
    user_agent TEXT,
    dispositivo_tipo VARCHAR(50),
    
    -- Información de la sesión
    sesion_id VARCHAR(255),
    ubicacion_geografica VARCHAR(255),
    
    -- Detalles del cambio (para actualización)
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Clave foránea
    CONSTRAINT fk_auditoria_cliente FOREIGN KEY (cliente_id) 
        REFERENCES clientes_api_lazarza(id) 
        ON DELETE CASCADE
);

-- Índices para auditoria_registros
CREATE INDEX IF NOT EXISTS idx_cliente_id_audit ON auditoria_registros(cliente_id);
CREATE INDEX IF NOT EXISTS idx_evento_tipo ON auditoria_registros(evento_tipo);
CREATE INDEX IF NOT EXISTS idx_canal ON auditoria_registros(canal);
CREATE INDEX IF NOT EXISTS idx_created_at ON auditoria_registros(created_at);
CREATE INDEX IF NOT EXISTS idx_usuario_id_audit ON auditoria_registros(usuario_id);

-- ============================================
-- Comentarios para la tabla auditoria_registros
-- ============================================

COMMENT ON TABLE auditoria_registros IS 'Tabla de auditoría completa para rastrear eventos de clientes';
COMMENT ON COLUMN auditoria_registros.evento_tipo IS 'Tipo de evento: REGISTRO, UPDATE, LOGIN, DELETE, etc.';
COMMENT ON COLUMN auditoria_registros.canal IS 'Canal: WEB, POS, PHONE, API, MANUAL';
COMMENT ON COLUMN auditoria_registros.dispositivo_tipo IS 'Tipo de dispositivo: MOBILE, DESKTOP, TABLET, KIOSK, etc.';
COMMENT ON COLUMN auditoria_registros.datos_anteriores IS 'Datos anteriores en formato JSON (para updates)';
COMMENT ON COLUMN auditoria_registros.datos_nuevos IS 'Datos nuevos en formato JSON (para updates)';
