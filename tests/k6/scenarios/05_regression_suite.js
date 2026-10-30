// tests/k6/scenarios/05_regression_suite.js
// Suite de Prueba de Regresión de Rendimiento y Carga Continua

import { check, sleep, group } from 'k6';
import http from 'k6/http';
import { Trend, Rate, Counter } from 'k6/metrics';
import { BASE_URL, standardThresholds, getStandardHeaders } from '../config.js';

// Métricas personalizadas
const publicPageLatency = new Trend('public_page_latency_ms');
const errorRate = new Rate('system_error_rate');
const transactionCounter = new Counter('total_transactions');

export const options = {
    stages: [
        { duration: '15s', target: 10 }, // Ramp-up inicial
        { duration: '30s', target: 25 }, // Carga sostenida (regresión)
        { duration: '20s', target: 50 }, // Pico de estrés / concurrencia
        { duration: '15s', target: 0 },  // Ramp-down
    ],
    thresholds: {
        ...standardThresholds,
        public_page_latency_ms: ['p(95)<1500', 'avg<800'],
        system_error_rate: ['rate<0.02'],
    },
};

export default function () {
    const headers = getStandardHeaders();

    group('Regresión - Páginas Públicas Principales', () => {
        const endpoints = [
            '/',
            '/servicios',
            '/habitaciones',
            '/restaurante',
            '/login',
            '/registro',
        ];

        // Seleccionar aleatoriamente un endpoint para distribuir carga o recorrerlos
        for (const ep of endpoints) {
            const start = Date.now();
            const res = http.get(`${BASE_URL}${ep}`, {
                headers,
                tags: { endpoint: ep },
            });
            const duration = Date.now() - start;

            publicPageLatency.add(duration);
            transactionCounter.add(1);

            const isSuccess = check(res, {
                [`${ep} responde con éxito (200/302)`]: (r) => r.status === 200 || r.status === 302,
                [`${ep} responde en menos de 1500ms`]: (r) => r.timings.duration < 1500,
            });

            if (!isSuccess) {
                errorRate.add(1);
            } else {
                errorRate.add(0);
            }

            sleep(0.5);
        }
    });
}
