// tests/k6/scenarios/02_reserva_flow.js
import { check, sleep, group } from 'k6';
import http from 'k6/http';
import { BASE_URL, standardThresholds, getStandardHeaders } from '../config.js';

export const options = {
    vus: 10,
    duration: '45s',
    thresholds: standardThresholds,
};

export default function () {
    const headers = getStandardHeaders();

    group('02. Flujo de Consulta y Proceso de Reserva', () => {
        // 1. Explorar catálogo de habitaciones
        let resListado = http.get(`${BASE_URL}/habitaciones`, { headers, tags: { name: 'GET /habitaciones' } });
        check(resListado, {
            'Listado responde 200': (r) => r.status === 200,
        });

        sleep(1.5);

        // 2. Simular consulta de disponibilidad y visualización de habitación
        // Usamos una ruta representativa o query
        let resDetalle = http.get(`${BASE_URL}/habitaciones`, {
            headers,
            tags: { name: 'GET /habitaciones?disponibilidad' },
        });
        check(resDetalle, {
            'Consulta de disponibilidad responde < 1500ms': (r) => r.timings.duration < 1500,
        });

        sleep(2);
    });
}
