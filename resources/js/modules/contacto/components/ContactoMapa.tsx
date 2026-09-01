import { MapPin, Navigation, Car, ShieldCheck } from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';

export const ContactoMapa = () => {
    const enlaceGoogleMaps =
        'https://maps.google.com/?q=Hotel+Bugambilias+Esteli+Nicaragua';
    const enlaceWaze =
        'https://waze.com/ul?q=Hotel%20Bugambilias%20Esteli%20Nicaragua';

    return (
        <div className="flex flex-col justify-between rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-8">
            <div>
                <div className="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-0.5 text-[10px] font-black text-emerald-600 uppercase dark:text-emerald-400">
                    <MapPin className="size-3" />
                    <span>Ubicación Estratégica</span>
                </div>

                <h3 className="mt-3 text-lg font-black text-foreground sm:text-xl">
                    Cómo Llegar al Hotel
                </h3>

                <p className="mt-2 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                    Estamos ubicados en la <strong>Salida Sur de Estelí</strong>
                    , en una zona residencial tranquila, segura y de fácil
                    acceso sobre la carretera Panamericana norte.
                </p>

                <div className="mt-4 space-y-2.5 rounded-2xl border border-border/80 bg-background/60 p-4 text-xs">
                    <div className="flex items-start gap-2 text-foreground">
                        <MapPin className="size-4 shrink-0 text-primary dark:text-rose-400" />
                        <span className="font-semibold">
                            Salida Sur Estelí, Restaurante Absoluto 1c. Oeste,
                            2c. Sur, 1c. Oeste.
                        </span>
                    </div>
                    <div className="flex items-center gap-2 text-muted-foreground">
                        <Car className="size-4 shrink-0 text-amber-500" />
                        <span>A sólo 5 minutos del centro de la ciudad.</span>
                    </div>
                    <div className="flex items-center gap-2 text-muted-foreground">
                        <ShieldCheck className="size-4 shrink-0 text-emerald-500" />
                        <span>Parqueo privado cerrado y vigilado 24h.</span>
                    </div>
                </div>
            </div>

            {/* Mapa Interactivo Embed / Botones de Navegación GPS */}
            <div className="mt-6">
                <div className="relative aspect-16/9 w-full overflow-hidden rounded-2xl border border-border bg-muted">
                    <iframe
                        title="Mapa de ubicación Hotel Bugambilias Estelí"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15549.330837583647!2d-86.36873138384955!3d13.088656686153676!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8f718c35399580a1%3A0xe54d8efdfb6b9e59!2sEstel%C3%AD%2C%20Nicaragua!5e0!3m2!1ses!2sni!4v1700000000000!5m2!1ses!2sni"
                        className="h-full w-full border-0"
                        allowFullScreen
                        loading="lazy"
                        referrerPolicy="no-referrer-when-downgrade"
                    />
                </div>

                <div className="mt-4 flex flex-wrap items-center gap-2.5">
                    <a
                        href={enlaceGoogleMaps}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={buttonVariants({
                            variant: 'outline',
                            size: 'sm',
                            className:
                                'cursor-pointer rounded-full border-border bg-card text-xs font-bold text-foreground hover:bg-accent active:scale-95',
                        })}
                    >
                        <Navigation className="size-3.5 text-primary dark:text-rose-400" />
                        <span>Abrir en Google Maps</span>
                    </a>

                    <a
                        href={enlaceWaze}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={buttonVariants({
                            variant: 'outline',
                            size: 'sm',
                            className:
                                'cursor-pointer rounded-full border-border bg-card text-xs font-bold text-foreground hover:bg-accent active:scale-95',
                        })}
                    >
                        <Car className="size-3.5 text-sky-500" />
                        <span>Abrir en Waze</span>
                    </a>
                </div>
            </div>
        </div>
    );
};

export default ContactoMapa;
