# Diccionario del esquema actualizado

Base principal local. Consulta de solo lectura: 2026-10-30T03:36:58+00:00. 149 tablas, 1706 columnas y 366 claves foráneas.

Este inventario describe la estructura observada; no certifica por sí solo las formas normales.

## act_plan_activos

Registros: 311.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `plan_id` | bigint | NO |
| `activo_id` | bigint | NO |

Restricciones:

- `act_plan_activos_activo_id_foreign`: `FOREIGN KEY (activo_id) REFERENCES inv_activos(id) ON DELETE CASCADE`.
- `act_plan_activos_activo_id_not_null`: `NOT NULL activo_id`.
- `act_plan_activos_pkey`: `PRIMARY KEY (plan_id, activo_id)`.
- `act_plan_activos_plan_id_foreign`: `FOREIGN KEY (plan_id) REFERENCES inv_planes_mantenimiento(id) ON DELETE CASCADE`.
- `act_plan_activos_plan_id_not_null`: `NOT NULL plan_id`.

## auditoria_jobs

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `usuario_id` | bigint | YES |
| `tipo_job` | character varying | NO |
| `nombre_job` | character varying | NO |
| `tipo_ejecucion` | character varying | NO |
| `estado` | character varying | NO |
| `mensaje` | text | YES |
| `ejecutado_en` | timestamp without time zone | YES |
| `completado_en` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `auditoria_jobs_estado_not_null`: `NOT NULL estado`.
- `auditoria_jobs_id_not_null`: `NOT NULL id`.
- `auditoria_jobs_nombre_job_not_null`: `NOT NULL nombre_job`.
- `auditoria_jobs_pkey`: `PRIMARY KEY (id)`.
- `auditoria_jobs_tipo_ejecucion_not_null`: `NOT NULL tipo_ejecucion`.
- `auditoria_jobs_tipo_job_not_null`: `NOT NULL tipo_job`.
- `auditoria_jobs_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL`.

## auditoria_reportes

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `usuario_id` | bigint | YES |
| `tipo_reporte` | character varying | NO |
| `parametros` | json | YES |
| `ruta_archivo` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `auditoria_reportes_id_not_null`: `NOT NULL id`.
- `auditoria_reportes_pkey`: `PRIMARY KEY (id)`.
- `auditoria_reportes_tipo_reporte_not_null`: `NOT NULL tipo_reporte`.
- `auditoria_reportes_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL`.

## audits

Registros: 95.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `user_type` | character varying | YES |
| `user_id` | bigint | YES |
| `event` | character varying | NO |
| `auditable_type` | character varying | NO |
| `auditable_id` | bigint | NO |
| `old_values` | text | YES |
| `new_values` | text | YES |
| `url` | text | YES |
| `ip_address` | inet | YES |
| `user_agent` | character varying | YES |
| `tags` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `audits_auditable_id_not_null`: `NOT NULL auditable_id`.
- `audits_auditable_type_not_null`: `NOT NULL auditable_type`.
- `audits_event_not_null`: `NOT NULL event`.
- `audits_id_not_null`: `NOT NULL id`.
- `audits_pkey`: `PRIMARY KEY (id)`.

## cache

Registros: 17.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `key` | character varying | NO |
| `value` | text | NO |
| `expiration` | bigint | NO |

Restricciones:

- `cache_expiration_not_null`: `NOT NULL expiration`.
- `cache_key_not_null`: `NOT NULL key`.
- `cache_pkey`: `PRIMARY KEY (key)`.
- `cache_value_not_null`: `NOT NULL value`.

## cache_locks

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `key` | character varying | NO |
| `owner` | character varying | NO |
| `expiration` | bigint | NO |

Restricciones:

- `cache_locks_expiration_not_null`: `NOT NULL expiration`.
- `cache_locks_key_not_null`: `NOT NULL key`.
- `cache_locks_owner_not_null`: `NOT NULL owner`.
- `cache_locks_pkey`: `PRIMARY KEY (key)`.

## cargos_facturacion

Registros: 3.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `tipo` | smallint | NO |
| `modo_calculo` | smallint | NO |
| `valor` | numeric | NO |
| `base_calculo` | smallint | NO |
| `orden` | smallint | NO |
| `obligatorio` | boolean | NO |
| `permite_modificacion` | boolean | NO |
| `areas` | jsonb | YES |
| `fecha_inicio` | date | YES |
| `fecha_fin` | date | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `cargos_facturacion_base_calculo_not_null`: `NOT NULL base_calculo`.
- `cargos_facturacion_codigo_not_null`: `NOT NULL codigo`.
- `cargos_facturacion_codigo_unique`: `UNIQUE (codigo)`.
- `cargos_facturacion_estado_not_null`: `NOT NULL estado`.
- `cargos_facturacion_id_not_null`: `NOT NULL id`.
- `cargos_facturacion_modo_calculo_not_null`: `NOT NULL modo_calculo`.
- `cargos_facturacion_nombre_not_null`: `NOT NULL nombre`.
- `cargos_facturacion_obligatorio_not_null`: `NOT NULL obligatorio`.
- `cargos_facturacion_orden_not_null`: `NOT NULL orden`.
- `cargos_facturacion_permite_modificacion_not_null`: `NOT NULL permite_modificacion`.
- `cargos_facturacion_pkey`: `PRIMARY KEY (id)`.
- `cargos_facturacion_tipo_not_null`: `NOT NULL tipo`.
- `cargos_facturacion_valor_not_null`: `NOT NULL valor`.
- `chk_cargos_base`: `CHECK (((base_calculo >= 1) AND (base_calculo <= 4)))`.
- `chk_cargos_estado`: `CHECK ((estado = ANY (ARRAY[0, 1])))`.
- `chk_cargos_modo`: `CHECK (((modo_calculo >= 1) AND (modo_calculo <= 3)))`.
- `chk_cargos_tipo`: `CHECK (((tipo >= 1) AND (tipo <= 6)))`.
- `chk_cargos_valor`: `CHECK ((valor >= (0)::numeric))`.
- `chk_cargos_vigencia`: `CHECK (((fecha_fin IS NULL) OR (fecha_inicio <= fecha_fin)))`.

## catalogo_tipos

Registros: 15.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `catalogo_tipos_codigo_not_null`: `NOT NULL codigo`.
- `catalogo_tipos_codigo_unique`: `UNIQUE (codigo)`.
- `catalogo_tipos_estado_not_null`: `NOT NULL estado`.
- `catalogo_tipos_id_not_null`: `NOT NULL id`.
- `catalogo_tipos_nombre_not_null`: `NOT NULL nombre`.
- `catalogo_tipos_pkey`: `PRIMARY KEY (id)`.

## catalogos

Registros: 134.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `catalogo_tipo_id` | bigint | NO |
| `padre_id` | bigint | YES |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `prefijo` | character varying | YES |
| `descripcion` | text | YES |
| `orden` | integer | NO |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `catalogos_catalogo_tipo_id_foreign`: `FOREIGN KEY (catalogo_tipo_id) REFERENCES catalogo_tipos(id)`.
- `catalogos_catalogo_tipo_id_not_null`: `NOT NULL catalogo_tipo_id`.
- `catalogos_codigo_not_null`: `NOT NULL codigo`.
- `catalogos_estado_not_null`: `NOT NULL estado`.
- `catalogos_id_not_null`: `NOT NULL id`.
- `catalogos_nombre_not_null`: `NOT NULL nombre`.
- `catalogos_orden_not_null`: `NOT NULL orden`.
- `catalogos_padre_id_foreign`: `FOREIGN KEY (padre_id) REFERENCES catalogos(id)`.
- `catalogos_pkey`: `PRIMARY KEY (id)`.
- `uq_catalogos_tipo_codigo`: `UNIQUE (catalogo_tipo_id, codigo)`.

## clientes

Registros: 151.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `persona_id` | bigint | NO |
| `catalogo_id` | bigint | NO |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `stripe_customer_id` | character varying | YES |

Restricciones:

- `clientes_catalogo_id_foreign`: `FOREIGN KEY (catalogo_id) REFERENCES catalogos(id) ON DELETE RESTRICT`.
- `clientes_catalogo_id_not_null`: `NOT NULL catalogo_id`.
- `clientes_estado_not_null`: `NOT NULL estado`.
- `clientes_id_not_null`: `NOT NULL id`.
- `clientes_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE`.
- `clientes_persona_id_not_null`: `NOT NULL persona_id`.
- `clientes_persona_id_unique`: `UNIQUE (persona_id)`.
- `clientes_pkey`: `PRIMARY KEY (id)`.
- `clientes_stripe_customer_id_unique`: `UNIQUE (stripe_customer_id)`.

## colaborador_cargos_historial

Registros: 22.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `colaborador_id` | bigint | NO |
| `cargo_id` | bigint | NO |
| `departamento_id` | bigint | YES |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `colaborador_cargos_historial_cargo_id_foreign`: `FOREIGN KEY (cargo_id) REFERENCES catalogos(id) ON DELETE CASCADE`.
- `colaborador_cargos_historial_cargo_id_not_null`: `NOT NULL cargo_id`.
- `colaborador_cargos_historial_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE`.
- `colaborador_cargos_historial_colaborador_id_not_null`: `NOT NULL colaborador_id`.
- `colaborador_cargos_historial_departamento_id_foreign`: `FOREIGN KEY (departamento_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `colaborador_cargos_historial_estado_not_null`: `NOT NULL estado`.
- `colaborador_cargos_historial_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `colaborador_cargos_historial_id_not_null`: `NOT NULL id`.
- `colaborador_cargos_historial_pkey`: `PRIMARY KEY (id)`.

## colaborador_contactos_emergencia

Registros: 16.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `colaborador_id` | bigint | NO |
| `nombre` | character varying | NO |
| `telefono` | character varying | NO |
| `parentesco` | character varying | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `colaborador_contactos_emergencia_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE`.
- `colaborador_contactos_emergencia_colaborador_id_not_null`: `NOT NULL colaborador_id`.
- `colaborador_contactos_emergencia_estado_not_null`: `NOT NULL estado`.
- `colaborador_contactos_emergencia_id_not_null`: `NOT NULL id`.
- `colaborador_contactos_emergencia_nombre_not_null`: `NOT NULL nombre`.
- `colaborador_contactos_emergencia_pkey`: `PRIMARY KEY (id)`.
- `colaborador_contactos_emergencia_telefono_not_null`: `NOT NULL telefono`.

## colaborador_datos_medicos

Registros: 15.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `colaborador_id` | bigint | NO |
| `tipo_sangre` | character varying | YES |
| `alergias` | text | YES |
| `enfermedades_cronicas` | text | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `colaborador_datos_medicos_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE`.
- `colaborador_datos_medicos_colaborador_id_not_null`: `NOT NULL colaborador_id`.
- `colaborador_datos_medicos_colaborador_id_unique`: `UNIQUE (colaborador_id)`.
- `colaborador_datos_medicos_estado_not_null`: `NOT NULL estado`.
- `colaborador_datos_medicos_id_not_null`: `NOT NULL id`.
- `colaborador_datos_medicos_pkey`: `PRIMARY KEY (id)`.

## colaborador_documentos

Registros: 30.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `colaborador_id` | bigint | NO |
| `tipo` | character varying | NO |
| `archivo` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `colaborador_documentos_archivo_not_null`: `NOT NULL archivo`.
- `colaborador_documentos_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE`.
- `colaborador_documentos_colaborador_id_not_null`: `NOT NULL colaborador_id`.
- `colaborador_documentos_id_not_null`: `NOT NULL id`.
- `colaborador_documentos_pkey`: `PRIMARY KEY (id)`.
- `colaborador_documentos_tipo_not_null`: `NOT NULL tipo`.

## colaborador_salarios

Registros: 22.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `colaborador_id` | bigint | NO |
| `salario` | numeric | NO |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `colaborador_salarios_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE`.
- `colaborador_salarios_colaborador_id_not_null`: `NOT NULL colaborador_id`.
- `colaborador_salarios_estado_not_null`: `NOT NULL estado`.
- `colaborador_salarios_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `colaborador_salarios_id_not_null`: `NOT NULL id`.
- `colaborador_salarios_pkey`: `PRIMARY KEY (id)`.
- `colaborador_salarios_salario_not_null`: `NOT NULL salario`.

## colaboradores

Registros: 15.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `persona_id` | bigint | NO |
| `codigo` | character varying | NO |
| `nss` | character varying | YES |
| `fecha_ingreso` | date | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `colaboradores_codigo_not_null`: `NOT NULL codigo`.
- `colaboradores_codigo_unique`: `UNIQUE (codigo)`.
- `colaboradores_estado_not_null`: `NOT NULL estado`.
- `colaboradores_id_not_null`: `NOT NULL id`.
- `colaboradores_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE`.
- `colaboradores_persona_id_not_null`: `NOT NULL persona_id`.
- `colaboradores_pkey`: `PRIMARY KEY (id)`.

## compra_historial

Registros: 36039.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `model_type` | character varying | NO |
| `model_id` | bigint | NO |
| `estado_anterior` | character varying | YES |
| `estado_nuevo` | character varying | NO |
| `usuario_id` | bigint | YES |
| `comentario` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `compra_historial_estado_nuevo_not_null`: `NOT NULL estado_nuevo`.
- `compra_historial_id_not_null`: `NOT NULL id`.
- `compra_historial_model_id_not_null`: `NOT NULL model_id`.
- `compra_historial_model_type_not_null`: `NOT NULL model_type`.
- `compra_historial_pkey`: `PRIMARY KEY (id)`.
- `compra_historial_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id)`.

## conflictos_identidad

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `persona_id` | bigint | YES |
| `tipo_conflicto` | character varying | NO |
| `datos_providos` | json | NO |
| `datos_existentes` | json | NO |
| `estado` | character varying | NO |
| `creado_por` | bigint | YES |
| `resuelto_por` | bigint | YES |
| `resuelto_en` | timestamp without time zone | YES |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `conflictos_identidad_creado_por_foreign`: `FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `conflictos_identidad_datos_existentes_not_null`: `NOT NULL datos_existentes`.
- `conflictos_identidad_datos_providos_not_null`: `NOT NULL datos_providos`.
- `conflictos_identidad_estado_not_null`: `NOT NULL estado`.
- `conflictos_identidad_id_not_null`: `NOT NULL id`.
- `conflictos_identidad_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE SET NULL`.
- `conflictos_identidad_pkey`: `PRIMARY KEY (id)`.
- `conflictos_identidad_resuelto_por_foreign`: `FOREIGN KEY (resuelto_por) REFERENCES users(id) ON DELETE SET NULL`.
- `conflictos_identidad_tipo_conflicto_not_null`: `NOT NULL tipo_conflicto`.

## cotizacion_items

Registros: 59925.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `cotizacion_id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `precio_unitario` | numeric | NO |
| `subtotal` | numeric | NO |
| `es_elegido` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `cotizacion_items_cantidad_not_null`: `NOT NULL cantidad`.
- `cotizacion_items_cotizacion_id_foreign`: `FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE`.
- `cotizacion_items_cotizacion_id_not_null`: `NOT NULL cotizacion_id`.
- `cotizacion_items_es_elegido_not_null`: `NOT NULL es_elegido`.
- `cotizacion_items_id_not_null`: `NOT NULL id`.
- `cotizacion_items_pkey`: `PRIMARY KEY (id)`.
- `cotizacion_items_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `cotizacion_items_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `cotizacion_items_producto_id_not_null`: `NOT NULL producto_id`.
- `cotizacion_items_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `cotizacion_items_subtotal_not_null`: `NOT NULL subtotal`.

## cotizaciones

