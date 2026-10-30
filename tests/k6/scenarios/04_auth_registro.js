// tests/k6/scenarios/04_auth_registro.js
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

    group('04. Autenticación y Registro', () => {
        // 1. Cargar formulario de login
        let resLogin = http.get(`${BASE_URL}/login`, { headers, tags: { name: 'GET /login' } });
        check(resLogin, {
            'Página de Login carga 200': (r) => r.status === 200,
        });

        sleep(1);

        // 2. Cargar formulario de registro público
        let resRegistro = http.get(`${BASE_URL}/registro`, { headers, tags: { name: 'GET /registro' } });
        check(resRegistro, {
            'Página de Registro responde (200/302)': (r) => r.status === 200 || r.status === 302,
        });

        sleep(1);
    });
}
