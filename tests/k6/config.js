// tests/k6/config.js
// Configuración base para suites de pruebas k6 en Hotel Bugambilias

// Resolver BASE_URL con prioridad:
// 1. Variable de entorno __ENV.BASE_URL
// 2. Variable de entorno __ENV.APP_URL
// 3. APP_URL leída dinámicamente desde el archivo .env
// 4. 'http://localhost' como fallback por defecto
function resolveBaseUrl() {
    if (__ENV.BASE_URL && __ENV.BASE_URL.trim() !== '') {
        return __ENV.BASE_URL.trim().replace(/\/+$/, '');
    }

    if (__ENV.APP_URL && __ENV.APP_URL.trim() !== '') {
        return __ENV.APP_URL.trim().replace(/\/+$/, '');
    }

    try {
        const envContent = open('../../.env');
        const match = envContent.match(/^APP_URL=(.+)$/m);

        if (match && match[1]) {
            const url = match[1].trim().replace(/^["']|["']$/g, '');

            if (url) {
                return url.replace(/\/+$/, '');
            }
        }
    } catch {
        // En caso de que .env no esté disponible, se utiliza el fallback
    }

    return 'http://localhost';
}

export const BASE_URL = resolveBaseUrl();

// Umbrales de calidad y regresión del sistema (RNF-01 / RNF-13 / RNF-14)
// En entorno de desarrollo local (Herd / Windows / APP_DEBUG=true), el arranque en frío y Vite elevan la latencia.
// Para forzar el umbral estricto de producción (<1500ms), pasar STRICT_THRESHOLDS=true.
const p95Threshold = __ENV.STRICT_THRESHOLDS === 'true' ? 'p(95)<1500' : 'p(95)<6000';
const p99Threshold = __ENV.STRICT_THRESHOLDS === 'true' ? 'p(99)<3000' : 'p(99)<10000';

export const standardThresholds = {
    http_req_duration: [p95Threshold, p99Threshold],
    // Tasa de errores HTTP < 1%
    http_req_failed: ['rate<0.01'],
};

// Cabeceras HTTP estándar para emular navegador Inertia / JSON
export function getStandardHeaders(custom = {}) {
    return {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language': 'es-NI,es;q=0.9,en;q=0.8',
        'User-Agent': 'k6-Regression-Runner/HotelBugambilias',
        ...custom,
    };
}

export function getInertiaHeaders(version = '1.0', custom = {}) {
    return {
        'Accept': 'text/html, application/xhtml+xml',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Inertia': 'true',
        'X-Inertia-Version': version,
        'Content-Type': 'application/json',
        ...custom,
    };
}
