CREATE OR REPLACE FUNCTION obtener_nombre_completo(p_persona_id INT)
RETURNS nombre_completo_t
LANGUAGE sql
STABLE
AS $$
    SELECT COALESCE(
        pj.razon_social,
        TRIM(CONCAT_WS(' ',
            p.primer_nombre,
            p.segundo_nombre,
            pn.primer_apellido,
            pn.segundo_apellido
        )),
        'Sin nombre'
    )::nombre_completo_t 
    FROM personas p
    LEFT JOIN personas_juridicas pj ON p.id = pj.persona_id
    LEFT JOIN personas_naturales pn ON p.id = pn.persona_id
    WHERE p.id = p_persona_id;
$$;
