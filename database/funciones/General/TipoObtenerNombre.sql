DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_type
        WHERE typname = 'nombre_completo_t'
    ) THEN
        CREATE DOMAIN nombre_completo_t AS VARCHAR(255)
        NOT NULL
        DEFAULT 'Sin nombre';
    END IF;
END
$$;
