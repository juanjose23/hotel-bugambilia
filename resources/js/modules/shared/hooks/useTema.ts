import { useSyncExternalStore } from 'react';

export type Tema = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'theme';

function obtenerTemaInicial(): Tema {
    if (typeof window === 'undefined') {
        return 'system';
    }

    return (localStorage.getItem(STORAGE_KEY) as Tema) || 'system';
}

function aplicarTemaAlDOM(tema: Tema): void {
    if (typeof window === 'undefined') {
        return;
    }

    const root = document.documentElement;
    root.classList.remove('light', 'dark');

    if (tema === 'system') {
        const esOscuro = window.matchMedia(
            '(prefers-color-scheme: dark)',
        ).matches;
        root.classList.add(esOscuro ? 'dark' : 'light');
    } else {
        root.classList.add(tema);
    }
}

let temaActual: Tema = obtenerTemaInicial();
const suscriptores = new Set<() => void>();

export function establecerTema(nuevoTema: Tema): void {
    temaActual = nuevoTema;

    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, nuevoTema);
        aplicarTemaAlDOM(nuevoTema);
    }

    suscriptores.forEach((fn) => fn());
}

export function alternarTema(): void {
    const siguiente = temaActual === 'dark' ? 'light' : 'dark';
    establecerTema(siguiente);
}

function suscribir(callback: () => void) {
    suscriptores.add(callback);

    return () => suscriptores.delete(callback);
}

function getSnapshot(): Tema {
    return temaActual;
}

function getServerSnapshot(): Tema {
    return 'system';
}

// Aplicar tema inicial inmediatamente sin esperar render
if (typeof window !== 'undefined') {
    aplicarTemaAlDOM(temaActual);
}

export function useTema() {
    const tema = useSyncExternalStore(
        suscribir,
        getSnapshot,
        getServerSnapshot,
    );

    return {
        tema,
        establecerTema,
        alternarTema,
    };
}

export default useTema;
