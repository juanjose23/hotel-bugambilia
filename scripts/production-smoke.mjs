import { spawnSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { copyFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
process.chdir(root);
const project = `hotel-production-smoke-${process.pid}`;
const directory = resolve('.local', project);
mkdirSync(directory, { recursive: true });
const envFile = resolve(directory, 'smoke.env');
const productionCompose = resolve(directory, 'production.yaml');
const smokeCompose = resolve(directory, 'smoke.yaml');
copyFileSync('compose.production.yaml', productionCompose);
copyFileSync('docker/compose.production-smoke.yaml', smokeCompose);
const image = process.env.PRODUCTION_IMAGE || 'hotel-bugambilia:production';
const revision = process.env.EXPECTED_REVISION || 'development';
const port = process.env.SMOKE_PORT || '8097';

if (!/^[a-zA-Z0-9._/:@-]+$/.test(image) || !/^\d+$/.test(port)) {
    throw new Error('Imagen o puerto de prueba inválidos.');
}

writeFileSync(
    envFile,
    [
        `HOTEL_IMAGE=${image}`,
        `APP_KEY=base64:${randomBytes(32).toString('base64')}`,
        'APP_URL=https://production-smoke.example.invalid',
        'TRUSTED_PROXIES=172.16.0.0/12,10.0.0.0/8,192.168.0.0/16',
        'DB_HOST=smoke-db',
        'DB_DATABASE=hotel_production_smoke',
        'DB_USERNAME=smoke',
        'DB_PASSWORD=smoke-only',
        `SMOKE_PORT=${port}`,
        '',
    ].join('\n'),
);
const compose = [
    'compose',
    '--project-name',
    project,
    '--env-file',
    envFile,
    '-f',
    productionCompose,
    '-f',
    smokeCompose,
];
function run(args, capture = false) {
    const result = spawnSync('docker', [...compose, ...args], {
        cwd: root,
        encoding: 'utf8',
        stdio: capture ? 'pipe' : 'inherit',
    });

    if (result.status !== 0 || result.error) {
        throw new Error(`Falló la comprobación Docker: ${args[0]}.`);
    }

    return result.stdout;
}

try {
    run(['up', '-d', '--wait', '--wait-timeout', '240']);
    const base = `http://127.0.0.1:${port}`;

    for (const path of ['/up', '/admin/login']) {
        const response = await fetch(`${base}${path}`, {
            signal: AbortSignal.timeout(15000),
        });

        if (response.status !== 200) {
            throw new Error(`${path}: HTTP ${response.status}.`);
        }

        console.log(`${path}: HTTP 200`);
    }

    const proxiedLogin = await fetch(`${base}/admin/login`, {
        headers: {
            'X-Forwarded-Proto': 'https',
            'X-Forwarded-Host': 'production-smoke.example.invalid',
            'X-Forwarded-Port': '443',
        },
        signal: AbortSignal.timeout(15000),
    });
    const loginHtml = await proxiedLogin.text();

    if (
        proxiedLogin.status !== 200 ||
        loginHtml.includes('http://production-smoke.example.invalid') ||
        !loginHtml.includes('https://production-smoke.example.invalid')
    ) {
        throw new Error(
            'La aplicación no reconoce correctamente HTTPS detrás del proxy.',
        );
    }

    const response = await fetch(`${base}/health-release.txt`, {
        signal: AbortSignal.timeout(15000),
    });

    if (!response.ok || (await response.text()).trim() !== revision) {
        throw new Error('La revisión de la imagen no coincide.');
    }

    run([
        'exec',
        '-T',
        'app',
        'sh',
        '-c',
        'test ! -f .env && test ! -d tests && test ! -d node_modules && test ! -f vendor/bin/pest && test -L public/storage',
    ]);
    run([
        'exec',
        '-T',
        'app',
        'php',
        '-r',
        "$config = require 'bootstrap/cache/config.php'; exit($config['app']['env'] === 'production' && $config['app']['debug'] === false && $config['session']['secure'] === true && $config['queue']['default'] === 'database' ? 0 : 1);",
    ]);
    const stateOutput = run(['ps', '--all', '--format', 'json'], true).trim();
    const states = stateOutput.startsWith('[')
        ? JSON.parse(stateOutput)
        : stateOutput.split(/\r?\n/).map((line) => JSON.parse(line));

    if (
        !states.some(
            (service) =>
                service.Service === 'release' &&
                service.State === 'exited' &&
                service.ExitCode === 0,
        )
    ) {
        throw new Error(
            'La migración de producción no finalizó correctamente.',
        );
    }

    for (const [override, message] of [
        ['APP_DEBUG=true', 'APP_DEBUG'],
        [
            'APP_KEY=base64:n1M8EUQLYLSLBZaUDcTrUqV0aGsChkJHm/K6q38jiAU=',
            'APP_KEY',
        ],
    ]) {
        const rejected = spawnSync(
            'docker',
            [
                ...compose,
                'run',
                '--rm',
                '--no-deps',
                '-e',
                override,
                'app',
                'php',
                'docker/validate-production.php',
            ],
            { cwd: root, encoding: 'utf8' },
        );

        if (
            rejected.status === 0 ||
            !`${rejected.stdout}${rejected.stderr}`.includes(message)
        ) {
            throw new Error(
                `No se rechazó la configuración incorrecta: ${message}.`,
            );
        }
    }

    const running = run(['ps', '--status', 'running', '--services'], true)
        .trim()
        .split(/\r?\n/);

    for (const service of ['app', 'worker', 'scheduler', 'smoke-db']) {
        if (!running.includes(service)) {
            throw new Error(`El servicio ${service} no está funcionando.`);
        }
    }

    console.log(
        'Producción: migraciones, aplicación, colas, scheduler, revisión y contenido de imagen correctos.',
    );
} catch (error) {
    run(['logs', '--tail', '80']);

    throw error;
} finally {
    run(['down', '--volumes', '--remove-orphans']);
}
