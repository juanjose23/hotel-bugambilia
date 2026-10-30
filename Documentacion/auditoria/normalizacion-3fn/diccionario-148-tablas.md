# Diccionario del esquema observado

Extracción: 2026-10-30T01:57:45+00:00. Base local hotel_bugambilias. Lectura: on.

Los recuentos incluyen filas borradas lógicamente. Este diccionario describe el estado observado, no declara 3FN. Los valores de registros no se exportaron.

## act_plan_activos
Módulo: Productos e inventario. Filas: 311.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| plan_id | int8 | No | PK; FK inv_planes_mantenimiento(id) |
| activo_id | int8 | No | FK inv_activos(id); PK |

## auditoria_jobs
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| usuario_id | int8 | Sí | FK users(id) |
| tipo_job | varchar | No | — |
| nombre_job | varchar | No | — |
| tipo_ejecucion | varchar | No | — |
| estado | varchar | No | — |
| mensaje | text | Sí | — |
| ejecutado_en | timestamp | Sí | — |
| completado_en | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## auditoria_reportes
Módulo: Infraestructura y acceso. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| usuario_id | int8 | Sí | FK users(id) |
| tipo_reporte | varchar | No | — |
| parametros | json | Sí | — |
| ruta_archivo | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## audits
Módulo: Infraestructura y acceso. Filas: 94.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| user_type | varchar | Sí | — |
| user_id | int8 | Sí | — |
| event | varchar | No | — |
| auditable_type | varchar | No | — |
| auditable_id | int8 | No | — |
| old_values | text | Sí | — |
| new_values | text | Sí | — |
| url | text | Sí | — |
| ip_address | inet | Sí | — |
| user_agent | varchar | Sí | — |
| tags | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## cache
Módulo: Infraestructura y acceso. Filas: 17.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| key | varchar | No | PK |
| value | text | No | — |
| expiration | int8 | No | — |

## cache_locks
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| key | varchar | No | PK |
| owner | varchar | No | — |
| expiration | int8 | No | — |

## cargos_facturacion
Módulo: Cuentas, facturación y tesorería. Filas: 3.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| tipo | int2 | No | — |
| modo_calculo | int2 | No | — |
| valor | numeric(12,4) | No | — |
| base_calculo | int2 | No | — |
| orden | int2 | No | — |
| obligatorio | bool | No | — |
| permite_modificacion | bool | No | — |
| areas | jsonb | Sí | — |
| fecha_inicio | date | Sí | — |
| fecha_fin | date | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## catalogo_tipos
Módulo: Catálogos y configuración. Filas: 15.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## catalogos
Módulo: Catálogos y configuración. Filas: 128.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| catalogo_tipo_id | int8 | No | FK catalogo_tipos(id); UNIQUE (catalogo_tipo_id, codigo) |
| padre_id | int8 | Sí | FK catalogos(id) |
| codigo | varchar | No | UNIQUE (catalogo_tipo_id, codigo) |
| nombre | varchar | No | — |
| prefijo | varchar | Sí | — |
| descripcion | text | Sí | — |
| orden | int4 | No | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## clientes
Módulo: Personas y terceros. Filas: 151.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| persona_id | int8 | No | FK personas(id); UNIQUE (persona_id) |
| catalogo_id | int8 | No | FK catalogos(id) |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| stripe_customer_id | varchar | Sí | UNIQUE (stripe_customer_id) |

## colaborador_cargos_historial
Módulo: Personas y terceros. Filas: 22.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| colaborador_id | int8 | No | FK colaboradores(id) |
| cargo_id | int8 | No | FK catalogos(id) |
| departamento_id | int8 | Sí | FK catalogos(id) |
| fecha_inicio | date | No | — |
| fecha_fin | date | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## colaborador_contactos_emergencia
Módulo: Personas y terceros. Filas: 16.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| colaborador_id | int8 | No | FK colaboradores(id) |
| nombre | varchar | No | — |
| telefono | varchar | No | — |
| parentesco | varchar | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## colaborador_datos_medicos
Módulo: Personas y terceros. Filas: 15.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| colaborador_id | int8 | No | FK colaboradores(id); UNIQUE (colaborador_id) |
| tipo_sangre | varchar | Sí | — |
| alergias | text | Sí | — |
| enfermedades_cronicas | text | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## colaborador_documentos
Módulo: Personas y terceros. Filas: 30.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| colaborador_id | int8 | No | FK colaboradores(id) |
| tipo | varchar | No | — |
| archivo | varchar | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## colaborador_salarios
Módulo: Personas y terceros. Filas: 22.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| colaborador_id | int8 | No | FK colaboradores(id) |
| salario | numeric(12,2) | No | — |
| fecha_inicio | date | No | — |
| fecha_fin | date | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

Índice especial `unique_active_salary_per_colaborador`:
```sql
CREATE UNIQUE INDEX unique_active_salary_per_colaborador ON public.colaborador_salarios USING btree (colaborador_id) WHERE (estado = 1)
```

