# Requerimientos del Sistema: Pruebas de Carga y Regresión de Rendimiento con k6

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos de la Suite de Pruebas de Regresión con k6
* **RF-147:** El software dispondrá de una suite automatizada de pruebas de rendimiento y regresión continua implementada con k6 (`tests/k6/scenarios/05_regression_suite.js`), mostrando en pantalla y en terminal métricas detalladas de latencia media, percentil 95 (p95), percentil 99 (p99), tasa de errores HTTP y rendimiento de transacciones por segundo (RPS).
* **RF-148:** El S.I. permitirá ejecutar pruebas de humo (_Smoke Tests_) sobre los módulos públicos (`tests/k6/scenarios/01_smoke_landing.js`), solicitando la URL base del entorno y verificando que el 100% de las rutas públicas críticas (Home, Servicios, Habitaciones, Restaurante) respondan con código HTTP 200 y sin regresión funcional.
* **RF-149:** El S.I. simulará la concurrencia de reservas de hotel y consultas de disponibilidad (`tests/k6/scenarios/02_reserva_flow.js`), solicitando peticiones simultáneas de usuarios virtuales (VUs) y verificando que la evaluación de días ocupados y cálculo de tarifas mantenga tiempos de respuesta estables sin bloqueos en base de datos.
* **RF-150:** El S.I. evaluará el comportamiento del menú gastronómico y TPV de restaurante (`tests/k6/scenarios/03_restaurante_pos.js`), verificando la estabilidad en la entrega de platillos activos y mapa de mesas durante picos de servicio.
* **RF-151:** Se asignará una configuración centralizada de umbrales y cabeceras (`tests/k6/config.js`) con la cual se estandarizarán los criterios de aceptación para todos los escenarios de prueba.
* *Excepción/Regla:* Las pruebas de carga deberán ejecutarse en entornos de pruebas o con base de datos de prueba para evitar la alteración de folios fiscales o reservas de producción.

##### Requerimientos del Proceso de Evaluación de Regresión de Rendimiento
* **RF-152:** Proceso de Evaluación de Regresión de Rendimiento en Pipeline o Despliegues:
  1) Ejecución automatizada de la suite de regresión (`npm run test:k6` o `vendor/bin/pest stress` mediante `pestphp/pest-plugin-stressless` integrado con k6):
     * i) Condición A (Cumplimiento de umbrales: p95 < 1500 ms y tasa de error < 1%): El sistema certificará que la versión bajo prueba no introduce regresiones de rendimiento y autorizará la promoción o despliegue a producción.
     * ii) Condición B (Incumplimiento de umbrales: p95 ≥ 1500 ms o tasa de error ≥ 1% debido a cuellos de botella en consultas N+1 o bloqueos de concurrencia): El sistema marcará la ejecución como fallida con código de salida diferente de cero, emitirá el reporte de métricas degradadas y bloqueará el paso a producción hasta que se optimicen las consultas o índices correspondientes.

---

#### 3.2. Requerimientos No Funcionales (RNF)

##### Umbrales de Rendimiento y Resistencia con k6
* **RNF-13:** La suite de regresión k6 validará que el percentil 95 (p95) del tiempo de respuesta del servidor se mantenga estrictamente por debajo de 1.5 segundos (1500 ms) bajo una carga sostenida de hasta 50 usuarios virtuales concurrentes.
* **RNF-14:** La tasa de fallos de peticiones HTTP (`http_req_failed`) en todos los escenarios de k6 no deberá exceder el 1.0% (`rate < 0.01`) durante toda la duración de las pruebas de regresión y estrés.
