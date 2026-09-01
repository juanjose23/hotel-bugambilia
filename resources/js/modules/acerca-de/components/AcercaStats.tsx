import type { EstadisticaHotel } from '../types';

const ESTADISTICAS: EstadisticaHotel[] = [
    {
        cifra: '3+',
        etiqueta: 'Años de Experiencia',
        descripcion: 'Hospitalidad y tradición hotelera en Estelí.',
    },
    {
        cifra: '10k+',
        etiqueta: 'Huéspedes Atendidos',
        descripcion: 'Viajeros nacionales e internacionales satisfechos.',
    },
    {
        cifra: '99%',
        etiqueta: 'Calificación Positiva',
        descripcion: 'Opiniones y recomendaciones sobresalientes.',
    },
    {
        cifra: '24/7',
        etiqueta: 'Atención Permanente',
        descripcion: 'Recepción y asistencia continua a su servicio.',
    },
];

export const AcercaStats = () => {
    return (
        <section
            aria-label="Cifras de Hotel Bugambilias"
            className="border-y border-border bg-card py-10 md:py-14"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="grid grid-cols-2 gap-6 lg:grid-cols-4 lg:gap-8">
                    {ESTADISTICAS.map((stat, idx) => (
                        <div key={idx} className="flex flex-col text-center">
                            <span className="text-3xl font-black tracking-tight text-bugambilia-600 sm:text-4xl dark:text-bugambilia-400">
                                {stat.cifra}
                            </span>
                            <span className="mt-1 text-xs font-black tracking-wider text-foreground uppercase">
                                {stat.etiqueta}
                            </span>
                            <p className="mt-1 text-[11px] text-muted-foreground">
                                {stat.descripcion}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default AcercaStats;
