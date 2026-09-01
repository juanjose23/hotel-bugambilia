import { Heart, MapPin, Award, Trees } from 'lucide-react';

export const AcercaHistoria = () => {
    return (
        <section
            aria-labelledby="titulo-historia"
            className="border-y border-border/50 bg-card/30 py-16 md:py-24"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-16">
                    {/* Contenido Editorial Humano (6 cols) */}
                    <div className="lg:col-span-6">
                        <div className="inline-flex items-center gap-2 text-xs font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                            <Trees className="size-4" aria-hidden="true" />
                            <span>Nuestras Raíces en Estelí</span>
                        </div>

                        <h2
                            id="titulo-historia"
                            className="mt-3 text-2xl font-black tracking-tight text-foreground sm:text-4xl"
                        >
                            Una tradición familiar nacida para brindar descanso
                            honesto y hospitalario.
                        </h2>

                        <div className="mt-6 space-y-4 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                            <p>
                                Hotel Bugambilias fue fundado con un propósito
                                claro: ofrecer en la salida sur de Estelí un
                                punto de encuentro donde el viajero no se sienta
                                en un establecimiento frío o distante, sino en
                                la calidez de un hogar bien cuidado.
                            </p>
                            <p>
                                Inspirados por las bugambilias que florecen con
                                fuerza y alegría en las tierras segovianas,
                                creamos patios amplios, corredores coloniales y
                                jardines que invitan a bajar el ritmo del día,
                                respirar la brisa fresca del norte y disfrutar
                                de un café bien servido.
                            </p>
                            <p>
                                Con el paso de los años, nos llena de orgullo
                                ver regresar a familias, turistas y
                                profesionales que encuentran en nuestro hotel su
                                parada de confianza y descanso asegurado en cada
                                visita a Nicaragua.
                            </p>
                        </div>

                        {/* Puntos destacados */}
                        <div className="mt-8 grid grid-cols-1 gap-4 border-t border-border/60 pt-6 sm:grid-cols-2">
                            <div className="flex items-start gap-3">
                                <div className="dark:bg-bugambilia-950/80 flex size-9 shrink-0 items-center justify-center rounded-2xl bg-bugambilia-100 text-bugambilia-600 dark:text-bugambilia-400">
                                    <MapPin
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <h3 className="text-xs font-black text-foreground">
                                        Fácil Acceso en Estelí
                                    </h3>
                                    <p className="text-[11px] text-muted-foreground">
                                        Salida sur, ubicación tranquila lejos
                                        del tráfico pesado.
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-start gap-3">
                                <div className="dark:bg-bugambilia-950/80 flex size-9 shrink-0 items-center justify-center rounded-2xl bg-bugambilia-100 text-bugambilia-600 dark:text-bugambilia-400">
                                    <Heart
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <h3 className="text-xs font-black text-foreground">
                                        Trato Personal y Noble
                                    </h3>
                                    <p className="text-[11px] text-muted-foreground">
                                        Atención esmerada por personas que aman
                                        servir.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Imagen y Composición Visual (6 cols) */}
                    <div className="relative lg:col-span-6">
                        <div className="relative mx-auto max-w-md overflow-hidden rounded-3xl border border-border bg-card shadow-xl lg:max-w-none">
                            <div className="aspect-4/3 w-full overflow-hidden bg-muted">
                                <img
                                    src="/images/service-events.webp"
                                    alt="Instalaciones coloniales y jardines de Hotel Bugambilias en Estelí"
                                    className="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                                    loading="lazy"
                                />
                            </div>

                            {/* Badge Flotante Superior */}
                            <div className="absolute top-4 right-4 flex items-center gap-2 rounded-2xl border border-border/80 bg-background/95 px-3.5 py-2 shadow-lg backdrop-blur-md dark:bg-card/90">
                                <Award
                                    className="size-4 text-amber-500"
                                    aria-hidden="true"
                                />
                                <div>
                                    <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                        Compromiso
                                    </span>
                                    <span className="text-xs font-black text-foreground">
                                        Hospitalidad de Corazón
                                    </span>
                                </div>
                            </div>

                            {/* Banner Inferior */}
                            <div className="border-t border-border bg-card/95 p-5 backdrop-blur-sm dark:bg-card/90">
                                <blockquote className="text-xs text-muted-foreground italic">
                                    "Queremos que al cruzar nuestras puertas
                                    sientas la tranquilidad de haber llegado a
                                    un lugar donde de verdad te cuidan."
                                </blockquote>
                                <cite className="mt-1.5 block text-[11px] font-black text-foreground not-italic">
                                    — La Dirección y Personal de Hotel
                                    Bugambilias
                                </cite>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default AcercaHistoria;
