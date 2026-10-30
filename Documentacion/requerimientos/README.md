# Catálogo Maestro de Requerimientos del Sistema — Hotel Bugambilias

Esta carpeta contiene la especificación exhaustiva y formal de los **Requerimientos Funcionales (RF)** y **Requerimientos No Funcionales (RNF)** del sistema Hotel Bugambilias, cubriendo cada módulo, proceso operativo, regla de negocio y flujo transaccional del proyecto.

---

## Estructura Modular de Requerimientos

| # | Archivo | Módulo / Proceso | Rango RF/RNF | Tipo |
| :-: | :--- | :--- | :--- | :--- |
| **—** | [PLANTILLA_REQUERIMIENTOS.md](PLANTILLA_REQUERIMIENTOS.md) | Formato estándar y guía de redacción | — | Plantilla |
| **01** | [01_catalogos_configuracion.md](01_catalogos_configuracion.md) | Tipos de Catálogo, Entradas, Monedas y Tasas de Cambio | `RF-01` a `RF-07` | Funcional |
| **02** | [02_servicios.md](02_servicios.md) | Servicios del Hotel, Tarifas, Galería y Portal Web | `RF-08` a `RF-16` | Funcional |
| **03** | [03_habitaciones_espacios.md](03_habitaciones_espacios.md) | Habitaciones, Clonación, Espacios Jerárquicos y Mobiliario Físico | `RF-17` a `RF-28` | Funcional |
| **04** | [04_reservas_estancias.md](04_reservas_estancias.md) | Reservas Públicas por Pasos, Bloqueo de Días, Stripe, Check-in y Check-out | `RF-29` a `RF-41` | Funcional |
| **05** | [05_restaurante_pos.md](05_restaurante_pos.md) | Platos, Recetas, Materia Prima, KDS, Consumo FEFO, Mapa de Mesas y POS | `RF-42` a `RF-59` | Funcional |
| **06** | [06_facturacion_caja.md](06_facturacion_caja.md) | Series DGI, Folios, Emisión Fiscal, Anulaciones, Pasarelas y Cortes de Caja | `RF-60` a `RF-69` | Funcional |
| **07** | [07_inventario_almacenes.md](07_inventario_almacenes.md) | Productos, SKU, Lotes FEFO, Cuarentena, Traslados, Inventario Físico y Kits | `RF-70` a `RF-84` | Funcional |
| **08** | [08_compras_proveedores.md](08_compras_proveedores.md) | Solicitudes, Cotizaciones, Scoring, Órdenes de Compra y Recepciones | `RF-85` a `RF-96` | Funcional |
| **09** | [09_activos_fijos.md](09_activos_fijos.md) | Registro Patrimonial, Individualización, Mantenimientos, Garantías y Bajas | `RF-97` a `RF-109` | Funcional |
| **10** | [10_limpieza_housekeeping.md](10_limpieza_housekeeping.md) | Turnos, Planificación, Materialización Diaria, Consumos y Recordatorios | `RF-110` a `RF-120` | Funcional |
| **11** | [11_promociones_paquetes.md](11_promociones_paquetes.md) | Promociones, Combos Polimórficos, Descuentos y Ofertas Web | `RF-121` a `RF-128` | Funcional |
| **12** | [12_clientes_usuarios_seguridad.md](12_clientes_usuarios_seguridad.md) | Registro Natural/Jurídico, Conflictos Levenshtein, Roles y Seguridad | `RF-129` a `RF-139` | Funcional |
| **13** | [13_background_jobs_auditoria.md](13_background_jobs_auditoria.md) | Tareas Programadas (No-Shows, Caducidades, Mantenimientos) y Auditoría | `RF-140` a `RF-146` | Funcional |
| **14** | [14_requerimientos_no_funcionales.md](14_requerimientos_no_funcionales.md) | Desempeño, Seguridad PCI-DSS, Integridad ACID, Disponibilidad y Calidad | `RNF-01` a `RNF-12` | No Funcional |
| **15** | [15_pruebas_k6_rendimiento.md](15_pruebas_k6_rendimiento.md) | Pruebas de Carga, Estrés, Concurrencia y Regresión de Rendimiento con k6 | `RF-147` a `RF-152` / `RNF-13` a `RNF-14` | Pruebas / Calidad |

---

## Formato Estándar Obligatorio

Todos los requerimientos funcionales y no funcionales siguen la convención técnica solicitada:

```markdown
* **RF-XX:** El software [verbo en futuro/presente] [acción/proceso], mostrando en pantalla [datos/resultados].
* **RF-XX:** El S.I. permitirá [función], solicitando [campos obligatorios] y verificando [criterio de validación].
* **RF-XX:** Se asignará [código/ID] con el cual se [control/regla de negocio].
* *Excepción/Regla:* Se podrán modificar los datos a excepción de [campos inmutables].

##### Requerimientos de [Proceso Condicional / Transaccional]
* **RF-XX:** [Nombre del proceso]: 1) [Paso principal de verificación]
  * i) [Condición A]: El sistema ejecutará [Acción A].
  * ii) [Condición B]: El sistema aplicará [Acción B/Multa/Reversión].
```
