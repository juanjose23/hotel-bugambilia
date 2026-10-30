// tests/k6/scenarios/01_smoke_landing.js
import { check, sleep, group } from 'k6';
import http from 'k6/http';
import { BASE_URL, standardThresholds, getStandardHeaders } from '../config.js';

export const options = {
    vus: 5,
    duration: '30s',
    thresholds: standardThresholds,
};

export default function () {
    const headers = getStandardHeaders();

    group('01. Portal Público - Home y Catálogos', () => {
        // 1. Home
        let resHome = http.get(`${BASE_URL}/`, { headers, tags: { name: 'GET /' } });
        check(resHome, {
            'Home status es 200': (r) => r.status === 200,
            'Home contiene hotel o bugambilia': (r) => r.body && (r.body.includes('Bugambilias') || r.body.includes('hotel')),
        });

        sleep(1);

        // 2. Servicios
        let resServicios = http.get(`${BASE_URL}/servicios`, { headers, tags: { name: 'GET /servicios' } });
        check(resServicios, {
            'Servicios status es 200': (r) => r.status === 200,
        });

        sleep(1);

        // 3. Habitaciones
        let resHabitaciones = http.get(`${BASE_URL}/habitaciones`, { headers, tags: { name: 'GET /habitaciones' } });
        check(resHabitaciones, {
            'Habitaciones status es 200': (r) => r.status === 200,
        });

        sleep(1);

        // 4. Restaurante
        let resRestaurante = http.get(`${BASE_URL}/restaurante`, { headers, tags: { name: 'GET /restaurante' } });
        check(resRestaurante, {
            'Restaurante status es 200': (r) => r.status === 200,
        });

        sleep(1);
    });
}
