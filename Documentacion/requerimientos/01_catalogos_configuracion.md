# Requerimientos del Sistema: Catálogos y Configuración Base

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Tipos de Catálogo
* **RF-01:** El software listará todos los tipos de catálogo del sistema (Cargos, Departamentos, Tipos de Cliente, Categorías de Producto, etc.), mostrando en pantalla el código identificador, nombre descriptivo y estado operativo (Activo/Inactivo).
* **RF-02:** El S.I. permitirá registrar y actualizar tipos de catálogo, solicitando código único y nombre descriptivo, verificando que el código no exista previamente en el sistema ni contenga caracteres especiales inválidos.
* **RF-03:** Se asignará un código nemotécnico único (ej. `TIPO_PROMOCION`, `CATEGORIA_SERVICIO`, `UNIDAD_MEDIDA`) con el cual se vincularán programáticamente las entidades del dominio sin depender de IDs numéricos volátiles.
* *Excepción/Regla:* Se podrán modificar el nombre descriptivo y el estado operativo a excepción del código nemotécnico del tipo de catálogo, el cual permanecerá inmutable para preservar la integridad referencial del código fuente.

##### Requerimientos del Módulo de Entradas de Catálogo
* **RF-04:** El software mostrará las entradas de catálogo agrupadas y filtradas por su tipo de catálogo correspondiente, mostrando en pantalla código, nombre, tipo asociado, valor o metadata opcional y estado.
* **RF-05:** El S.I. permitirá crear y editar entradas de catálogo, solicitando tipo de catálogo, código único por tipo y nombre, verificando que no existan duplicados dentro del mismo tipo de catálogo.
* **RF-06:** Se asignará un identificador secuencial y código normalizado con el cual se relacionarán las llaves foráneas en servicios, habitaciones, compras, inventario y facturación.
* *Excepción/Regla:* Las entradas de catálogo del sistema que tengan registros vinculados en tablas transaccionales no podrán ser eliminadas físicamente, permitiéndose únicamente su desactivación lógica.

##### Requerimientos del Proceso de Configuración de Monedas y Tasas de Cambio
* **RF-07:** Proceso de Actualización de Tasa de Cambio Oficial (NIO / USD):
  1) Verificación de la fecha de vigencia y valor de la tasa de cambio ingresada:
     * i) Condición A (Tasa de cambio válida con fecha igual o posterior al día actual): El sistema registrará la nueva tasa oficial, actualizará la referencia para conversiones automáticas en ventas y reservas, y mantendrá el histórico de tasas anteriores sin alterar transacciones ya cerradas.
     * ii) Condición B (Tasa de cambio con valor cero, negativo o fecha retroactiva a periodos contables cerrados): El sistema rechazará la operación, alertará al usuario y mantendrá como vigente la última tasa oficial registrada.
