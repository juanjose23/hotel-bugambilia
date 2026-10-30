// tests/k6/scenarios/03_restaurante_pos.js
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

    group('03. Restaurante, Menú y TPV', () => {
        // 1. Cargar vista pública del restaurante y menú
        let resRest = http.get(`${BASE_URL}/restaurante`, { headers, tags: { name: 'GET /restaurante' } });
        check(resRest, {
            'Menú de restaurante carga con éxito (200)': (r) => r.status === 200,
            'Tiempo de respuesta de menú es ágil (<1500ms)': (r) => r.timings.duration < 1500,
        });

        sleep(1);
    });
}
