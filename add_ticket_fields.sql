-- Añadir campos de ticket a la tabla compras
-- Ejecutar este script directamente en PostgreSQL

-- Conectarse a la base de datos
\c postgres;
SET search_path TO appweb, public;

-- Añadir nuevos campos a la tabla compras
ALTER TABLE compras 
ADD COLUMN numero_ticket VARCHAR(50) UNIQUE,
ADD COLUMN descripcion TEXT,
ADD COLUMN metodo_pago VARCHAR(20) DEFAULT 'efectivo' CHECK (metodo_pago IN ('efectivo', 'tarjeta', 'transferencia')),
ADD COLUMN fecha_compra TIMESTAMP;

-- Crear índices para mejor rendimiento
CREATE INDEX idx_compras_numero_ticket ON compras(numero_ticket);
CREATE INDEX idx_compras_fecha_compra ON compras(fecha_compra);

-- Verificar que los campos se añadieron correctamente
SELECT column_name, data_type, is_nullable, column_default 
FROM information_schema.columns 
WHERE table_name = 'compras' 
AND table_schema = 'appweb'
ORDER BY ordinal_position;

-- Mostrar estructura final de la tabla
\d compras;