interface RestauranteStepperReservaProps {
    pasoActual: 1 | 2 | 3;
    alHacerClic: (paso: 1 | 2 | 3) => void;
}

const PASOS = [
    { numero: 1 as const, titulo: 'Mesa & Horario', detalle: 'Plano y fecha' },
    { numero: 2 as const, titulo: 'Pre-ordenar Menú', detalle: 'Opcional' },
    {
        numero: 3 as const,
        titulo: 'Confirmación',
        detalle: 'Datos de contacto',
    },
];

export const RestauranteStepperReserva = ({
    pasoActual,
    alHacerClic,
}: RestauranteStepperReservaProps) => {
    return (
        <div className="mb-8 flex items-center justify-between rounded-3xl border border-border/80 bg-card p-4 shadow-xs">
            {PASOS.map((paso, index) => (
                <>
                    {index > 0 && (
                        <div className="mx-2 h-px flex-1 bg-border/80" />
                    )}
                    <div
                        className={`flex flex-1 items-center gap-3 ${
                            index === 0
                                ? ''
                                : index === 1
                                  ? 'justify-center'
                                  : 'justify-end'
                        }`}
                    >
                        <button
                            type="button"
                            onClick={() => alHacerClic(paso.numero)}
                            className={`flex size-9 cursor-pointer items-center justify-center rounded-2xl text-xs font-black transition-all ${
                                pasoActual === paso.numero
                                    ? 'bg-primary text-primary-foreground shadow-md'
                                    : 'bg-muted text-muted-foreground'
                            }`}
                        >
                            {paso.numero}
                        </button>
                        <div className="hidden sm:block">
                            <span className="block text-xs font-black text-foreground">
                                {paso.titulo}
                            </span>
                            <span className="text-[10px] text-muted-foreground">
                                {paso.detalle}
                            </span>
                        </div>
                    </div>
                </>
            ))}
        </div>
    );
};

export default RestauranteStepperReserva;
