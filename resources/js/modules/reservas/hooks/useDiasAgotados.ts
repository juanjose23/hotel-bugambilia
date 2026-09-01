import { useQuery } from '@tanstack/react-query';

interface UseDiasAgotadosProps {
    slug?: string;
    diasIniciales?: string[];
    meses?: number;
    adultos?: number;
    ninos?: number;
}

interface DiasAgotadosResponse {
    dias_agotados: string[];
}

interface UseDiasAgotadosReturn {
    diasAgotados: string[];
    cargando: boolean;
    error: string | null;
    recargar: () => Promise<void>;
}

export const fetchDiasAgotados = async (
    slug: string,
    meses = 18,
    adultos?: number,
    ninos?: number,
    signal?: AbortSignal,
): Promise<string[]> => {
    const params = new URLSearchParams({ meses: String(meses) });

    if (typeof adultos === 'number' && adultos > 0) {
        params.append('adultos', String(adultos));
    }

    if (typeof ninos === 'number' && ninos > 0) {
        params.append('ninos', String(ninos));
    }

    const response = await fetch(
        `/habitaciones/${encodeURIComponent(slug)}/dias-agotados?${params}`,
        {
            headers: { Accept: 'application/json' },
            signal,
        },
    );

    if (!response.ok) {
        throw new Error(
            `Error al consultar días agotados (${response.status})`,
        );
    }

    const data: DiasAgotadosResponse = await response.json();

    return Array.isArray(data.dias_agotados) ? data.dias_agotados : [];
};

export const useDiasAgotados = ({
    slug,
    diasIniciales = [],
    meses = 18,
    adultos,
    ninos,
}: UseDiasAgotadosProps): UseDiasAgotadosReturn => {
    const {
        data: diasAgotados = diasIniciales,
        isLoading: cargando,
        error: rawError,
        refetch,
    } = useQuery({
        queryKey: [
            'habitaciones',
            slug,
            'dias-agotados',
            meses,
            adultos,
            ninos,
        ],
        queryFn: ({ signal }) =>
            fetchDiasAgotados(slug!, meses, adultos, ninos, signal),
        enabled: Boolean(slug),
        initialData: diasIniciales.length > 0 ? diasIniciales : undefined,
    });

    const error = rawError instanceof Error ? rawError.message : null;

    const recargar = async () => {
        await refetch();
    };

    return {
        diasAgotados: diasAgotados || [],
        cargando,
        error,
        recargar,
    };
};

export default useDiasAgotados;