Registros: 15325.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `solicitud_id` | bigint | NO |
| `proveedor_id` | bigint | NO |
| `fecha_cotizacion` | date | NO |
| `fecha_vencimiento` | date | YES |
| `dias_entrega` | integer | NO |
| `condicion_pago_id` | bigint | YES |
| `subtotal` | numeric | NO |
| `impuestos` | numeric | NO |
| `costo_envio` | numeric | NO |
| `descuento` | numeric | NO |
| `total` | numeric | NO |
| `moneda_id` | bigint | NO |
| `tasa_cambio` | numeric | NO |
| `archivo_pdf` | character varying | YES |
| `es_elegida` | boolean | NO |
| `estado` | integer | NO |
| `observaciones` | text | YES |
| `creada_por` | bigint | YES |
| `elegida_por` | bigint | YES |
| `elegida_en` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `cotizaciones_condicion_pago_id_foreign`: `FOREIGN KEY (condicion_pago_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `cotizaciones_costo_envio_not_null`: `NOT NULL costo_envio`.
- `cotizaciones_creada_por_foreign`: `FOREIGN KEY (creada_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cotizaciones_descuento_not_null`: `NOT NULL descuento`.
- `cotizaciones_dias_entrega_not_null`: `NOT NULL dias_entrega`.
- `cotizaciones_elegida_por_foreign`: `FOREIGN KEY (elegida_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cotizaciones_es_elegida_not_null`: `NOT NULL es_elegida`.
- `cotizaciones_estado_not_null`: `NOT NULL estado`.
- `cotizaciones_fecha_cotizacion_not_null`: `NOT NULL fecha_cotizacion`.
- `cotizaciones_id_not_null`: `NOT NULL id`.
- `cotizaciones_impuestos_not_null`: `NOT NULL impuestos`.
- `cotizaciones_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE CASCADE`.
- `cotizaciones_moneda_id_not_null`: `NOT NULL moneda_id`.
- `cotizaciones_pkey`: `PRIMARY KEY (id)`.
- `cotizaciones_proveedor_id_foreign`: `FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE`.
- `cotizaciones_proveedor_id_not_null`: `NOT NULL proveedor_id`.
- `cotizaciones_solicitud_id_foreign`: `FOREIGN KEY (solicitud_id) REFERENCES solicitudes_compra(id) ON DELETE CASCADE`.
- `cotizaciones_solicitud_id_not_null`: `NOT NULL solicitud_id`.
- `cotizaciones_subtotal_not_null`: `NOT NULL subtotal`.
- `cotizaciones_tasa_cambio_not_null`: `NOT NULL tasa_cambio`.
- `cotizaciones_total_not_null`: `NOT NULL total`.

## cuenta_cargos

Registros: 3.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `cuenta_id` | bigint | NO |
| `cargo_id` | bigint | YES |
| `tipo` | smallint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `modo_calculo` | smallint | NO |
| `valor` | numeric | NO |
| `base_calculo` | smallint | NO |
| `base_monto` | numeric | NO |
| `monto` | numeric | NO |
| `origen_type` | character varying | YES |
| `origen_id` | bigint | YES |
| `aplicado_por` | bigint | YES |
| `estado` | smallint | NO |
| `observaciones` | text | YES |
| `anulado_por` | bigint | YES |
| `anulado_en` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `moneda_id` | bigint | NO |

Restricciones:

- `chk_cuenta_cargos_base`: `CHECK (((base_calculo >= 1) AND (base_calculo <= 4)))`.
- `chk_cuenta_cargos_estado`: `CHECK ((estado = ANY (ARRAY[0, 1])))`.
- `chk_cuenta_cargos_modo`: `CHECK (((modo_calculo >= 1) AND (modo_calculo <= 3)))`.
- `chk_cuenta_cargos_monto`: `CHECK ((monto >= (0)::numeric))`.
- `chk_cuenta_cargos_tipo`: `CHECK (((tipo >= 1) AND (tipo <= 6)))`.
- `cuenta_cargos_anulado_por_foreign`: `FOREIGN KEY (anulado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cuenta_cargos_aplicado_por_foreign`: `FOREIGN KEY (aplicado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cuenta_cargos_base_calculo_not_null`: `NOT NULL base_calculo`.
- `cuenta_cargos_base_monto_not_null`: `NOT NULL base_monto`.
- `cuenta_cargos_cargo_id_foreign`: `FOREIGN KEY (cargo_id) REFERENCES cargos_facturacion(id) ON DELETE SET NULL`.
- `cuenta_cargos_codigo_not_null`: `NOT NULL codigo`.
- `cuenta_cargos_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `cuenta_cargos_cuenta_id_not_null`: `NOT NULL cuenta_id`.
- `cuenta_cargos_estado_not_null`: `NOT NULL estado`.
- `cuenta_cargos_id_not_null`: `NOT NULL id`.
- `cuenta_cargos_modo_calculo_not_null`: `NOT NULL modo_calculo`.
- `cuenta_cargos_moneda_id_not_null`: `NOT NULL moneda_id`.
- `cuenta_cargos_monto_not_null`: `NOT NULL monto`.
- `cuenta_cargos_nombre_not_null`: `NOT NULL nombre`.
- `cuenta_cargos_pkey`: `PRIMARY KEY (id)`.
- `cuenta_cargos_tipo_not_null`: `NOT NULL tipo`.
- `cuenta_cargos_valor_not_null`: `NOT NULL valor`.
- `fk_cuenta_cargos_moneda`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.

## cuenta_detalles

Registros: 22075.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `cuenta_id` | bigint | NO |
| `origen_type` | character varying | YES |
| `origen_id` | bigint | YES |
| `tipo_detalle` | smallint | YES |
| `espacio_id` | bigint | YES |
| `concepto` | character varying | NO |
| `descripcion` | text | YES |
| `cantidad` | numeric | NO |
| `precio_unitario` | numeric | NO |
| `subtotal` | numeric | NO |
| `total` | numeric | NO |
| `estado` | smallint | NO |
| `metadatos` | jsonb | YES |
| `creador_id` | bigint | YES |
| `anulado_por` | bigint | YES |
| `anulado_en` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `moneda_id` | bigint | NO |

Restricciones:

- `cuenta_detalles_anulado_por_foreign`: `FOREIGN KEY (anulado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cuenta_detalles_cantidad_not_null`: `NOT NULL cantidad`.
- `cuenta_detalles_concepto_not_null`: `NOT NULL concepto`.
- `cuenta_detalles_creador_id_foreign`: `FOREIGN KEY (creador_id) REFERENCES users(id) ON DELETE SET NULL`.
- `cuenta_detalles_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `cuenta_detalles_cuenta_id_not_null`: `NOT NULL cuenta_id`.
- `cuenta_detalles_espacio_id_foreign`: `FOREIGN KEY (espacio_id) REFERENCES espacios(id) ON DELETE SET NULL`.
- `cuenta_detalles_estado_not_null`: `NOT NULL estado`.
- `cuenta_detalles_id_not_null`: `NOT NULL id`.
- `cuenta_detalles_moneda_id_not_null`: `NOT NULL moneda_id`.
- `cuenta_detalles_pkey`: `PRIMARY KEY (id)`.
- `cuenta_detalles_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `cuenta_detalles_subtotal_not_null`: `NOT NULL subtotal`.
- `cuenta_detalles_total_not_null`: `NOT NULL total`.
- `fk_cuenta_detalles_moneda`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.

## cuentas

Registros: 9771.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `numero_cuenta` | character varying | NO |
| `tipo_cuenta` | smallint | NO |
| `estado` | smallint | NO |
| `cliente_id` | bigint | YES |
| `estancia_id` | bigint | YES |
| `reserva_id` | bigint | YES |
| `moneda_id` | bigint | YES |
| `limite_autorizado` | numeric | YES |
| `subtotal` | numeric | NO |
| `descuento_total` | numeric | NO |
| `impuesto_total` | numeric | NO |
| `cargo_servicio_total` | numeric | NO |
| `propina_total` | numeric | NO |
| `recargo_total` | numeric | NO |
| `total` | numeric | NO |
| `total_pagado` | numeric | NO |
| `saldo` | numeric | NO |
| `abierta_at` | timestamp without time zone | NO |
| `cerrada_at` | timestamp without time zone | YES |
| `abierta_por` | bigint | YES |
| `cerrada_por` | bigint | YES |
| `actualizado_por` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `cuentas_abierta_at_not_null`: `NOT NULL abierta_at`.
- `cuentas_abierta_por_foreign`: `FOREIGN KEY (abierta_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cuentas_actualizado_por_foreign`: `FOREIGN KEY (actualizado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cuentas_cargo_servicio_total_not_null`: `NOT NULL cargo_servicio_total`.
- `cuentas_cerrada_por_foreign`: `FOREIGN KEY (cerrada_por) REFERENCES users(id) ON DELETE SET NULL`.
- `cuentas_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL`.
- `cuentas_descuento_total_not_null`: `NOT NULL descuento_total`.
- `cuentas_estado_not_null`: `NOT NULL estado`.
- `cuentas_estancia_id_foreign`: `FOREIGN KEY (estancia_id) REFERENCES estancias(id) ON DELETE RESTRICT`.
- `cuentas_estancia_reserva_fk`: `FOREIGN KEY (estancia_id, reserva_id) REFERENCES estancias(id, reserva_id) ON DELETE RESTRICT`.
- `cuentas_id_not_null`: `NOT NULL id`.
- `cuentas_impuesto_total_not_null`: `NOT NULL impuesto_total`.
- `cuentas_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE SET NULL`.
- `cuentas_numero_cuenta_not_null`: `NOT NULL numero_cuenta`.
- `cuentas_numero_cuenta_unique`: `UNIQUE (numero_cuenta)`.
- `cuentas_pkey`: `PRIMARY KEY (id)`.
- `cuentas_propina_total_not_null`: `NOT NULL propina_total`.
- `cuentas_recargo_total_not_null`: `NOT NULL recargo_total`.
- `cuentas_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE SET NULL`.
- `cuentas_saldo_not_null`: `NOT NULL saldo`.
- `cuentas_subtotal_not_null`: `NOT NULL subtotal`.
- `cuentas_tipo_cuenta_not_null`: `NOT NULL tipo_cuenta`.
- `cuentas_total_not_null`: `NOT NULL total`.
- `cuentas_total_pagado_not_null`: `NOT NULL total_pagado`.

## detalles_habitacion

Registros: 122.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `habitacion_id` | bigint | NO |
| `capacidad_adultos` | integer | NO |
| `capacidad_ninos` | integer | NO |
| `medidas` | numeric | YES |
| `vistas_legacy` | json | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `detalles_habitacion_capacidad_adultos_not_null`: `NOT NULL capacidad_adultos`.
- `detalles_habitacion_capacidad_ninos_not_null`: `NOT NULL capacidad_ninos`.
- `detalles_habitacion_habitacion_id_foreign`: `FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE`.
- `detalles_habitacion_habitacion_id_not_null`: `NOT NULL habitacion_id`.
- `detalles_habitacion_habitacion_id_unique`: `UNIQUE (habitacion_id)`.
- `detalles_habitacion_id_not_null`: `NOT NULL id`.
- `detalles_habitacion_pkey`: `PRIMARY KEY (id)`.

## devolucion_items

Registros: 356.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `devolucion_id` | bigint | NO |
| `lote_id` | bigint | YES |
| `recepcion_item_id` | bigint | YES |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `unidad_medida_id` | bigint | YES |
| `cantidad_devolver` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `devolucion_items_cantidad_devolver_not_null`: `NOT NULL cantidad_devolver`.
- `devolucion_items_devolucion_id_foreign`: `FOREIGN KEY (devolucion_id) REFERENCES devoluciones_compra(id) ON DELETE CASCADE`.
- `devolucion_items_devolucion_id_not_null`: `NOT NULL devolucion_id`.
- `devolucion_items_id_not_null`: `NOT NULL id`.
- `devolucion_items_lote_id_foreign`: `FOREIGN KEY (lote_id) REFERENCES inv_lotes(id) ON DELETE SET NULL`.
- `devolucion_items_pkey`: `PRIMARY KEY (id)`.
- `devolucion_items_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id)`.
- `devolucion_items_producto_id_not_null`: `NOT NULL producto_id`.
- `devolucion_items_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `devolucion_items_recepcion_item_id_foreign`: `FOREIGN KEY (recepcion_item_id) REFERENCES recepcion_items(id) ON DELETE SET NULL`.
- `devolucion_items_unidad_medida_id_foreign`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE SET NULL`.

## devoluciones_compra

Registros: 142.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `orden_compra_id` | bigint | NO |
| `recepcion_compra_id` | bigint | YES |
| `fecha_devolucion` | date | NO |
| `estado` | integer | NO |
| `motivo` | text | NO |
| `documento_externo` | character varying | YES |
| `creado_por_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `devoluciones_compra_codigo_not_null`: `NOT NULL codigo`.
- `devoluciones_compra_codigo_unique`: `UNIQUE (codigo)`.
- `devoluciones_compra_creado_por_id_foreign`: `FOREIGN KEY (creado_por_id) REFERENCES users(id)`.
- `devoluciones_compra_creado_por_id_not_null`: `NOT NULL creado_por_id`.
- `devoluciones_compra_estado_not_null`: `NOT NULL estado`.
- `devoluciones_compra_fecha_devolucion_not_null`: `NOT NULL fecha_devolucion`.
- `devoluciones_compra_id_not_null`: `NOT NULL id`.
- `devoluciones_compra_motivo_not_null`: `NOT NULL motivo`.
- `devoluciones_compra_orden_compra_id_foreign`: `FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE`.
- `devoluciones_compra_orden_compra_id_not_null`: `NOT NULL orden_compra_id`.
- `devoluciones_compra_pkey`: `PRIMARY KEY (id)`.
- `devoluciones_compra_recepcion_compra_id_foreign`: `FOREIGN KEY (recepcion_compra_id) REFERENCES recepciones_compra(id) ON DELETE SET NULL`.

## driverjs_tour_completions

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `user_id` | bigint | NO |
| `tour_name` | character varying | NO |
| `completed_at` | timestamp without time zone | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `driverjs_tour_completions_completed_at_not_null`: `NOT NULL completed_at`.
- `driverjs_tour_completions_id_not_null`: `NOT NULL id`.
- `driverjs_tour_completions_pkey`: `PRIMARY KEY (id)`.
- `driverjs_tour_completions_tour_name_not_null`: `NOT NULL tour_name`.
- `driverjs_tour_completions_user_id_foreign`: `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`.
- `driverjs_tour_completions_user_id_not_null`: `NOT NULL user_id`.
- `driverjs_tour_completions_user_id_tour_name_unique`: `UNIQUE (user_id, tour_name)`.

## espacios

Registros: 13.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `padre_id` | bigint | YES |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `descripcion` | text | YES |
| `tipo` | character varying | NO |
| `capacidad_personas` | integer | NO |
| `ubicacion_id` | bigint | YES |
| `estado` | smallint | NO |
| `orden` | integer | NO |
| `meta_datos` | json | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `web` | boolean | NO |
| `reservable` | boolean | NO |
| `reservable_id` | bigint | YES |
| `slug` | character varying | YES |
| `ubicacion_cocina_id` | bigint | YES |
| `ubicacion_bar_id` | bigint | YES |

Restricciones:

- `chk_espacios_capacidad`: `CHECK ((capacidad_personas >= 0))`.
- `chk_espacios_estado`: `CHECK ((estado = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))`.
- `espacios_capacidad_personas_not_null`: `NOT NULL capacidad_personas`.
- `espacios_codigo_not_null`: `NOT NULL codigo`.
- `espacios_codigo_unique`: `UNIQUE (codigo)`.
- `espacios_estado_not_null`: `NOT NULL estado`.
- `espacios_id_not_null`: `NOT NULL id`.
- `espacios_nombre_not_null`: `NOT NULL nombre`.
- `espacios_orden_not_null`: `NOT NULL orden`.
- `espacios_padre_id_foreign`: `FOREIGN KEY (padre_id) REFERENCES espacios(id) ON UPDATE CASCADE ON DELETE SET NULL`.
- `espacios_pkey`: `PRIMARY KEY (id)`.
- `espacios_reservable_id_foreign`: `FOREIGN KEY (reservable_id) REFERENCES recursos_reservables(id) ON DELETE SET NULL`.
- `espacios_reservable_id_unique`: `UNIQUE (reservable_id)`.
- `espacios_reservable_not_null`: `NOT NULL reservable`.
- `espacios_slug_unique`: `UNIQUE (slug)`.
- `espacios_tipo_not_null`: `NOT NULL tipo`.
- `espacios_ubicacion_bar_id_foreign`: `FOREIGN KEY (ubicacion_bar_id) REFERENCES ubicaciones(id) ON DELETE RESTRICT`.
- `espacios_ubicacion_cocina_id_foreign`: `FOREIGN KEY (ubicacion_cocina_id) REFERENCES ubicaciones(id) ON DELETE RESTRICT`.
- `espacios_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id)`.
- `espacios_web_not_null`: `NOT NULL web`.

## estancias

Registros: 9702.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `reserva_id` | bigint | NO |
| `habitacion_id` | bigint | YES |
| `usuario_check_in_id` | bigint | YES |
| `usuario_check_out_id` | bigint | YES |
| `check_in_at` | timestamp without time zone | NO |
| `check_out_at` | timestamp without time zone | YES |
| `cantidad_llaves` | smallint | NO |
| `estado` | smallint | NO |
| `observaciones_entrada` | text | YES |
| `observaciones_salida` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `reserva_detalle_id` | bigint | YES |
| `fecha_entrada_programada` | timestamp without time zone | YES |
| `fecha_salida_programada` | timestamp without time zone | YES |
| `fecha_check_in_real` | timestamp without time zone | YES |
| `fecha_check_out_real` | timestamp without time zone | YES |

Restricciones:

- `estancias_cantidad_llaves_not_null`: `NOT NULL cantidad_llaves`.
- `estancias_check_in_at_not_null`: `NOT NULL check_in_at`.
- `estancias_detalle_reserva_fk`: `FOREIGN KEY (reserva_detalle_id, reserva_id) REFERENCES reserva_detalles(id, reserva_id) ON DELETE RESTRICT`.
- `estancias_estado_not_null`: `NOT NULL estado`.
- `estancias_habitacion_id_foreign`: `FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE SET NULL`.
- `estancias_id_not_null`: `NOT NULL id`.
- `estancias_id_reserva_unique`: `UNIQUE (id, reserva_id)`.
- `estancias_pkey`: `PRIMARY KEY (id)`.
- `estancias_reserva_detalle_id_foreign`: `FOREIGN KEY (reserva_detalle_id) REFERENCES reserva_detalles(id) ON DELETE SET NULL`.
- `estancias_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE`.
- `estancias_reserva_id_not_null`: `NOT NULL reserva_id`.
- `estancias_usuario_check_in_id_foreign`: `FOREIGN KEY (usuario_check_in_id) REFERENCES users(id) ON DELETE SET NULL`.
- `estancias_usuario_check_out_id_foreign`: `FOREIGN KEY (usuario_check_out_id) REFERENCES users(id) ON DELETE SET NULL`.

## factura_autorizaciones_dgi

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `factura_serie_id` | bigint | NO |
| `numero_autorizacion` | character varying | NO |
| `ruc_emisor` | character varying | NO |
| `razon_social_emisor` | character varying | NO |
| `nombre_comercial_emisor` | character varying | YES |
| `direccion_emisor` | character varying | YES |
| `fecha_autorizacion` | date | NO |
| `vence_at` | date | YES |
| `rango_desde` | bigint | NO |
| `rango_hasta` | bigint | NO |
| `pie_imprenta_fiscal` | character varying | YES |
| `activa` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `factura_autorizaciones_dgi_activa_not_null`: `NOT NULL activa`.
- `factura_autorizaciones_dgi_factura_serie_id_foreign`: `FOREIGN KEY (factura_serie_id) REFERENCES factura_series(id) ON DELETE RESTRICT`.
- `factura_autorizaciones_dgi_factura_serie_id_not_null`: `NOT NULL factura_serie_id`.
- `factura_autorizaciones_dgi_fecha_autorizacion_not_null`: `NOT NULL fecha_autorizacion`.
- `factura_autorizaciones_dgi_id_not_null`: `NOT NULL id`.
- `factura_autorizaciones_dgi_numero_autorizacion_not_null`: `NOT NULL numero_autorizacion`.
- `factura_autorizaciones_dgi_numero_autorizacion_unique`: `UNIQUE (numero_autorizacion)`.
- `factura_autorizaciones_dgi_pkey`: `PRIMARY KEY (id)`.
- `factura_autorizaciones_dgi_rango_desde_not_null`: `NOT NULL rango_desde`.
- `factura_autorizaciones_dgi_rango_hasta_not_null`: `NOT NULL rango_hasta`.
- `factura_autorizaciones_dgi_razon_social_emisor_not_null`: `NOT NULL razon_social_emisor`.
- `factura_autorizaciones_dgi_ruc_emisor_not_null`: `NOT NULL ruc_emisor`.

## factura_detalles

Registros: 22036.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `factura_id` | bigint | NO |
| `venta_detalle_id` | bigint | YES |
| `codigo` | character varying | YES |
| `concepto` | character varying | NO |
| `unidad_medida` | character varying | NO |
| `cantidad` | numeric | NO |
| `precio_unitario` | numeric | NO |
| `subtotal` | numeric | NO |
| `descuento` | numeric | NO |
| `iva_porcentaje` | numeric | NO |
| `iva` | numeric | NO |
| `total_linea` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `origen_type` | character varying | YES |
| `origen_id` | bigint | YES |

Restricciones:

- `chk_fd_cantidad`: `CHECK ((cantidad > (0)::numeric))`.
- `chk_fd_totales`: `CHECK (((subtotal >= (0)::numeric) AND (iva >= (0)::numeric) AND (total_linea >= (0)::numeric)))`.
- `factura_detalles_cantidad_not_null`: `NOT NULL cantidad`.
- `factura_detalles_concepto_not_null`: `NOT NULL concepto`.
- `factura_detalles_descuento_not_null`: `NOT NULL descuento`.
- `factura_detalles_factura_id_foreign`: `FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE RESTRICT`.
- `factura_detalles_factura_id_not_null`: `NOT NULL factura_id`.
- `factura_detalles_id_not_null`: `NOT NULL id`.
- `factura_detalles_iva_not_null`: `NOT NULL iva`.
- `factura_detalles_iva_porcentaje_not_null`: `NOT NULL iva_porcentaje`.
- `factura_detalles_pkey`: `PRIMARY KEY (id)`.
- `factura_detalles_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `factura_detalles_subtotal_not_null`: `NOT NULL subtotal`.
- `factura_detalles_total_linea_not_null`: `NOT NULL total_linea`.
- `factura_detalles_unidad_medida_not_null`: `NOT NULL unidad_medida`.
- `factura_detalles_venta_detalle_id_foreign`: `FOREIGN KEY (venta_detalle_id) REFERENCES venta_detalles(id) ON DELETE SET NULL`.

## factura_folios

Registros: 9721.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `factura_serie_id` | bigint | NO |
| `factura_autorizacion_dgi_id` | bigint | NO |
| `factura_id` | bigint | YES |
| `numero_correlativo` | bigint | NO |
| `numero` | character varying | NO |
| `estado` | smallint | NO |
| `reservado_at` | timestamp without time zone | YES |
| `emitido_at` | timestamp without time zone | YES |
| `anulado_at` | timestamp without time zone | YES |
| `fallido_at` | timestamp without time zone | YES |
| `reservado_por` | bigint | YES |
| `motivo` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `chk_ff_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4])))`.
- `factura_folios_estado_not_null`: `NOT NULL estado`.
- `factura_folios_factura_autorizacion_dgi_id_foreign`: `FOREIGN KEY (factura_autorizacion_dgi_id) REFERENCES factura_autorizaciones_dgi(id) ON DELETE RESTRICT`.
- `factura_folios_factura_autorizacion_dgi_id_not_null`: `NOT NULL factura_autorizacion_dgi_id`.
- `factura_folios_factura_id_foreign`: `FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE SET NULL`.
- `factura_folios_factura_serie_id_foreign`: `FOREIGN KEY (factura_serie_id) REFERENCES factura_series(id) ON DELETE RESTRICT`.
- `factura_folios_factura_serie_id_not_null`: `NOT NULL factura_serie_id`.
- `factura_folios_id_not_null`: `NOT NULL id`.
- `factura_folios_numero_correlativo_not_null`: `NOT NULL numero_correlativo`.
- `factura_folios_numero_not_null`: `NOT NULL numero`.
- `factura_folios_pkey`: `PRIMARY KEY (id)`.
- `factura_folios_reservado_por_foreign`: `FOREIGN KEY (reservado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `uq_folio_serie_correlativo`: `UNIQUE (factura_serie_id, numero_correlativo)`.
- `uq_folio_serie_numero`: `UNIQUE (factura_serie_id, numero)`.

## factura_series

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `sucursal_codigo` | character varying | YES |
| `siguiente_numero` | bigint | NO |
| `activa` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `caja_id` | bigint | YES |

Restricciones:

- `factura_series_activa_not_null`: `NOT NULL activa`.
- `factura_series_caja_id_foreign`: `FOREIGN KEY (caja_id) REFERENCES tes_cajas(id) ON DELETE SET NULL`.
- `factura_series_codigo_not_null`: `NOT NULL codigo`.
- `factura_series_codigo_unique`: `UNIQUE (codigo)`.
- `factura_series_id_not_null`: `NOT NULL id`.
- `factura_series_nombre_not_null`: `NOT NULL nombre`.
- `factura_series_pkey`: `PRIMARY KEY (id)`.
- `factura_series_siguiente_numero_not_null`: `NOT NULL siguiente_numero`.

## facturas

Registros: 9726.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `factura_serie_id` | bigint | NO |
| `factura_autorizacion_dgi_id` | bigint | YES |
| `venta_id` | bigint | YES |
| `cuenta_id` | bigint | YES |
| `cliente_id` | bigint | YES |
| `tipo` | smallint | NO |
| `estado` | smallint | NO |
| `numero` | character varying | NO |
| `numero_correlativo` | bigint | NO |
| `fecha_emision` | timestamp without time zone | YES |
| `fecha_vencimiento` | date | YES |
| `moneda_id` | bigint | NO |
| `moneda_base_id` | bigint | NO |
| `tasa_cambio_id` | bigint | YES |
| `tasa_cambio` | numeric | NO |
| `subtotal` | numeric | NO |
| `descuento_total` | numeric | NO |
| `iva_total` | numeric | NO |
| `servicio_total` | numeric | NO |
| `propina_total` | numeric | NO |
| `recargo_total` | numeric | NO |
| `total` | numeric | NO |
| `subtotal_base` | numeric | NO |
| `iva_total_base` | numeric | NO |
| `total_base` | numeric | NO |
| `datos_receptor` | jsonb | YES |
| `pdf_path` | character varying | YES |
| `hash_documento` | character varying | YES |
| `motivo_anulacion` | text | YES |
| `anulada_at` | timestamp without time zone | YES |
| `emitida_por` | bigint | YES |
| `anulada_por` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `numero_autorizacion_dgi` | character varying | YES |
| `rango_autorizado_desde` | bigint | YES |
| `rango_autorizado_hasta` | bigint | YES |

Restricciones:

- `chk_facturas_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4])))`.
- `chk_facturas_tipo`: `CHECK ((tipo = ANY (ARRAY[1, 2, 3])))`.
- `chk_facturas_totales`: `CHECK (((subtotal >= (0)::numeric) AND (iva_total >= (0)::numeric) AND (total >= (0)::numeric)))`.
- `facturas_anulada_por_foreign`: `FOREIGN KEY (anulada_por) REFERENCES users(id) ON DELETE SET NULL`.
- `facturas_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL`.
- `facturas_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE SET NULL`.
- `facturas_descuento_total_not_null`: `NOT NULL descuento_total`.
- `facturas_emitida_por_foreign`: `FOREIGN KEY (emitida_por) REFERENCES users(id) ON DELETE SET NULL`.
- `facturas_estado_not_null`: `NOT NULL estado`.
- `facturas_factura_autorizacion_dgi_id_foreign`: `FOREIGN KEY (factura_autorizacion_dgi_id) REFERENCES factura_autorizaciones_dgi(id) ON DELETE SET NULL`.
- `facturas_factura_serie_id_foreign`: `FOREIGN KEY (factura_serie_id) REFERENCES factura_series(id) ON DELETE RESTRICT`.
- `facturas_factura_serie_id_not_null`: `NOT NULL factura_serie_id`.
- `facturas_hash_documento_unique`: `UNIQUE (hash_documento)`.
- `facturas_id_not_null`: `NOT NULL id`.
- `facturas_iva_total_base_not_null`: `NOT NULL iva_total_base`.
- `facturas_iva_total_not_null`: `NOT NULL iva_total`.
- `facturas_moneda_base_id_foreign`: `FOREIGN KEY (moneda_base_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `facturas_moneda_base_id_not_null`: `NOT NULL moneda_base_id`.
- `facturas_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `facturas_moneda_id_not_null`: `NOT NULL moneda_id`.
- `facturas_numero_correlativo_not_null`: `NOT NULL numero_correlativo`.
- `facturas_numero_not_null`: `NOT NULL numero`.
- `facturas_pkey`: `PRIMARY KEY (id)`.
- `facturas_propina_total_not_null`: `NOT NULL propina_total`.
- `facturas_recargo_total_not_null`: `NOT NULL recargo_total`.
- `facturas_servicio_total_not_null`: `NOT NULL servicio_total`.
- `facturas_subtotal_base_not_null`: `NOT NULL subtotal_base`.
- `facturas_subtotal_not_null`: `NOT NULL subtotal`.
- `facturas_tasa_cambio_id_foreign`: `FOREIGN KEY (tasa_cambio_id) REFERENCES tasas_cambio(id) ON DELETE SET NULL`.
- `facturas_tasa_cambio_not_null`: `NOT NULL tasa_cambio`.
- `facturas_tipo_not_null`: `NOT NULL tipo`.
- `facturas_total_base_not_null`: `NOT NULL total_base`.
- `facturas_total_not_null`: `NOT NULL total`.
- `facturas_venta_cuenta_fk`: `FOREIGN KEY (venta_id, cuenta_id) REFERENCES ventas(id, cuenta_id) ON DELETE RESTRICT`.
- `facturas_venta_id_foreign`: `FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE SET NULL`.
- `uq_factura_serie_correlativo`: `UNIQUE (factura_serie_id, numero_correlativo)`.
- `uq_factura_serie_numero`: `UNIQUE (factura_serie_id, numero)`.

## failed_jobs

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `uuid` | character varying | NO |
| `connection` | text | NO |
| `queue` | text | NO |
| `payload` | text | NO |
| `exception` | text | NO |
| `failed_at` | timestamp without time zone | NO |

Restricciones:

- `failed_jobs_connection_not_null`: `NOT NULL connection`.
- `failed_jobs_exception_not_null`: `NOT NULL exception`.
- `failed_jobs_failed_at_not_null`: `NOT NULL failed_at`.
- `failed_jobs_id_not_null`: `NOT NULL id`.
- `failed_jobs_payload_not_null`: `NOT NULL payload`.
- `failed_jobs_pkey`: `PRIMARY KEY (id)`.
- `failed_jobs_queue_not_null`: `NOT NULL queue`.
- `failed_jobs_uuid_not_null`: `NOT NULL uuid`.
- `failed_jobs_uuid_unique`: `UNIQUE (uuid)`.

## habitacion_historial

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `model_type` | character varying | NO |
| `model_id` | bigint | NO |
| `estado_anterior` | character varying | YES |
| `estado_nuevo` | character varying | NO |
| `usuario_id` | bigint | YES |
| `comentario` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `habitacion_historial_estado_nuevo_not_null`: `NOT NULL estado_nuevo`.
- `habitacion_historial_id_not_null`: `NOT NULL id`.
- `habitacion_historial_model_id_not_null`: `NOT NULL model_id`.
- `habitacion_historial_model_type_not_null`: `NOT NULL model_type`.
- `habitacion_historial_pkey`: `PRIMARY KEY (id)`.
- `habitacion_historial_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id)`.

## habitacion_vistas

Registros: 188.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `habitacion_id` | bigint | NO |
| `catalogo_id` | bigint | NO |
| `orden` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `habitacion_vistas_catalogo_id_foreign`: `FOREIGN KEY (catalogo_id) REFERENCES catalogos(id) ON DELETE RESTRICT`.
- `habitacion_vistas_catalogo_id_not_null`: `NOT NULL catalogo_id`.
- `habitacion_vistas_habitacion_id_catalogo_id_unique`: `UNIQUE (habitacion_id, catalogo_id)`.
- `habitacion_vistas_habitacion_id_foreign`: `FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE`.
- `habitacion_vistas_habitacion_id_not_null`: `NOT NULL habitacion_id`.
- `habitacion_vistas_id_not_null`: `NOT NULL id`.
- `habitacion_vistas_orden_not_null`: `NOT NULL orden`.
- `habitacion_vistas_pkey`: `PRIMARY KEY (id)`.

## habitaciones

Registros: 122.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `numero` | integer | YES |
| `slug` | character varying | NO |
| `nombre` | character varying | NO |
| `descripcion` | text | YES |
| `categoria_id` | bigint | NO |
| `ubicacion_id` | bigint | NO |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `reservable_id` | bigint | YES |

Restricciones:

- `chk_habitaciones_estado`: `CHECK ((estado = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))`.
- `habitaciones_categoria_id_foreign`: `FOREIGN KEY (categoria_id) REFERENCES catalogos(id)`.
- `habitaciones_categoria_id_not_null`: `NOT NULL categoria_id`.
- `habitaciones_codigo_not_null`: `NOT NULL codigo`.
- `habitaciones_codigo_unique`: `UNIQUE (codigo)`.
- `habitaciones_estado_not_null`: `NOT NULL estado`.
- `habitaciones_id_not_null`: `NOT NULL id`.
- `habitaciones_nombre_not_null`: `NOT NULL nombre`.
- `habitaciones_pkey`: `PRIMARY KEY (id)`.
- `habitaciones_reservable_id_foreign`: `FOREIGN KEY (reservable_id) REFERENCES recursos_reservables(id) ON DELETE SET NULL`.
- `habitaciones_reservable_id_unique`: `UNIQUE (reservable_id)`.
- `habitaciones_slug_not_null`: `NOT NULL slug`.
- `habitaciones_slug_unique`: `UNIQUE (slug)`.
- `habitaciones_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id)`.
- `habitaciones_ubicacion_id_not_null`: `NOT NULL ubicacion_id`.

## imagenes

Registros: 490.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `url` | character varying | NO |
| `public_id` | character varying | YES |
| `orden` | integer | NO |
| `imagenable_type` | character varying | NO |
| `imagenable_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `imagenes_id_not_null`: `NOT NULL id`.
- `imagenes_imagenable_id_not_null`: `NOT NULL imagenable_id`.
- `imagenes_imagenable_type_not_null`: `NOT NULL imagenable_type`.
- `imagenes_orden_not_null`: `NOT NULL orden`.
- `imagenes_pkey`: `PRIMARY KEY (id)`.
- `imagenes_url_not_null`: `NOT NULL url`.

