# Suite de Pruebas de Carga y Regresión de Rendimiento con k6 — Hotel Bugambilias

Esta suite contiene pruebas automatizadas de rendimiento, estrés y regresión continua para garantizar que ningún cambio de código degrade la velocidad de respuesta, introduzca consultas N+1 o cause bloqueos de concurrencia en la plataforma.

---

## 🎯 Requerimientos y Criterios de Aceptación (RNF-01 / RNF-13 / RNF-14)

- **Percentil 95 (p95):** Latencia inferior a **1,500 ms** bajo carga concurrente.
- **Percentil 99 (p99):** Latencia inferior a **3,000 ms**.
- **Tasa de error HTTP:** Inferior al **1%** (`rate < 0.01`).

---

## 📁 Estructura de la Suite

```text
tests/k6/
├── config.js                      # Configuración base, URL y umbrales estándar
├── scenarios/
│   ├── 01_smoke_landing.js        # Smoke test: Home, servicios, habitaciones, restaurante
│   ├── 02_reserva_flow.js         # Flujo de disponibilidad y reservas
│   ├── 03_restaurante_pos.js      # Menú y TPV de restaurante
│   ├── 04_auth_registro.js        # Carga de formularios de login y registro
│   └── 05_regression_suite.js     # Suite maestra de regresión con ramp-up y estrés
└── README.md                      # Esta guía de uso
```

---

## 🚀 Comandos de Ejecución

Antes de ejecutar las pruebas, asegúrese de que el servidor web esté en funcionamiento:
- **Con Laravel Herd:** El servidor responde directamente en `http://localhost` o `http://hotel-bugambilia.test` (puerto 80 estándar, **sin** `:8000`).
- **Con Artisan:** `php artisan serve` (puerto 8000).

### 1. Opción Laravel / Pest Stressless (k6 para PHP):
El proyecto cuenta con `pestphp/pest-plugin-stressless` (desarrollado por el equipo de Pest y motorizado por k6):
```bash
# Probar el portal con Pest Stressless contra Herd
npm run test:stress

# O indicando el dominio de Herd directamente:
vendor/bin/pest stress http://localhost --concurrency=5
vendor/bin/pest stress http://hotel-bugambilia.test --concurrency=5
```

### 2. Vía NPM Scripts con k6 nativo:

| Comando | Escenario | Propósito |
| :--- | :--- | :--- |
| `npm run test:k6:smoke` | `01_smoke_landing.js` | Comprobación rápida de rutas públicas activas (5 VUs, 30s) |
| `npm run test:k6:reserva` | `02_reserva_flow.js` | Carga sobre motor de reservas y disponibilidad (10 VUs, 45s) |
| `npm run test:k6:restaurante` | `03_restaurante_pos.js` | Carga sobre menú y pedidos de restaurante (10 VUs, 45s) |
| `npm run test:k6:auth` | `04_auth_registro.js` | Comprobación de formularios de acceso y registro (5 VUs, 30s) |
| `npm run test:k6` | `05_regression_suite.js` | **Suite completa de regresión:** ramp-up hasta 50 VUs simultáneos |

---

## ⚙️ Parámetros Personalizados

Para apuntar a un servidor remoto, staging o puerto diferente:

```bash
k6 run -e BASE_URL=https://staging.hotelbugambilias.com tests/k6/scenarios/05_regression_suite.js
```

Para generar un reporte HTML interactivo con k6-reporter:

```bash
k6 run --out json=tests/k6/reporte.json tests/k6/scenarios/05_regression_suite.js
```
