import assert from 'node:assert/strict';
import test from 'node:test';
import {
    deploymentConfiguration,
    deployProduction,
} from './deploy-production.mjs';

const env = {
    DEPLOY_PLATFORM: 'coolify',
    DEPLOY_TOKEN: 'synthetic-token',
    DEPLOY_URL: 'https://deploy.example.invalid/api/deploy',
    PRODUCTION_URL: 'https://hotel.example.invalid',
    EXPECTED_REVISION: `${'a'.repeat(40)}-100-2`,
};

test('rechaza configuración incompleta y transporte sin HTTPS antes de desplegar', () => {
    assert.throws(
        () => deploymentConfiguration({ ...env, DEPLOY_TOKEN: '' }),
        /DEPLOY_TOKEN/,
    );
    assert.throws(
        () =>
            deploymentConfiguration({
                ...env,
                DEPLOY_URL: 'http://example.invalid',
            }),
        /HTTPS/,
    );
    assert.throws(
        () => deploymentConfiguration({ ...env, EXPECTED_REVISION: 'latest' }),
        /revisión/,
    );
    assert.throws(
        () => deploymentConfiguration({ ...env, DEPLOY_PLATFORM: 'dokploy' }),
        /DOKPLOY_COMPOSE_ID/,
    );
});

test('Coolify exige revisión nueva y dos páginas sanas antes de dar éxito', async () => {
    const calls = [];
    await deployProduction({
        env,
        fetchImpl: async (url, options) => {
            calls.push({ url: String(url), options });

            return new Response(
                String(url).includes('health-release')
                    ? env.EXPECTED_REVISION
                    : 'ok',
            );
        },
    });
    assert.equal(calls.length, 4);
    assert.equal(calls[0].options.method, 'POST');
    assert.equal(
        calls[0].options.headers.Authorization,
        'Bearer synthetic-token',
    );
    assert.ok(calls[2].url.endsWith('/up'));
    assert.ok(calls[3].url.endsWith('/admin/login'));
});

test('Dokploy envía el compose correcto conservando sus volúmenes', () => {
    const config = deploymentConfiguration({
        ...env,
        DEPLOY_PLATFORM: 'dokploy',
        DOKPLOY_COMPOSE_ID: 'hotel-compose',
    });
    assert.equal(config.request.headers['x-api-key'], 'synthetic-token');
    assert.deepEqual(JSON.parse(config.request.body), {
        composeId: 'hotel-compose',
        freshVolumes: false,
    });
});

test('un intento anterior del mismo commit con HTTP 200 no confirma el despliegue', async () => {
    let clock = 0;
    let probes = 0;
    await assert.rejects(
        deployProduction({
            env,
            now: () => (clock += 10),
            timeout: 40,
            wait: async () => {},
            fetchImpl: async (url) => {
                if (String(url).includes('health-release')) {
                    probes += 1;

                    return new Response(`${'a'.repeat(40)}-100-1`);
                }

                return new Response('queued');
            },
        }),
        /no confirmó/,
    );
    assert.ok(probes > 0);
});

test('una revisión nueva con login fallido tampoco confirma el despliegue', async () => {
    let clock = 0;
    await assert.rejects(
        deployProduction({
            env,
            now: () => (clock += 10),
            timeout: 40,
            wait: async () => {},
            fetchImpl: async (url) =>
                new Response(
                    String(url).includes('health-release')
                        ? env.EXPECTED_REVISION
                        : '',
                    {
                        status: String(url).endsWith('/admin/login')
                            ? 500
                            : 200,
                    },
                ),
        }),
        /no confirmó/,
    );
});

test('un rechazo de la API detiene el despliegue sin consultas posteriores', async () => {
    let calls = 0;
    await assert.rejects(
        deployProduction({
            env,
            fetchImpl: async () => {
                calls += 1;

                return new Response('', { status: 401 });
            },
        }),
        /HTTP 401/,
    );
    assert.equal(calls, 1);
});