## inv_activo_asignaciones

Registros: 32432.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `activo_id` | bigint | NO |
| `asignable_type` | character varying | NO |
| `asignable_id` | bigint | NO |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | YES |
| `motivo` | character varying | YES |
| `asignado_por_id` | bigint | YES |
| `recibido_por_id` | bigint | YES |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_estado_asignaciones`: `CHECK ((estado = ANY (ARRAY[1, 2, 3])))`.
- `inv_activo_asignaciones_activo_id_foreign`: `FOREIGN KEY (activo_id) REFERENCES inv_activos(id) ON DELETE CASCADE`.
- `inv_activo_asignaciones_activo_id_not_null`: `NOT NULL activo_id`.
- `inv_activo_asignaciones_asignable_id_not_null`: `NOT NULL asignable_id`.
- `inv_activo_asignaciones_asignable_type_not_null`: `NOT NULL asignable_type`.
- `inv_activo_asignaciones_asignado_por_id_foreign`: `FOREIGN KEY (asignado_por_id) REFERENCES users(id) ON DELETE SET NULL`.
- `inv_activo_asignaciones_estado_not_null`: `NOT NULL estado`.
- `inv_activo_asignaciones_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `inv_activo_asignaciones_id_not_null`: `NOT NULL id`.
- `inv_activo_asignaciones_pkey`: `PRIMARY KEY (id)`.
- `inv_activo_asignaciones_recibido_por_id_foreign`: `FOREIGN KEY (recibido_por_id) REFERENCES users(id) ON DELETE SET NULL`.

## inv_activo_bajas

Registros: 180.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `activo_id` | bigint | NO |
| `fecha_baja` | date | NO |
| `motivo_tipo` | character varying | NO |
| `motivo_detalle` | text | NO |
| `valor_residual` | numeric | YES |
| `aprobado_por_id` | bigint | YES |
| `creado_por_id` | bigint | NO |
| `documento_soporte` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_motivo_tipo_bajas`: `CHECK (((motivo_tipo)::text = ANY ((ARRAY['obsolescencia'::character varying, 'daño_irreparable'::character varying, 'robo'::character varying, 'perdida'::character varying, 'donacion'::character varying, 'venta'::character varying])::text[])))`.
- `inv_activo_bajas_activo_id_foreign`: `FOREIGN KEY (activo_id) REFERENCES inv_activos(id) ON DELETE CASCADE`.
- `inv_activo_bajas_activo_id_not_null`: `NOT NULL activo_id`.
- `inv_activo_bajas_aprobado_por_id_foreign`: `FOREIGN KEY (aprobado_por_id) REFERENCES users(id) ON DELETE SET NULL`.
- `inv_activo_bajas_codigo_not_null`: `NOT NULL codigo`.
- `inv_activo_bajas_codigo_unique`: `UNIQUE (codigo)`.
- `inv_activo_bajas_creado_por_id_foreign`: `FOREIGN KEY (creado_por_id) REFERENCES users(id) ON DELETE CASCADE`.
- `inv_activo_bajas_creado_por_id_not_null`: `NOT NULL creado_por_id`.
- `inv_activo_bajas_fecha_baja_not_null`: `NOT NULL fecha_baja`.
- `inv_activo_bajas_id_not_null`: `NOT NULL id`.
- `inv_activo_bajas_motivo_detalle_not_null`: `NOT NULL motivo_detalle`.
- `inv_activo_bajas_motivo_tipo_not_null`: `NOT NULL motivo_tipo`.
- `inv_activo_bajas_pkey`: `PRIMARY KEY (id)`.

## inv_activos

Registros: 32312.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo_inventario` | character varying | NO |
| `individualizacion_id` | bigint | YES |
| `recepcion_item_id` | bigint | YES |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `nombre_descriptivo` | character varying | YES |
| `numero_serie` | character varying | YES |
| `fecha_adquisicion` | date | NO |
| `costo_adquisicion` | numeric | YES |
| `moneda_id` | bigint | YES |
| `proveedor_id` | bigint | YES |
| `vida_util_meses` | integer | YES |
| `fecha_garantia_fin` | date | YES |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_estado_activos`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4, 5, 6])))`.
- `inv_activos_codigo_inventario_not_null`: `NOT NULL codigo_inventario`.
- `inv_activos_codigo_inventario_unique`: `UNIQUE (codigo_inventario)`.
- `inv_activos_estado_not_null`: `NOT NULL estado`.
- `inv_activos_fecha_adquisicion_not_null`: `NOT NULL fecha_adquisicion`.
- `inv_activos_id_not_null`: `NOT NULL id`.
- `inv_activos_individualizacion_id_foreign`: `FOREIGN KEY (individualizacion_id) REFERENCES inv_registro_individualizacion(id) ON DELETE CASCADE`.
- `inv_activos_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE SET NULL`.
- `inv_activos_pkey`: `PRIMARY KEY (id)`.
- `inv_activos_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `inv_activos_producto_id_not_null`: `NOT NULL producto_id`.
- `inv_activos_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `inv_activos_proveedor_id_foreign`: `FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL`.
- `inv_activos_recepcion_item_id_foreign`: `FOREIGN KEY (recepcion_item_id) REFERENCES recepcion_items(id) ON DELETE SET NULL`.

## inv_inventarios_fisicos

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `fecha_toma` | date | NO |
| `estado` | character varying | NO |
| `creado_por_id` | bigint | YES |
| `observaciones` | text | YES |
| `datos_hoja` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `inv_inventarios_fisicos_codigo_not_null`: `NOT NULL codigo`.
- `inv_inventarios_fisicos_codigo_unique`: `UNIQUE (codigo)`.
- `inv_inventarios_fisicos_creado_por_id_foreign`: `FOREIGN KEY (creado_por_id) REFERENCES users(id) ON DELETE SET NULL`.
- `inv_inventarios_fisicos_estado_not_null`: `NOT NULL estado`.
- `inv_inventarios_fisicos_fecha_toma_not_null`: `NOT NULL fecha_toma`.
- `inv_inventarios_fisicos_id_not_null`: `NOT NULL id`.
- `inv_inventarios_fisicos_pkey`: `PRIMARY KEY (id)`.

## inv_lotes

Registros: 490562.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo_lote` | character varying | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `estado` | smallint | NO |
| `cantidad_disponible` | numeric | NO |
| `cantidad_inicial` | numeric | NO |
| `costo_unitario` | numeric | YES |
| `costo_total` | numeric | YES |
| `ubicacion_id` | bigint | YES |
| `ubicacion_detalle_id` | bigint | YES |
| `fecha_vencimiento` | date | YES |
| `lote_proveedor` | character varying | YES |
| `proveedor_id` | bigint | YES |
| `fecha_recepcion` | date | NO |
| `recepcion_item_id` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `inv_lotes_cantidad_disponible_not_null`: `NOT NULL cantidad_disponible`.
- `inv_lotes_cantidad_inicial_not_null`: `NOT NULL cantidad_inicial`.
- `inv_lotes_codigo_lote_not_null`: `NOT NULL codigo_lote`.
- `inv_lotes_estado_not_null`: `NOT NULL estado`.
- `inv_lotes_fecha_recepcion_not_null`: `NOT NULL fecha_recepcion`.
- `inv_lotes_id_not_null`: `NOT NULL id`.
- `inv_lotes_pkey`: `PRIMARY KEY (id)`.
- `inv_lotes_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id)`.
- `inv_lotes_producto_id_not_null`: `NOT NULL producto_id`.
- `inv_lotes_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `inv_lotes_proveedor_id_foreign`: `FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL`.
- `inv_lotes_recepcion_item_id_foreign`: `FOREIGN KEY (recepcion_item_id) REFERENCES recepcion_items(id) ON DELETE SET NULL`.
- `inv_lotes_ubicacion_detalle_id_foreign`: `FOREIGN KEY (ubicacion_detalle_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `inv_lotes_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.

## inv_mantenimiento_notificaciones

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `mantenimiento_id` | bigint | NO |
| `tipo` | character varying | NO |
| `canal` | character varying | NO |
| `enviado_a` | bigint | YES |
| `enviado_at` | timestamp without time zone | NO |
| `metadata` | jsonb | YES |

Restricciones:

- `inv_mantenimiento_notificaciones_canal_not_null`: `NOT NULL canal`.
- `inv_mantenimiento_notificaciones_enviado_a_foreign`: `FOREIGN KEY (enviado_a) REFERENCES users(id) ON DELETE SET NULL`.
- `inv_mantenimiento_notificaciones_enviado_at_not_null`: `NOT NULL enviado_at`.
- `inv_mantenimiento_notificaciones_id_not_null`: `NOT NULL id`.
- `inv_mantenimiento_notificaciones_mantenimiento_id_foreign`: `FOREIGN KEY (mantenimiento_id) REFERENCES inv_mantenimientos(id) ON DELETE CASCADE`.
- `inv_mantenimiento_notificaciones_mantenimiento_id_not_null`: `NOT NULL mantenimiento_id`.
- `inv_mantenimiento_notificaciones_pkey`: `PRIMARY KEY (id)`.
- `inv_mantenimiento_notificaciones_tipo_not_null`: `NOT NULL tipo`.

## inv_mantenimientos

Registros: 14078.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `plan_id` | bigint | YES |
| `tipo` | character varying | YES |
| `activo_id` | bigint | NO |
| `fecha_programada` | date | NO |
| `fecha_realizada` | date | YES |
| `costo_real` | numeric | YES |
| `realizado_por_id` | bigint | YES |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_estado_mantenimiento`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4])))`.
- `inv_mantenimientos_activo_id_foreign`: `FOREIGN KEY (activo_id) REFERENCES inv_activos(id) ON DELETE CASCADE`.
- `inv_mantenimientos_activo_id_not_null`: `NOT NULL activo_id`.
- `inv_mantenimientos_estado_not_null`: `NOT NULL estado`.
- `inv_mantenimientos_fecha_programada_not_null`: `NOT NULL fecha_programada`.
- `inv_mantenimientos_id_not_null`: `NOT NULL id`.
- `inv_mantenimientos_pkey`: `PRIMARY KEY (id)`.
- `inv_mantenimientos_plan_id_foreign`: `FOREIGN KEY (plan_id) REFERENCES inv_planes_mantenimiento(id) ON DELETE CASCADE`.
- `inv_mantenimientos_realizado_por_id_foreign`: `FOREIGN KEY (realizado_por_id) REFERENCES users(id) ON DELETE SET NULL`.

## inv_movimientos

Registros: 1452750.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `tipo` | character varying | NO |
| `lote_id` | bigint | YES |
| `producto_id` | bigint | NO |
| `cantidad` | numeric | NO |
| `costo_unitario` | numeric | YES |
| `costo_total` | numeric | YES |
| `ubicacion_origen_id` | bigint | YES |
| `ubicacion_destino_id` | bigint | YES |
| `documento_tipo` | character varying | YES |
| `documento_id` | bigint | YES |
| `referencia` | character varying | YES |
| `creado_por_id` | bigint | YES |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | NO |

Restricciones:

