import { resolve } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';
import { fileURLToPath } from 'node:url';

export function deploymentConfiguration(env) {
    function required(name) {
        const value = env[name]?.trim();

        if (!value) {
            throw new Error(`Falta configurar ${name}.`);
        }

        return value;
    }
    function secureUrl(value, name) {
        const url = new URL(value);

        if (url.protocol !== 'https:' || url.username || url.password) {
            throw new Error(
                `${name} debe ser una URL HTTPS sin credenciales integradas.`,
            );
        }

        return url;
    }
    const platform = required('DEPLOY_PLATFORM');
    const token = required('DEPLOY_TOKEN');
    const target = secureUrl(required('DEPLOY_URL'), 'DEPLOY_URL');
    const publicUrl = secureUrl(required('PRODUCTION_URL'), 'PRODUCTION_URL');
    const revision = required('EXPECTED_REVISION');

    if (
        !['coolify', 'dokploy'].includes(platform) ||
        !/^[a-f0-9]{40}-[0-9]+-[0-9]+$/.test(revision)
    ) {
        throw new Error('Plataforma o revisión de producción inválidas.');
    }

    const composeId =
        platform === 'dokploy' ? required('DOKPLOY_COMPOSE_ID') : null;
    const headers =
        platform === 'coolify'
            ? { Authorization: `Bearer ${token}` }
            : { 'x-api-key': token, 'Content-Type': 'application/json' };

    return {
        target,
        publicUrl,
        revision,
        request: {
            method: 'POST',
            headers,
            redirect: 'error',
            ...(composeId
                ? { body: JSON.stringify({ composeId, freshVolumes: false }) }
                : {}),
        },
    };
}

export async function deployProduction({
    env = process.env,
    fetchImpl = fetch,
    now = Date.now,
    wait = delay,
    timeout = 600000,
    validateOnly = false,
} = {}) {
    const { target, publicUrl, revision, request } =
        deploymentConfiguration(env);

    if (validateOnly) {
        console.log('Configuración de despliegue completa.');

        return;
    }

    const response = await fetchImpl(target, {
        ...request,
        signal: AbortSignal.timeout(30000),
    });

    if (!response.ok) {
        throw new Error(
            `La plataforma rechazó el despliegue: HTTP ${response.status}.`,
        );
    }

    console.log(
        'Despliegue solicitado. Verificando la revisión publicada durante un máximo de 10 minutos.',
    );
    const deadline = now() + timeout;

    while (now() < deadline) {
        try {
            const release = await fetchImpl(
                new URL(`/health-release.txt?revision=${revision}`, publicUrl),
                {
                    cache: 'no-store',
                    signal: AbortSignal.timeout(10000),
                    redirect: 'error',
                },
            );

            if (release.ok && (await release.text()).trim() === revision) {
                const checks = await Promise.all(
                    ['/up', '/admin/login'].map((path) =>
                        fetchImpl(new URL(path, publicUrl), {
                            cache: 'no-store',
                            signal: AbortSignal.timeout(10000),
                            redirect: 'error',
                        }),
                    ),
                );

                if (checks.every((check) => check.status === 200)) {
                    console.log(`Producción verificada: ${revision}.`);

                    return;
                }
            }
        } catch {
            // La plataforma puede estar reemplazando contenedores; se reintenta la lectura.
        }

        await wait(5000);
    }

    throw new Error(
        'La plataforma aceptó la solicitud, pero producción no confirmó la nueva revisión y su estado. Revisa el despliegue.',
    );
}

if (
    process.argv[1] &&
    resolve(process.argv[1]) === fileURLToPath(import.meta.url)
) {
    await deployProduction({
        validateOnly: process.argv.includes('--validate'),
    });
}
