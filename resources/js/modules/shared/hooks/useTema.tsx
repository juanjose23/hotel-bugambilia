import { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
type Tema = 'light' | 'dark' | 'system';
type TipoContextoTema = {
    tema: Tema;
    definirTema: (tema: Tema) => void;
    alternarTema: () => void;
};
const ContextoTema = createContext<TipoContextoTema | undefined>(undefined);
const obtenerTemaSistema = (): 'light' | 'dark' => {
    if (typeof window === 'undefined') {
        return 'light';
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
};
const obtenerTemaAlmacenado = (): Tema | null => {
    try {
        const almacenado = localStorage.getItem('theme');

        if (
            almacenado === 'light' ||
            almacenado === 'dark' ||
            almacenado === 'system'
        ) {
            return almacenado;
        }
    } catch {
        // localStorage no disponible
    }

    return null;
};
const aplicarTema = (tema: Tema) => {
    const resuelto = tema === 'system' ? obtenerTemaSistema() : tema;
    document.documentElement.classList.toggle('dark', resuelto === 'dark');
};
export const ProveedorTema = ({
    children,
    temaInicial = 'system',
}: {
    children: ReactNode;
    temaInicial?: Tema;
}) => {
    const [tema, setTemaState] = useState<Tema>(() => {
        return obtenerTemaAlmacenado() ?? temaInicial;
    });
    const definirTema = (nuevoTema: Tema) => {
        setTemaState(nuevoTema);

        try {
            localStorage.setItem('theme', nuevoTema);
        } catch {
            // localStorage no disponible
        }

        aplicarTema(nuevoTema);
    };
    const alternarTema = () => {
        definirTema(tema === 'dark' ? 'light' : 'dark');
    };
    useEffect(() => {
        aplicarTema(tema);
    }, [tema]);
    useEffect(() => {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const manejador = () => {
            if (tema === 'system') {
                aplicarTema('system');
            }
        };
        mediaQuery.addEventListener('change', manejador);

        return () => mediaQuery.removeEventListener('change', manejador);
    }, [tema]);

    return (
        <ContextoTema.Provider value={{ tema, definirTema, alternarTema }}>
            {children}
        </ContextoTema.Provider>
    );
};
export const useTema = (): TipoContextoTema => {
    const contexto = useContext(ContextoTema);

    if (!contexto) {
        throw new Error('useTema debe usarse dentro de un ProveedorTema');
    }

    return contexto;
};