- `inv_movimientos_cantidad_not_null`: `NOT NULL cantidad`.
- `inv_movimientos_creado_por_id_foreign`: `FOREIGN KEY (creado_por_id) REFERENCES users(id) ON DELETE SET NULL`.
- `inv_movimientos_created_at_not_null`: `NOT NULL created_at`.
- `inv_movimientos_id_not_null`: `NOT NULL id`.
- `inv_movimientos_lote_id_foreign`: `FOREIGN KEY (lote_id) REFERENCES inv_lotes(id) ON DELETE SET NULL`.
- `inv_movimientos_pkey`: `PRIMARY KEY (id)`.
- `inv_movimientos_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id)`.
- `inv_movimientos_producto_id_not_null`: `NOT NULL producto_id`.
- `inv_movimientos_tipo_not_null`: `NOT NULL tipo`.
- `inv_movimientos_ubicacion_destino_id_foreign`: `FOREIGN KEY (ubicacion_destino_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `inv_movimientos_ubicacion_origen_id_foreign`: `FOREIGN KEY (ubicacion_origen_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.

## inv_planes_mantenimiento

Registros: 5.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `nombre` | character varying | NO |
| `tipo` | character varying | NO |
| `proveedor_id` | bigint | YES |
| `frecuencia_dias` | integer | YES |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | YES |
| `costo_estimado` | numeric | YES |
| `fecha_ultimo_mantenimiento` | date | YES |
| `fecha_proximo_mantenimiento` | date | YES |
| `moneda_id` | bigint | YES |
| `descripcion` | text | YES |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_estado_plan`: `CHECK ((estado = ANY (ARRAY[1, 2, 3])))`.
- `chk_tipo_plan`: `CHECK (((tipo)::text = ANY ((ARRAY['preventivo'::character varying, 'correctivo'::character varying, 'garantia'::character varying, 'inspeccion'::character varying])::text[])))`.
- `inv_planes_mantenimiento_estado_not_null`: `NOT NULL estado`.
- `inv_planes_mantenimiento_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `inv_planes_mantenimiento_id_not_null`: `NOT NULL id`.
- `inv_planes_mantenimiento_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE SET NULL`.
- `inv_planes_mantenimiento_nombre_not_null`: `NOT NULL nombre`.
- `inv_planes_mantenimiento_pkey`: `PRIMARY KEY (id)`.
- `inv_planes_mantenimiento_proveedor_id_foreign`: `FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL`.
- `inv_planes_mantenimiento_tipo_not_null`: `NOT NULL tipo`.

## inv_prefijos_codigo

Registros: 37.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `prefijo` | character varying | NO |
| `ultimo_numero` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `inv_prefijos_codigo_id_not_null`: `NOT NULL id`.
- `inv_prefijos_codigo_pkey`: `PRIMARY KEY (id)`.
- `inv_prefijos_codigo_prefijo_not_null`: `NOT NULL prefijo`.
- `inv_prefijos_codigo_prefijo_unique`: `UNIQUE (prefijo)`.
- `inv_prefijos_codigo_ultimo_numero_not_null`: `NOT NULL ultimo_numero`.

## inv_registro_individualizacion

Registros: 5012.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `recepcion_item_id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `cantidad_total` | integer | NO |
| `cantidad_registrada` | integer | NO |
| `estado` | integer | NO |
| `registrado_por_id` | bigint | YES |
| `fecha_completado` | timestamp without time zone | YES |
| `notes` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `chk_estado_indiv`: `CHECK ((estado = ANY (ARRAY[1, 2, 3])))`.
- `inv_registro_individualizacion_cantidad_registrada_not_null`: `NOT NULL cantidad_registrada`.
- `inv_registro_individualizacion_cantidad_total_not_null`: `NOT NULL cantidad_total`.
- `inv_registro_individualizacion_estado_not_null`: `NOT NULL estado`.
- `inv_registro_individualizacion_id_not_null`: `NOT NULL id`.
- `inv_registro_individualizacion_pkey`: `PRIMARY KEY (id)`.
- `inv_registro_individualizacion_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `inv_registro_individualizacion_producto_id_not_null`: `NOT NULL producto_id`.
- `inv_registro_individualizacion_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `inv_registro_individualizacion_recepcion_item_id_foreign`: `FOREIGN KEY (recepcion_item_id) REFERENCES recepcion_items(id) ON DELETE CASCADE`.
- `inv_registro_individualizacion_recepcion_item_id_not_null`: `NOT NULL recepcion_item_id`.
- `inv_registro_individualizacion_recepcion_item_id_unique`: `UNIQUE (recepcion_item_id)`.
- `inv_registro_individualizacion_registrado_por_id_foreign`: `FOREIGN KEY (registrado_por_id) REFERENCES users(id) ON DELETE SET NULL`.

## inv_stock

Registros: 16017.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `lote_id` | bigint | YES |
| `ubicacion_id` | bigint | NO |
| `ubicacion_detalle_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `inv_stock_cantidad_not_null`: `NOT NULL cantidad`.
- `inv_stock_id_not_null`: `NOT NULL id`.
- `inv_stock_lote_id_foreign`: `FOREIGN KEY (lote_id) REFERENCES inv_lotes(id) ON DELETE SET NULL`.
- `inv_stock_pkey`: `PRIMARY KEY (id)`.
- `inv_stock_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `inv_stock_producto_id_not_null`: `NOT NULL producto_id`.
- `inv_stock_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `inv_stock_ubicacion_detalle_id_foreign`: `FOREIGN KEY (ubicacion_detalle_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `inv_stock_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id) ON DELETE CASCADE`.
- `inv_stock_ubicacion_id_not_null`: `NOT NULL ubicacion_id`.
- `inv_stock_unique`: `UNIQUE (producto_id, producto_variante_id, lote_id, ubicacion_id)`.

## job_batches

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | character varying | NO |
| `name` | character varying | NO |
| `total_jobs` | integer | NO |
| `pending_jobs` | integer | NO |
| `failed_jobs` | integer | NO |
| `failed_job_ids` | text | NO |
| `options` | text | YES |
| `cancelled_at` | integer | YES |
| `created_at` | integer | NO |
| `finished_at` | integer | YES |

Restricciones:

- `job_batches_created_at_not_null`: `NOT NULL created_at`.
- `job_batches_failed_job_ids_not_null`: `NOT NULL failed_job_ids`.
- `job_batches_failed_jobs_not_null`: `NOT NULL failed_jobs`.
- `job_batches_id_not_null`: `NOT NULL id`.
- `job_batches_name_not_null`: `NOT NULL name`.
- `job_batches_pending_jobs_not_null`: `NOT NULL pending_jobs`.
- `job_batches_pkey`: `PRIMARY KEY (id)`.
- `job_batches_total_jobs_not_null`: `NOT NULL total_jobs`.

## jobs

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `queue` | character varying | NO |
| `payload` | text | NO |
| `attempts` | smallint | NO |
| `reserved_at` | integer | YES |
| `available_at` | integer | NO |
| `created_at` | integer | NO |

Restricciones:

- `jobs_attempts_not_null`: `NOT NULL attempts`.
- `jobs_available_at_not_null`: `NOT NULL available_at`.
- `jobs_created_at_not_null`: `NOT NULL created_at`.
- `jobs_id_not_null`: `NOT NULL id`.
- `jobs_payload_not_null`: `NOT NULL payload`.
- `jobs_pkey`: `PRIMARY KEY (id)`.
- `jobs_queue_not_null`: `NOT NULL queue`.

## limp_ejecuciones

Registros: 26.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `horario_id` | bigint | YES |
| `solicitud_id` | bigint | YES |
| `limpiable_type` | character varying | NO |
| `limpiable_id` | bigint | NO |
| `turno_id` | bigint | NO |
| `colaborador_id` | bigint | YES |
| `carrito_id` | bigint | YES |
| `fecha` | date | NO |
| `hora_inicio` | time without time zone | YES |
| `hora_fin` | time without time zone | YES |
| `estado` | integer | NO |
| `estado_previo` | integer | YES |
| `detalles_checklist` | json | YES |
| `consumos` | json | YES |
| `observaciones` | text | YES |
| `recordatorio_enviado_at` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `limp_ejecuciones_carrito_id_foreign`: `FOREIGN KEY (carrito_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `limp_ejecuciones_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL`.
- `limp_ejecuciones_estado_not_null`: `NOT NULL estado`.
- `limp_ejecuciones_fecha_not_null`: `NOT NULL fecha`.
- `limp_ejecuciones_horario_id_foreign`: `FOREIGN KEY (horario_id) REFERENCES limp_horarios(id) ON DELETE SET NULL`.
- `limp_ejecuciones_id_not_null`: `NOT NULL id`.
- `limp_ejecuciones_limpiable_id_not_null`: `NOT NULL limpiable_id`.
- `limp_ejecuciones_limpiable_type_not_null`: `NOT NULL limpiable_type`.
- `limp_ejecuciones_pkey`: `PRIMARY KEY (id)`.
- `limp_ejecuciones_solicitud_id_foreign`: `FOREIGN KEY (solicitud_id) REFERENCES solicitud_limpiezas(id) ON DELETE SET NULL`.
- `limp_ejecuciones_turno_id_foreign`: `FOREIGN KEY (turno_id) REFERENCES limp_horario_turnos(id) ON DELETE CASCADE`.
- `limp_ejecuciones_turno_id_not_null`: `NOT NULL turno_id`.

## limp_horario_detalles

Registros: 135.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `horario_id` | bigint | NO |
| `limpiable_type` | character varying | NO |
| `limpiable_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `limp_horario_detalles_horario_id_foreign`: `FOREIGN KEY (horario_id) REFERENCES limp_horarios(id) ON DELETE CASCADE`.
- `limp_horario_detalles_horario_id_not_null`: `NOT NULL horario_id`.
- `limp_horario_detalles_id_not_null`: `NOT NULL id`.
- `limp_horario_detalles_limpiable_id_not_null`: `NOT NULL limpiable_id`.
- `limp_horario_detalles_limpiable_type_not_null`: `NOT NULL limpiable_type`.
- `limp_horario_detalles_pkey`: `PRIMARY KEY (id)`.

## limp_horario_turnos

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `nombre` | character varying | NO |
| `lider_id` | bigint | YES |
| `apoyo_id` | bigint | YES |
| `hora_inicio` | time without time zone | NO |
| `hora_fin` | time without time zone | NO |
| `estado` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `es_lavanderia` | boolean | NO |

Restricciones:

- `limp_horario_turnos_apoyo_id_foreign`: `FOREIGN KEY (apoyo_id) REFERENCES colaboradores(id) ON DELETE SET NULL`.
- `limp_horario_turnos_es_lavanderia_not_null`: `NOT NULL es_lavanderia`.
- `limp_horario_turnos_estado_not_null`: `NOT NULL estado`.
- `limp_horario_turnos_hora_fin_not_null`: `NOT NULL hora_fin`.
- `limp_horario_turnos_hora_inicio_not_null`: `NOT NULL hora_inicio`.
- `limp_horario_turnos_id_not_null`: `NOT NULL id`.
- `limp_horario_turnos_lider_id_foreign`: `FOREIGN KEY (lider_id) REFERENCES colaboradores(id) ON DELETE SET NULL`.
- `limp_horario_turnos_nombre_not_null`: `NOT NULL nombre`.
- `limp_horario_turnos_pkey`: `PRIMARY KEY (id)`.

## limp_horarios

Registros: 20.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `turno_id` | bigint | YES |
| `hora_estimada` | time without time zone | NO |
| `frecuencia` | character varying | NO |
| `dia_semana` | character varying | YES |
| `checklist` | json | YES |
| `activo` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `duracion_estimada_minutos` | smallint | NO |

Restricciones:

- `limp_horarios_activo_not_null`: `NOT NULL activo`.
- `limp_horarios_duracion_estimada_minutos_not_null`: `NOT NULL duracion_estimada_minutos`.
- `limp_horarios_frecuencia_not_null`: `NOT NULL frecuencia`.
- `limp_horarios_hora_estimada_not_null`: `NOT NULL hora_estimada`.
- `limp_horarios_id_not_null`: `NOT NULL id`.
- `limp_horarios_pkey`: `PRIMARY KEY (id)`.
- `limp_horarios_turno_id_foreign`: `FOREIGN KEY (turno_id) REFERENCES limp_horario_turnos(id) ON DELETE CASCADE`.

## limp_lavanderia_procesos

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `lote_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `estado` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `limp_lavanderia_procesos_cantidad_not_null`: `NOT NULL cantidad`.
- `limp_lavanderia_procesos_estado_not_null`: `NOT NULL estado`.
- `limp_lavanderia_procesos_id_not_null`: `NOT NULL id`.
- `limp_lavanderia_procesos_lote_id_foreign`: `FOREIGN KEY (lote_id) REFERENCES inv_lotes(id) ON DELETE SET NULL`.
- `limp_lavanderia_procesos_pkey`: `PRIMARY KEY (id)`.
- `limp_lavanderia_procesos_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `limp_lavanderia_procesos_producto_id_not_null`: `NOT NULL producto_id`.
- `limp_lavanderia_procesos_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.

## limp_sustituciones_stock

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `ejecucion_id` | bigint | NO |
| `producto_id` | bigint | NO |
| `sustituto_producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `sustituto_variante_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `limp_sustituciones_stock_cantidad_not_null`: `NOT NULL cantidad`.
- `limp_sustituciones_stock_ejecucion_id_foreign`: `FOREIGN KEY (ejecucion_id) REFERENCES limp_ejecuciones(id) ON DELETE CASCADE`.
- `limp_sustituciones_stock_ejecucion_id_not_null`: `NOT NULL ejecucion_id`.
- `limp_sustituciones_stock_id_not_null`: `NOT NULL id`.
- `limp_sustituciones_stock_pkey`: `PRIMARY KEY (id)`.
- `limp_sustituciones_stock_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `limp_sustituciones_stock_producto_id_not_null`: `NOT NULL producto_id`.
- `limp_sustituciones_stock_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `limp_sustituciones_stock_sustituto_producto_id_foreign`: `FOREIGN KEY (sustituto_producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `limp_sustituciones_stock_sustituto_producto_id_not_null`: `NOT NULL sustituto_producto_id`.
- `limp_sustituciones_stock_sustituto_variante_id_foreign`: `FOREIGN KEY (sustituto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.

## limp_turno_carritos

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `turno_id` | bigint | NO |
| `ubicacion_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `limp_turno_carritos_id_not_null`: `NOT NULL id`.
- `limp_turno_carritos_pkey`: `PRIMARY KEY (id)`.
- `limp_turno_carritos_turno_id_foreign`: `FOREIGN KEY (turno_id) REFERENCES limp_horario_turnos(id) ON DELETE CASCADE`.
- `limp_turno_carritos_turno_id_not_null`: `NOT NULL turno_id`.
- `limp_turno_carritos_turno_id_ubicacion_id_unique`: `UNIQUE (turno_id, ubicacion_id)`.
- `limp_turno_carritos_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id) ON DELETE CASCADE`.
- `limp_turno_carritos_ubicacion_id_not_null`: `NOT NULL ubicacion_id`.

## migrations

Registros: 153.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | integer | NO |
| `migration` | character varying | NO |
| `batch` | integer | NO |

Restricciones:

- `migrations_batch_not_null`: `NOT NULL batch`.
- `migrations_id_not_null`: `NOT NULL id`.
- `migrations_migration_not_null`: `NOT NULL migration`.
- `migrations_pkey`: `PRIMARY KEY (id)`.

## model_has_permissions

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `permission_id` | bigint | NO |
| `model_type` | character varying | NO |
| `model_id` | bigint | NO |

Restricciones:

- `model_has_permissions_model_id_not_null`: `NOT NULL model_id`.
- `model_has_permissions_model_type_not_null`: `NOT NULL model_type`.
- `model_has_permissions_permission_id_foreign`: `FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE`.
- `model_has_permissions_permission_id_not_null`: `NOT NULL permission_id`.
- `model_has_permissions_pkey`: `PRIMARY KEY (permission_id, model_id, model_type)`.

## model_has_roles

Registros: 24.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `role_id` | bigint | NO |
| `model_type` | character varying | NO |
| `model_id` | bigint | NO |

Restricciones:

- `model_has_roles_model_id_not_null`: `NOT NULL model_id`.
- `model_has_roles_model_type_not_null`: `NOT NULL model_type`.
- `model_has_roles_pkey`: `PRIMARY KEY (role_id, model_id, model_type)`.
- `model_has_roles_role_id_foreign`: `FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE`.
- `model_has_roles_role_id_not_null`: `NOT NULL role_id`.

## monedas

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `simbolo` | character varying | NO |
| `es_predeterminada` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `monedas_codigo_not_null`: `NOT NULL codigo`.
- `monedas_codigo_unique`: `UNIQUE (codigo)`.
- `monedas_es_predeterminada_not_null`: `NOT NULL es_predeterminada`.
- `monedas_id_not_null`: `NOT NULL id`.
- `monedas_nombre_not_null`: `NOT NULL nombre`.
- `monedas_pkey`: `PRIMARY KEY (id)`.
- `monedas_simbolo_not_null`: `NOT NULL simbolo`.

## notifications

Registros: 22.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | uuid | NO |
| `type` | character varying | NO |
| `notifiable_type` | character varying | NO |
| `notifiable_id` | bigint | NO |
| `data` | jsonb | NO |
| `read_at` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `notifications_data_not_null`: `NOT NULL data`.
- `notifications_id_not_null`: `NOT NULL id`.
- `notifications_notifiable_id_not_null`: `NOT NULL notifiable_id`.
- `notifications_notifiable_type_not_null`: `NOT NULL notifiable_type`.
- `notifications_pkey`: `PRIMARY KEY (id)`.
- `notifications_type_not_null`: `NOT NULL type`.

## orden_compra_items

Registros: 25454.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `orden_compra_id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `unidad_medida_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `precio_unitario` | numeric | NO |
| `subtotal` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `orden_compra_items_cantidad_not_null`: `NOT NULL cantidad`.
- `orden_compra_items_id_not_null`: `NOT NULL id`.
- `orden_compra_items_orden_compra_id_foreign`: `FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE`.
- `orden_compra_items_orden_compra_id_not_null`: `NOT NULL orden_compra_id`.
- `orden_compra_items_pkey`: `PRIMARY KEY (id)`.
- `orden_compra_items_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `orden_compra_items_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id)`.
- `orden_compra_items_producto_id_not_null`: `NOT NULL producto_id`.
- `orden_compra_items_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `orden_compra_items_subtotal_not_null`: `NOT NULL subtotal`.
- `orden_compra_items_unidad_medida_id_foreign`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE SET NULL`.

## ordenes_compra

Registros: 5102.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `proveedor_id` | bigint | NO |
| `proveedor_contacto_id` | bigint | YES |
| `solicitud_id` | bigint | YES |
| `cotizacion_id` | bigint | YES |
| `fecha_orden` | date | NO |
| `fecha_entrega_estimada` | date | YES |
| `condicion_pago_id` | bigint | YES |
| `subtotal` | numeric | NO |
| `impuestos` | numeric | NO |
| `total` | numeric | NO |
| `tasa_cambio` | numeric | NO |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_ordenes_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4, 5, 6, 7, 8])))`.
- `ordenes_compra_codigo_not_null`: `NOT NULL codigo`.
- `ordenes_compra_codigo_unique`: `UNIQUE (codigo)`.
- `ordenes_compra_condicion_pago_id_foreign`: `FOREIGN KEY (condicion_pago_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `ordenes_compra_cotizacion_id_foreign`: `FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE SET NULL`.
- `ordenes_compra_estado_not_null`: `NOT NULL estado`.
- `ordenes_compra_fecha_orden_not_null`: `NOT NULL fecha_orden`.
- `ordenes_compra_id_not_null`: `NOT NULL id`.
- `ordenes_compra_impuestos_not_null`: `NOT NULL impuestos`.
- `ordenes_compra_pkey`: `PRIMARY KEY (id)`.
- `ordenes_compra_proveedor_contacto_id_foreign`: `FOREIGN KEY (proveedor_contacto_id) REFERENCES proveedor_contactos(id) ON DELETE SET NULL`.
- `ordenes_compra_proveedor_id_foreign`: `FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE`.
- `ordenes_compra_proveedor_id_not_null`: `NOT NULL proveedor_id`.
- `ordenes_compra_solicitud_id_foreign`: `FOREIGN KEY (solicitud_id) REFERENCES solicitudes_compra(id) ON DELETE SET NULL`.
- `ordenes_compra_subtotal_not_null`: `NOT NULL subtotal`.
- `ordenes_compra_tasa_cambio_not_null`: `NOT NULL tasa_cambio`.
- `ordenes_compra_total_not_null`: `NOT NULL total`.

## pago_conciliaciones

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `pago_transaccion_id` | bigint | NO |
| `estado` | smallint | NO |
| `monto_esperado` | numeric | NO |
| `monto_recibido` | numeric | NO |
| `diferencia` | numeric | NO |
| `conciliada_at` | timestamp without time zone | YES |
| `conciliada_por` | bigint | YES |
| `observaciones` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `reembolsada_at` | timestamp without time zone | YES |

Restricciones:

- `chk_pc_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4, 5])))`.
- `pago_conciliaciones_conciliada_por_foreign`: `FOREIGN KEY (conciliada_por) REFERENCES users(id) ON DELETE SET NULL`.
- `pago_conciliaciones_diferencia_not_null`: `NOT NULL diferencia`.
- `pago_conciliaciones_estado_not_null`: `NOT NULL estado`.
- `pago_conciliaciones_id_not_null`: `NOT NULL id`.
- `pago_conciliaciones_monto_esperado_not_null`: `NOT NULL monto_esperado`.
- `pago_conciliaciones_monto_recibido_not_null`: `NOT NULL monto_recibido`.
- `pago_conciliaciones_pago_transaccion_id_foreign`: `FOREIGN KEY (pago_transaccion_id) REFERENCES pago_transacciones(id) ON DELETE RESTRICT`.
- `pago_conciliaciones_pago_transaccion_id_not_null`: `NOT NULL pago_transaccion_id`.
- `pago_conciliaciones_pago_transaccion_id_unique`: `UNIQUE (pago_transaccion_id)`.
- `pago_conciliaciones_pkey`: `PRIMARY KEY (id)`.

## pago_transacciones

Registros: 15.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `pasarela_pago_id` | bigint | NO |
| `cuenta_id` | bigint | YES |
| `venta_id` | bigint | YES |
| `factura_id` | bigint | YES |
| `pago_cuenta_id` | bigint | YES |
| `referencia_interna` | character varying | NO |
| `referencia_pasarela` | character varying | YES |
| `idempotency_key` | character varying | NO |
| `estado` | smallint | NO |
| `moneda_id` | bigint | NO |
| `moneda_base_id` | bigint | NO |
| `monto` | numeric | NO |
| `monto_base` | numeric | NO |
| `tasa_cambio` | numeric | NO |
| `solicitada_at` | timestamp without time zone | YES |
| `autorizada_at` | timestamp without time zone | YES |
| `capturada_at` | timestamp without time zone | YES |
| `fallida_at` | timestamp without time zone | YES |
| `reembolsada_at` | timestamp without time zone | YES |
| `codigo_respuesta` | character varying | YES |
| `mensaje_respuesta` | character varying | YES |
| `request_payload` | jsonb | YES |
| `response_payload` | jsonb | YES |
| `webhook_payload` | jsonb | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `reserva_id` | bigint | YES |

Restricciones:

- `chk_pt_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4, 5, 6])))`.
- `chk_pt_montos`: `CHECK (((monto >= (0)::numeric) AND (monto_base >= (0)::numeric)))`.
- `pago_transacciones_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE SET NULL`.
- `pago_transacciones_estado_not_null`: `NOT NULL estado`.
- `pago_transacciones_factura_id_foreign`: `FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE SET NULL`.
- `pago_transacciones_id_not_null`: `NOT NULL id`.
- `pago_transacciones_idempotency_key_not_null`: `NOT NULL idempotency_key`.
- `pago_transacciones_idempotency_key_unique`: `UNIQUE (idempotency_key)`.
- `pago_transacciones_moneda_base_id_foreign`: `FOREIGN KEY (moneda_base_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `pago_transacciones_moneda_base_id_not_null`: `NOT NULL moneda_base_id`.
- `pago_transacciones_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `pago_transacciones_moneda_id_not_null`: `NOT NULL moneda_id`.
- `pago_transacciones_monto_base_not_null`: `NOT NULL monto_base`.
- `pago_transacciones_monto_not_null`: `NOT NULL monto`.
- `pago_transacciones_pago_cuenta_id_foreign`: `FOREIGN KEY (pago_cuenta_id) REFERENCES pagos_cuenta(id) ON DELETE SET NULL`.
- `pago_transacciones_pasarela_pago_id_foreign`: `FOREIGN KEY (pasarela_pago_id) REFERENCES pasarelas_pago(id) ON DELETE RESTRICT`.
- `pago_transacciones_pasarela_pago_id_not_null`: `NOT NULL pasarela_pago_id`.
- `pago_transacciones_pkey`: `PRIMARY KEY (id)`.
- `pago_transacciones_referencia_interna_not_null`: `NOT NULL referencia_interna`.
- `pago_transacciones_referencia_interna_unique`: `UNIQUE (referencia_interna)`.
- `pago_transacciones_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE SET NULL`.
- `pago_transacciones_tasa_cambio_not_null`: `NOT NULL tasa_cambio`.
- `pago_transacciones_venta_id_foreign`: `FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE SET NULL`.

## pagos_cuenta

Registros: 19445.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `cuenta_id` | bigint | NO |
| `venta_id` | bigint | YES |
| `forma_pago` | smallint | NO |
| `moneda_id` | bigint | YES |
| `monto` | numeric | NO |
| `propina` | numeric | NO |
| `referencia_transaccion` | character varying | YES |
| `estado` | smallint | NO |
| `observaciones` | character varying | YES |
| `usuario_id` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `pagos_cuenta_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `pagos_cuenta_cuenta_id_not_null`: `NOT NULL cuenta_id`.
- `pagos_cuenta_estado_not_null`: `NOT NULL estado`.
- `pagos_cuenta_forma_pago_not_null`: `NOT NULL forma_pago`.
- `pagos_cuenta_id_not_null`: `NOT NULL id`.
- `pagos_cuenta_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE SET NULL`.
- `pagos_cuenta_monto_not_null`: `NOT NULL monto`.
- `pagos_cuenta_pkey`: `PRIMARY KEY (id)`.
- `pagos_cuenta_propina_not_null`: `NOT NULL propina`.
- `pagos_cuenta_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL`.
- `pagos_cuenta_venta_cuenta_fk`: `FOREIGN KEY (venta_id, cuenta_id) REFERENCES ventas(id, cuenta_id) ON DELETE RESTRICT`.
- `pagos_cuenta_venta_id_foreign`: `FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT`.

## paises

Registros: 193.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo_iso2` | character varying | NO |
| `codigo_iso3` | character varying | NO |
| `nombre` | character varying | NO |
| `codigo_telefono` | character varying | YES |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `paises_codigo_iso2_not_null`: `NOT NULL codigo_iso2`.
- `paises_codigo_iso2_unique`: `UNIQUE (codigo_iso2)`.
- `paises_codigo_iso3_not_null`: `NOT NULL codigo_iso3`.
- `paises_codigo_iso3_unique`: `UNIQUE (codigo_iso3)`.
- `paises_estado_not_null`: `NOT NULL estado`.
- `paises_id_not_null`: `NOT NULL id`.
- `paises_nombre_not_null`: `NOT NULL nombre`.
- `paises_nombre_unique`: `UNIQUE (nombre)`.
- `paises_pkey`: `PRIMARY KEY (id)`.

## pasarelas_pago

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `activa` | boolean | NO |
| `modo_prueba` | boolean | NO |
| `configuracion` | jsonb | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `proveedor` | character varying | YES |
| `gestion` | character varying | YES |

Restricciones:

- `pasarelas_pago_activa_not_null`: `NOT NULL activa`.
- `pasarelas_pago_codigo_not_null`: `NOT NULL codigo`.
- `pasarelas_pago_codigo_unique`: `UNIQUE (codigo)`.
- `pasarelas_pago_id_not_null`: `NOT NULL id`.
- `pasarelas_pago_modo_prueba_not_null`: `NOT NULL modo_prueba`.
- `pasarelas_pago_nombre_not_null`: `NOT NULL nombre`.
- `pasarelas_pago_pkey`: `PRIMARY KEY (id)`.

## password_reset_tokens

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `email` | character varying | NO |
| `token` | character varying | NO |
| `created_at` | timestamp without time zone | YES |

Restricciones:

- `password_reset_tokens_email_not_null`: `NOT NULL email`.
- `password_reset_tokens_pkey`: `PRIMARY KEY (email)`.
- `password_reset_tokens_token_not_null`: `NOT NULL token`.

## pedido_items

Registros: 88077.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `pedido_id` | bigint | NO |
| `plato_id` | bigint | YES |
| `area_cocina` | character varying | NO |
| `cantidad` | numeric | NO |
| `precio_unitario` | numeric | NO |
| `subtotal` | numeric | NO |
| `estado` | smallint | NO |
| `notas` | text | YES |
| `observaciones` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `bloqueo_stock_detalle` | json | YES |
| `bloqueado_stock_en` | timestamp without time zone | YES |
| `tipo_item` | character varying | NO |
| `producto_id` | bigint | YES |
| `producto_variante_id` | bigint | YES |

Restricciones:

- `pedido_items_area_cocina_not_null`: `NOT NULL area_cocina`.
- `pedido_items_cantidad_not_null`: `NOT NULL cantidad`.
- `pedido_items_estado_not_null`: `NOT NULL estado`.
- `pedido_items_id_not_null`: `NOT NULL id`.
- `pedido_items_pedido_id_foreign`: `FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT`.
- `pedido_items_pedido_id_not_null`: `NOT NULL pedido_id`.
- `pedido_items_pkey`: `PRIMARY KEY (id)`.
- `pedido_items_plato_id_foreign`: `FOREIGN KEY (plato_id) REFERENCES platos(id) ON DELETE SET NULL`.
- `pedido_items_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `pedido_items_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL`.
- `pedido_items_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `pedido_items_subtotal_not_null`: `NOT NULL subtotal`.
- `pedido_items_tipo_item_not_null`: `NOT NULL tipo_item`.

## pedidos

Registros: 35292.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `mesa_id` | bigint | YES |
| `mesero_id` | bigint | YES |
| `cliente_id` | bigint | YES |
| `cuenta_id` | bigint | YES |
| `estado` | smallint | NO |
| `subtotal` | numeric | NO |
| `padre_pedido_id` | bigint | YES |
| `consecutivo_comanda` | integer | NO |
| `abierto_en` | timestamp without time zone | YES |
| `cerrado_en` | timestamp without time zone | YES |
| `cargado_en` | timestamp without time zone | YES |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `total` | numeric | NO |

Restricciones:

- `pedidos_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL`.
- `pedidos_codigo_not_null`: `NOT NULL codigo`.
- `pedidos_codigo_unique`: `UNIQUE (codigo)`.
- `pedidos_consecutivo_comanda_not_null`: `NOT NULL consecutivo_comanda`.
- `pedidos_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `pedidos_estado_not_null`: `NOT NULL estado`.
- `pedidos_id_not_null`: `NOT NULL id`.
- `pedidos_mesa_id_foreign`: `FOREIGN KEY (mesa_id) REFERENCES espacios(id) ON DELETE SET NULL`.
- `pedidos_mesero_id_foreign`: `FOREIGN KEY (mesero_id) REFERENCES colaboradores(id) ON DELETE SET NULL`.
- `pedidos_padre_pedido_id_foreign`: `FOREIGN KEY (padre_pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL`.
- `pedidos_pkey`: `PRIMARY KEY (id)`.
- `pedidos_subtotal_not_null`: `NOT NULL subtotal`.
- `pedidos_total_not_null`: `NOT NULL total`.

## permissions

Registros: 27.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `name` | character varying | NO |
| `guard_name` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `permissions_guard_name_not_null`: `NOT NULL guard_name`.
- `permissions_id_not_null`: `NOT NULL id`.
- `permissions_name_guard_name_unique`: `UNIQUE (name, guard_name)`.
- `permissions_name_not_null`: `NOT NULL name`.
- `permissions_pkey`: `PRIMARY KEY (id)`.

## personas

Registros: 175.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `primer_nombre` | character varying | NO |
| `segundo_nombre` | character varying | YES |
| `pais_id` | bigint | YES |
| `tipo_persona` | character varying | NO |
| `telefono` | character varying | YES |
| `direccion` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `personas_id_not_null`: `NOT NULL id`.
- `personas_pais_id_foreign`: `FOREIGN KEY (pais_id) REFERENCES paises(id) ON DELETE SET NULL`.
- `personas_pkey`: `PRIMARY KEY (id)`.
- `personas_primer_nombre_not_null`: `NOT NULL primer_nombre`.
- `personas_tipo_persona_check`: `CHECK (((tipo_persona)::text = ANY ((ARRAY['natural'::character varying, 'juridica'::character varying])::text[])))`.
- `personas_tipo_persona_not_null`: `NOT NULL tipo_persona`.

## personas_juridicas

Registros: 24.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `persona_id` | bigint | NO |
| `razon_social` | character varying | NO |
| `tipo_identificacion` | character varying | YES |
| `numero_identificacion` | character varying | YES |
| `fecha_constitucion` | date | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_identificacion_completa`: `CHECK ((((tipo_identificacion IS NULL) AND (numero_identificacion IS NULL)) OR ((tipo_identificacion IS NOT NULL) AND (numero_identificacion IS NOT NULL))))`.
- `personas_juridicas_id_not_null`: `NOT NULL id`.
- `personas_juridicas_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE`.
- `personas_juridicas_persona_id_not_null`: `NOT NULL persona_id`.
- `personas_juridicas_pkey`: `PRIMARY KEY (id)`.
- `personas_juridicas_razon_social_not_null`: `NOT NULL razon_social`.
- `personas_juridicas_tipo_identificacion_check`: `CHECK (((tipo_identificacion)::text = ANY ((ARRAY['ruc'::character varying, 'nit'::character varying, 'tax_id'::character varying, 'registro_comercial'::character varying, 'otro'::character varying])::text[])))`.
- `uq_personas_juridicas_identificacion`: `UNIQUE (tipo_identificacion, numero_identificacion)`.

## personas_naturales

Registros: 151.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `persona_id` | bigint | NO |
| `primer_apellido` | character varying | NO |
| `segundo_apellido` | character varying | YES |
| `tipo_identificacion` | character varying | YES |
| `numero_identificacion` | character varying | YES |
| `sexo` | character varying | YES |
| `fecha_nacimiento` | date | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_identificacion_completa`: `CHECK ((((tipo_identificacion IS NULL) AND (numero_identificacion IS NULL)) OR ((tipo_identificacion IS NOT NULL) AND (numero_identificacion IS NOT NULL))))`.
- `personas_naturales_id_not_null`: `NOT NULL id`.
- `personas_naturales_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE`.
- `personas_naturales_persona_id_not_null`: `NOT NULL persona_id`.
- `personas_naturales_pkey`: `PRIMARY KEY (id)`.
- `personas_naturales_primer_apellido_not_null`: `NOT NULL primer_apellido`.
- `personas_naturales_sexo_check`: `CHECK (((sexo)::text = ANY ((ARRAY['M'::character varying, 'F'::character varying, 'O'::character varying])::text[])))`.
- `personas_naturales_tipo_identificacion_check`: `CHECK (((tipo_identificacion)::text = ANY ((ARRAY['cedula'::character varying, 'dni'::character varying, 'pasaporte'::character varying, 'residencia'::character varying, 'nit'::character varying, 'ruc'::character varying, 'otro'::character varying])::text[])))`.
- `uq_personas_naturales_identificacion`: `UNIQUE (tipo_identificacion, numero_identificacion)`.

## platos

Registros: 19.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `categoria_id` | bigint | YES |
| `producto_receta_id` | bigint | YES |
| `descripcion` | text | YES |
| `web` | boolean | NO |
| `estado` | integer | NO |
| `tiempo_preparacion` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `area_cocina` | character varying | NO |
| `instrucciones_preparacion` | text | YES |

Restricciones:

- `platos_area_cocina_not_null`: `NOT NULL area_cocina`.
- `platos_categoria_id_foreign`: `FOREIGN KEY (categoria_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `platos_codigo_not_null`: `NOT NULL codigo`.
- `platos_codigo_unique`: `UNIQUE (codigo)`.
- `platos_estado_not_null`: `NOT NULL estado`.
- `platos_id_not_null`: `NOT NULL id`.
- `platos_nombre_not_null`: `NOT NULL nombre`.
- `platos_pkey`: `PRIMARY KEY (id)`.
- `platos_producto_receta_id_foreign`: `FOREIGN KEY (producto_receta_id) REFERENCES productos(id) ON DELETE SET NULL`.
- `platos_web_not_null`: `NOT NULL web`.

## politica_penalizaciones

Registros: 3.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `politica_id` | bigint | NO |
| `min_unidades` | integer | YES |
| `max_unidades` | integer | YES |
| `unidad` | smallint | NO |
| `porcentaje` | numeric | NO |
| `aplica_no_show` | boolean | NO |
| `orden` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `politica_penalizaciones_aplica_no_show_not_null`: `NOT NULL aplica_no_show`.
- `politica_penalizaciones_id_not_null`: `NOT NULL id`.
- `politica_penalizaciones_orden_not_null`: `NOT NULL orden`.
- `politica_penalizaciones_pkey`: `PRIMARY KEY (id)`.
- `politica_penalizaciones_politica_id_foreign`: `FOREIGN KEY (politica_id) REFERENCES politicas(id) ON DELETE CASCADE`.
- `politica_penalizaciones_politica_id_not_null`: `NOT NULL politica_id`.
- `politica_penalizaciones_porcentaje_not_null`: `NOT NULL porcentaje`.
- `politica_penalizaciones_unidad_not_null`: `NOT NULL unidad`.

## politicaables

Registros: 1386.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `politica_id` | bigint | NO |
| `politicaable_type` | character varying | NO |
| `politicaable_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `politicaables_id_not_null`: `NOT NULL id`.
- `politicaables_pkey`: `PRIMARY KEY (id)`.
- `politicaables_politica_id_foreign`: `FOREIGN KEY (politica_id) REFERENCES politicas(id) ON DELETE CASCADE`.
- `politicaables_politica_id_not_null`: `NOT NULL politica_id`.
- `politicaables_politicaable_id_not_null`: `NOT NULL politicaable_id`.
- `politicaables_politicaable_type_not_null`: `NOT NULL politicaable_type`.
- `politicaables_unique`: `UNIQUE (politica_id, politicaable_type, politicaable_id)`.

## politicas

Registros: 21.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `titulo` | character varying | NO |
| `descripcion` | text | YES |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `aplica_penalizacion` | boolean | NO |

Restricciones:

- `politicas_aplica_penalizacion_not_null`: `NOT NULL aplica_penalizacion`.
- `politicas_estado_not_null`: `NOT NULL estado`.
- `politicas_id_not_null`: `NOT NULL id`.
- `politicas_pkey`: `PRIMARY KEY (id)`.
- `politicas_titulo_not_null`: `NOT NULL titulo`.
- `politicas_titulo_unique`: `UNIQUE (titulo)`.

## precios

Registros: 717.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `priceable_type` | character varying | NO |
| `priceable_id` | bigint | NO |
| `moneda_id` | bigint | NO |
| `precio` | numeric | NO |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | YES |
| `estado` | integer | NO |
| `es_oferta` | boolean | NO |
| `tipo_precio` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `precios_es_oferta_not_null`: `NOT NULL es_oferta`.
- `precios_estado_not_null`: `NOT NULL estado`.
- `precios_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `precios_id_not_null`: `NOT NULL id`.
- `precios_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id)`.
- `precios_moneda_id_not_null`: `NOT NULL moneda_id`.
- `precios_pkey`: `PRIMARY KEY (id)`.
- `precios_precio_not_null`: `NOT NULL precio`.
- `precios_priceable_id_not_null`: `NOT NULL priceable_id`.
- `precios_priceable_type_not_null`: `NOT NULL priceable_type`.
- `precios_tipo_precio_not_null`: `NOT NULL tipo_precio`.

## proceso_items

Registros: 11418.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `proceso_id` | bigint | NO |
| `producto_destino_id` | bigint | NO |
| `variante_destino_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `peso_unitario` | numeric | YES |
| `peso_total` | numeric | YES |
| `costo_asignado` | numeric | NO |
| `es_merma` | boolean | NO |
| `ubicacion_destino_id` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `proceso_items_cantidad_not_null`: `NOT NULL cantidad`.
- `proceso_items_costo_asignado_not_null`: `NOT NULL costo_asignado`.
- `proceso_items_es_merma_not_null`: `NOT NULL es_merma`.
- `proceso_items_id_not_null`: `NOT NULL id`.
- `proceso_items_pkey`: `PRIMARY KEY (id)`.
- `proceso_items_proceso_id_foreign`: `FOREIGN KEY (proceso_id) REFERENCES procesos_cocina(id) ON DELETE CASCADE`.
- `proceso_items_proceso_id_not_null`: `NOT NULL proceso_id`.
- `proceso_items_producto_destino_id_foreign`: `FOREIGN KEY (producto_destino_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `proceso_items_producto_destino_id_not_null`: `NOT NULL producto_destino_id`.
- `proceso_items_ubicacion_destino_id_foreign`: `FOREIGN KEY (ubicacion_destino_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `proceso_items_variante_destino_id_foreign`: `FOREIGN KEY (variante_destino_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.

## procesos_cocina

Registros: 2378.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `plato_id` | bigint | YES |
| `cantidad_platos` | smallint | YES |
| `producto_origen_id` | bigint | NO |
| `variante_origen_id` | bigint | YES |
| `cantidad_procesada` | numeric | NO |
| `costo_total` | numeric | NO |
| `realizado_por` | bigint | YES |
| `observaciones` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `tipo_registro` | character varying | NO |
| `confirmado_at` | timestamp without time zone | YES |
| `lote_resultado_id` | bigint | YES |
| `cantidad_obtenida` | numeric | YES |
| `pedido_item_id` | bigint | YES |

Restricciones:

- `procesos_cocina_cantidad_procesada_not_null`: `NOT NULL cantidad_procesada`.
- `procesos_cocina_codigo_not_null`: `NOT NULL codigo`.
- `procesos_cocina_codigo_unique`: `UNIQUE (codigo)`.
- `procesos_cocina_costo_total_not_null`: `NOT NULL costo_total`.
- `procesos_cocina_id_not_null`: `NOT NULL id`.
- `procesos_cocina_lote_resultado_id_foreign`: `FOREIGN KEY (lote_resultado_id) REFERENCES inv_lotes(id) ON DELETE RESTRICT`.
- `procesos_cocina_pedido_item_id_foreign`: `FOREIGN KEY (pedido_item_id) REFERENCES pedido_items(id) ON DELETE RESTRICT`.
- `procesos_cocina_pedido_item_id_unique`: `UNIQUE (pedido_item_id)`.
- `procesos_cocina_pkey`: `PRIMARY KEY (id)`.
- `procesos_cocina_plato_id_foreign`: `FOREIGN KEY (plato_id) REFERENCES platos(id) ON DELETE SET NULL`.
- `procesos_cocina_producto_origen_id_foreign`: `FOREIGN KEY (producto_origen_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `procesos_cocina_producto_origen_id_not_null`: `NOT NULL producto_origen_id`.
- `procesos_cocina_realizado_por_foreign`: `FOREIGN KEY (realizado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `procesos_cocina_tipo_registro_not_null`: `NOT NULL tipo_registro`.
- `procesos_cocina_variante_origen_id_foreign`: `FOREIGN KEY (variante_origen_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.

## producto_kit

Registros: 139.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `producto_padre_id` | bigint | NO |
| `producto_variante_id` | bigint | NO |
| `cantidad` | numeric | NO |
| `lote_id` | bigint | YES |
| `talla` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `producto_kit_cantidad_not_null`: `NOT NULL cantidad`.
- `producto_kit_id_not_null`: `NOT NULL id`.
- `producto_kit_lote_id_foreign`: `FOREIGN KEY (lote_id) REFERENCES inv_lotes(id) ON DELETE SET NULL`.
- `producto_kit_pkey`: `PRIMARY KEY (id)`.
- `producto_kit_producto_padre_id_foreign`: `FOREIGN KEY (producto_padre_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `producto_kit_producto_padre_id_not_null`: `NOT NULL producto_padre_id`.
- `producto_kit_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE CASCADE`.
- `producto_kit_producto_variante_id_not_null`: `NOT NULL producto_variante_id`.

## producto_variantes

Registros: 733.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `producto_id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre_variante` | character varying | NO |
| `atributos` | json | YES |
| `unidad_medida_id` | bigint | YES |
| `unidades_por_empaque` | numeric | NO |
| `peso` | numeric | YES |
| `volumen` | numeric | YES |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_estado_producto_variantes`: `CHECK ((estado = ANY (ARRAY[0, 1])))`.
- `producto_variantes_codigo_not_null`: `NOT NULL codigo`.
- `producto_variantes_codigo_unique`: `UNIQUE (codigo)`.
- `producto_variantes_estado_not_null`: `NOT NULL estado`.
- `producto_variantes_id_not_null`: `NOT NULL id`.
- `producto_variantes_nombre_variante_not_null`: `NOT NULL nombre_variante`.
- `producto_variantes_pkey`: `PRIMARY KEY (id)`.
- `producto_variantes_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `producto_variantes_producto_id_not_null`: `NOT NULL producto_id`.
- `producto_variantes_unidad_medida_id_foreign`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE CASCADE`.
- `producto_variantes_unidades_por_empaque_not_null`: `NOT NULL unidades_por_empaque`.
- `uq_variante_por_producto`: `UNIQUE (producto_id, nombre_variante)`.

## productos

Registros: 415.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `categoria_id` | bigint | NO |
| `marca_id` | bigint | YES |
| `nombre` | character varying | NO |
| `descripcion` | text | YES |
| `unidad_medida_id` | bigint | YES |
| `tipo` | integer | YES |
| `estado` | integer | NO |
| `es_transformable` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `rendimiento_porciones` | numeric | NO |

Restricciones:

- `chk_estado_productos`: `CHECK ((estado = ANY (ARRAY[0, 1])))`.
- `chk_tipo_productos`: `CHECK ((tipo = ANY (ARRAY[1, 2, 3])))`.
- `productos_categoria_id_foreign`: `FOREIGN KEY (categoria_id) REFERENCES catalogos(id) ON DELETE CASCADE`.
- `productos_categoria_id_not_null`: `NOT NULL categoria_id`.
- `productos_es_transformable_not_null`: `NOT NULL es_transformable`.
- `productos_estado_not_null`: `NOT NULL estado`.
- `productos_id_not_null`: `NOT NULL id`.
- `productos_marca_id_foreign`: `FOREIGN KEY (marca_id) REFERENCES catalogos(id) ON DELETE CASCADE`.
- `productos_nombre_not_null`: `NOT NULL nombre`.
- `productos_pkey`: `PRIMARY KEY (id)`.
- `productos_rendimiento_porciones_not_null`: `NOT NULL rendimiento_porciones`.
- `productos_unidad_medida_id_foreign`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE CASCADE`.
- `uq_productos_nombre_categoria`: `UNIQUE (nombre, categoria_id)`.

## promocion_beneficio_reglas

Registros: 7.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `beneficio_id` | bigint | NO |
| `tipo_regla` | character varying | NO |
| `operador` | character varying | NO |
| `valor_numerico` | numeric | YES |
| `valor_texto` | character varying | YES |
| `obligatoria` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `promocion_beneficio_reglas_beneficio_id_foreign`: `FOREIGN KEY (beneficio_id) REFERENCES promocion_beneficios(id) ON DELETE CASCADE`.
- `promocion_beneficio_reglas_beneficio_id_not_null`: `NOT NULL beneficio_id`.
- `promocion_beneficio_reglas_id_not_null`: `NOT NULL id`.
- `promocion_beneficio_reglas_obligatoria_not_null`: `NOT NULL obligatoria`.
- `promocion_beneficio_reglas_operador_not_null`: `NOT NULL operador`.
- `promocion_beneficio_reglas_pkey`: `PRIMARY KEY (id)`.
- `promocion_beneficio_reglas_tipo_regla_not_null`: `NOT NULL tipo_regla`.

## promocion_beneficio_usos

Registros: 5.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `beneficio_id` | bigint | NO |
| `cliente_id` | bigint | YES |
| `reserva_id` | bigint | YES |
| `cuenta_id` | bigint | YES |
| `venta_id` | bigint | YES |
| `monto_descuento` | numeric | NO |
| `estado` | character varying | NO |
| `usado_en` | timestamp without time zone | YES |
| `metadata` | json | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `promocion_beneficio_usos_beneficio_id_foreign`: `FOREIGN KEY (beneficio_id) REFERENCES promocion_beneficios(id) ON DELETE CASCADE`.
- `promocion_beneficio_usos_beneficio_id_not_null`: `NOT NULL beneficio_id`.
- `promocion_beneficio_usos_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL`.
- `promocion_beneficio_usos_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE SET NULL`.
- `promocion_beneficio_usos_estado_not_null`: `NOT NULL estado`.
- `promocion_beneficio_usos_id_not_null`: `NOT NULL id`.
- `promocion_beneficio_usos_monto_descuento_not_null`: `NOT NULL monto_descuento`.
- `promocion_beneficio_usos_pkey`: `PRIMARY KEY (id)`.
- `promocion_beneficio_usos_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE SET NULL`.
- `promocion_beneficio_usos_venta_id_foreign`: `FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE SET NULL`.

## promocion_beneficios

Registros: 11.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `promocion_id` | bigint | YES |
| `segmento_cliente_id` | bigint | YES |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `tipo` | character varying | NO |
| `valor` | numeric | YES |
| `es_porcentaje` | boolean | NO |
| `combinable` | boolean | NO |
| `limite_usos_por_cliente` | smallint | YES |
| `fecha_inicio` | date | YES |
| `fecha_fin` | date | YES |
| `activo` | boolean | NO |
| `descripcion` | text | YES |
| `metadata` | json | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `promocion_beneficios_activo_not_null`: `NOT NULL activo`.
- `promocion_beneficios_codigo_not_null`: `NOT NULL codigo`.
- `promocion_beneficios_codigo_unique`: `UNIQUE (codigo)`.
- `promocion_beneficios_combinable_not_null`: `NOT NULL combinable`.
- `promocion_beneficios_es_porcentaje_not_null`: `NOT NULL es_porcentaje`.
- `promocion_beneficios_id_not_null`: `NOT NULL id`.
- `promocion_beneficios_nombre_not_null`: `NOT NULL nombre`.
- `promocion_beneficios_pkey`: `PRIMARY KEY (id)`.
- `promocion_beneficios_promocion_id_foreign`: `FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE SET NULL`.
- `promocion_beneficios_segmento_cliente_id_foreign`: `FOREIGN KEY (segmento_cliente_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `promocion_beneficios_tipo_not_null`: `NOT NULL tipo`.

## promocion_items

Registros: 14.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `promocion_id` | bigint | NO |
| `item_type` | character varying | NO |
| `item_id` | bigint | NO |
| `precio_especial` | numeric | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `promocion_items_id_not_null`: `NOT NULL id`.
- `promocion_items_item_id_not_null`: `NOT NULL item_id`.
- `promocion_items_item_type_not_null`: `NOT NULL item_type`.
- `promocion_items_pkey`: `PRIMARY KEY (id)`.
- `promocion_items_promocion_id_foreign`: `FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE CASCADE`.
- `promocion_items_promocion_id_not_null`: `NOT NULL promocion_id`.

## promociones

Registros: 6.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `descripcion` | text | YES |
| `tipo_promocion_id` | bigint | YES |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | NO |
| `descuento_porcentaje` | numeric | YES |
| `descuento_monto` | numeric | YES |
| `precio_paquete` | numeric | YES |
| `estado` | integer | NO |
| `web` | boolean | NO |
| `condiciones` | text | YES |
| `orden` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `promociones_codigo_not_null`: `NOT NULL codigo`.
- `promociones_codigo_unique`: `UNIQUE (codigo)`.
- `promociones_estado_not_null`: `NOT NULL estado`.
- `promociones_fecha_fin_not_null`: `NOT NULL fecha_fin`.
- `promociones_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `promociones_id_not_null`: `NOT NULL id`.
- `promociones_nombre_not_null`: `NOT NULL nombre`.
- `promociones_orden_not_null`: `NOT NULL orden`.
- `promociones_pkey`: `PRIMARY KEY (id)`.
- `promociones_tipo_promocion_id_foreign`: `FOREIGN KEY (tipo_promocion_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `promociones_web_not_null`: `NOT NULL web`.

## proveedor_contactos

Registros: 12.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `proveedor_id` | bigint | NO |
| `nombre` | character varying | NO |
| `cargo` | character varying | YES |
| `telefono` | character varying | YES |
| `email` | character varying | YES |
| `principal` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `proveedor_contactos_id_not_null`: `NOT NULL id`.
- `proveedor_contactos_nombre_not_null`: `NOT NULL nombre`.
- `proveedor_contactos_pkey`: `PRIMARY KEY (id)`.
- `proveedor_contactos_principal_not_null`: `NOT NULL principal`.
- `proveedor_contactos_proveedor_id_foreign`: `FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE`.
- `proveedor_contactos_proveedor_id_not_null`: `NOT NULL proveedor_id`.

## proveedores

Registros: 6.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `persona_id` | bigint | NO |
| `tipo_proveedor_id` | bigint | YES |
| `direccion_fiscal` | character varying | YES |
| `notas` | text | YES |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_proveedores_estado`: `CHECK ((estado = ANY (ARRAY[0, 1])))`.
- `proveedores_codigo_not_null`: `NOT NULL codigo`.
- `proveedores_codigo_unique`: `UNIQUE (codigo)`.
- `proveedores_estado_not_null`: `NOT NULL estado`.
- `proveedores_id_not_null`: `NOT NULL id`.
- `proveedores_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE`.
- `proveedores_persona_id_not_null`: `NOT NULL persona_id`.
- `proveedores_pkey`: `PRIMARY KEY (id)`.
- `proveedores_tipo_proveedor_id_foreign`: `FOREIGN KEY (tipo_proveedor_id) REFERENCES catalogos(id) ON DELETE SET NULL`.

## recepcion_items

Registros: 22891.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `recepcion_id` | bigint | NO |
| `orden_item_id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `unidad_medida_id` | bigint | YES |
| `cantidad_recibida` | numeric | NO |
| `cantidad_rechazada` | numeric | NO |
| `motivo_rechazo` | character varying | YES |
| `nota` | text | YES |
| `lote_proveedor` | character varying | YES |
| `fecha_vencimiento` | date | YES |
| `ubicacion_id` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `recepcion_items_cantidad_rechazada_not_null`: `NOT NULL cantidad_rechazada`.
- `recepcion_items_cantidad_recibida_not_null`: `NOT NULL cantidad_recibida`.
- `recepcion_items_id_not_null`: `NOT NULL id`.
- `recepcion_items_orden_item_id_foreign`: `FOREIGN KEY (orden_item_id) REFERENCES orden_compra_items(id) ON DELETE CASCADE`.
- `recepcion_items_orden_item_id_not_null`: `NOT NULL orden_item_id`.
- `recepcion_items_pkey`: `PRIMARY KEY (id)`.
- `recepcion_items_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id)`.
- `recepcion_items_producto_id_not_null`: `NOT NULL producto_id`.
- `recepcion_items_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `recepcion_items_recepcion_id_foreign`: `FOREIGN KEY (recepcion_id) REFERENCES recepciones_compra(id) ON DELETE CASCADE`.
- `recepcion_items_recepcion_id_not_null`: `NOT NULL recepcion_id`.
- `recepcion_items_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `recepcion_items_unidad_medida_id_foreign`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE SET NULL`.

## recepciones_compra

Registros: 4570.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `orden_compra_id` | bigint | NO |
| `fecha_recepcion` | date | NO |
| `guia_remision` | character varying | YES |
| `recibido_por_id` | bigint | NO |
| `ubicacion_id` | bigint | YES |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_recepciones_estado`: `CHECK ((estado = ANY (ARRAY[0, 1, 2, 3, 4, 5])))`.
- `recepciones_compra_codigo_not_null`: `NOT NULL codigo`.
- `recepciones_compra_codigo_unique`: `UNIQUE (codigo)`.
- `recepciones_compra_estado_not_null`: `NOT NULL estado`.
- `recepciones_compra_fecha_recepcion_not_null`: `NOT NULL fecha_recepcion`.
- `recepciones_compra_id_not_null`: `NOT NULL id`.
- `recepciones_compra_orden_compra_id_foreign`: `FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE`.
- `recepciones_compra_orden_compra_id_not_null`: `NOT NULL orden_compra_id`.
- `recepciones_compra_pkey`: `PRIMARY KEY (id)`.
- `recepciones_compra_recibido_por_id_foreign`: `FOREIGN KEY (recibido_por_id) REFERENCES users(id)`.
- `recepciones_compra_recibido_por_id_not_null`: `NOT NULL recibido_por_id`.
- `recepciones_compra_ubicacion_id_foreign`: `FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.

## recursos_reservables

Registros: 160.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `tipo` | smallint | NO |
| `nombre` | character varying | NO |
| `capacidad` | smallint | YES |
| `control_disponibilidad` | smallint | NO |
| `duracion_minutos` | integer | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `recursos_reservables_control_disponibilidad_not_null`: `NOT NULL control_disponibilidad`.
- `recursos_reservables_estado_not_null`: `NOT NULL estado`.
- `recursos_reservables_id_not_null`: `NOT NULL id`.
- `recursos_reservables_nombre_not_null`: `NOT NULL nombre`.
- `recursos_reservables_pkey`: `PRIMARY KEY (id)`.
- `recursos_reservables_tipo_not_null`: `NOT NULL tipo`.

## reserva_detalles

Registros: 10106.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `reserva_id` | bigint | NO |
| `reservable_id` | bigint | NO |
| `parent_id` | bigint | YES |
| `estado` | smallint | NO |
| `fecha_inicio` | timestamp without time zone | NO |
| `fecha_fin` | timestamp without time zone | YES |
| `cantidad` | integer | NO |
| `adultos` | smallint | NO |
| `ninos` | smallint | NO |
| `precio_unitario` | numeric | NO |
| `descuento` | numeric | NO |
| `impuestos` | numeric | NO |
| `subtotal` | numeric | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `hold_expires_at` | timestamp without time zone | YES |
| `confirmado_at` | timestamp without time zone | YES |
| `cancelado_at` | timestamp without time zone | YES |
| `estancia_id` | bigint | YES |
| `origen` | smallint | NO |

Restricciones:

- `reserva_detalles_adultos_not_null`: `NOT NULL adultos`.
- `reserva_detalles_cantidad_not_null`: `NOT NULL cantidad`.
- `reserva_detalles_descuento_not_null`: `NOT NULL descuento`.
- `reserva_detalles_estado_not_null`: `NOT NULL estado`.
- `reserva_detalles_estancia_id_foreign`: `FOREIGN KEY (estancia_id) REFERENCES estancias(id) ON DELETE SET NULL`.
- `reserva_detalles_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `reserva_detalles_id_not_null`: `NOT NULL id`.
- `reserva_detalles_id_reserva_unique`: `UNIQUE (id, reserva_id)`.
- `reserva_detalles_impuestos_not_null`: `NOT NULL impuestos`.
- `reserva_detalles_ninos_not_null`: `NOT NULL ninos`.
- `reserva_detalles_origen_not_null`: `NOT NULL origen`.
- `reserva_detalles_parent_id_foreign`: `FOREIGN KEY (parent_id) REFERENCES reserva_detalles(id) ON DELETE SET NULL`.
- `reserva_detalles_pkey`: `PRIMARY KEY (id)`.
- `reserva_detalles_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `reserva_detalles_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE`.
- `reserva_detalles_reserva_id_not_null`: `NOT NULL reserva_id`.
- `reserva_detalles_reservable_id_foreign`: `FOREIGN KEY (reservable_id) REFERENCES recursos_reservables(id) ON DELETE RESTRICT`.
- `reserva_detalles_reservable_id_not_null`: `NOT NULL reservable_id`.
- `reserva_detalles_subtotal_not_null`: `NOT NULL subtotal`.

## reserva_estado_historial

Registros: 10108.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `reserva_id` | bigint | NO |
| `estado_anterior` | smallint | YES |
| `estado_nuevo` | smallint | NO |
| `motivo` | text | YES |
| `usuario_id` | bigint | YES |
| `created_at` | timestamp without time zone | NO |

Restricciones:

- `reserva_estado_historial_created_at_not_null`: `NOT NULL created_at`.
- `reserva_estado_historial_estado_nuevo_not_null`: `NOT NULL estado_nuevo`.
- `reserva_estado_historial_id_not_null`: `NOT NULL id`.
- `reserva_estado_historial_pkey`: `PRIMARY KEY (id)`.
- `reserva_estado_historial_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE`.
- `reserva_estado_historial_reserva_id_not_null`: `NOT NULL reserva_id`.
- `reserva_estado_historial_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL`.

## reserva_huespedes

Registros: 10105.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `reserva_detalle_id` | bigint | NO |
| `nombre` | character varying | NO |
| `tipo_identificacion` | smallint | YES |
| `identificacion` | character varying | YES |
| `tipo_huesped` | smallint | NO |
| `es_titular` | boolean | NO |
| `fecha_nacimiento` | date | YES |
| `telefono` | character varying | YES |
| `email` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `reserva_huespedes_es_titular_not_null`: `NOT NULL es_titular`.
- `reserva_huespedes_id_not_null`: `NOT NULL id`.
- `reserva_huespedes_nombre_not_null`: `NOT NULL nombre`.
- `reserva_huespedes_pkey`: `PRIMARY KEY (id)`.
- `reserva_huespedes_reserva_detalle_id_foreign`: `FOREIGN KEY (reserva_detalle_id) REFERENCES reserva_detalles(id) ON DELETE CASCADE`.
- `reserva_huespedes_reserva_detalle_id_not_null`: `NOT NULL reserva_detalle_id`.
- `reserva_huespedes_tipo_huesped_not_null`: `NOT NULL tipo_huesped`.

## reserva_servicios

Registros: 3224.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `reserva_id` | bigint | NO |
| `servicio_id` | bigint | NO |
| `cantidad` | integer | NO |
| `precio` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `reserva_servicios_cantidad_not_null`: `NOT NULL cantidad`.
- `reserva_servicios_id_not_null`: `NOT NULL id`.
- `reserva_servicios_pkey`: `PRIMARY KEY (id)`.
- `reserva_servicios_precio_not_null`: `NOT NULL precio`.
- `reserva_servicios_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE`.
- `reserva_servicios_reserva_id_not_null`: `NOT NULL reserva_id`.
- `reserva_servicios_servicio_id_foreign`: `FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE`.
- `reserva_servicios_servicio_id_not_null`: `NOT NULL servicio_id`.

## reservas

Registros: 10106.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo_reserva` | character varying | NO |
| `cliente_id` | bigint | YES |
| `nombre_cliente` | character varying | NO |
| `telefono_cliente` | character varying | YES |
| `email_cliente` | character varying | YES |
| `tipo_reserva` | character varying | NO |
| `habitacion_id` | bigint | YES |
| `espacio_id` | bigint | YES |
| `servicio_id` | bigint | YES |
| `promocion_id` | bigint | YES |
| `fecha_check_in` | date | NO |
| `fecha_check_out` | date | YES |
| `hora_reserva` | character varying | YES |
| `adultos` | integer | NO |
| `ninos` | integer | NO |
| `acompanantes_legacy` | json | YES |
| `estado` | smallint | NO |
| `subtotal` | numeric | YES |
| `descuento` | numeric | NO |
| `total` | numeric | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `moneda_id` | bigint | YES |
| `tipo_pago` | character varying | NO |
| `total_pagado` | numeric | NO |
| `saldo` | numeric | NO |
| `solicita_cuenta` | boolean | NO |
| `limite_cuenta_solicitado` | numeric | YES |

Restricciones:

- `reservas_adultos_not_null`: `NOT NULL adultos`.
- `reservas_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL`.
- `reservas_codigo_reserva_not_null`: `NOT NULL codigo_reserva`.
- `reservas_codigo_reserva_unique`: `UNIQUE (codigo_reserva)`.
- `reservas_descuento_not_null`: `NOT NULL descuento`.
- `reservas_espacio_id_foreign`: `FOREIGN KEY (espacio_id) REFERENCES espacios(id) ON DELETE SET NULL`.
- `reservas_estado_not_null`: `NOT NULL estado`.
- `reservas_fecha_check_in_not_null`: `NOT NULL fecha_check_in`.
- `reservas_habitacion_id_foreign`: `FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE SET NULL`.
- `reservas_id_not_null`: `NOT NULL id`.
- `reservas_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE SET NULL`.
- `reservas_ninos_not_null`: `NOT NULL ninos`.
- `reservas_nombre_cliente_not_null`: `NOT NULL nombre_cliente`.
- `reservas_pkey`: `PRIMARY KEY (id)`.
- `reservas_promocion_id_foreign`: `FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE SET NULL`.
- `reservas_saldo_not_null`: `NOT NULL saldo`.
- `reservas_servicio_id_foreign`: `FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE SET NULL`.
- `reservas_solicita_cuenta_not_null`: `NOT NULL solicita_cuenta`.
- `reservas_tipo_pago_not_null`: `NOT NULL tipo_pago`.
- `reservas_tipo_reserva_not_null`: `NOT NULL tipo_reserva`.
- `reservas_total_not_null`: `NOT NULL total`.
- `reservas_total_pagado_not_null`: `NOT NULL total_pagado`.

## reservas_bitacora

Registros: 324.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `reserva_id` | bigint | NO |
| `tipo` | character varying | NO |
| `datos` | jsonb | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `reservas_bitacora_id_not_null`: `NOT NULL id`.
- `reservas_bitacora_pkey`: `PRIMARY KEY (id)`.
- `reservas_bitacora_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE`.
- `reservas_bitacora_reserva_id_not_null`: `NOT NULL reserva_id`.
- `reservas_bitacora_tipo_not_null`: `NOT NULL tipo`.

## restaurante_buffet_cierres

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `buffet_id` | bigint | NO |
| `stock_id` | bigint | NO |
| `cantidad_servida` | numeric | NO |
| `sobrante` | numeric | NO |
| `merma` | numeric | NO |
| `costo_consumido` | numeric | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_buffet_cierres_buffet_id_foreign`: `FOREIGN KEY (buffet_id) REFERENCES restaurante_buffets(id) ON DELETE RESTRICT`.
- `restaurante_buffet_cierres_buffet_id_not_null`: `NOT NULL buffet_id`.
- `restaurante_buffet_cierres_buffet_id_stock_id_unique`: `UNIQUE (buffet_id, stock_id)`.
- `restaurante_buffet_cierres_cantidad_servida_not_null`: `NOT NULL cantidad_servida`.
- `restaurante_buffet_cierres_costo_consumido_not_null`: `NOT NULL costo_consumido`.
- `restaurante_buffet_cierres_id_not_null`: `NOT NULL id`.
- `restaurante_buffet_cierres_merma_not_null`: `NOT NULL merma`.
- `restaurante_buffet_cierres_pkey`: `PRIMARY KEY (id)`.
- `restaurante_buffet_cierres_sobrante_not_null`: `NOT NULL sobrante`.
- `restaurante_buffet_cierres_stock_id_foreign`: `FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE RESTRICT`.
- `restaurante_buffet_cierres_stock_id_not_null`: `NOT NULL stock_id`.

## restaurante_buffet_platos

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `buffet_id` | bigint | NO |
| `plato_id` | bigint | NO |
| `producto_variante_id` | bigint | NO |
| `porciones_previstas` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_buffet_platos_buffet_id_foreign`: `FOREIGN KEY (buffet_id) REFERENCES restaurante_buffets(id) ON DELETE RESTRICT`.
- `restaurante_buffet_platos_buffet_id_not_null`: `NOT NULL buffet_id`.
- `restaurante_buffet_platos_buffet_id_plato_id_unique`: `UNIQUE (buffet_id, plato_id)`.
- `restaurante_buffet_platos_buffet_id_producto_variante_id_unique`: `UNIQUE (buffet_id, producto_variante_id)`.
- `restaurante_buffet_platos_id_not_null`: `NOT NULL id`.
- `restaurante_buffet_platos_pkey`: `PRIMARY KEY (id)`.
- `restaurante_buffet_platos_plato_id_foreign`: `FOREIGN KEY (plato_id) REFERENCES platos(id) ON DELETE RESTRICT`.
- `restaurante_buffet_platos_plato_id_not_null`: `NOT NULL plato_id`.
- `restaurante_buffet_platos_porciones_previstas_not_null`: `NOT NULL porciones_previstas`.
- `restaurante_buffet_platos_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT`.
- `restaurante_buffet_platos_producto_variante_id_not_null`: `NOT NULL producto_variante_id`.

## restaurante_buffets

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `nombre` | character varying | NO |
| `fecha_servicio` | date | NO |
| `reserva_id` | bigint | YES |
| `moneda_id` | bigint | NO |
| `modalidad` | character varying | NO |
| `precio_persona` | numeric | NO |
| `precio_paquete` | numeric | NO |
| `comensales_previstos` | integer | NO |
| `comensales_reales` | integer | NO |
| `estado` | character varying | NO |
| `cerrado_at` | timestamp without time zone | YES |
| `cuenta_id` | bigint | YES |
| `cargado_at` | timestamp without time zone | YES |
| `personas_cobradas` | integer | NO |
| `paquetes_cobrados` | integer | NO |
| `creado_por` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `tipo_servicio` | character varying | NO |

Restricciones:

- `restaurante_buffets_comensales_previstos_not_null`: `NOT NULL comensales_previstos`.
- `restaurante_buffets_comensales_reales_not_null`: `NOT NULL comensales_reales`.
- `restaurante_buffets_creado_por_foreign`: `FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `restaurante_buffets_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `restaurante_buffets_estado_not_null`: `NOT NULL estado`.
- `restaurante_buffets_fecha_servicio_not_null`: `NOT NULL fecha_servicio`.
- `restaurante_buffets_id_not_null`: `NOT NULL id`.
- `restaurante_buffets_modalidad_not_null`: `NOT NULL modalidad`.
- `restaurante_buffets_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `restaurante_buffets_moneda_id_not_null`: `NOT NULL moneda_id`.
- `restaurante_buffets_nombre_not_null`: `NOT NULL nombre`.
- `restaurante_buffets_paquetes_cobrados_not_null`: `NOT NULL paquetes_cobrados`.
- `restaurante_buffets_personas_cobradas_not_null`: `NOT NULL personas_cobradas`.
- `restaurante_buffets_pkey`: `PRIMARY KEY (id)`.
- `restaurante_buffets_precio_paquete_not_null`: `NOT NULL precio_paquete`.
- `restaurante_buffets_precio_persona_not_null`: `NOT NULL precio_persona`.
- `restaurante_buffets_reserva_id_foreign`: `FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE RESTRICT`.
- `restaurante_buffets_tipo_servicio_not_null`: `NOT NULL tipo_servicio`.

## restaurante_recetas_transformacion_materia_prima

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `producto_materia_prima_id` | bigint | NO |
| `variante_materia_prima_id` | bigint | NO |
| `producto_bruto_id` | bigint | NO |
| `variante_bruta_id` | bigint | NO |
| `cantidad_bruta` | numeric | NO |
| `cantidad_resultado` | numeric | NO |
| `merma_estimada` | numeric | NO |
| `unidad_medida_id` | bigint | YES |
| `estado` | boolean | NO |
| `observaciones` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_recetas_transfor_producto_materia_prima_id_not_null`: `NOT NULL producto_materia_prima_id`.
- `restaurante_recetas_transfor_variante_materia_prima_id_not_null`: `NOT NULL variante_materia_prima_id`.
- `restaurante_recetas_transformacion__cantidad_resultado_not_null`: `NOT NULL cantidad_resultado`.
- `restaurante_recetas_transformacion_m_producto_bruto_id_not_null`: `NOT NULL producto_bruto_id`.
- `restaurante_recetas_transformacion_m_variante_bruta_id_not_null`: `NOT NULL variante_bruta_id`.
- `restaurante_recetas_transformacion_mate_cantidad_bruta_not_null`: `NOT NULL cantidad_bruta`.
- `restaurante_recetas_transformacion_mate_merma_estimada_not_null`: `NOT NULL merma_estimada`.
- `restaurante_recetas_transformacion_materia_prim_estado_not_null`: `NOT NULL estado`.
- `restaurante_recetas_transformacion_materia_prima_id_not_null`: `NOT NULL id`.
- `restaurante_recetas_transformacion_materia_prima_pkey`: `PRIMARY KEY (id)`.
- `restaurante_recetas_transformacion_materia_prima_producto_bruto`: `FOREIGN KEY (producto_bruto_id) REFERENCES productos(id)`.
- `restaurante_recetas_transformacion_materia_prima_producto_mater`: `FOREIGN KEY (producto_materia_prima_id) REFERENCES productos(id)`.
- `restaurante_recetas_transformacion_materia_prima_unidad_medida_`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `restaurante_recetas_transformacion_materia_prima_variante_bruta`: `FOREIGN KEY (variante_bruta_id) REFERENCES producto_variantes(id)`.
- `restaurante_recetas_transformacion_materia_prima_variante_mater`: `FOREIGN KEY (variante_materia_prima_id) REFERENCES producto_variantes(id)`.
- `uq_receta_tmp_variantes`: `UNIQUE (variante_materia_prima_id, variante_bruta_id, deleted_at)`.

## restaurante_sustituciones_ingredientes

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `pedido_item_id` | bigint | NO |
| `plato_id` | bigint | YES |
| `producto_original_id` | bigint | YES |
| `variante_original_id` | bigint | NO |
| `producto_sustituto_id` | bigint | YES |
| `variante_sustituta_id` | bigint | NO |
| `cantidad_requerida` | numeric | NO |
| `cantidad_usada` | numeric | NO |
| `motivo` | character varying | YES |
| `autorizado_por` | bigint | YES |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_sustituciones_ingred_variante_sustituta_id_not_null`: `NOT NULL variante_sustituta_id`.
- `restaurante_sustituciones_ingredi_variante_original_id_not_null`: `NOT NULL variante_original_id`.
- `restaurante_sustituciones_ingredien_cantidad_requerida_not_null`: `NOT NULL cantidad_requerida`.
- `restaurante_sustituciones_ingredientes_autorizado_por_foreign`: `FOREIGN KEY (autorizado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `restaurante_sustituciones_ingredientes_cantidad_usada_not_null`: `NOT NULL cantidad_usada`.
- `restaurante_sustituciones_ingredientes_estado_not_null`: `NOT NULL estado`.
- `restaurante_sustituciones_ingredientes_id_not_null`: `NOT NULL id`.
- `restaurante_sustituciones_ingredientes_pedido_item_id_foreign`: `FOREIGN KEY (pedido_item_id) REFERENCES pedido_items(id) ON DELETE CASCADE`.
- `restaurante_sustituciones_ingredientes_pedido_item_id_not_null`: `NOT NULL pedido_item_id`.
- `restaurante_sustituciones_ingredientes_pkey`: `PRIMARY KEY (id)`.
- `restaurante_sustituciones_ingredientes_plato_id_foreign`: `FOREIGN KEY (plato_id) REFERENCES platos(id) ON DELETE SET NULL`.
- `restaurante_sustituciones_ingredientes_producto_original_id_for`: `FOREIGN KEY (producto_original_id) REFERENCES productos(id) ON DELETE SET NULL`.
- `restaurante_sustituciones_ingredientes_producto_sustituto_id_fo`: `FOREIGN KEY (producto_sustituto_id) REFERENCES productos(id) ON DELETE SET NULL`.
- `restaurante_sustituciones_ingredientes_variante_original_id_for`: `FOREIGN KEY (variante_original_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT`.
- `restaurante_sustituciones_ingredientes_variante_sustituta_id_fo`: `FOREIGN KEY (variante_sustituta_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT`.

## restaurante_transformacion_materia_prima_items

Registros: 17650.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `transformacion_id` | bigint | NO |
| `producto_destino_id` | bigint | NO |
| `variante_destino_id` | bigint | YES |
| `ubicacion_destino_id` | bigint | YES |
| `cantidad` | numeric | NO |
| `costo_asignado` | numeric | NO |
| `es_merma` | boolean | NO |
| `observaciones` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_transformacion_materia_p_transformacion_id_not_null`: `NOT NULL transformacion_id`.
- `restaurante_transformacion_materia_prim_costo_asignado_not_null`: `NOT NULL costo_asignado`.
- `restaurante_transformacion_materia_prima_item_cantidad_not_null`: `NOT NULL cantidad`.
- `restaurante_transformacion_materia_prima_item_es_merma_not_null`: `NOT NULL es_merma`.
- `restaurante_transformacion_materia_prima_items_id_not_null`: `NOT NULL id`.
- `restaurante_transformacion_materia_prima_items_pkey`: `PRIMARY KEY (id)`.
- `restaurante_transformacion_materia_prima_items_producto_destino`: `FOREIGN KEY (producto_destino_id) REFERENCES productos(id)`.
- `restaurante_transformacion_materia_prima_items_transformacion_i`: `FOREIGN KEY (transformacion_id) REFERENCES restaurante_transformaciones_materia_prima(id) ON DELETE CASCADE`.
- `restaurante_transformacion_materia_prima_items_ubicacion_destin`: `FOREIGN KEY (ubicacion_destino_id) REFERENCES ubicaciones(id) ON DELETE SET NULL`.
- `restaurante_transformacion_materia_prima_items_variante_destino`: `FOREIGN KEY (variante_destino_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `restaurante_transformacion_materia_producto_destino_id_not_null`: `NOT NULL producto_destino_id`.

## restaurante_transformaciones_materia_prima

Registros: 3208.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `producto_origen_id` | bigint | NO |
| `variante_origen_id` | bigint | NO |
| `ubicacion_origen_id` | bigint | NO |
| `cantidad_procesada` | numeric | NO |
| `costo_total` | numeric | NO |
| `realizado_por` | bigint | YES |
| `observaciones` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_transformaciones_mater_ubicacion_origen_id_not_null`: `NOT NULL ubicacion_origen_id`.
- `restaurante_transformaciones_materi_cantidad_procesada_not_null`: `NOT NULL cantidad_procesada`.
- `restaurante_transformaciones_materi_producto_origen_id_not_null`: `NOT NULL producto_origen_id`.
- `restaurante_transformaciones_materi_variante_origen_id_not_null`: `NOT NULL variante_origen_id`.
- `restaurante_transformaciones_materia_prima_codigo_not_null`: `NOT NULL codigo`.
- `restaurante_transformaciones_materia_prima_codigo_unique`: `UNIQUE (codigo)`.
- `restaurante_transformaciones_materia_prima_costo_total_not_null`: `NOT NULL costo_total`.
- `restaurante_transformaciones_materia_prima_id_not_null`: `NOT NULL id`.
- `restaurante_transformaciones_materia_prima_pkey`: `PRIMARY KEY (id)`.
- `restaurante_transformaciones_materia_prima_producto_origen_id_f`: `FOREIGN KEY (producto_origen_id) REFERENCES productos(id)`.
- `restaurante_transformaciones_materia_prima_realizado_por_foreig`: `FOREIGN KEY (realizado_por) REFERENCES users(id) ON DELETE SET NULL`.
- `restaurante_transformaciones_materia_prima_ubicacion_origen_id_`: `FOREIGN KEY (ubicacion_origen_id) REFERENCES ubicaciones(id)`.
- `restaurante_transformaciones_materia_prima_variante_origen_id_f`: `FOREIGN KEY (variante_origen_id) REFERENCES producto_variantes(id)`.

## restaurante_zonas_delivery

Registros: 17.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `costo_envio` | numeric | NO |
| `activo` | boolean | NO |
| `municipios` | json | NO |
| `orden` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `restaurante_zonas_delivery_activo_not_null`: `NOT NULL activo`.
- `restaurante_zonas_delivery_codigo_not_null`: `NOT NULL codigo`.
- `restaurante_zonas_delivery_codigo_unique`: `UNIQUE (codigo)`.
- `restaurante_zonas_delivery_costo_envio_not_null`: `NOT NULL costo_envio`.
- `restaurante_zonas_delivery_id_not_null`: `NOT NULL id`.
- `restaurante_zonas_delivery_municipios_not_null`: `NOT NULL municipios`.
- `restaurante_zonas_delivery_nombre_not_null`: `NOT NULL nombre`.
- `restaurante_zonas_delivery_orden_not_null`: `NOT NULL orden`.
- `restaurante_zonas_delivery_pkey`: `PRIMARY KEY (id)`.

## role_has_permissions

Registros: 216.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `permission_id` | bigint | NO |
| `role_id` | bigint | NO |

Restricciones:

- `role_has_permissions_permission_id_foreign`: `FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE`.
- `role_has_permissions_permission_id_not_null`: `NOT NULL permission_id`.
- `role_has_permissions_pkey`: `PRIMARY KEY (permission_id, role_id)`.
- `role_has_permissions_role_id_foreign`: `FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE`.
- `role_has_permissions_role_id_not_null`: `NOT NULL role_id`.

## roles

Registros: 29.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `name` | character varying | NO |
| `guard_name` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `roles_guard_name_not_null`: `NOT NULL guard_name`.
- `roles_id_not_null`: `NOT NULL id`.
- `roles_name_guard_name_unique`: `UNIQUE (name, guard_name)`.
- `roles_name_not_null`: `NOT NULL name`.
- `roles_pkey`: `PRIMARY KEY (id)`.

## secuencias

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `clave` | character varying | YES |
| `tipo` | character varying | YES |
| `anio` | integer | YES |
| `ultimo_valor` | bigint | NO |
| `ultimo_numero` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `secuencias_id_not_null`: `NOT NULL id`.
- `secuencias_pkey`: `PRIMARY KEY (id)`.
- `secuencias_ultimo_numero_not_null`: `NOT NULL ultimo_numero`.
- `secuencias_ultimo_valor_not_null`: `NOT NULL ultimo_valor`.

## servicio_asignaciones

Registros: 498.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `serviceable_type` | character varying | NO |
| `serviceable_id` | bigint | NO |
| `servicio_id` | bigint | NO |
| `incluido` | boolean | NO |
| `estado` | smallint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `servicio_asignaciones_estado_not_null`: `NOT NULL estado`.
- `servicio_asignaciones_id_not_null`: `NOT NULL id`.
- `servicio_asignaciones_incluido_not_null`: `NOT NULL incluido`.
- `servicio_asignaciones_pkey`: `PRIMARY KEY (id)`.
- `servicio_asignaciones_serviceable_id_not_null`: `NOT NULL serviceable_id`.
- `servicio_asignaciones_serviceable_type_not_null`: `NOT NULL serviceable_type`.
- `servicio_asignaciones_servicio_id_foreign`: `FOREIGN KEY (servicio_id) REFERENCES servicios(id)`.
- `servicio_asignaciones_servicio_id_not_null`: `NOT NULL servicio_id`.

## servicios

Registros: 25.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `categoria_id` | bigint | YES |
| `descripcion` | text | YES |
| `icono` | character varying | YES |
| `estado` | integer | NO |
| `web` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `reservable_id` | bigint | YES |

Restricciones:

- `servicios_categoria_id_foreign`: `FOREIGN KEY (categoria_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `servicios_codigo_not_null`: `NOT NULL codigo`.
- `servicios_codigo_unique`: `UNIQUE (codigo)`.
- `servicios_estado_check`: `CHECK ((estado = ANY (ARRAY[1, 2, 3])))`.
- `servicios_estado_not_null`: `NOT NULL estado`.
- `servicios_id_not_null`: `NOT NULL id`.
- `servicios_nombre_not_null`: `NOT NULL nombre`.
- `servicios_pkey`: `PRIMARY KEY (id)`.
- `servicios_reservable_id_foreign`: `FOREIGN KEY (reservable_id) REFERENCES recursos_reservables(id) ON DELETE SET NULL`.
- `servicios_reservable_id_unique`: `UNIQUE (reservable_id)`.
- `servicios_web_not_null`: `NOT NULL web`.

## servicios_precios

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `servicio_id` | bigint | NO |
| `moneda_id` | bigint | NO |
| `precio` | numeric | NO |
| `fecha_inicio` | date | NO |
| `fecha_fin` | date | YES |
| `estado` | integer | NO |
| `es_oferta` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_fecha`: `CHECK (((fecha_fin IS NULL) OR (fecha_fin >= fecha_inicio)))`.
- `chk_precio`: `CHECK ((precio >= (0)::numeric))`.
- `chk_precio_vigente`: `CHECK (((estado <> 1) OR (precio > (0)::numeric)))`.
- `servicios_precios_es_oferta_not_null`: `NOT NULL es_oferta`.
- `servicios_precios_estado_check`: `CHECK ((estado = ANY (ARRAY[1, 2])))`.
- `servicios_precios_estado_not_null`: `NOT NULL estado`.
- `servicios_precios_fecha_inicio_not_null`: `NOT NULL fecha_inicio`.
- `servicios_precios_id_not_null`: `NOT NULL id`.
- `servicios_precios_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id)`.
- `servicios_precios_moneda_id_not_null`: `NOT NULL moneda_id`.
- `servicios_precios_pkey`: `PRIMARY KEY (id)`.
- `servicios_precios_precio_not_null`: `NOT NULL precio`.
- `servicios_precios_servicio_id_foreign`: `FOREIGN KEY (servicio_id) REFERENCES servicios(id)`.
- `servicios_precios_servicio_id_not_null`: `NOT NULL servicio_id`.

## sessions

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | character varying | NO |
| `user_id` | bigint | YES |
| `ip_address` | character varying | YES |
| `user_agent` | text | YES |
| `payload` | text | NO |
| `last_activity` | integer | NO |

Restricciones:

- `sessions_id_not_null`: `NOT NULL id`.
- `sessions_last_activity_not_null`: `NOT NULL last_activity`.
- `sessions_payload_not_null`: `NOT NULL payload`.
- `sessions_pkey`: `PRIMARY KEY (id)`.

## social_accounts

Registros: 2.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `user_id` | bigint | NO |
| `provider` | character varying | NO |
| `provider_id` | character varying | NO |
| `provider_email` | character varying | YES |
| `avatar` | character varying | YES |
| `provider_data` | json | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `social_accounts_id_not_null`: `NOT NULL id`.
- `social_accounts_pkey`: `PRIMARY KEY (id)`.
- `social_accounts_provider_id_not_null`: `NOT NULL provider_id`.
- `social_accounts_provider_not_null`: `NOT NULL provider`.
- `social_accounts_provider_provider_id_unique`: `UNIQUE (provider, provider_id)`.
- `social_accounts_user_id_foreign`: `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`.
- `social_accounts_user_id_not_null`: `NOT NULL user_id`.

## solicitud_items

Registros: 30170.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `solicitud_id` | bigint | NO |
| `producto_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `unidad_medida_id` | bigint | YES |
| `cantidad_solicitada` | numeric | NO |
| `cantidad_aprobada` | numeric | NO |
| `observaciones` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `solicitud_items_cantidad_aprobada_not_null`: `NOT NULL cantidad_aprobada`.
- `solicitud_items_cantidad_solicitada_not_null`: `NOT NULL cantidad_solicitada`.
- `solicitud_items_id_not_null`: `NOT NULL id`.
- `solicitud_items_pkey`: `PRIMARY KEY (id)`.
- `solicitud_items_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id)`.
- `solicitud_items_producto_id_not_null`: `NOT NULL producto_id`.
- `solicitud_items_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id) ON DELETE SET NULL`.
- `solicitud_items_solicitud_id_foreign`: `FOREIGN KEY (solicitud_id) REFERENCES solicitudes_compra(id) ON DELETE CASCADE`.
- `solicitud_items_solicitud_id_not_null`: `NOT NULL solicitud_id`.
- `solicitud_items_unidad_medida_id_foreign`: `FOREIGN KEY (unidad_medida_id) REFERENCES catalogos(id) ON DELETE SET NULL`.

## solicitud_limpiezas

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `limpiable_type` | character varying | NO |
| `limpiable_id` | bigint | NO |
| `personal_id` | bigint | YES |
| `creador_id` | bigint | YES |
| `prioridad` | character varying | NO |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `solicitud_limpiezas_creador_id_foreign`: `FOREIGN KEY (creador_id) REFERENCES users(id) ON DELETE SET NULL`.
- `solicitud_limpiezas_estado_not_null`: `NOT NULL estado`.
- `solicitud_limpiezas_id_not_null`: `NOT NULL id`.
- `solicitud_limpiezas_limpiable_id_not_null`: `NOT NULL limpiable_id`.
- `solicitud_limpiezas_limpiable_type_not_null`: `NOT NULL limpiable_type`.
- `solicitud_limpiezas_personal_id_foreign`: `FOREIGN KEY (personal_id) REFERENCES users(id) ON DELETE SET NULL`.
- `solicitud_limpiezas_pkey`: `PRIMARY KEY (id)`.
- `solicitud_limpiezas_prioridad_not_null`: `NOT NULL prioridad`.

## solicitudes_compra

Registros: 6185.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `colaborador_id` | bigint | NO |
| `departamento_solicitante_id` | bigint | YES |
| `fecha_solicitud` | date | NO |
| `fecha_necesita` | date | YES |
| `motivo` | text | YES |
| `estado` | integer | NO |
| `notas` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `chk_solicitudes_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3, 4, 5])))`.
- `solicitudes_compra_codigo_not_null`: `NOT NULL codigo`.
- `solicitudes_compra_codigo_unique`: `UNIQUE (codigo)`.
- `solicitudes_compra_colaborador_id_foreign`: `FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE`.
- `solicitudes_compra_colaborador_id_not_null`: `NOT NULL colaborador_id`.
- `solicitudes_compra_departamento_solicitante_id_foreign`: `FOREIGN KEY (departamento_solicitante_id) REFERENCES catalogos(id) ON DELETE SET NULL`.
- `solicitudes_compra_estado_not_null`: `NOT NULL estado`.
- `solicitudes_compra_fecha_solicitud_not_null`: `NOT NULL fecha_solicitud`.
- `solicitudes_compra_id_not_null`: `NOT NULL id`.
- `solicitudes_compra_pkey`: `PRIMARY KEY (id)`.

## stocks

Registros: 2004.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `stockable_type` | character varying | NO |
| `stockable_id` | bigint | NO |
| `producto_variante_id` | bigint | YES |
| `lote_id` | bigint | YES |
| `cantidad_ideal` | numeric | NO |
| `cantidad_actual` | numeric | NO |
| `estado` | character varying | YES |
| `ultima_verificacion` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |
| `producto_id` | bigint | YES |

Restricciones:

- `stocks_cantidad_actual_not_null`: `NOT NULL cantidad_actual`.
- `stocks_cantidad_ideal_not_null`: `NOT NULL cantidad_ideal`.
- `stocks_id_not_null`: `NOT NULL id`.
- `stocks_lote_id_foreign`: `FOREIGN KEY (lote_id) REFERENCES inv_lotes(id) ON DELETE SET NULL`.
- `stocks_pkey`: `PRIMARY KEY (id)`.
- `stocks_producto_id_foreign`: `FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE`.
- `stocks_producto_variante_id_foreign`: `FOREIGN KEY (producto_variante_id) REFERENCES producto_variantes(id)`.
- `stocks_stockable_id_not_null`: `NOT NULL stockable_id`.
- `stocks_stockable_type_not_null`: `NOT NULL stockable_type`.

## tarjeta_cliente

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `cliente_id` | bigint | NO |
| `stripe_payment_method_id` | character varying | NO |
| `stripe_customer_id` | character varying | NO |
| `ultimo_digitos` | character varying | NO |
| `marca` | character varying | NO |
| `exp_month` | smallint | NO |
| `exp_year` | smallint | NO |
| `es_predeterminada` | boolean | NO |
| `eliminado_at` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tarjeta_cliente_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE`.
- `tarjeta_cliente_cliente_id_not_null`: `NOT NULL cliente_id`.
- `tarjeta_cliente_es_predeterminada_not_null`: `NOT NULL es_predeterminada`.
- `tarjeta_cliente_exp_month_not_null`: `NOT NULL exp_month`.
- `tarjeta_cliente_exp_year_not_null`: `NOT NULL exp_year`.
- `tarjeta_cliente_id_not_null`: `NOT NULL id`.
- `tarjeta_cliente_marca_not_null`: `NOT NULL marca`.
- `tarjeta_cliente_pkey`: `PRIMARY KEY (id)`.
- `tarjeta_cliente_stripe_customer_id_not_null`: `NOT NULL stripe_customer_id`.
- `tarjeta_cliente_stripe_payment_method_id_not_null`: `NOT NULL stripe_payment_method_id`.
- `tarjeta_cliente_stripe_payment_method_id_unique`: `UNIQUE (stripe_payment_method_id)`.
- `tarjeta_cliente_ultimo_digitos_not_null`: `NOT NULL ultimo_digitos`.

## tasas_cambio

Registros: 1.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `fecha` | date | NO |
| `moneda_origen_id` | bigint | NO |
| `moneda_destino_id` | bigint | NO |
| `tasa` | numeric | NO |
| `es_fija` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `tasas_cambio_es_fija_not_null`: `NOT NULL es_fija`.
- `tasas_cambio_fecha_not_null`: `NOT NULL fecha`.
- `tasas_cambio_id_not_null`: `NOT NULL id`.
- `tasas_cambio_moneda_destino_id_foreign`: `FOREIGN KEY (moneda_destino_id) REFERENCES monedas(id) ON DELETE CASCADE`.
- `tasas_cambio_moneda_destino_id_not_null`: `NOT NULL moneda_destino_id`.
- `tasas_cambio_moneda_origen_id_foreign`: `FOREIGN KEY (moneda_origen_id) REFERENCES monedas(id) ON DELETE CASCADE`.
- `tasas_cambio_moneda_origen_id_not_null`: `NOT NULL moneda_origen_id`.
- `tasas_cambio_pkey`: `PRIMARY KEY (id)`.
- `tasas_cambio_tasa_not_null`: `NOT NULL tasa`.
- `uq_tasa_fecha_monedas_ids`: `UNIQUE (fecha, moneda_origen_id, moneda_destino_id)`.

## tes_abastecimientos

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `sesion_id` | bigint | NO |
| `fondo_id` | bigint | NO |
| `solicitante_id` | bigint | NO |
| `aprobado_por` | bigint | YES |
| `importe` | numeric | NO |
| `referencia` | character varying | NO |
| `estado` | character varying | NO |
| `decision` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_abastecimientos_aprobado_por_foreign`: `FOREIGN KEY (aprobado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_abastecimientos_estado_not_null`: `NOT NULL estado`.
- `tes_abastecimientos_fondo_id_foreign`: `FOREIGN KEY (fondo_id) REFERENCES tes_fondos(id) ON DELETE RESTRICT`.
- `tes_abastecimientos_fondo_id_not_null`: `NOT NULL fondo_id`.
- `tes_abastecimientos_id_not_null`: `NOT NULL id`.
- `tes_abastecimientos_importe_not_null`: `NOT NULL importe`.
- `tes_abastecimientos_pkey`: `PRIMARY KEY (id)`.
- `tes_abastecimientos_referencia_not_null`: `NOT NULL referencia`.
- `tes_abastecimientos_sesion_id_foreign`: `FOREIGN KEY (sesion_id) REFERENCES tes_sesiones_caja(id) ON DELETE RESTRICT`.
- `tes_abastecimientos_sesion_id_not_null`: `NOT NULL sesion_id`.
- `tes_abastecimientos_solicitante_id_foreign`: `FOREIGN KEY (solicitante_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_abastecimientos_solicitante_id_not_null`: `NOT NULL solicitante_id`.

## tes_caja_monedas

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `caja_id` | bigint | NO |
| `moneda_id` | bigint | NO |

Restricciones:

- `tes_caja_monedas_caja_id_foreign`: `FOREIGN KEY (caja_id) REFERENCES tes_cajas(id) ON DELETE RESTRICT`.
- `tes_caja_monedas_caja_id_moneda_id_unique`: `UNIQUE (caja_id, moneda_id)`.
- `tes_caja_monedas_caja_id_not_null`: `NOT NULL caja_id`.
- `tes_caja_monedas_id_not_null`: `NOT NULL id`.
- `tes_caja_monedas_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `tes_caja_monedas_moneda_id_not_null`: `NOT NULL moneda_id`.
- `tes_caja_monedas_pkey`: `PRIMARY KEY (id)`.

## tes_cajas

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `ubicacion` | character varying | NO |
| `activa` | boolean | NO |
| `creada_por` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_cajas_activa_not_null`: `NOT NULL activa`.
- `tes_cajas_codigo_not_null`: `NOT NULL codigo`.
- `tes_cajas_codigo_unique`: `UNIQUE (codigo)`.
- `tes_cajas_creada_por_foreign`: `FOREIGN KEY (creada_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_cajas_creada_por_not_null`: `NOT NULL creada_por`.
- `tes_cajas_id_not_null`: `NOT NULL id`.
- `tes_cajas_nombre_not_null`: `NOT NULL nombre`.
- `tes_cajas_pkey`: `PRIMARY KEY (id)`.
- `tes_cajas_ubicacion_not_null`: `NOT NULL ubicacion`.

## tes_comprobantes

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | uuid | NO |
| `cuenta_id` | bigint | NO |
| `fondo_id` | bigint | NO |
| `usuario_id` | bigint | NO |
| `importe` | numeric | NO |
| `fecha` | date | NO |
| `referencia` | character varying | NO |
| `estado` | character varying | NO |
| `decision` | text | YES |
| `revisado_por` | bigint | YES |
| `pago_cuenta_id` | bigint | YES |
| `extracto_id` | bigint | YES |
| `revisado_at` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_comprobantes_check`: `CHECK (((importe > (0)::numeric) AND ((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'aprobado'::character varying, 'rechazado'::character varying, 'aclaracion'::character varying])::text[]))))`.
- `tes_comprobantes_codigo_not_null`: `NOT NULL codigo`.
- `tes_comprobantes_codigo_unique`: `UNIQUE (codigo)`.
- `tes_comprobantes_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `tes_comprobantes_cuenta_id_not_null`: `NOT NULL cuenta_id`.
- `tes_comprobantes_estado_not_null`: `NOT NULL estado`.
- `tes_comprobantes_extracto_id_foreign`: `FOREIGN KEY (extracto_id) REFERENCES tes_extractos(id) ON DELETE RESTRICT`.
- `tes_comprobantes_extracto_id_unique`: `UNIQUE (extracto_id)`.
- `tes_comprobantes_fecha_not_null`: `NOT NULL fecha`.
- `tes_comprobantes_fondo_id_foreign`: `FOREIGN KEY (fondo_id) REFERENCES tes_fondos(id) ON DELETE RESTRICT`.
- `tes_comprobantes_fondo_id_not_null`: `NOT NULL fondo_id`.
- `tes_comprobantes_fondo_id_referencia_unique`: `UNIQUE (fondo_id, referencia)`.
- `tes_comprobantes_id_not_null`: `NOT NULL id`.
- `tes_comprobantes_importe_not_null`: `NOT NULL importe`.
- `tes_comprobantes_pago_cuenta_id_foreign`: `FOREIGN KEY (pago_cuenta_id) REFERENCES pagos_cuenta(id) ON DELETE RESTRICT`.
- `tes_comprobantes_pago_cuenta_id_unique`: `UNIQUE (pago_cuenta_id)`.
- `tes_comprobantes_pkey`: `PRIMARY KEY (id)`.
- `tes_comprobantes_referencia_not_null`: `NOT NULL referencia`.
- `tes_comprobantes_revisado_por_foreign`: `FOREIGN KEY (revisado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_comprobantes_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_comprobantes_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_conciliaciones

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `extracto_id` | bigint | NO |
| `movimiento_id` | bigint | NO |
| `pago_transaccion_id` | bigint | YES |
| `comision` | numeric | NO |
| `usuario_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_conciliaciones_comision_not_null`: `NOT NULL comision`.
- `tes_conciliaciones_extracto_id_foreign`: `FOREIGN KEY (extracto_id) REFERENCES tes_extractos(id) ON DELETE RESTRICT`.
- `tes_conciliaciones_extracto_id_not_null`: `NOT NULL extracto_id`.
- `tes_conciliaciones_extracto_id_unique`: `UNIQUE (extracto_id)`.
- `tes_conciliaciones_id_not_null`: `NOT NULL id`.
- `tes_conciliaciones_movimiento_id_foreign`: `FOREIGN KEY (movimiento_id) REFERENCES tes_movimientos_fondo(id) ON DELETE RESTRICT`.
- `tes_conciliaciones_movimiento_id_not_null`: `NOT NULL movimiento_id`.
- `tes_conciliaciones_movimiento_id_unique`: `UNIQUE (movimiento_id)`.
- `tes_conciliaciones_pago_transaccion_id_foreign`: `FOREIGN KEY (pago_transaccion_id) REFERENCES pago_transacciones(id) ON DELETE RESTRICT`.
- `tes_conciliaciones_pago_transaccion_id_unique`: `UNIQUE (pago_transaccion_id)`.
- `tes_conciliaciones_pkey`: `PRIMARY KEY (id)`.
- `tes_conciliaciones_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_conciliaciones_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_decisiones_comprobante

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `comprobante_id` | bigint | NO |
| `usuario_id` | bigint | NO |
| `decision` | character varying | NO |
| `motivo` | text | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_decisiones_comprobante_comprobante_id_foreign`: `FOREIGN KEY (comprobante_id) REFERENCES tes_comprobantes(id) ON DELETE RESTRICT`.
- `tes_decisiones_comprobante_comprobante_id_not_null`: `NOT NULL comprobante_id`.
- `tes_decisiones_comprobante_decision_not_null`: `NOT NULL decision`.
- `tes_decisiones_comprobante_id_not_null`: `NOT NULL id`.
- `tes_decisiones_comprobante_motivo_not_null`: `NOT NULL motivo`.
- `tes_decisiones_comprobante_pkey`: `PRIMARY KEY (id)`.
- `tes_decisiones_comprobante_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_decisiones_comprobante_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_devoluciones

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `pago_cuenta_id` | bigint | NO |
| `sesion_id` | bigint | NO |
| `solicitante_id` | bigint | NO |
| `aprobado_por` | bigint | YES |
| `importe` | numeric | NO |
| `motivo` | character varying | NO |
| `estado` | character varying | NO |
| `decision` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_devoluciones_aprobado_por_foreign`: `FOREIGN KEY (aprobado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_devoluciones_check`: `CHECK (((importe > (0)::numeric) AND ((estado)::text = ANY ((ARRAY['solicitada'::character varying, 'aprobada'::character varying, 'rechazada'::character varying])::text[]))))`.
- `tes_devoluciones_estado_not_null`: `NOT NULL estado`.
- `tes_devoluciones_id_not_null`: `NOT NULL id`.
- `tes_devoluciones_importe_not_null`: `NOT NULL importe`.
- `tes_devoluciones_motivo_not_null`: `NOT NULL motivo`.
- `tes_devoluciones_pago_cuenta_id_foreign`: `FOREIGN KEY (pago_cuenta_id) REFERENCES pagos_cuenta(id) ON DELETE RESTRICT`.
- `tes_devoluciones_pago_cuenta_id_not_null`: `NOT NULL pago_cuenta_id`.
- `tes_devoluciones_pkey`: `PRIMARY KEY (id)`.
- `tes_devoluciones_sesion_id_foreign`: `FOREIGN KEY (sesion_id) REFERENCES tes_sesiones_caja(id) ON DELETE RESTRICT`.
- `tes_devoluciones_sesion_id_not_null`: `NOT NULL sesion_id`.
- `tes_devoluciones_solicitante_id_foreign`: `FOREIGN KEY (solicitante_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_devoluciones_solicitante_id_not_null`: `NOT NULL solicitante_id`.

## tes_entregas

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `sesion_id` | bigint | NO |
| `fondo_id` | bigint | NO |
| `entregado_por` | bigint | NO |
| `recibido_por` | bigint | YES |
| `importe` | numeric | NO |
| `referencia` | character varying | NO |
| `estado` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_entregas_check`: `CHECK (((importe > (0)::numeric) AND ((estado)::text = ANY ((ARRAY['en_transito'::character varying, 'recibida'::character varying])::text[]))))`.
- `tes_entregas_entregado_por_foreign`: `FOREIGN KEY (entregado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_entregas_entregado_por_not_null`: `NOT NULL entregado_por`.
- `tes_entregas_estado_not_null`: `NOT NULL estado`.
- `tes_entregas_fondo_id_foreign`: `FOREIGN KEY (fondo_id) REFERENCES tes_fondos(id) ON DELETE RESTRICT`.
- `tes_entregas_fondo_id_not_null`: `NOT NULL fondo_id`.
- `tes_entregas_id_not_null`: `NOT NULL id`.
- `tes_entregas_importe_not_null`: `NOT NULL importe`.
- `tes_entregas_pkey`: `PRIMARY KEY (id)`.
- `tes_entregas_recibido_por_foreign`: `FOREIGN KEY (recibido_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_entregas_referencia_not_null`: `NOT NULL referencia`.
- `tes_entregas_sesion_id_foreign`: `FOREIGN KEY (sesion_id) REFERENCES tes_sesiones_caja(id) ON DELETE RESTRICT`.
- `tes_entregas_sesion_id_not_null`: `NOT NULL sesion_id`.

## tes_evidencias

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `comprobante_id` | bigint | NO |
| `ruta` | character varying | NO |
| `sha256` | character varying | NO |
| `usuario_id` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_evidencias_comprobante_id_foreign`: `FOREIGN KEY (comprobante_id) REFERENCES tes_comprobantes(id) ON DELETE RESTRICT`.
- `tes_evidencias_comprobante_id_not_null`: `NOT NULL comprobante_id`.
- `tes_evidencias_id_not_null`: `NOT NULL id`.
- `tes_evidencias_pkey`: `PRIMARY KEY (id)`.
- `tes_evidencias_ruta_not_null`: `NOT NULL ruta`.
- `tes_evidencias_sha256_not_null`: `NOT NULL sha256`.
- `tes_evidencias_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_evidencias_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_extractos

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `fondo_id` | bigint | NO |
| `fecha` | date | NO |
| `referencia` | character varying | NO |
| `importe` | numeric | NO |
| `soporte` | character varying | NO |
| `sha256` | character varying | NO |
| `registrado_por` | bigint | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_extractos_fecha_not_null`: `NOT NULL fecha`.
- `tes_extractos_fondo_id_foreign`: `FOREIGN KEY (fondo_id) REFERENCES tes_fondos(id) ON DELETE RESTRICT`.
- `tes_extractos_fondo_id_not_null`: `NOT NULL fondo_id`.
- `tes_extractos_fondo_id_referencia_unique`: `UNIQUE (fondo_id, referencia)`.
- `tes_extractos_id_not_null`: `NOT NULL id`.
- `tes_extractos_importe_check`: `CHECK ((importe <> (0)::numeric))`.
- `tes_extractos_importe_not_null`: `NOT NULL importe`.
- `tes_extractos_pkey`: `PRIMARY KEY (id)`.
- `tes_extractos_referencia_not_null`: `NOT NULL referencia`.
- `tes_extractos_registrado_por_foreign`: `FOREIGN KEY (registrado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_extractos_registrado_por_not_null`: `NOT NULL registrado_por`.
- `tes_extractos_sha256_not_null`: `NOT NULL sha256`.
- `tes_extractos_soporte_not_null`: `NOT NULL soporte`.

## tes_fondos

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | character varying | NO |
| `nombre` | character varying | NO |
| `tipo` | character varying | NO |
| `moneda_id` | bigint | NO |
| `responsable_id` | bigint | NO |
| `banco` | character varying | YES |
| `identificacion` | character varying | YES |
| `limite_vale` | numeric | NO |
| `activo` | boolean | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_fondos_activo_not_null`: `NOT NULL activo`.
- `tes_fondos_codigo_not_null`: `NOT NULL codigo`.
- `tes_fondos_codigo_unique`: `UNIQUE (codigo)`.
- `tes_fondos_id_not_null`: `NOT NULL id`.
- `tes_fondos_limite_vale_not_null`: `NOT NULL limite_vale`.
- `tes_fondos_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `tes_fondos_moneda_id_not_null`: `NOT NULL moneda_id`.
- `tes_fondos_nombre_not_null`: `NOT NULL nombre`.
- `tes_fondos_pkey`: `PRIMARY KEY (id)`.
- `tes_fondos_responsable_id_foreign`: `FOREIGN KEY (responsable_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_fondos_responsable_id_not_null`: `NOT NULL responsable_id`.
- `tes_fondos_tipo_not_null`: `NOT NULL tipo`.
- `tes_tipo_fondo`: `CHECK ((((tipo)::text = ANY ((ARRAY['banco'::character varying, 'caja_chica'::character varying, 'boveda'::character varying])::text[])) AND (limite_vale >= (0)::numeric)))`.

## tes_movimientos_caja

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `clave_operacion` | uuid | NO |
| `sesion_id` | bigint | NO |
| `moneda_id` | bigint | NO |
| `usuario_id` | bigint | NO |
| `tipo` | character varying | NO |
| `importe` | numeric | NO |
| `concepto` | character varying | NO |
| `referencia` | character varying | YES |
| `pago_cuenta_id` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_movimiento_valido`: `CHECK (((importe > (0)::numeric) AND ((tipo)::text = ANY ((ARRAY['ingreso'::character varying, 'egreso'::character varying, 'cobro'::character varying, 'vuelto'::character varying])::text[]))))`.
- `tes_movimientos_caja_clave_operacion_not_null`: `NOT NULL clave_operacion`.
- `tes_movimientos_caja_clave_operacion_unique`: `UNIQUE (clave_operacion)`.
- `tes_movimientos_caja_concepto_not_null`: `NOT NULL concepto`.
- `tes_movimientos_caja_id_not_null`: `NOT NULL id`.
- `tes_movimientos_caja_importe_not_null`: `NOT NULL importe`.
- `tes_movimientos_caja_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `tes_movimientos_caja_moneda_id_not_null`: `NOT NULL moneda_id`.
- `tes_movimientos_caja_pago_cuenta_id_foreign`: `FOREIGN KEY (pago_cuenta_id) REFERENCES pagos_cuenta(id) ON DELETE RESTRICT`.
- `tes_movimientos_caja_pago_cuenta_id_tipo_unique`: `UNIQUE (pago_cuenta_id, tipo)`.
- `tes_movimientos_caja_pkey`: `PRIMARY KEY (id)`.
- `tes_movimientos_caja_sesion_id_foreign`: `FOREIGN KEY (sesion_id) REFERENCES tes_sesiones_caja(id) ON DELETE RESTRICT`.
- `tes_movimientos_caja_sesion_id_not_null`: `NOT NULL sesion_id`.
- `tes_movimientos_caja_tipo_not_null`: `NOT NULL tipo`.
- `tes_movimientos_caja_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_movimientos_caja_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_movimientos_fondo

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `fondo_id` | bigint | NO |
| `operacion_id` | bigint | NO |
| `usuario_id` | bigint | NO |
| `importe` | numeric | NO |
| `concepto` | character varying | NO |
| `referencia` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_movimientos_fondo_concepto_not_null`: `NOT NULL concepto`.
- `tes_movimientos_fondo_fondo_id_foreign`: `FOREIGN KEY (fondo_id) REFERENCES tes_fondos(id) ON DELETE RESTRICT`.
- `tes_movimientos_fondo_fondo_id_not_null`: `NOT NULL fondo_id`.
- `tes_movimientos_fondo_fondo_id_operacion_id_unique`: `UNIQUE (fondo_id, operacion_id)`.
- `tes_movimientos_fondo_id_not_null`: `NOT NULL id`.
- `tes_movimientos_fondo_importe_check`: `CHECK ((importe <> (0)::numeric))`.
- `tes_movimientos_fondo_importe_not_null`: `NOT NULL importe`.
- `tes_movimientos_fondo_operacion_id_foreign`: `FOREIGN KEY (operacion_id) REFERENCES tes_operaciones(id) ON DELETE RESTRICT`.
- `tes_movimientos_fondo_operacion_id_not_null`: `NOT NULL operacion_id`.
- `tes_movimientos_fondo_pkey`: `PRIMARY KEY (id)`.
- `tes_movimientos_fondo_referencia_not_null`: `NOT NULL referencia`.
- `tes_movimientos_fondo_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_movimientos_fondo_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_operaciones

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `clave` | uuid | NO |
| `huella` | character varying | NO |
| `tipo` | character varying | NO |
| `usuario_id` | bigint | NO |
| `resultado` | json | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_operaciones_clave_not_null`: `NOT NULL clave`.
- `tes_operaciones_clave_unique`: `UNIQUE (clave)`.
- `tes_operaciones_huella_not_null`: `NOT NULL huella`.
- `tes_operaciones_id_not_null`: `NOT NULL id`.
- `tes_operaciones_pkey`: `PRIMARY KEY (id)`.
- `tes_operaciones_tipo_not_null`: `NOT NULL tipo`.
- `tes_operaciones_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_operaciones_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_operaciones_cobro

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `clave` | uuid | NO |
| `cuenta_id` | bigint | NO |
| `usuario_id` | bigint | NO |
| `pago_cuenta_id` | bigint | NO |
| `venta_id` | bigint | YES |
| `huella_peticion` | character varying | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_operaciones_cobro_clave_not_null`: `NOT NULL clave`.
- `tes_operaciones_cobro_clave_unique`: `UNIQUE (clave)`.
- `tes_operaciones_cobro_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE RESTRICT`.
- `tes_operaciones_cobro_cuenta_id_not_null`: `NOT NULL cuenta_id`.
- `tes_operaciones_cobro_huella_peticion_not_null`: `NOT NULL huella_peticion`.
- `tes_operaciones_cobro_id_not_null`: `NOT NULL id`.
- `tes_operaciones_cobro_pago_cuenta_id_foreign`: `FOREIGN KEY (pago_cuenta_id) REFERENCES pagos_cuenta(id) ON DELETE RESTRICT`.
- `tes_operaciones_cobro_pago_cuenta_id_not_null`: `NOT NULL pago_cuenta_id`.
- `tes_operaciones_cobro_pago_cuenta_id_unique`: `UNIQUE (pago_cuenta_id)`.
- `tes_operaciones_cobro_pkey`: `PRIMARY KEY (id)`.
- `tes_operaciones_cobro_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_operaciones_cobro_usuario_id_not_null`: `NOT NULL usuario_id`.
- `tes_operaciones_cobro_venta_id_foreign`: `FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT`.

## tes_saldos_sesion

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `sesion_id` | bigint | NO |
| `moneda_id` | bigint | NO |
| `apertura` | numeric | NO |
| `esperado_cierre` | numeric | YES |
| `contado` | numeric | YES |
| `diferencia` | numeric | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_apertura_valida`: `CHECK (((apertura >= (0)::numeric) AND ((contado IS NULL) OR (contado >= (0)::numeric))))`.
- `tes_saldos_sesion_apertura_not_null`: `NOT NULL apertura`.
- `tes_saldos_sesion_id_not_null`: `NOT NULL id`.
- `tes_saldos_sesion_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `tes_saldos_sesion_moneda_id_not_null`: `NOT NULL moneda_id`.
- `tes_saldos_sesion_pkey`: `PRIMARY KEY (id)`.
- `tes_saldos_sesion_sesion_id_foreign`: `FOREIGN KEY (sesion_id) REFERENCES tes_sesiones_caja(id) ON DELETE RESTRICT`.
- `tes_saldos_sesion_sesion_id_moneda_id_unique`: `UNIQUE (sesion_id, moneda_id)`.
- `tes_saldos_sesion_sesion_id_not_null`: `NOT NULL sesion_id`.

## tes_sesiones_caja

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `codigo` | uuid | NO |
| `caja_id` | bigint | NO |
| `usuario_id` | bigint | NO |
| `estado` | character varying | NO |
| `origen_fondo` | text | NO |
| `abierta_at` | timestamp without time zone | NO |
| `contada_at` | timestamp without time zone | YES |
| `cerrada_at` | timestamp without time zone | YES |
| `cerrada_por` | bigint | YES |
| `motivo_diferencia` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_estado_sesion_valido`: `CHECK (((estado)::text = ANY ((ARRAY['abierta'::character varying, 'pendiente_revision'::character varying, 'cerrada'::character varying])::text[])))`.
- `tes_sesiones_caja_abierta_at_not_null`: `NOT NULL abierta_at`.
- `tes_sesiones_caja_caja_id_foreign`: `FOREIGN KEY (caja_id) REFERENCES tes_cajas(id) ON DELETE RESTRICT`.
- `tes_sesiones_caja_caja_id_not_null`: `NOT NULL caja_id`.
- `tes_sesiones_caja_cerrada_por_foreign`: `FOREIGN KEY (cerrada_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_sesiones_caja_codigo_not_null`: `NOT NULL codigo`.
- `tes_sesiones_caja_codigo_unique`: `UNIQUE (codigo)`.
- `tes_sesiones_caja_estado_not_null`: `NOT NULL estado`.
- `tes_sesiones_caja_id_not_null`: `NOT NULL id`.
- `tes_sesiones_caja_origen_fondo_not_null`: `NOT NULL origen_fondo`.
- `tes_sesiones_caja_pkey`: `PRIMARY KEY (id)`.
- `tes_sesiones_caja_usuario_id_foreign`: `FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_sesiones_caja_usuario_id_not_null`: `NOT NULL usuario_id`.

## tes_vales

Registros: 0.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `fondo_id` | bigint | NO |
| `solicitante_id` | bigint | NO |
| `beneficiario` | character varying | NO |
| `concepto` | character varying | NO |
| `importe` | numeric | NO |
| `vence_at` | date | NO |
| `estado` | character varying | NO |
| `aprobado_por` | bigint | YES |
| `liquidado_por` | bigint | YES |
| `gastado` | numeric | YES |
| `devuelto` | numeric | YES |
| `referencia` | character varying | YES |
| `soporte` | character varying | YES |
| `resolucion` | text | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `tes_vales_aprobado_por_foreign`: `FOREIGN KEY (aprobado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_vales_beneficiario_not_null`: `NOT NULL beneficiario`.
- `tes_vales_check`: `CHECK (((importe > (0)::numeric) AND ((gastado IS NULL) OR (gastado >= (0)::numeric)) AND ((devuelto IS NULL) OR (devuelto >= (0)::numeric)) AND ((estado)::text = ANY ((ARRAY['solicitado'::character varying, 'entregado'::character varying, 'liquidado'::character varying, 'rechazado'::character varying])::text[]))))`.
- `tes_vales_concepto_not_null`: `NOT NULL concepto`.
- `tes_vales_estado_not_null`: `NOT NULL estado`.
- `tes_vales_fondo_id_foreign`: `FOREIGN KEY (fondo_id) REFERENCES tes_fondos(id) ON DELETE RESTRICT`.
- `tes_vales_fondo_id_not_null`: `NOT NULL fondo_id`.
- `tes_vales_id_not_null`: `NOT NULL id`.
- `tes_vales_importe_not_null`: `NOT NULL importe`.
- `tes_vales_liquidado_por_foreign`: `FOREIGN KEY (liquidado_por) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_vales_pkey`: `PRIMARY KEY (id)`.
- `tes_vales_solicitante_id_foreign`: `FOREIGN KEY (solicitante_id) REFERENCES users(id) ON DELETE RESTRICT`.
- `tes_vales_solicitante_id_not_null`: `NOT NULL solicitante_id`.
- `tes_vales_vence_at_not_null`: `NOT NULL vence_at`.

## ubicaciones

Registros: 122.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `padre_id` | bigint | YES |
| `tipo` | character varying | NO |
| `nombre` | character varying | NO |
| `descripcion` | text | YES |
| `orden` | integer | NO |
| `estado` | integer | NO |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `ubicaciones_estado_not_null`: `NOT NULL estado`.
- `ubicaciones_id_not_null`: `NOT NULL id`.
- `ubicaciones_nombre_not_null`: `NOT NULL nombre`.
- `ubicaciones_orden_not_null`: `NOT NULL orden`.
- `ubicaciones_padre_id_foreign`: `FOREIGN KEY (padre_id) REFERENCES ubicaciones(id) ON UPDATE CASCADE ON DELETE SET NULL`.
- `ubicaciones_padre_id_orden_unique`: `UNIQUE (padre_id, orden)`.
- `ubicaciones_pkey`: `PRIMARY KEY (id)`.
- `ubicaciones_tipo_not_null`: `NOT NULL tipo`.

## users

Registros: 30.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `persona_id` | bigint | YES |
| `name` | character varying | NO |
| `email` | character varying | YES |
| `email_verified_at` | timestamp without time zone | YES |
| `password` | character varying | NO |
| `is_admin` | boolean | NO |
| `password_change_required` | boolean | NO |
| `remember_token` | character varying | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `deleted_at` | timestamp without time zone | YES |

Restricciones:

- `users_email_unique`: `UNIQUE (email)`.
- `users_id_not_null`: `NOT NULL id`.
- `users_is_admin_not_null`: `NOT NULL is_admin`.
- `users_name_not_null`: `NOT NULL name`.
- `users_password_change_required_not_null`: `NOT NULL password_change_required`.
- `users_password_not_null`: `NOT NULL password`.
- `users_persona_id_foreign`: `FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE SET NULL`.
- `users_pkey`: `PRIMARY KEY (id)`.

## venta_detalles

Registros: 22045.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `venta_id` | bigint | NO |
| `concepto` | character varying | NO |
| `cantidad` | numeric | NO |
| `precio_unitario` | numeric | NO |
| `subtotal` | numeric | NO |
| `descuento` | numeric | NO |
| `impuesto` | numeric | NO |
| `servicio` | numeric | NO |
| `propina` | numeric | NO |
| `recargo` | numeric | NO |
| `total_linea` | numeric | NO |
| `origen_type` | character varying | YES |
| `origen_id` | bigint | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |
| `moneda_id` | bigint | NO |

Restricciones:

- `chk_vd_cantidad`: `CHECK ((cantidad > (0)::numeric))`.
- `chk_vd_subtotal`: `CHECK ((subtotal >= (0)::numeric))`.
- `chk_vd_total`: `CHECK ((total_linea >= (0)::numeric))`.
- `fk_venta_detalles_moneda`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE RESTRICT`.
- `venta_detalles_cantidad_not_null`: `NOT NULL cantidad`.
- `venta_detalles_concepto_not_null`: `NOT NULL concepto`.
- `venta_detalles_descuento_not_null`: `NOT NULL descuento`.
- `venta_detalles_id_not_null`: `NOT NULL id`.
- `venta_detalles_impuesto_not_null`: `NOT NULL impuesto`.
- `venta_detalles_moneda_id_not_null`: `NOT NULL moneda_id`.
- `venta_detalles_pkey`: `PRIMARY KEY (id)`.
- `venta_detalles_precio_unitario_not_null`: `NOT NULL precio_unitario`.
- `venta_detalles_propina_not_null`: `NOT NULL propina`.
- `venta_detalles_recargo_not_null`: `NOT NULL recargo`.
- `venta_detalles_servicio_not_null`: `NOT NULL servicio`.
- `venta_detalles_subtotal_not_null`: `NOT NULL subtotal`.
- `venta_detalles_total_linea_not_null`: `NOT NULL total_linea`.
- `venta_detalles_venta_id_foreign`: `FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT`.
- `venta_detalles_venta_id_not_null`: `NOT NULL venta_id`.

## ventas

Registros: 9735.

| Columna | Tipo | Acepta NULL |
|---|---|---|
| `id` | bigint | NO |
| `numero_venta` | character varying | NO |
| `cuenta_id` | bigint | YES |
| `cliente_id` | bigint | YES |
| `moneda_id` | bigint | YES |
| `subtotal` | numeric | NO |
| `descuento_total` | numeric | NO |
| `impuesto_total` | numeric | NO |
| `servicio_total` | numeric | NO |
| `propina_total` | numeric | NO |
| `recargo_total` | numeric | NO |
| `total` | numeric | NO |
| `estado` | smallint | NO |
| `datos_fiscales` | jsonb | YES |
| `creada_por` | bigint | YES |
| `anulada_por` | bigint | YES |
| `anulada_en` | timestamp without time zone | YES |
| `created_at` | timestamp without time zone | YES |
| `updated_at` | timestamp without time zone | YES |

Restricciones:

- `chk_ventas_descuento`: `CHECK ((descuento_total >= (0)::numeric))`.
- `chk_ventas_estado`: `CHECK ((estado = ANY (ARRAY[1, 2, 3])))`.
- `chk_ventas_impuesto`: `CHECK ((impuesto_total >= (0)::numeric))`.
- `chk_ventas_propina`: `CHECK ((propina_total >= (0)::numeric))`.
- `chk_ventas_recargo`: `CHECK ((recargo_total >= (0)::numeric))`.
- `chk_ventas_servicio`: `CHECK ((servicio_total >= (0)::numeric))`.
- `chk_ventas_subtotal`: `CHECK ((subtotal >= (0)::numeric))`.
- `chk_ventas_total`: `CHECK ((total >= (0)::numeric))`.
- `ventas_anulada_por_foreign`: `FOREIGN KEY (anulada_por) REFERENCES users(id) ON DELETE SET NULL`.
- `ventas_cliente_id_foreign`: `FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL`.
- `ventas_creada_por_foreign`: `FOREIGN KEY (creada_por) REFERENCES users(id) ON DELETE SET NULL`.
- `ventas_cuenta_id_foreign`: `FOREIGN KEY (cuenta_id) REFERENCES cuentas(id) ON DELETE SET NULL`.
- `ventas_descuento_total_not_null`: `NOT NULL descuento_total`.
- `ventas_estado_not_null`: `NOT NULL estado`.
- `ventas_id_cuenta_unique`: `UNIQUE (id, cuenta_id)`.
- `ventas_id_not_null`: `NOT NULL id`.
- `ventas_impuesto_total_not_null`: `NOT NULL impuesto_total`.
- `ventas_moneda_id_foreign`: `FOREIGN KEY (moneda_id) REFERENCES monedas(id) ON DELETE SET NULL`.
- `ventas_numero_venta_not_null`: `NOT NULL numero_venta`.
- `ventas_numero_venta_unique`: `UNIQUE (numero_venta)`.
- `ventas_pkey`: `PRIMARY KEY (id)`.
- `ventas_propina_total_not_null`: `NOT NULL propina_total`.
- `ventas_recargo_total_not_null`: `NOT NULL recargo_total`.
- `ventas_servicio_total_not_null`: `NOT NULL servicio_total`.
- `ventas_subtotal_not_null`: `NOT NULL subtotal`.
- `ventas_total_not_null`: `NOT NULL total`.
