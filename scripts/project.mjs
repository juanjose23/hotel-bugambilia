import { spawn, spawnSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
process.chdir(root);
let php = process.env.PHP_BINARY || 'php';

// En Windows algunos gestores publican php.bat; PHP_BINARY devuelve el ejecutable real.
if (!process.env.PHP_BINARY) {
    const discovery =
        process.platform === 'win32'
            ? spawnSync('php scripts/php-binary.php', {
                  shell: true,
                  encoding: 'utf8',
              })
            : spawnSync('php', ['scripts/php-binary.php'], {
                  encoding: 'utf8',
              });

    if (discovery.status === 0 && discovery.stdout.trim()) {
        php = discovery.stdout.trim();
    }
}

const task = process.argv[2];
const isGitHook = ['pre-commit', 'pre-push'].includes(task);
const args = process.argv.slice(3);
const commands = {
    format: [
        process.execPath,
        ['node_modules/prettier/bin/prettier.cjs', '--check', 'resources/'],
    ],
    lint: [
        process.execPath,
        [
            'node_modules/eslint/bin/eslint.js',
            'resources/js',
            'scripts',
            '.husky/install.mjs',
        ],
    ],
    types: [process.execPath, ['node_modules/typescript/bin/tsc', '--noEmit']],
    pint: [php, ['vendor/bin/pint', '--test']],
    phpstan: [
        php,
        ['vendor/bin/phpstan', 'analyse', '--level=9', '--memory-limit=1G'],
    ],
    build: [process.execPath, ['node_modules/vite/bin/vite.js', 'build']],
};
function run(binary, parameters, env = process.env) {
    const label =
        binary === php ? 'php' : binary === process.execPath ? 'node' : binary;
    console.log(`> ${label} ${parameters.join(' ')}`);
    const result = spawnSync(binary, parameters, {
        cwd: root,
        env,
        stdio: 'inherit',
    });

    if (result.error) {
        throw new Error(
            `No se pudo ejecutar ${binary}. Instala PHP 8.4+ y Node 22.12+ en tu equipo. ${result.error.message}`,
        );
    }

    if (result.status !== 0) {
        throw new Error(
            `La comprobación terminó con código ${result.status ?? result.signal}.`,
        );
    }
}
function testEnvironment(mode) {
    const result = spawnSync(php, ['scripts/test-environment.php', mode], {
        cwd: root,
        env: process.env,
        encoding: 'utf8',
    });

    if (result.status !== 0 || result.error) {
        throw new Error(
            result.stderr || result.stdout || `No se encontró ${php}.`,
        );
    }

    return { ...process.env, ...JSON.parse(result.stdout) };
}
function test() {
    const env = testEnvironment('testing');
    run(
        php,
        ['-d', 'memory_limit=1G', 'vendor/pestphp/pest/bin/pest', ...args],
        env,
    );
}
function setupDusk(env) {
    run(php, ['artisan', 'migrate:fresh', '--force', '--no-interaction'], env);
    run(
        php,
        [
            'artisan',
            'db:seed',
            '--class=Database\\Seeders\\DuskSeeder',
            '--force',
            '--no-interaction',
        ],
        env,
    );
}
async function dusk() {
    const env = testEnvironment('dusk');
    setupDusk(env);
    let server;

    try {
        const url = new URL(env.APP_URL);

        if (env.DUSK_EXTERNAL_SERVER !== '1') {
            if (
                !['localhost', '127.0.0.1'].includes(url.hostname) ||
                url.protocol !== 'http:'
            ) {
                throw new Error(
                    'Para Dusk local usa APP_URL=http://127.0.0.1:8010, o DUSK_EXTERNAL_SERVER=1 con un servidor de pruebas dedicado.',
                );
            }

            const port = url.port || '80';

            try {
                await fetch(url, { signal: AbortSignal.timeout(500) });

                throw new Error(
                    `El puerto ${port} ya está ocupado. Usa otro puerto dedicado a Dusk.`,
                );
            } catch (error) {
                if (error.message.includes('ya está ocupado')) {
                    throw error;
                }
            }

            server = spawn(
                php,
                [
                    '-S',
                    `127.0.0.1:${port}`,
                    '-t',
                    'public',
                    'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php',
                ],
                { cwd: root, env, stdio: 'ignore' },
            );
            server.on('error', () => {});
        }

        let ready = false;

        for (let attempt = 0; attempt < 60; attempt++) {
            try {
                const response = await fetch(url, {
                    signal: AbortSignal.timeout(2000),
                });

                if (response.ok) {
                    ready = true;
                    break;
                }
            } catch {
                /* El servidor todavía inicia. */
            }

            await new Promise((resolve) => setTimeout(resolve, 500));
        }

        if (!ready) {
            throw new Error(
                'El servidor Dusk no respondió. Comprueba APP_URL y el contenedor dusk-app.',
            );
        }

        // El proceso padre debe seguir atendiendo las peticiones del navegador local.
        const status = await new Promise((resolveStatus, reject) => {
            const child = spawn(
                php,
                [
                    '-d',
                    'memory_limit=1G',
                    'vendor/pestphp/pest/bin/pest',
                    '-c',
                    'phpunit.dusk.xml',
                    ...args,
                ],
                { cwd: root, env, stdio: 'inherit' },
            );
            child.once('error', reject);
            child.once('exit', resolveStatus);
        });

        if (status !== 0) {
            throw new Error(
                `Dusk terminó con código ${status}. Revisa tests/Browser/screenshots y console.`,
            );
        }
    } finally {
        server?.kill();
    }
}

try {
    const mode = isGitHook
        ? 'native'
        : (process.env.HOTEL_CHECK_MODE ??
          (existsSync('.hotel-checks.json')
              ? JSON.parse(readFileSync('.hotel-checks.json', 'utf8')).mode
              : 'native'));

    if (!['native', 'docker'].includes(mode)) {
        throw new Error(
            'HOTEL_CHECK_MODE o .hotel-checks.json debe indicar native o docker.',
        );
    }

    if (mode === 'docker') {
        const profile = task?.startsWith('dusk') ? 'dusk' : 'quality';
        run('docker', [
            'compose',
            '--profile',
            profile,
            'run',
            '--rm',
            '--build',
            profile === 'dusk' ? 'dusk' : 'tools',
            'node',
            'scripts/project.mjs',
            task,
            ...args,
        ]);
        process.exit(0);
    }

    if (!existsSync('vendor/autoload.php')) {
        throw new Error(
            'Instala las dependencias locales con composer install antes de ejecutar las comprobaciones.',
        );
    }

    switch (task) {
        case 'test':
            test();
            break;
        case 'dusk':
            await dusk();
            break;
        case 'dusk:setup':
            setupDusk(testEnvironment('dusk'));
            break;
        case 'pre-commit':
            for (const check of ['format', 'lint', 'types', 'pint']) {
                run(...commands[check]);
            }

            break;
        case 'pre-push':
            for (const check of ['phpstan']) {
                run(...commands[check]);
            }

            test();
            run(...commands.build);
            break;
        case 'check':
            for (const check of [
                'format',
                'lint',
                'types',
                'pint',
                'phpstan',
            ]) {
                run(...commands[check]);
            }

            test();
            run(...commands.build);
            break;
        default:
            throw new Error(
                'Usa: node scripts/project.mjs pre-commit | pre-push | check | test | dusk:setup | dusk',
            );
    }
} catch (error) {
    console.error(error.message);
    process.exitCode = 1;
}