## colaboradores
Módulo: Personas y terceros. Filas: 15.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| persona_id | int8 | No | FK personas(id) |
| codigo | varchar | No | UNIQUE (codigo) |
| nss | varchar | Sí | — |
| fecha_ingreso | date | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## compra_historial
Módulo: Compras y recepciones. Filas: 36,039.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| model_type | varchar | No | — |
| model_id | int8 | No | — |
| estado_anterior | varchar | Sí | — |
| estado_nuevo | varchar | No | — |
| usuario_id | int8 | Sí | FK users(id) |
| comentario | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## conflictos_identidad
Módulo: Catálogos y configuración. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| persona_id | int8 | Sí | FK personas(id) |
| tipo_conflicto | varchar | No | — |
| datos_providos | json | No | — |
| datos_existentes | json | No | — |
| estado | varchar | No | — |
| creado_por | int8 | Sí | FK users(id) |
| resuelto_por | int8 | Sí | FK users(id) |
| resuelto_en | timestamp | Sí | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## cotizacion_items
Módulo: Compras y recepciones. Filas: 59,925.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| cotizacion_id | int8 | No | FK cotizaciones(id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| cantidad | numeric(12,2) | No | — |
| precio_unitario | numeric(12,2) | No | — |
| subtotal | numeric(12,2) | No | — |
| es_elegido | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## cotizaciones
Módulo: Compras y recepciones. Filas: 15,325.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| solicitud_id | int8 | No | FK solicitudes_compra(id) |
| proveedor_id | int8 | No | FK proveedores(id) |
| fecha_cotizacion | date | No | — |
| fecha_vencimiento | date | Sí | — |
| dias_entrega | int4 | No | — |
| condicion_pago_id | int8 | Sí | FK catalogos(id) |
| subtotal | numeric(15,2) | No | — |
| impuestos | numeric(15,2) | No | — |
| costo_envio | numeric(15,2) | No | — |
| descuento | numeric(15,2) | No | — |
| total | numeric(15,2) | No | — |
| moneda_id | int8 | No | FK monedas(id) |
| tasa_cambio | numeric(10,4) | No | — |
| archivo_pdf | varchar | Sí | — |
| es_elegida | bool | No | — |
| estado | int4 | No | — |
| observaciones | text | Sí | — |
| creada_por | int8 | Sí | FK users(id) |
| elegida_por | int8 | Sí | FK users(id) |
| elegida_en | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

Índice especial `uq_cotizacion_elegida_por_solicitud`:
```sql
CREATE UNIQUE INDEX uq_cotizacion_elegida_por_solicitud ON public.cotizaciones USING btree (solicitud_id) WHERE ((es_elegida = true) AND (deleted_at IS NULL))
```

## cuenta_cargos
Módulo: Cuentas, facturación y tesorería. Filas: 3.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| cuenta_id | int8 | No | FK cuentas(id) |
| cargo_id | int8 | Sí | FK cargos_facturacion(id) |
| tipo | int2 | No | — |
| codigo | varchar | No | — |
| nombre | varchar | No | — |
| modo_calculo | int2 | No | — |
| valor | numeric(12,4) | No | — |
| base_calculo | int2 | No | — |
| base_monto | numeric(12,2) | No | — |
| monto | numeric(12,2) | No | — |
| origen_type | varchar | Sí | — |
| origen_id | int8 | Sí | — |
| aplicado_por | int8 | Sí | FK users(id) |
| estado | int2 | No | — |
| observaciones | text | Sí | — |
| anulado_por | int8 | Sí | FK users(id) |
| anulado_en | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| moneda_id | int8 | No | FK monedas(id) |

Índice especial `uq_cuenta_cargo_origen`:
```sql
CREATE UNIQUE INDEX uq_cuenta_cargo_origen ON public.cuenta_cargos USING btree (cuenta_id, cargo_id, origen_type, origen_id) WHERE ((estado = 1) AND (origen_type IS NOT NULL) AND (cargo_id IS NOT NULL))
```

## cuenta_detalles
Módulo: Cuentas, facturación y tesorería. Filas: 22,075.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| cuenta_id | int8 | No | FK cuentas(id) |
| origen_type | varchar | Sí | — |
| origen_id | int8 | Sí | — |
| tipo_detalle | int2 | Sí | — |
| espacio_id | int8 | Sí | FK espacios(id) |
| concepto | varchar | No | — |
| descripcion | text | Sí | — |
| cantidad | numeric(10,2) | No | — |
| precio_unitario | numeric(12,2) | No | — |
| subtotal | numeric(12,2) | No | — |
| total | numeric(12,2) | No | — |
| estado | int2 | No | — |
| metadatos | jsonb | Sí | — |
| creador_id | int8 | Sí | FK users(id) |
| anulado_por | int8 | Sí | FK users(id) |
| anulado_en | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| moneda_id | int8 | No | FK monedas(id) |

## cuentas
Módulo: Cuentas, facturación y tesorería. Filas: 9,771.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| numero_cuenta | varchar | No | UNIQUE (numero_cuenta) |
| tipo_cuenta | int2 | No | — |
| estado | int2 | No | — |
| cliente_id | int8 | Sí | FK clientes(id) |
| estancia_id | int8 | Sí | — |
| reserva_id | int8 | Sí | FK reservas(id) |
| moneda_id | int8 | Sí | FK monedas(id) |
| limite_autorizado | numeric(12,2) | Sí | — |
| subtotal | numeric(12,2) | No | — |
| descuento_total | numeric(12,2) | No | — |
| impuesto_total | numeric(12,2) | No | — |
| cargo_servicio_total | numeric(12,2) | No | — |
| propina_total | numeric(12,2) | No | — |
| recargo_total | numeric(12,2) | No | — |
| total | numeric(12,2) | No | — |
| total_pagado | numeric(12,2) | No | — |
| saldo | numeric(12,2) | No | — |
| abierta_at | timestamp | No | — |
| cerrada_at | timestamp | Sí | — |
| abierta_por | int8 | Sí | FK users(id) |
| cerrada_por | int8 | Sí | FK users(id) |
| actualizado_por | int8 | Sí | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## detalles_habitacion
Módulo: Habitaciones, espacios y servicios. Filas: 122.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| habitacion_id | int8 | No | FK habitaciones(id); UNIQUE (habitacion_id) |
| capacidad_adultos | int4 | No | — |
| capacidad_ninos | int4 | No | — |
| medidas | numeric(7,2) | Sí | — |
| vistas | json | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## devolucion_items
Módulo: Compras y recepciones. Filas: 356.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| devolucion_id | int8 | No | FK devoluciones_compra(id) |
| lote_id | int8 | Sí | FK inv_lotes(id) |
| recepcion_item_id | int8 | Sí | FK recepcion_items(id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| cantidad_devolver | numeric(12,2) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## devoluciones_compra
Módulo: Compras y recepciones. Filas: 142.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| orden_compra_id | int8 | No | FK ordenes_compra(id) |
| recepcion_compra_id | int8 | Sí | FK recepciones_compra(id) |
| fecha_devolucion | date | No | — |
| estado | int4 | No | — |
| motivo | text | No | — |
| documento_externo | varchar | Sí | — |
| creado_por_id | int8 | No | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## driverjs_tour_completions
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| user_id | int8 | No | FK users(id); UNIQUE (user_id, tour_name) |
| tour_name | varchar | No | UNIQUE (user_id, tour_name) |
| completed_at | timestamp | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## espacios
Módulo: Habitaciones, espacios y servicios. Filas: 13.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| padre_id | int8 | Sí | FK espacios(id) |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| descripcion | text | Sí | — |
| tipo | varchar | No | — |
| capacidad_personas | int4 | No | — |
| ubicacion_id | int8 | Sí | FK ubicaciones(id) |
| estado | int2 | No | — |
| orden | int4 | No | — |
| meta_datos | json | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| web | bool | No | — |
| reservable | bool | No | — |
| reservable_id | int8 | Sí | FK recursos_reservables(id); UNIQUE (reservable_id) |
| slug | varchar | Sí | UNIQUE (slug) |
| ubicacion_cocina_id | int8 | Sí | FK ubicaciones(id) |
| ubicacion_bar_id | int8 | Sí | FK ubicaciones(id) |

## estancias
Módulo: Reservas y estancias. Filas: 9,702.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| reserva_id | int8 | No | FK reservas(id) |
| habitacion_id | int8 | Sí | FK habitaciones(id) |
| usuario_check_in_id | int8 | Sí | FK users(id) |
| usuario_check_out_id | int8 | Sí | FK users(id) |
| check_in_at | timestamp | No | — |
| check_out_at | timestamp | Sí | — |
| cantidad_llaves | int2 | No | — |
| estado | int2 | No | — |
| observaciones_entrada | text | Sí | — |
| observaciones_salida | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| reserva_detalle_id | int8 | Sí | FK reserva_detalles(id) |
| fecha_entrada_programada | timestamp | Sí | — |
| fecha_salida_programada | timestamp | Sí | — |
| fecha_check_in_real | timestamp | Sí | — |
| fecha_check_out_real | timestamp | Sí | — |

## factura_autorizaciones_dgi
Módulo: Cuentas, facturación y tesorería. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| factura_serie_id | int8 | No | FK factura_series(id) |
| numero_autorizacion | varchar | No | UNIQUE (numero_autorizacion) |
| ruc_emisor | varchar | No | — |
| razon_social_emisor | varchar | No | — |
| nombre_comercial_emisor | varchar | Sí | — |
| direccion_emisor | varchar | Sí | — |
| fecha_autorizacion | date | No | — |
| vence_at | date | Sí | — |
| rango_desde | int8 | No | — |
| rango_hasta | int8 | No | — |
| pie_imprenta_fiscal | varchar | Sí | — |
| activa | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## factura_detalles
Módulo: Cuentas, facturación y tesorería. Filas: 22,036.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| factura_id | int8 | No | FK facturas(id) |
| venta_detalle_id | int8 | Sí | FK venta_detalles(id) |
| codigo | varchar | Sí | — |
| concepto | varchar | No | — |
| unidad_medida | varchar | No | — |
| cantidad | numeric(12,2) | No | — |
| precio_unitario | numeric(14,2) | No | — |
| subtotal | numeric(14,2) | No | — |
| descuento | numeric(14,2) | No | — |
| iva_porcentaje | numeric(7,4) | No | — |
| iva | numeric(14,2) | No | — |
| total_linea | numeric(14,2) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| origen_type | varchar | Sí | — |
| origen_id | int8 | Sí | — |

## factura_folios
Módulo: Cuentas, facturación y tesorería. Filas: 9,721.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| factura_serie_id | int8 | No | FK factura_series(id); UNIQUE (factura_serie_id, numero_correlativo); UNIQUE (factura_serie_id, numero) |
| factura_autorizacion_dgi_id | int8 | No | FK factura_autorizaciones_dgi(id) |
| factura_id | int8 | Sí | FK facturas(id) |
| numero_correlativo | int8 | No | UNIQUE (factura_serie_id, numero_correlativo) |
| numero | varchar | No | UNIQUE (factura_serie_id, numero) |
| estado | int2 | No | — |
| reservado_at | timestamp | Sí | — |
| emitido_at | timestamp | Sí | — |
| anulado_at | timestamp | Sí | — |
| fallido_at | timestamp | Sí | — |
| reservado_por | int8 | Sí | FK users(id) |
| motivo | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## factura_series
Módulo: Cuentas, facturación y tesorería. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| sucursal_codigo | varchar | Sí | — |
| siguiente_numero | int8 | No | — |
| activa | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| caja_id | int8 | Sí | FK tes_cajas(id) |

## facturas
Módulo: Cuentas, facturación y tesorería. Filas: 9,726.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| factura_serie_id | int8 | No | FK factura_series(id); UNIQUE (factura_serie_id, numero_correlativo); UNIQUE (factura_serie_id, numero) |
| factura_autorizacion_dgi_id | int8 | Sí | FK factura_autorizaciones_dgi(id) |
| venta_id | int8 | Sí | FK ventas(id) |
| cuenta_id | int8 | Sí | FK cuentas(id) |
| cliente_id | int8 | Sí | FK clientes(id) |
| tipo | int2 | No | — |
| estado | int2 | No | — |
| numero | varchar | No | UNIQUE (factura_serie_id, numero) |
| numero_correlativo | int8 | No | UNIQUE (factura_serie_id, numero_correlativo) |
| fecha_emision | timestamp | Sí | — |
| fecha_vencimiento | date | Sí | — |
| moneda_id | int8 | No | FK monedas(id) |
| moneda_base_id | int8 | No | FK monedas(id) |
| tasa_cambio_id | int8 | Sí | FK tasas_cambio(id) |
| tasa_cambio | numeric(14,6) | No | — |
| subtotal | numeric(14,2) | No | — |
| descuento_total | numeric(14,2) | No | — |
| iva_total | numeric(14,2) | No | — |
| servicio_total | numeric(14,2) | No | — |
| propina_total | numeric(14,2) | No | — |
| recargo_total | numeric(14,2) | No | — |
| total | numeric(14,2) | No | — |
| subtotal_base | numeric(14,2) | No | — |
| iva_total_base | numeric(14,2) | No | — |
| total_base | numeric(14,2) | No | — |
| datos_receptor | jsonb | Sí | — |
| pdf_path | varchar | Sí | — |
| hash_documento | varchar | Sí | UNIQUE (hash_documento) |
| motivo_anulacion | text | Sí | — |
| anulada_at | timestamp | Sí | — |
| emitida_por | int8 | Sí | FK users(id) |
| anulada_por | int8 | Sí | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| numero_autorizacion_dgi | varchar | Sí | — |
| rango_autorizado_desde | int8 | Sí | — |
| rango_autorizado_hasta | int8 | Sí | — |

## failed_jobs
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| uuid | varchar | No | UNIQUE (uuid) |
| connection | text | No | — |
| queue | text | No | — |
| payload | text | No | — |
| exception | text | No | — |
| failed_at | timestamp | No | — |

## habitacion_historial
Módulo: Habitaciones, espacios y servicios. Filas: 1.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| model_type | varchar | No | — |
| model_id | int8 | No | — |
| estado_anterior | varchar | Sí | — |
| estado_nuevo | varchar | No | — |
| usuario_id | int8 | Sí | FK users(id) |
| comentario | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## habitaciones
Módulo: Habitaciones, espacios y servicios. Filas: 122.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| numero | int4 | Sí | — |
| slug | varchar | No | UNIQUE (slug) |
| nombre | varchar | No | — |
| descripcion | text | Sí | — |
| categoria_id | int8 | No | FK catalogos(id) |
| ubicacion_id | int8 | No | FK ubicaciones(id) |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| reservable_id | int8 | Sí | FK recursos_reservables(id); UNIQUE (reservable_id) |

## imagenes
Módulo: Catálogos y configuración. Filas: 490.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| url | varchar | No | — |
| public_id | varchar | Sí | — |
| orden | int4 | No | — |
| imagenable_type | varchar | No | — |
| imagenable_id | int8 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_activo_asignaciones
Módulo: Productos e inventario. Filas: 32,432.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| activo_id | int8 | No | FK inv_activos(id) |
| asignable_type | varchar | No | — |
| asignable_id | int8 | No | — |
| fecha_inicio | date | No | — |
| fecha_fin | date | Sí | — |
| motivo | varchar | Sí | — |
| asignado_por_id | int8 | Sí | FK users(id) |
| recibido_por_id | int8 | Sí | FK users(id) |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_activo_bajas
Módulo: Productos e inventario. Filas: 180.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| activo_id | int8 | No | FK inv_activos(id) |
| fecha_baja | date | No | — |
| motivo_tipo | varchar | No | — |
| motivo_detalle | text | No | — |
| valor_residual | numeric(12,2) | Sí | — |
| aprobado_por_id | int8 | Sí | FK users(id) |
| creado_por_id | int8 | No | FK users(id) |
| documento_soporte | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_activos
Módulo: Productos e inventario. Filas: 32,312.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo_inventario | varchar | No | UNIQUE (codigo_inventario) |
| individualizacion_id | int8 | Sí | FK inv_registro_individualizacion(id) |
| recepcion_item_id | int8 | Sí | FK recepcion_items(id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| nombre_descriptivo | varchar | Sí | — |
| numero_serie | varchar | Sí | — |
| fecha_adquisicion | date | No | — |
| costo_adquisicion | numeric(14,2) | Sí | — |
| moneda_id | int8 | Sí | FK monedas(id) |
| proveedor_id | int8 | Sí | FK proveedores(id) |
| vida_util_meses | int4 | Sí | — |
| fecha_garantia_fin | date | Sí | — |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_inventarios_fisicos
Módulo: Productos e inventario. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| fecha_toma | date | No | — |
| estado | varchar | No | — |
| creado_por_id | int8 | Sí | FK users(id) |
| observaciones | text | Sí | — |
| datos_hoja | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_lotes
Módulo: Productos e inventario. Filas: 490,562.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo_lote | varchar | No | — |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| estado | int2 | No | — |
| cantidad_disponible | numeric(14,4) | No | — |
| cantidad_inicial | numeric(14,4) | No | — |
| costo_unitario | numeric(14,6) | Sí | — |
| costo_total | numeric(14,2) | Sí | — |
| ubicacion_id | int8 | Sí | FK ubicaciones(id) |
| ubicacion_detalle_id | int8 | Sí | FK ubicaciones(id) |
| fecha_vencimiento | date | Sí | — |
| lote_proveedor | varchar | Sí | — |
| proveedor_id | int8 | Sí | FK proveedores(id) |
| fecha_recepcion | date | No | — |
| recepcion_item_id | int8 | Sí | FK recepcion_items(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_mantenimiento_notificaciones
Módulo: Productos e inventario. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| mantenimiento_id | int8 | No | FK inv_mantenimientos(id) |
| tipo | varchar | No | — |
| canal | varchar | No | — |
| enviado_a | int8 | Sí | FK users(id) |
| enviado_at | timestamp | No | — |
| metadata | jsonb | Sí | — |

## inv_mantenimientos
Módulo: Productos e inventario. Filas: 14,078.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| plan_id | int8 | Sí | FK inv_planes_mantenimiento(id) |
| tipo | varchar | Sí | — |
| activo_id | int8 | No | FK inv_activos(id) |
| fecha_programada | date | No | — |
| fecha_realizada | date | Sí | — |
| costo_real | numeric(12,2) | Sí | — |
| realizado_por_id | int8 | Sí | FK users(id) |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_movimientos
Módulo: Productos e inventario. Filas: 1,452,750.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| tipo | varchar | No | — |
| lote_id | int8 | Sí | FK inv_lotes(id) |
| producto_id | int8 | No | FK productos(id) |
| cantidad | numeric(14,4) | No | — |
| costo_unitario | numeric(14,6) | Sí | — |
| costo_total | numeric(14,2) | Sí | — |
| ubicacion_origen_id | int8 | Sí | FK ubicaciones(id) |
| ubicacion_destino_id | int8 | Sí | FK ubicaciones(id) |
| documento_tipo | varchar | Sí | — |
| documento_id | int8 | Sí | — |
| referencia | varchar | Sí | — |
| creado_por_id | int8 | Sí | FK users(id) |
| notas | text | Sí | — |
| created_at | timestamp | No | — |

## inv_planes_mantenimiento
Módulo: Productos e inventario. Filas: 5.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| nombre | varchar | No | — |
| tipo | varchar | No | — |
| proveedor_id | int8 | Sí | FK proveedores(id) |
| frecuencia_dias | int4 | Sí | — |
| fecha_inicio | date | No | — |
| fecha_fin | date | Sí | — |
| costo_estimado | numeric(12,2) | Sí | — |
| fecha_ultimo_mantenimiento | date | Sí | — |
| fecha_proximo_mantenimiento | date | Sí | — |
| moneda_id | int8 | Sí | FK monedas(id) |
| descripcion | text | Sí | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## inv_prefijos_codigo
Módulo: Productos e inventario. Filas: 37.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| prefijo | varchar | No | UNIQUE (prefijo) |
| ultimo_numero | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## inv_registro_individualizacion
Módulo: Productos e inventario. Filas: 5,012.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| recepcion_item_id | int8 | No | FK recepcion_items(id); UNIQUE (recepcion_item_id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| cantidad_total | int4 | No | — |
| cantidad_registrada | int4 | No | — |
| estado | int4 | No | — |
| registrado_por_id | int8 | Sí | FK users(id) |
| fecha_completado | timestamp | Sí | — |
| notes | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## inv_stock
Módulo: Productos e inventario. Filas: 16,017.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| producto_id | int8 | No | FK productos(id); UNIQUE (producto_id, producto_variante_id, lote_id, ubicacion_id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id); UNIQUE (producto_id, producto_variante_id, lote_id, ubicacion_id) |
| lote_id | int8 | Sí | FK inv_lotes(id); UNIQUE (producto_id, producto_variante_id, lote_id, ubicacion_id) |
| ubicacion_id | int8 | No | FK ubicaciones(id); UNIQUE (producto_id, producto_variante_id, lote_id, ubicacion_id) |
| ubicacion_detalle_id | int8 | Sí | FK ubicaciones(id) |
| cantidad | numeric(14,4) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## job_batches
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | varchar | No | PK |
| name | varchar | No | — |
| total_jobs | int4 | No | — |
| pending_jobs | int4 | No | — |
| failed_jobs | int4 | No | — |
| failed_job_ids | text | No | — |
| options | text | Sí | — |
| cancelled_at | int4 | Sí | — |
| created_at | int4 | No | — |
| finished_at | int4 | Sí | — |

## jobs
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| queue | varchar | No | — |
| payload | text | No | — |
| attempts | int2 | No | — |
| reserved_at | int4 | Sí | — |
| available_at | int4 | No | — |
| created_at | int4 | No | — |

## limp_ejecuciones
Módulo: Limpieza y lavandería. Filas: 26.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| horario_id | int8 | Sí | FK limp_horarios(id) |
| solicitud_id | int8 | Sí | FK solicitud_limpiezas(id) |
| limpiable_type | varchar | No | — |
| limpiable_id | int8 | No | — |
| turno_id | int8 | No | FK limp_horario_turnos(id) |
| colaborador_id | int8 | Sí | FK colaboradores(id) |
| carrito_id | int8 | Sí | FK ubicaciones(id) |
| fecha | date | No | — |
| hora_inicio | time | Sí | — |
| hora_fin | time | Sí | — |
| estado | int4 | No | — |
| estado_previo | int4 | Sí | — |
| detalles_checklist | json | Sí | — |
| consumos | json | Sí | — |
| observaciones | text | Sí | — |
| recordatorio_enviado_at | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## limp_horario_detalles
Módulo: Limpieza y lavandería. Filas: 135.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| horario_id | int8 | No | FK limp_horarios(id) |
| limpiable_type | varchar | No | — |
| limpiable_id | int8 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## limp_horario_turnos
Módulo: Limpieza y lavandería. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| nombre | varchar | No | — |
| lider_id | int8 | Sí | FK colaboradores(id) |
| apoyo_id | int8 | Sí | FK colaboradores(id) |
| hora_inicio | time | No | — |
| hora_fin | time | No | — |
| estado | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| es_lavanderia | bool | No | — |

## limp_horarios
Módulo: Limpieza y lavandería. Filas: 20.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| turno_id | int8 | Sí | FK limp_horario_turnos(id) |
| hora_estimada | time | No | — |
| frecuencia | varchar | No | — |
| dia_semana | varchar | Sí | — |
| checklist | json | Sí | — |
| activo | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| duracion_estimada_minutos | int2 | No | — |

## limp_lavanderia_procesos
Módulo: Limpieza y lavandería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| lote_id | int8 | Sí | FK inv_lotes(id) |
| cantidad | numeric(12,4) | No | — |
| estado | varchar | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## limp_sustituciones_stock
Módulo: Limpieza y lavandería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| ejecucion_id | int8 | No | FK limp_ejecuciones(id) |
| producto_id | int8 | No | FK productos(id) |
| sustituto_producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| sustituto_variante_id | int8 | Sí | FK producto_variantes(id) |
| cantidad | numeric(12,4) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## limp_turno_carritos
Módulo: Limpieza y lavandería. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| turno_id | int8 | No | FK limp_horario_turnos(id); UNIQUE (turno_id, ubicacion_id) |
| ubicacion_id | int8 | No | UNIQUE (turno_id, ubicacion_id); FK ubicaciones(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## migrations
Módulo: Infraestructura y acceso. Filas: 149.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int4 | No | PK |
| migration | varchar | No | — |
| batch | int4 | No | — |

## model_has_permissions
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| permission_id | int8 | No | FK permissions(id); PK |
| model_type | varchar | No | PK |
| model_id | int8 | No | PK |

## model_has_roles
Módulo: Infraestructura y acceso. Filas: 24.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| role_id | int8 | No | PK; FK roles(id) |
| model_type | varchar | No | PK |
| model_id | int8 | No | PK |

## monedas
Módulo: Catálogos y configuración. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| simbolo | varchar | No | — |
| es_predeterminada | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## notifications
Módulo: Infraestructura y acceso. Filas: 22.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | uuid | No | PK |
| type | varchar | No | — |
| notifiable_type | varchar | No | — |
| notifiable_id | int8 | No | — |
| data | jsonb | No | — |
| read_at | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## orden_compra_items
Módulo: Compras y recepciones. Filas: 25,454.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| orden_compra_id | int8 | No | FK ordenes_compra(id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| cantidad | numeric(12,2) | No | — |
| precio_unitario | numeric(12,2) | No | — |
| subtotal | numeric(12,2) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## ordenes_compra
Módulo: Compras y recepciones. Filas: 5,102.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| proveedor_id | int8 | No | FK proveedores(id) |
| proveedor_contacto_id | int8 | Sí | FK proveedor_contactos(id) |
| solicitud_id | int8 | Sí | FK solicitudes_compra(id) |
| cotizacion_id | int8 | Sí | FK cotizaciones(id) |
| fecha_orden | date | No | — |
| fecha_entrega_estimada | date | Sí | — |
| condicion_pago_id | int8 | Sí | FK catalogos(id) |
| subtotal | numeric(15,2) | No | — |
| impuestos | numeric(15,2) | No | — |
| total | numeric(15,2) | No | — |
| tasa_cambio | numeric(10,4) | No | — |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## pago_conciliaciones
Módulo: Cuentas, facturación y tesorería. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| pago_transaccion_id | int8 | No | FK pago_transacciones(id); UNIQUE (pago_transaccion_id) |
| estado | int2 | No | — |
| monto_esperado | numeric(14,2) | No | — |
| monto_recibido | numeric(14,2) | No | — |
| diferencia | numeric(14,2) | No | — |
| conciliada_at | timestamp | Sí | — |
| conciliada_por | int8 | Sí | FK users(id) |
| observaciones | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| reembolsada_at | timestamp | Sí | — |

## pago_transacciones
Módulo: Cuentas, facturación y tesorería. Filas: 15.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| pasarela_pago_id | int8 | No | FK pasarelas_pago(id) |
| cuenta_id | int8 | Sí | FK cuentas(id) |
| venta_id | int8 | Sí | FK ventas(id) |
| factura_id | int8 | Sí | FK facturas(id) |
| pago_cuenta_id | int8 | Sí | FK pagos_cuenta(id) |
| referencia_interna | varchar | No | UNIQUE (referencia_interna) |
| referencia_pasarela | varchar | Sí | — |
| idempotency_key | varchar | No | UNIQUE (idempotency_key) |
| estado | int2 | No | — |
| moneda_id | int8 | No | FK monedas(id) |
| moneda_base_id | int8 | No | FK monedas(id) |
| monto | numeric(14,2) | No | — |
| monto_base | numeric(14,2) | No | — |
| tasa_cambio | numeric(14,6) | No | — |
| solicitada_at | timestamp | Sí | — |
| autorizada_at | timestamp | Sí | — |
| capturada_at | timestamp | Sí | — |
| fallida_at | timestamp | Sí | — |
| reembolsada_at | timestamp | Sí | — |
| codigo_respuesta | varchar | Sí | — |
| mensaje_respuesta | varchar | Sí | — |
| request_payload | jsonb | Sí | — |
| response_payload | jsonb | Sí | — |
| webhook_payload | jsonb | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| reserva_id | int8 | Sí | FK reservas(id) |

## pagos_cuenta
Módulo: Cuentas, facturación y tesorería. Filas: 19,445.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| cuenta_id | int8 | No | FK cuentas(id) |
| venta_id | int8 | Sí | — |
| forma_pago | int2 | No | — |
| moneda_id | int8 | Sí | FK monedas(id) |
| monto | numeric(12,2) | No | — |
| propina | numeric(12,2) | No | — |
| referencia_transaccion | varchar | Sí | — |
| estado | int2 | No | — |
| observaciones | varchar | Sí | — |
| usuario_id | int8 | Sí | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## paises
Módulo: Catálogos y configuración. Filas: 193.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo_iso2 | varchar | No | UNIQUE (codigo_iso2) |
| codigo_iso3 | varchar | No | UNIQUE (codigo_iso3) |
| nombre | varchar | No | UNIQUE (nombre) |
| codigo_telefono | varchar | Sí | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## pasarelas_pago
Módulo: Cuentas, facturación y tesorería. Filas: 1.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| activa | bool | No | — |
| modo_prueba | bool | No | — |
| configuracion | jsonb | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| proveedor | varchar | Sí | — |
| gestion | varchar | Sí | — |

## password_reset_tokens
Módulo: Infraestructura y acceso. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| email | varchar | No | PK |
| token | varchar | No | — |
| created_at | timestamp | Sí | — |

## pedido_items
Módulo: Cocina y restaurante. Filas: 88,077.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| pedido_id | int8 | No | FK pedidos(id) |
| plato_id | int8 | Sí | FK platos(id) |
| area_cocina | varchar | No | — |
| cantidad | numeric(10,2) | No | — |
| precio_unitario | numeric(10,2) | No | — |
| subtotal | numeric(10,2) | No | — |
| estado | int2 | No | — |
| notas | text | Sí | — |
| observaciones | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| bloqueo_stock_detalle | json | Sí | — |
| bloqueado_stock_en | timestamp | Sí | — |
| tipo_item | varchar | No | — |
| producto_id | int8 | Sí | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |

## pedidos
Módulo: Cocina y restaurante. Filas: 35,292.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| mesa_id | int8 | Sí | FK espacios(id) |
| mesero_id | int8 | Sí | FK colaboradores(id) |
| cliente_id | int8 | Sí | FK clientes(id) |
| cuenta_id | int8 | Sí | — |
| estado | int2 | No | — |
| subtotal | numeric(10,2) | No | — |
| padre_pedido_id | int8 | Sí | FK pedidos(id) |
| consecutivo_comanda | int4 | No | — |
| abierto_en | timestamp | Sí | — |
| cerrado_en | timestamp | Sí | — |
| cargado_en | timestamp | Sí | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| total | numeric(10,2) | No | — |

## permissions
Módulo: Infraestructura y acceso. Filas: 27.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| name | varchar | No | UNIQUE (name, guard_name) |
| guard_name | varchar | No | UNIQUE (name, guard_name) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## personas
Módulo: Personas y terceros. Filas: 175.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| primer_nombre | varchar | No | — |
| segundo_nombre | varchar | Sí | — |
| pais_id | int8 | Sí | FK paises(id) |
| tipo_persona | varchar | No | — |
| telefono | varchar | Sí | — |
| direccion | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## personas_juridicas
Módulo: Personas y terceros. Filas: 24.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| persona_id | int8 | No | FK personas(id) |
| razon_social | varchar | No | — |
| tipo_identificacion | varchar | Sí | UNIQUE (tipo_identificacion, numero_identificacion) |
| numero_identificacion | varchar | Sí | UNIQUE (tipo_identificacion, numero_identificacion) |
| fecha_constitucion | date | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## personas_naturales
Módulo: Personas y terceros. Filas: 151.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| persona_id | int8 | No | FK personas(id) |
| primer_apellido | varchar | No | — |
| segundo_apellido | varchar | Sí | — |
| tipo_identificacion | varchar | Sí | UNIQUE (tipo_identificacion, numero_identificacion) |
| numero_identificacion | varchar | Sí | UNIQUE (tipo_identificacion, numero_identificacion) |
| sexo | varchar | Sí | — |
| fecha_nacimiento | date | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## platos
Módulo: Cocina y restaurante. Filas: 19.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| categoria_id | int8 | Sí | FK catalogos(id) |
| producto_receta_id | int8 | Sí | FK productos(id) |
| descripcion | text | Sí | — |
| web | bool | No | — |
| estado | int4 | No | — |
| tiempo_preparacion | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| area_cocina | varchar | No | — |
| instrucciones_preparacion | text | Sí | — |

## politica_penalizaciones
Módulo: Habitaciones, espacios y servicios. Filas: 3.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| politica_id | int8 | No | FK politicas(id) |
| min_unidades | int4 | Sí | — |
| max_unidades | int4 | Sí | — |
| unidad | int2 | No | — |
| porcentaje | numeric(5,2) | No | — |
| aplica_no_show | bool | No | — |
| orden | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## politicaables
Módulo: Habitaciones, espacios y servicios. Filas: 1,386.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| politica_id | int8 | No | FK politicas(id); UNIQUE (politica_id, politicaable_type, politicaable_id) |
| politicaable_type | varchar | No | UNIQUE (politica_id, politicaable_type, politicaable_id) |
| politicaable_id | int8 | No | UNIQUE (politica_id, politicaable_type, politicaable_id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## politicas
Módulo: Habitaciones, espacios y servicios. Filas: 21.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| titulo | varchar | No | UNIQUE (titulo) |
| descripcion | text | Sí | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| aplica_penalizacion | bool | No | — |

## precios
Módulo: Habitaciones, espacios y servicios. Filas: 717.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| priceable_type | varchar | No | — |
| priceable_id | int8 | No | — |
| moneda_id | int8 | No | FK monedas(id) |
| precio | numeric(10,2) | No | — |
| fecha_inicio | date | No | — |
| fecha_fin | date | Sí | — |
| estado | int4 | No | — |
| es_oferta | bool | No | — |
| tipo_precio | varchar | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## proceso_items
Módulo: Cocina y restaurante. Filas: 11,418.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| proceso_id | int8 | No | FK procesos_cocina(id) |
| producto_destino_id | int8 | No | FK productos(id) |
| variante_destino_id | int8 | Sí | FK producto_variantes(id) |
| cantidad | numeric(10,3) | No | — |
| peso_unitario | numeric(8,3) | Sí | — |
| peso_total | numeric(8,3) | Sí | — |
| costo_asignado | numeric(10,2) | No | — |
| es_merma | bool | No | — |
| ubicacion_destino_id | int8 | Sí | FK ubicaciones(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## procesos_cocina
Módulo: Cocina y restaurante. Filas: 2,378.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| plato_id | int8 | Sí | FK platos(id) |
| cantidad_platos | int2 | Sí | — |
| producto_origen_id | int8 | No | FK productos(id) |
| variante_origen_id | int8 | Sí | FK producto_variantes(id) |
| cantidad_procesada | numeric(10,3) | No | — |
| costo_total | numeric(10,2) | No | — |
| realizado_por | int8 | Sí | FK users(id) |
| observaciones | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| tipo_registro | varchar | No | — |
| confirmado_at | timestamp | Sí | — |
| lote_resultado_id | int8 | Sí | FK inv_lotes(id) |
| cantidad_obtenida | numeric(14,4) | Sí | — |
| pedido_item_id | int8 | Sí | FK pedido_items(id); UNIQUE (pedido_item_id) |

## producto_kit
Módulo: Productos e inventario. Filas: 139.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| producto_padre_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | No | FK producto_variantes(id) |
| cantidad | numeric(12,4) | No | — |
| lote_id | int8 | Sí | FK inv_lotes(id) |
| talla | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

Índice especial `uq_producto_kit_padre_variante`:
```sql
CREATE UNIQUE INDEX uq_producto_kit_padre_variante ON public.producto_kit USING btree (producto_padre_id, producto_variante_id) WHERE (deleted_at IS NULL)
```

## producto_variantes
Módulo: Productos e inventario. Filas: 733.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| producto_id | int8 | No | FK productos(id); UNIQUE (producto_id, nombre_variante) |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre_variante | varchar | No | UNIQUE (producto_id, nombre_variante) |
| atributos | json | Sí | — |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| unidades_por_empaque | numeric(14,4) | No | — |
| peso | numeric(8,2) | Sí | — |
| volumen | numeric(8,2) | Sí | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## productos
Módulo: Productos e inventario. Filas: 415.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| categoria_id | int8 | No | FK catalogos(id); UNIQUE (nombre, categoria_id) |
| marca_id | int8 | Sí | FK catalogos(id) |
| nombre | varchar | No | UNIQUE (nombre, categoria_id) |
| descripcion | text | Sí | — |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| tipo | int4 | Sí | — |
| estado | int4 | No | — |
| es_transformable | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| rendimiento_porciones | numeric(10,2) | No | — |

## promocion_beneficio_reglas
Módulo: Promociones. Filas: 7.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| beneficio_id | int8 | No | FK promocion_beneficios(id) |
| tipo_regla | varchar | No | — |
| operador | varchar | No | — |
| valor_numerico | numeric(12,2) | Sí | — |
| valor_texto | varchar | Sí | — |
| obligatoria | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## promocion_beneficio_usos
Módulo: Promociones. Filas: 5.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| beneficio_id | int8 | No | FK promocion_beneficios(id) |
| cliente_id | int8 | Sí | FK clientes(id) |
| reserva_id | int8 | Sí | FK reservas(id) |
| cuenta_id | int8 | Sí | FK cuentas(id) |
| venta_id | int8 | Sí | FK ventas(id) |
| monto_descuento | numeric(12,2) | No | — |
| estado | varchar | No | — |
| usado_en | timestamp | Sí | — |
| metadata | json | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## promocion_beneficios
Módulo: Promociones. Filas: 11.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| promocion_id | int8 | Sí | FK promociones(id) |
| segmento_cliente_id | int8 | Sí | FK catalogos(id) |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| tipo | varchar | No | — |
| valor | numeric(12,2) | Sí | — |
| es_porcentaje | bool | No | — |
| combinable | bool | No | — |
| limite_usos_por_cliente | int2 | Sí | — |
| fecha_inicio | date | Sí | — |
| fecha_fin | date | Sí | — |
| activo | bool | No | — |
| descripcion | text | Sí | — |
| metadata | json | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## promocion_items
Módulo: Promociones. Filas: 14.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| promocion_id | int8 | No | FK promociones(id) |
| item_type | varchar | No | — |
| item_id | int8 | No | — |
| precio_especial | numeric(10,2) | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## promociones
Módulo: Promociones. Filas: 6.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| descripcion | text | Sí | — |
| tipo_promocion_id | int8 | Sí | FK catalogos(id) |
| fecha_inicio | date | No | — |
| fecha_fin | date | No | — |
| descuento_porcentaje | numeric(5,2) | Sí | — |
| descuento_monto | numeric(10,2) | Sí | — |
| precio_paquete | numeric(10,2) | Sí | — |
| estado | int4 | No | — |
| web | bool | No | — |
| condiciones | text | Sí | — |
| orden | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## proveedor_contactos
Módulo: Personas y terceros. Filas: 12.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| proveedor_id | int8 | No | FK proveedores(id) |
| nombre | varchar | No | — |
| cargo | varchar | Sí | — |
| telefono | varchar | Sí | — |
| email | varchar | Sí | — |
| principal | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## proveedores
Módulo: Personas y terceros. Filas: 6.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| persona_id | int8 | No | FK personas(id) |
| tipo_proveedor_id | int8 | Sí | FK catalogos(id) |
| direccion_fiscal | varchar | Sí | — |
| notas | text | Sí | — |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## recepcion_items
Módulo: Compras y recepciones. Filas: 22,891.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| recepcion_id | int8 | No | FK recepciones_compra(id) |
| orden_item_id | int8 | No | FK orden_compra_items(id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| cantidad_recibida | numeric(12,2) | No | — |
| cantidad_rechazada | numeric(12,2) | No | — |
| motivo_rechazo | varchar | Sí | — |
| nota | text | Sí | — |
| lote_proveedor | varchar | Sí | — |
| fecha_vencimiento | date | Sí | — |
| ubicacion_id | int8 | Sí | FK ubicaciones(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## recepciones_compra
Módulo: Compras y recepciones. Filas: 4,570.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| orden_compra_id | int8 | No | FK ordenes_compra(id) |
| fecha_recepcion | date | No | — |
| guia_remision | varchar | Sí | — |
| recibido_por_id | int8 | No | FK users(id) |
| ubicacion_id | int8 | Sí | FK ubicaciones(id) |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## recursos_reservables
Módulo: Reservas y estancias. Filas: 160.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| tipo | int2 | No | — |
| nombre | varchar | No | — |
| capacidad | int2 | Sí | — |
| control_disponibilidad | int2 | No | — |
| duracion_minutos | int4 | Sí | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## reserva_detalles
Módulo: Reservas y estancias. Filas: 10,106.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| reserva_id | int8 | No | FK reservas(id) |
| reservable_id | int8 | No | FK recursos_reservables(id) |
| parent_id | int8 | Sí | FK reserva_detalles(id) |
| estado | int2 | No | — |
| fecha_inicio | timestamp | No | — |
| fecha_fin | timestamp | Sí | — |
| cantidad | int4 | No | — |
| adultos | int2 | No | — |
| ninos | int2 | No | — |
| precio_unitario | numeric(12,2) | No | — |
| descuento | numeric(12,2) | No | — |
| impuestos | numeric(12,2) | No | — |
| subtotal | numeric(12,2) | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| hold_expires_at | timestamp | Sí | — |
| confirmado_at | timestamp | Sí | — |
| cancelado_at | timestamp | Sí | — |
| estancia_id | int8 | Sí | FK estancias(id) |
| origen | int2 | No | — |

## reserva_estado_historial
Módulo: Reservas y estancias. Filas: 10,108.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| reserva_id | int8 | No | FK reservas(id) |
| estado_anterior | int2 | Sí | — |
| estado_nuevo | int2 | No | — |
| motivo | text | Sí | — |
| usuario_id | int8 | Sí | FK users(id) |
| created_at | timestamp | No | — |

## reserva_huespedes
Módulo: Reservas y estancias. Filas: 10,104.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| reserva_detalle_id | int8 | No | FK reserva_detalles(id) |
| nombre | varchar | No | — |
| tipo_identificacion | int2 | Sí | — |
| identificacion | varchar | Sí | — |
| tipo_huesped | int2 | No | — |
| es_titular | bool | No | — |
| fecha_nacimiento | date | Sí | — |
| telefono | varchar | Sí | — |
| email | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## reserva_servicios
Módulo: Reservas y estancias. Filas: 3,224.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| reserva_id | int8 | No | FK reservas(id) |
| servicio_id | int8 | No | FK servicios(id) |
| cantidad | int4 | No | — |
| precio | numeric(12,2) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## reservas
Módulo: Reservas y estancias. Filas: 10,106.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo_reserva | varchar | No | UNIQUE (codigo_reserva) |
| cliente_id | int8 | Sí | FK clientes(id) |
| nombre_cliente | varchar | No | — |
| telefono_cliente | varchar | Sí | — |
| email_cliente | varchar | Sí | — |
| tipo_reserva | varchar | No | — |
| habitacion_id | int8 | Sí | FK habitaciones(id) |
| espacio_id | int8 | Sí | FK espacios(id) |
| servicio_id | int8 | Sí | FK servicios(id) |
| promocion_id | int8 | Sí | FK promociones(id) |
| fecha_check_in | date | No | — |
| fecha_check_out | date | Sí | — |
| hora_reserva | varchar | Sí | — |
| adultos | int4 | No | — |
| ninos | int4 | No | — |
| acompanantes | json | Sí | — |
| estado | int2 | No | — |
| subtotal | numeric(12,2) | Sí | — |
| descuento | numeric(12,2) | No | — |
| total | numeric(12,2) | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| moneda_id | int8 | Sí | FK monedas(id) |
| tipo_pago | varchar | No | — |
| total_pagado | numeric(12,2) | No | — |
| saldo | numeric(12,2) | No | — |
| solicita_cuenta | bool | No | — |
| limite_cuenta_solicitado | numeric(12,2) | Sí | — |

## reservas_bitacora
Módulo: Reservas y estancias. Filas: 324.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| reserva_id | int8 | No | FK reservas(id) |
| tipo | varchar | No | — |
| datos | jsonb | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## restaurante_buffet_cierres
Módulo: Cocina y restaurante. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| buffet_id | int8 | No | FK restaurante_buffets(id); UNIQUE (buffet_id, stock_id) |
| stock_id | int8 | No | UNIQUE (buffet_id, stock_id); FK stocks(id) |
| cantidad_servida | numeric(14,4) | No | — |
| sobrante | numeric(14,4) | No | — |
| merma | numeric(14,4) | No | — |
| costo_consumido | numeric(14,2) | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## restaurante_buffet_platos
Módulo: Cocina y restaurante. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| buffet_id | int8 | No | FK restaurante_buffets(id); UNIQUE (buffet_id, plato_id); UNIQUE (buffet_id, producto_variante_id) |
| plato_id | int8 | No | UNIQUE (buffet_id, plato_id); FK platos(id) |
| producto_variante_id | int8 | No | UNIQUE (buffet_id, producto_variante_id); FK producto_variantes(id) |
| porciones_previstas | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## restaurante_buffets
Módulo: Cocina y restaurante. Filas: 1.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| nombre | varchar | No | — |
| fecha_servicio | date | No | — |
| reserva_id | int8 | Sí | FK reservas(id) |
| moneda_id | int8 | No | FK monedas(id) |
| modalidad | varchar | No | — |
| precio_persona | numeric(14,2) | No | — |
| precio_paquete | numeric(14,2) | No | — |
| comensales_previstos | int4 | No | — |
| comensales_reales | int4 | No | — |
| estado | varchar | No | — |
| cerrado_at | timestamp | Sí | — |
| cuenta_id | int8 | Sí | FK cuentas(id) |
| cargado_at | timestamp | Sí | — |
| personas_cobradas | int4 | No | — |
| paquetes_cobrados | int4 | No | — |
| creado_por | int8 | Sí | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| tipo_servicio | varchar | No | — |

## restaurante_recetas_transformacion_materia_prima
Módulo: Cocina y restaurante. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| producto_materia_prima_id | int8 | No | FK productos(id) |
| variante_materia_prima_id | int8 | No | FK producto_variantes(id); UNIQUE (variante_materia_prima_id, variante_bruta_id, deleted_at) |
| producto_bruto_id | int8 | No | FK productos(id) |
| variante_bruta_id | int8 | No | FK producto_variantes(id); UNIQUE (variante_materia_prima_id, variante_bruta_id, deleted_at) |
| cantidad_bruta | numeric(12,4) | No | — |
| cantidad_resultado | numeric(12,4) | No | — |
| merma_estimada | numeric(12,4) | No | — |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| estado | bool | No | — |
| observaciones | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | UNIQUE (variante_materia_prima_id, variante_bruta_id, deleted_at) |

## restaurante_sustituciones_ingredientes
Módulo: Cocina y restaurante. Filas: 1.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| pedido_item_id | int8 | No | FK pedido_items(id) |
| plato_id | int8 | Sí | FK platos(id) |
| producto_original_id | int8 | Sí | FK productos(id) |
| variante_original_id | int8 | No | FK producto_variantes(id) |
| producto_sustituto_id | int8 | Sí | FK productos(id) |
| variante_sustituta_id | int8 | No | FK producto_variantes(id) |
| cantidad_requerida | numeric(12,4) | No | — |
| cantidad_usada | numeric(12,4) | No | — |
| motivo | varchar | Sí | — |
| autorizado_por | int8 | Sí | FK users(id) |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## restaurante_transformacion_materia_prima_items
Módulo: Cocina y restaurante. Filas: 17,650.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| transformacion_id | int8 | No | FK restaurante_transformaciones_materia_prima(id) |
| producto_destino_id | int8 | No | FK productos(id) |
| variante_destino_id | int8 | Sí | FK producto_variantes(id) |
| ubicacion_destino_id | int8 | Sí | FK ubicaciones(id) |
| cantidad | numeric(12,4) | No | — |
| costo_asignado | numeric(14,2) | No | — |
| es_merma | bool | No | — |
| observaciones | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## restaurante_transformaciones_materia_prima
Módulo: Cocina y restaurante. Filas: 3,208.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| producto_origen_id | int8 | No | FK productos(id) |
| variante_origen_id | int8 | No | FK producto_variantes(id) |
| ubicacion_origen_id | int8 | No | FK ubicaciones(id) |
| cantidad_procesada | numeric(12,4) | No | — |
| costo_total | numeric(14,2) | No | — |
| realizado_por | int8 | Sí | FK users(id) |
| observaciones | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## restaurante_zonas_delivery
Módulo: Cocina y restaurante. Filas: 17.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| costo_envio | numeric(10,2) | No | — |
| activo | bool | No | — |
| municipios | json | No | — |
| orden | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## role_has_permissions
Módulo: Infraestructura y acceso. Filas: 216.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| permission_id | int8 | No | FK permissions(id); PK |
| role_id | int8 | No | PK; FK roles(id) |

## roles
Módulo: Infraestructura y acceso. Filas: 29.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| name | varchar | No | UNIQUE (name, guard_name) |
| guard_name | varchar | No | UNIQUE (name, guard_name) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## secuencias
Módulo: Catálogos y configuración. Filas: 1.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| clave | varchar | Sí | — |
| tipo | varchar | Sí | — |
| anio | int4 | Sí | — |
| ultimo_valor | int8 | No | — |
| ultimo_numero | int8 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## servicio_asignaciones
Módulo: Habitaciones, espacios y servicios. Filas: 498.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| serviceable_type | varchar | No | — |
| serviceable_id | int8 | No | — |
| servicio_id | int8 | No | FK servicios(id) |
| incluido | bool | No | — |
| estado | int2 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

Índice especial `uq_servicio_asignacion`:
```sql
CREATE UNIQUE INDEX uq_servicio_asignacion ON public.servicio_asignaciones USING btree (serviceable_type, serviceable_id, servicio_id) WHERE (deleted_at IS NULL)
```

## servicios
Módulo: Habitaciones, espacios y servicios. Filas: 25.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| categoria_id | int8 | Sí | FK catalogos(id) |
| descripcion | text | Sí | — |
| icono | varchar | Sí | — |
| estado | int4 | No | — |
| web | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| reservable_id | int8 | Sí | FK recursos_reservables(id); UNIQUE (reservable_id) |

## servicios_precios
Módulo: Habitaciones, espacios y servicios. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| servicio_id | int8 | No | FK servicios(id) |
| moneda_id | int8 | No | FK monedas(id) |
| precio | numeric(10,2) | No | — |
| fecha_inicio | date | No | — |
| fecha_fin | date | Sí | — |
| estado | int4 | No | — |
| es_oferta | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

Índice especial `unique_precio_vigente`:
```sql
CREATE UNIQUE INDEX unique_precio_vigente ON public.servicios_precios USING btree (servicio_id, moneda_id) WHERE ((estado = 1) AND (es_oferta = false) AND (deleted_at IS NULL))
```

## sessions
Módulo: Infraestructura y acceso. Filas: 4.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | varchar | No | PK |
| user_id | int8 | Sí | — |
| ip_address | varchar | Sí | — |
| user_agent | text | Sí | — |
| payload | text | No | — |
| last_activity | int4 | No | — |

## social_accounts
Módulo: Infraestructura y acceso. Filas: 2.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| user_id | int8 | No | FK users(id) |
| provider | varchar | No | UNIQUE (provider, provider_id) |
| provider_id | varchar | No | UNIQUE (provider, provider_id) |
| provider_email | varchar | Sí | — |
| avatar | varchar | Sí | — |
| provider_data | json | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## solicitud_items
Módulo: Compras y recepciones. Filas: 30,170.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| solicitud_id | int8 | No | FK solicitudes_compra(id) |
| producto_id | int8 | No | FK productos(id) |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| unidad_medida_id | int8 | Sí | FK catalogos(id) |
| cantidad_solicitada | numeric(12,2) | No | — |
| cantidad_aprobada | numeric(12,2) | No | — |
| observaciones | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## solicitud_limpiezas
Módulo: Limpieza y lavandería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| limpiable_type | varchar | No | — |
| limpiable_id | int8 | No | — |
| personal_id | int8 | Sí | FK users(id) |
| creador_id | int8 | Sí | FK users(id) |
| prioridad | varchar | No | — |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## solicitudes_compra
Módulo: Compras y recepciones. Filas: 6,185.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| colaborador_id | int8 | No | FK colaboradores(id) |
| departamento_solicitante_id | int8 | Sí | FK catalogos(id) |
| fecha_solicitud | date | No | — |
| fecha_necesita | date | Sí | — |
| motivo | text | Sí | — |
| estado | int4 | No | — |
| notas | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## stocks
Módulo: Productos e inventario. Filas: 2,004.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| stockable_type | varchar | No | — |
| stockable_id | int8 | No | — |
| producto_variante_id | int8 | Sí | FK producto_variantes(id) |
| lote_id | int8 | Sí | FK inv_lotes(id) |
| cantidad_ideal | numeric(12,4) | No | — |
| cantidad_actual | numeric(12,4) | No | — |
| estado | varchar | Sí | — |
| ultima_verificacion | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |
| producto_id | int8 | Sí | FK productos(id) |

Índice especial `uq_stock_variante`:
```sql
CREATE UNIQUE INDEX uq_stock_variante ON public.stocks USING btree (stockable_type, stockable_id, (
CASE
    WHEN (producto_variante_id IS NULL) THEN COALESCE(producto_id, (0)::bigint)
    ELSE (0)::bigint
END), COALESCE(producto_variante_id, (0)::bigint), COALESCE(lote_id, (0)::bigint)) WHERE (deleted_at IS NULL)
```

## tarjeta_cliente
Módulo: Personas y terceros. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| cliente_id | int8 | No | FK clientes(id) |
| stripe_payment_method_id | varchar | No | UNIQUE (stripe_payment_method_id) |
| stripe_customer_id | varchar | No | — |
| ultimo_digitos | varchar | No | — |
| marca | varchar | No | — |
| exp_month | int2 | No | — |
| exp_year | int2 | No | — |
| es_predeterminada | bool | No | — |
| eliminado_at | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tasas_cambio
Módulo: Catálogos y configuración. Filas: 1.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| fecha | date | No | UNIQUE (fecha, moneda_origen_id, moneda_destino_id) |
| moneda_origen_id | int8 | No | FK monedas(id); UNIQUE (fecha, moneda_origen_id, moneda_destino_id) |
| moneda_destino_id | int8 | No | FK monedas(id); UNIQUE (fecha, moneda_origen_id, moneda_destino_id) |
| tasa | numeric(10,4) | No | — |
| es_fija | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## tes_abastecimientos
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| sesion_id | int8 | No | FK tes_sesiones_caja(id) |
| fondo_id | int8 | No | FK tes_fondos(id) |
| solicitante_id | int8 | No | FK users(id) |
| aprobado_por | int8 | Sí | FK users(id) |
| importe | numeric(14,2) | No | — |
| referencia | varchar | No | — |
| estado | varchar | No | — |
| decision | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_caja_monedas
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| caja_id | int8 | No | FK tes_cajas(id); UNIQUE (caja_id, moneda_id) |
| moneda_id | int8 | No | UNIQUE (caja_id, moneda_id); FK monedas(id) |

## tes_cajas
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| ubicacion | varchar | No | — |
| activa | bool | No | — |
| creada_por | int8 | No | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_comprobantes
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | uuid | No | UNIQUE (codigo) |
| cuenta_id | int8 | No | FK cuentas(id) |
| fondo_id | int8 | No | FK tes_fondos(id); UNIQUE (fondo_id, referencia) |
| usuario_id | int8 | No | FK users(id) |
| importe | numeric(14,2) | No | — |
| fecha | date | No | — |
| referencia | varchar | No | UNIQUE (fondo_id, referencia) |
| estado | varchar | No | — |
| decision | text | Sí | — |
| revisado_por | int8 | Sí | FK users(id) |
| pago_cuenta_id | int8 | Sí | FK pagos_cuenta(id); UNIQUE (pago_cuenta_id) |
| extracto_id | int8 | Sí | FK tes_extractos(id); UNIQUE (extracto_id) |
| revisado_at | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_conciliaciones
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| extracto_id | int8 | No | FK tes_extractos(id); UNIQUE (extracto_id) |
| movimiento_id | int8 | No | FK tes_movimientos_fondo(id); UNIQUE (movimiento_id) |
| pago_transaccion_id | int8 | Sí | FK pago_transacciones(id); UNIQUE (pago_transaccion_id) |
| comision | numeric(14,2) | No | — |
| usuario_id | int8 | No | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_decisiones_comprobante
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| comprobante_id | int8 | No | FK tes_comprobantes(id) |
| usuario_id | int8 | No | FK users(id) |
| decision | varchar | No | — |
| motivo | text | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_devoluciones
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| pago_cuenta_id | int8 | No | FK pagos_cuenta(id) |
| sesion_id | int8 | No | FK tes_sesiones_caja(id) |
| solicitante_id | int8 | No | FK users(id) |
| aprobado_por | int8 | Sí | FK users(id) |
| importe | numeric(14,2) | No | — |
| motivo | varchar | No | — |
| estado | varchar | No | — |
| decision | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_entregas
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| sesion_id | int8 | No | FK tes_sesiones_caja(id) |
| fondo_id | int8 | No | FK tes_fondos(id) |
| entregado_por | int8 | No | FK users(id) |
| recibido_por | int8 | Sí | FK users(id) |
| importe | numeric(14,2) | No | — |
| referencia | varchar | No | — |
| estado | varchar | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_evidencias
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| comprobante_id | int8 | No | FK tes_comprobantes(id) |
| ruta | varchar | No | — |
| sha256 | varchar | No | — |
| usuario_id | int8 | No | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_extractos
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| fondo_id | int8 | No | FK tes_fondos(id); UNIQUE (fondo_id, referencia) |
| fecha | date | No | — |
| referencia | varchar | No | UNIQUE (fondo_id, referencia) |
| importe | numeric(14,2) | No | — |
| soporte | varchar | No | — |
| sha256 | varchar | No | — |
| registrado_por | int8 | No | FK users(id) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_fondos
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | varchar | No | UNIQUE (codigo) |
| nombre | varchar | No | — |
| tipo | varchar | No | — |
| moneda_id | int8 | No | FK monedas(id) |
| responsable_id | int8 | No | FK users(id) |
| banco | varchar | Sí | — |
| identificacion | varchar | Sí | — |
| limite_vale | numeric(14,2) | No | — |
| activo | bool | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_movimientos_caja
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| clave_operacion | uuid | No | UNIQUE (clave_operacion) |
| sesion_id | int8 | No | FK tes_sesiones_caja(id) |
| moneda_id | int8 | No | FK monedas(id) |
| usuario_id | int8 | No | FK users(id) |
| tipo | varchar | No | UNIQUE (pago_cuenta_id, tipo) |
| importe | numeric(14,2) | No | — |
| concepto | varchar | No | — |
| referencia | varchar | Sí | — |
| pago_cuenta_id | int8 | Sí | FK pagos_cuenta(id); UNIQUE (pago_cuenta_id, tipo) |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_movimientos_fondo
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| fondo_id | int8 | No | FK tes_fondos(id); UNIQUE (fondo_id, operacion_id) |
| operacion_id | int8 | No | UNIQUE (fondo_id, operacion_id); FK tes_operaciones(id) |
| usuario_id | int8 | No | FK users(id) |
| importe | numeric(14,2) | No | — |
| concepto | varchar | No | — |
| referencia | varchar | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_operaciones
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| clave | uuid | No | UNIQUE (clave) |
| huella | varchar | No | — |
| tipo | varchar | No | — |
| usuario_id | int8 | No | FK users(id) |
| resultado | json | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_operaciones_cobro
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| clave | uuid | No | UNIQUE (clave) |
| cuenta_id | int8 | No | FK cuentas(id) |
| usuario_id | int8 | No | FK users(id) |
| pago_cuenta_id | int8 | No | FK pagos_cuenta(id); UNIQUE (pago_cuenta_id) |
| venta_id | int8 | Sí | FK ventas(id) |
| huella_peticion | varchar | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_saldos_sesion
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| sesion_id | int8 | No | FK tes_sesiones_caja(id); UNIQUE (sesion_id, moneda_id) |
| moneda_id | int8 | No | FK monedas(id); UNIQUE (sesion_id, moneda_id) |
| apertura | numeric(14,2) | No | — |
| esperado_cierre | numeric(14,2) | Sí | — |
| contado | numeric(14,2) | Sí | — |
| diferencia | numeric(14,2) | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## tes_sesiones_caja
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| codigo | uuid | No | UNIQUE (codigo) |
| caja_id | int8 | No | FK tes_cajas(id) |
| usuario_id | int8 | No | FK users(id) |
| estado | varchar | No | — |
| origen_fondo | text | No | — |
| abierta_at | timestamp | No | — |
| contada_at | timestamp | Sí | — |
| cerrada_at | timestamp | Sí | — |
| cerrada_por | int8 | Sí | FK users(id) |
| motivo_diferencia | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

Índice especial `tes_caja_activa_unica`:
```sql
CREATE UNIQUE INDEX tes_caja_activa_unica ON public.tes_sesiones_caja USING btree (caja_id) WHERE ((estado)::text = ANY ((ARRAY['abierta'::character varying, 'pendiente_revision'::character varying])::text[]))
```

Índice especial `tes_cajero_activo_unico`:
```sql
CREATE UNIQUE INDEX tes_cajero_activo_unico ON public.tes_sesiones_caja USING btree (usuario_id) WHERE ((estado)::text = ANY ((ARRAY['abierta'::character varying, 'pendiente_revision'::character varying])::text[]))
```

## tes_vales
Módulo: Cuentas, facturación y tesorería. Filas: 0.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| fondo_id | int8 | No | FK tes_fondos(id) |
| solicitante_id | int8 | No | FK users(id) |
| beneficiario | varchar | No | — |
| concepto | varchar | No | — |
| importe | numeric(14,2) | No | — |
| vence_at | date | No | — |
| estado | varchar | No | — |
| aprobado_por | int8 | Sí | FK users(id) |
| liquidado_por | int8 | Sí | FK users(id) |
| gastado | numeric(14,2) | Sí | — |
| devuelto | numeric(14,2) | Sí | — |
| referencia | varchar | Sí | — |
| soporte | varchar | Sí | — |
| resolucion | text | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |

## ubicaciones
Módulo: Catálogos y configuración. Filas: 122.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| padre_id | int8 | Sí | FK ubicaciones(id); UNIQUE (padre_id, orden) |
| tipo | varchar | No | — |
| nombre | varchar | No | — |
| descripcion | text | Sí | — |
| orden | int4 | No | UNIQUE (padre_id, orden) |
| estado | int4 | No | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

## users
Módulo: Personas y terceros. Filas: 30.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| persona_id | int8 | Sí | FK personas(id) |
| name | varchar | No | — |
| email | varchar | Sí | UNIQUE (email) |
| email_verified_at | timestamp | Sí | — |
| password | varchar | No | — |
| is_admin | bool | No | — |
| password_change_required | bool | No | — |
| remember_token | varchar | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| deleted_at | timestamp | Sí | — |

Índice especial `users_persona_id_unique`:
```sql
CREATE UNIQUE INDEX users_persona_id_unique ON public.users USING btree (persona_id) WHERE (persona_id IS NOT NULL)
```

## venta_detalles
Módulo: Cuentas, facturación y tesorería. Filas: 22,045.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| venta_id | int8 | No | FK ventas(id) |
| concepto | varchar | No | — |
| cantidad | numeric(10,2) | No | — |
| precio_unitario | numeric(12,2) | No | — |
| subtotal | numeric(12,2) | No | — |
| descuento | numeric(12,2) | No | — |
| impuesto | numeric(12,2) | No | — |
| servicio | numeric(12,2) | No | — |
| propina | numeric(12,2) | No | — |
| recargo | numeric(12,2) | No | — |
| total_linea | numeric(12,2) | No | — |
| origen_type | varchar | Sí | — |
| origen_id | int8 | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
| moneda_id | int8 | No | FK monedas(id) |

## ventas
Módulo: Cuentas, facturación y tesorería. Filas: 9,735.

| Columna | Tipo | Admite NULL | Restricciones |
|---|---|---|---|
| id | int8 | No | PK |
| numero_venta | varchar | No | UNIQUE (numero_venta) |
| cuenta_id | int8 | Sí | FK cuentas(id) |
| cliente_id | int8 | Sí | FK clientes(id) |
| moneda_id | int8 | Sí | FK monedas(id) |
| subtotal | numeric(12,2) | No | — |
| descuento_total | numeric(12,2) | No | — |
| impuesto_total | numeric(12,2) | No | — |
| servicio_total | numeric(12,2) | No | — |
| propina_total | numeric(12,2) | No | — |
| recargo_total | numeric(12,2) | No | — |
| total | numeric(12,2) | No | — |
| estado | int2 | No | — |
| datos_fiscales | jsonb | Sí | — |
| creada_por | int8 | Sí | FK users(id) |
| anulada_por | int8 | Sí | FK users(id) |
| anulada_en | timestamp | Sí | — |
| created_at | timestamp | Sí | — |
| updated_at | timestamp | Sí | — |
