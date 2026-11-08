# Guía Operativa y de Arquitectura: Cajas, Arqueos y Operación con Cajero Único

## 1. Visión General y Propósito

El sistema de Tesorería y Cajas del **Hotel Bugambilias** permite gestionar de forma flexible e integrada los cobros y liquidaciones del hotel, admitiendo dos modalidades operativas según la demanda y el horario:

1. **Operación Multiárea (Temporada alta / Puntos simultáneos):** Múltiples colaboradores operan en simultáneo diferentes cajas físicas (Recepción, Restaurante, Bar Piscina, Eventos).
2. **Operación con Cajero Único (Turno nocturno / Baja ocupación):** Un solo colaborador centraliza la custodia de dinero y los cobros de todas las cuentas del hotel desde una sola caja física activa.

---

## 2. Catálogo y Ubicaciones Físicas de Cajas

Cada caja operativa (`tes_cajas`) está normalizada y vinculada a:
- **Tipo de Caja (`tipo_caja_id`):** Clasificación en la tabla `catalogos` bajo el tipo `TIPO_CAJA`.
  - `CAJA_FRONT_DESK`: Recepción y atención a huéspedes.
  - `CAJA_POS_RESTAURANTE`: Punto de venta de alimentos y bebidas.
  - `CAJA_BAR_PISCINA`: Bar, terraza y área húmeda.
  - `CAJA_EVENTOS`: Salón de conferencias, banquetes y bodas.
  - `CAJA_CHICA_OPERATIVA`: Fondo rotativo para compras menores y urgencias.
  - `CAJA_GENERAL`: Bóveda central y custodia de tesorería.
- **Ubicación Física (`ubicacion_id`):** Referencia jerárquica a la tabla `ubicaciones` (Edificio > Sector > Zona).

### Cajas Operativas del Hotel:
- **`CJ-RECEP-01`** — *Caja Recepción Principal* (Sector Recepción / Lobby)
- **`CJ-REST-01`** — *Caja Restaurante Bugambilias* (Sector Restaurante)
- **`CJ-PISC-01`** — *Caja Bar de Piscina* (Sector Área de Piscina)
- **`CJ-EVENT-01`** — *Caja Banquetes y Eventos* (Sector Salón de Eventos)
- **`CJ-CHICA-01`** — *Caja Chica Administración* (Edificio Principal)

---

## 3. Escenario: Operación con Cajero Único (Turno Nocturno)

Cuando solo hay un cajero de servicio (ejemplo: noche o guardia reducida):

### 3.1. Apertura del Turno
1. El colaborador ingresa a **Mi turno de caja** (`/admin/cajas/tesoreria/turno`).
2. Abre únicamente la caja donde custodiará el dinero en efectivo (ej. `CJ-RECEP-01` — *Caja Recepción Principal*).
3. Ingresa el fondo recibido en córdobas (NIO) y dólares (USD).
4. Las restantes cajas (`CJ-REST-01`, `CJ-PISC-01`, etc.) quedan en estado **`Disponible / Sin turno activo`**. No generan descuadres, no exigen arqueos y no bloquean ninguna operación.

### 3.2. Centralización de Cobros de Diferentes Áreas
El cajero único puede liquidar cobros de **cualquier módulo del hotel**:
- **Cuentas de Estancia / Habitaciones:** Check-in, liquidación de habitación o abonos.
- **Cuentas de Restaurante / Room Service:** Comandas de cocina y pedidos nocturnos.
- **Cuentas de Bar o Servicios:** Bebidas, lavandería o traslados.

Al procesar un cobro en efectivo con `CobrarDesdeCaja` o `RegistrarPagoCuenta`:
1. El sistema consulta `$cajas->activaUsuario($cajeroId)`.
2. Identifica la sesión activa en `CJ-RECEP-01`.
3. Registra el movimiento de `cobro` (y `vuelto` si aplica) exclusivamente en esa sesión.
4. Las cajas de otras áreas (`CJ-REST-01`) se mantienen con 0 movimientos.

### 3.3. Arqueo y Cierre Individual
Al terminar la jornada:
- El colaborador arquea y cuenta **únicamente** su gaveta física.
- El saldo esperado suma exactamente:
  $$\text{Fondo inicial} + \text{Cobros en efectivo recibidos} - \text{Vueltos entregados} + \text{Ingresos} - \text{Egresos}$$
- Al registrar el conteo real, la sesión se cierra (`cerrada`) y el sistema queda 100% cuadrado.

---

## 4. Cobros en Efectivo vs. Cobros Electrónicos

| Canal | Método de Pago | ¿Afecta la Gaveta / Turno? | Destino del Dinero |
| :--- | :--- | :---: | :--- |
| **Mostrador / Turno** | **Efectivo** | **SÍ** | Incrementa el saldo físico de la gaveta (`MovimientoCaja`). Exige turno activo. |
| **Mostrador / Turno** | **Tarjeta POS física** | **NO** | Disminuye el saldo de la cuenta; se audita con el voucher del POS. |
| **Mostrador / Turno** | **Transferencia Bancaria** | **NO** | Disminuye el saldo de la cuenta; se audita en *Comprobantes de transferencia*. |
| **Portal Web Huéspedes** | **Stripe / Pasarela** | **NO** | Disminuye el saldo de la cuenta; ingresa a la cuenta bancaria del hotel. |

> [!IMPORTANT]
> Ningún cobro en efectivo puede realizarse sin que el usuario tenga un turno de caja abierto. Si lo intenta, el sistema arroja la excepción: *"Abre tu turno de caja antes de recibir efectivo."*

---

## 5. El Abono del 50% en Reservaciones

1. **Creación con Política 50%:** La reserva se registra con `TipoPagoReserva::ABONO_50`.
2. **Generación de Cuenta:** Se abre la cuenta con los cargos de la estancia y se aplica el abono inicial correspondiente al 50%.
   - Si se abona en efectivo presencialmente, entra al turno del cajero en recepción.
   - Si se abona online (Stripe), entra a la cuenta bancaria sin alterar el arqueo físico.
3. **Saldo Pendiente (50%):** Permanece como saldo en el folio y en la reserva.
4. **Liquidación en Check-out:** En el momento de la salida, el huésped liquida el 50% restante (más consumos adicionales). Si paga en efectivo, ese saldo ingresa a la caja del colaborador que realiza el check-out.

---

## 6. Monitoreo y Auditoría de Cajas

En el panel administrativo:
- **Gestión de cajas (`/admin/cajas/tesoreria/cajas`):** Muestra el colaborador asignado a cada caja y el tiempo que lleva abierta (ej. *Abierto hace 2 horas*). Incluye botón **"Ver turno"** para inspeccionar saldos y movimientos en vivo.
- **Arqueos y cierres (`/admin/cajas/tesoreria/cierres`):** Muestra la duración exacta de cada turno (ej. *7 horas 30 minutos*), cantidad de movimientos y permite auditar el desglose completo de transacciones por moneda.
