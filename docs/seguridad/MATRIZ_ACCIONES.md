# Matriz de Acciones y Permisos (P2P)

Este documento detalla la lógica de visibilidad de las acciones en el sistema, diferenciando entre el control de acceso (Shield) y la lógica de negocio (Estado del Registro).

## 1. Tipos de "Visibilidad" en Filament

Existen dos capas de seguridad que controlan si un botón o acción aparece para el usuario:

### A. Capa de Autorización (Shield)
Controla el acceso según el **Rol del Usuario**.
- **Implementación**: `auth()->user()->can('Compras:ImprimirSolicitud')`
- **Requisito**: El permiso debe existir en la BD y estar asignado al rol en Shield UI.
- **Configuración**: `config/filament-shield.php` (Arreglo `custom_permissions`).

### B. Capa de Lógica de Negocio (Estado)
Controla la disponibilidad según el **Estado del Registro**.
- **Implementación**: `->visible(fn ($record) => $record->estado === Estado::...)`
- **Requisito**: Que el registro cumpla con las reglas de integridad del flujo.

---

## 2. Módulo de Compras (P2P)

### Solicitudes de Compra
| Acción | Permiso Shield | Lógica de Estado (Negocio) |
| :--- | :--- | :--- |
| **Crear** | `Create:Solicitud` | N/A |
| **Editar** | `Update:Solicitud` | Solo si estado es `Borrador`. |
| **Eliminar** | `Delete:Solicitud` | Solo si estado es `Borrador`. |
| **Aprobar** | N/A | Solo si estado es `Borrador` o `Pendiente`. |
| **Rechazar** | N/A | Solo si estado es `Pendiente`. |
| **Cancelar** | N/A | Solo si estado es `Aprobada` y no tiene OCs vinculadas. |
| **Imprimir** | `Compras:ImprimirSolicitud` | Visible en cualquier estado (siempre que no esté en papelera). |

### Cotizaciones
| Acción | Permiso Shield | Lógica de Estado (Negocio) |
| :--- | :--- | :--- |
| **Ver Comparativa** | `Compras:ViewComparativaCotizaciones` | Solo si la solicitud tiene cotizaciones y no hay una ganadora aún. |
| **Imprimir Comparativa** | `Compras:ImprimirComparativa` | Disponible en la página de comparativa. |
| **Imprimir Cotización** | `Compras:ImprimirCotizacion` | Visible en cualquier estado. |

### Órdenes de Compra
| Acción | Permiso Shield | Lógica de Estado (Negocio) |
| :--- | :--- | :--- |
| **Emitir (OC)** | N/A | Solo si estado es `Borrador`. |
| **Imprimir OC** | `Compras:ImprimirOrdenCompra` | Visible en cualquier estado. |

### Recepciones
| Acción | Permiso Shield | Lógica de Estado (Negocio) |
| :--- | :--- | :--- |
| **Imprimir Recepción** | `Compras:ImprimirRecepcion` | Visible en cualquier estado. |
| **Confirmar Recepción** | N/A | Solo si estado es `Pendiente`. Transiciones: `Completa`, `Parcial`, `ConDiscrepancia`, `Rechazada`, `EnCuarentena`. |
| **Editar Recepción** | N/A | Solo si estado es `Pendiente`. |
| **Eliminar Recepción** | N/A | Solo si estado es `Pendiente`. |

### Devoluciones
| Acción | Permiso Shield | Lógica de Estado (Negocio) |
| :--- | :--- | :--- |
| **Crear** | `Create:DevolucionCompra` | N/A |
| **Editar** | `Update:DevolucionCompra` | Solo si estado es `Borrador`. |
| **Eliminar** | `Delete:DevolucionCompra` | Solo si estado es `Borrador`. |
| **Confirmar** | `Compras:ConfirmarDevolucion` | Solo si estado es `Borrador`. Descuenta stock y libera PO. |
| **Imprimir** | `Compras:ImprimirDevolucion` | Visible en cualquier estado. |

---

## 3. Matriz de Permisos Personalizados (Custom)

Estos permisos deben estar registrados en `config/filament-shield.php` bajo la clave `custom_permissions`.

| Clave del Permiso | Aplicación |
| :--- | :--- |
| `Compras:ImprimirSolicitud` | Botón Imprimir en Solicitudes. |
| `Compras:ImprimirCotizacion` | Botón Imprimir en Cotizaciones. |
| `Compras:ImprimirOrdenCompra` | Botón Imprimir en Órdenes de Compra. |
| `Compras:ImprimirRecepcion` | Botón Imprimir en Recepciones. |
| `Compras:ImprimirReportesCompras` | Acceso a reportes gerenciales de compras. |
| `Compras:ImprimirComparativa` | Botón de impresión en el dashboard de comparativa. |
| `Compras:ViewComparativaSolicitud` | Acceso a la página de Comparativa de Solicitud. |
| `Compras:ViewComparativaCotizaciones` | Acceso a la página de Comparativa de Cotizaciones. |
| `Compras:ImprimirDevolucion` | Botón Imprimir en Devoluciones. |
| `Compras:ConfirmarDevolucion` | Botón Confirmar en Devoluciones (descuenta stock). |

### Inventario
| Clave del Permiso | Aplicación |
| :--- | :--- |
| `Inventario:ReporteStock` | Acceso al reporte HTB-INV-001 (Stock por Producto) |
| `Inventario:ReporteMovimientos` | Acceso al reporte HTB-INV-003 (Movimientos) |
| `Inventario:ReporteCuarentena` | Acceso al reporte HTB-INV-004 (Lotes en Cuarentena) |
| `Inventario:ReporteProximosVencer` | Acceso al reporte HTB-INV-005 (Próximos a Vencer) |
| `Inventario:ReporteMermas` | Acceso al reporte HTB-INV-006 (Mermas) |
| `Inventario:ReporteValorizacion` | Acceso al reporte HTB-INV-007 (Valorización) |
| `Inventario:ReporteRotacion` | Acceso al reporte HTB-INV-008 (Rotación) |
| `Inventario:ReporteMermasTotales` | Acceso al reporte HTB-INV-009 (Mermas Totales) |
| `Inventario:ReporteTrazabilidad` | Acceso al reporte HTB-INV-011 (Trazabilidad) |
| `Inventario:ReporteVencidos` | Acceso al reporte HTB-INV-012 (Lotes Vencidos) |

### Servicios
| Clave del Permiso | Aplicación |
| :--- | :--- |
| `Servicios:ReporteHistoricoPrecios` | Acceso al reporte HTB-SER-001 (Histórico de Precios) |

---

## 4. Sensibilidad a Mayúsculas (PostgreSQL)

**IMPORTANTE:** El sistema utiliza PostgreSQL, el cual es sensible a mayúsculas. 
- Los permisos definidos en `config/filament-shield.php` se guardan en la base de datos exactamente como se definen (PascalCase).
- Al usar `can('...')` en el código, el nombre debe coincidir **carácter por carácter**.

Ejemplo:
- ✅ Correcto: `auth()->user()->can('Compras:ImprimirSolicitud')`
- ❌ Incorrecto: `auth()->user()->can('compras:imprimir_solicitud')`
