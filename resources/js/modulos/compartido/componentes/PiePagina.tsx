import { Link } from '@inertiajs/react';
import {
    MessageCircle,
    Mail,
    Phone,
    MapPin,
    ArrowUpCircle,
    Globe,
    Star,
    ShieldCheck,
    Award,
} from 'lucide-react';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
interface PropiedadesPiePagina {
    hotelInfo?: {
        nombre?: string;
        telefono?: string;
        email?: string;
        direccion?: string;
    };
}
export const PiePagina = ({ hotelInfo }: PropiedadesPiePagina) => {
    const { hotel } = usePropiedadesPagina();
    const nombre = hotelInfo?.nombre || hotel?.name || 'Hotel Bugambilias';
    const telefono = hotelInfo?.telefono || hotel?.telefono || '+505 8713 6805';
    const email =
        hotelInfo?.email || hotel?.email || 'recepcion@bugambiliashotel.com';
    const direccion =
        hotelInfo?.direccion ||
        hotel?.direccion ||
        'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste, Estelí, Nicaragua';
    const irArriba = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <footer className="relative overflow-hidden border-t border-gray-900 bg-gray-950 pt-16 pb-20 font-sans text-white md:pb-10">
            <div className="relative z-10 container mx-auto px-4 sm:px-6">
                <div className="mb-12 grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">
                    {/* Info de la marca */}
                    <div className="lg:col-span-4">
                        <Link href="/" className="group mb-6 inline-block">
                            <span className="block text-2xl font-black tracking-tighter text-white">
                                HOTEL{' '}
                                <span className="text-bugambilia-500">
                                    BUGAMBILIAS
                                </span>
                            </span>
                            <span className="-mt-1 flex items-center gap-1 text-[9px] font-bold tracking-[0.3em] text-amber-400/90 uppercase">
                                <Star className="h-3 w-3 fill-amber-400" />
                                <Star className="h-3 w-3 fill-amber-400" />
                                <Star className="h-3 w-3 fill-amber-400" />
                                <Star className="h-3 w-3 fill-amber-400" />
                                <Star className="h-3 w-3 fill-amber-400" />
                                <span className="ml-1">Estelí, Nicaragua</span>
                            </span>
                        </Link>
                        <p className="mb-6 max-w-sm text-sm leading-relaxed text-gray-400">
                            Hospitalidad exclusiva y confort con el encanto
                            auténtico del norte de Nicaragua. Más de 35 años
                            brindando experiencias memorables.
                        </p>
                        <div className="flex items-center gap-3">
                            <a
                                href="https://facebook.com"
                                target="_blank"
                                rel="noreferrer"
                                aria-label="Sitio Web"
                                className="group flex h-10 w-10 items-center justify-center rounded-full border border-gray-800 bg-gray-900 transition-all hover:border-bugambilia-500 hover:bg-bugambilia-600"
                            >
                                <Globe className="h-4 w-4 text-gray-400 group-hover:text-white" />
                            </a>
                            <a
                                href={`https://wa.me/${telefono.replace(/[^0-9]/g, '')}`}
                                target="_blank"
                                rel="noreferrer"
                                aria-label="WhatsApp Concierge"
                                className="group flex h-10 w-10 items-center justify-center rounded-full border border-gray-800 bg-gray-900 transition-all hover:border-bugambilia-500 hover:bg-bugambilia-600"
                            >
                                <MessageCircle className="h-4 w-4 text-gray-400 group-hover:text-white" />
                            </a>
                            <a
                                href={`mailto:${email}`}
                                aria-label="Correo electrónico"
                                className="group flex h-10 w-10 items-center justify-center rounded-full border border-gray-800 bg-gray-900 transition-all hover:border-bugambilia-500 hover:bg-bugambilia-600"
                            >
                                <Mail className="h-4 w-4 text-gray-400 group-hover:text-white" />
                            </a>
                        </div>
                    </div>

                    {/* Enlaces rápidos */}
                    <div className="lg:col-span-2">
                        <h4 className="mb-6 text-[10px] font-black tracking-[0.2em] text-bugambilia-400 uppercase">
                            Navegación
                        </h4>
                        <ul className="space-y-3">
                            {[
                                { etiqueta: 'Inicio', href: '/' },
                                {
                                    etiqueta: 'Habitaciones',
                                    href: '/habitaciones',
                                },
                                {
                                    etiqueta: 'Espacios & Eventos',
                                    href: '/espacios',
                                },
                                { etiqueta: 'Servicios', href: '/servicios' },
                                {
                                    etiqueta: 'Portal de Huéspedes',
                                    href: '/mis-reservas',
                                },
                                { etiqueta: 'Acerca de', href: '/acerca-de' },
                                { etiqueta: 'Contacto', href: '/contacto' },
                            ].map((item) => (
                                <li key={item.etiqueta}>
                                    <Link
                                        href={item.href}
                                        className="flex items-center gap-1.5 text-xs font-semibold text-gray-400 transition-colors hover:text-white"
                                    >
                                        <span>{item.etiqueta}</span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Experiencias */}
                    <div className="lg:col-span-2">
                        <h4 className="mb-6 text-[10px] font-black tracking-[0.2em] text-bugambilia-400 uppercase">
                            Experiencias
                        </h4>
                        <ul className="space-y-3">
                            {[
                                'Piscina & Solárium',
                                'Gastronomía Típica',
                                'Bar & Terraza Lounge',
                                'Eventos & Reuniones',
                                'Fibra Óptica High-Speed',
                            ].map((item) => (
                                <li
                                    key={item}
                                    className="text-xs font-semibold text-gray-400 transition-colors hover:text-gray-200"
                                >
                                    {item}
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Contacto */}
                    <div className="lg:col-span-4">
                        <h4 className="mb-6 text-[10px] font-black tracking-[0.2em] text-bugambilia-400 uppercase">
                            Concierge & Ubicación
                        </h4>
                        <div className="space-y-4">
                            <div className="flex items-start gap-3">
                                <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-bugambilia-500" />
                                <div>
                                    <p className="mb-0.5 text-[10px] font-black tracking-wider text-white uppercase">
                                        Dirección
                                    </p>
                                    <p className="text-xs leading-relaxed text-gray-400">
                                        {direccion}
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <Phone className="mt-0.5 h-4 w-4 shrink-0 text-bugambilia-500" />
                                <div>
                                    <p className="mb-0.5 text-[10px] font-black tracking-wider text-white uppercase">
                                        Reservaciones Directas
                                    </p>
                                    <p className="text-xs text-gray-400">
                                        {telefono}
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <Mail className="mt-0.5 h-4 w-4 shrink-0 text-bugambilia-500" />
                                <div>
                                    <p className="mb-0.5 text-[10px] font-black tracking-wider text-white uppercase">
                                        Correo Electrónico
                                    </p>
                                    <p className="text-xs text-gray-400">
                                        {email}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Badges de certificación */}
                <div className="my-8 grid grid-cols-2 gap-4 border-t border-b border-gray-900/80 py-6 text-center sm:grid-cols-4">
                    <div className="flex items-center justify-center gap-2 text-xs font-semibold text-gray-400">
                        <ShieldCheck className="h-4 w-4 text-bugambilia-500" />
                        <span>Reserva 100% Segura</span>
                    </div>
                    <div className="flex items-center justify-center gap-2 text-xs font-semibold text-gray-400">
                        <Award className="h-4 w-4 text-amber-400" />
                        <span>Certificado de Excelencia</span>
                    </div>
                    <div className="flex items-center justify-center gap-2 text-xs font-semibold text-gray-400">
                        <Star className="h-4 w-4 fill-amber-400 text-amber-400" />
                        <span>4.9 / 5 Reseñas</span>
                    </div>
                    <div className="flex items-center justify-center gap-2 text-xs font-semibold text-gray-400">
                        <Globe className="h-4 w-4 text-bugambilia-500" />
                        <span>Atención Multilingüe</span>
                    </div>
                </div>

                {/* Barra inferior */}
                <div className="flex flex-col items-center justify-between gap-4 text-xs font-medium text-gray-500 md:flex-row">
                    <div className="flex flex-wrap justify-center gap-6 md:justify-start">
                        <span>
                            © {new Date().getFullYear()} {nombre}. Todos los
                            derechos reservados.
                        </span>
                        <span className="cursor-pointer hover:text-gray-300">
                            Privacidad
                        </span>
                        <span className="cursor-pointer hover:text-gray-300">
                            Términos
                        </span>
                        <span className="cursor-pointer hover:text-gray-300">
                            Mapa del sitio
                        </span>
                    </div>

                    <div className="flex items-center gap-6">
                        <div className="flex items-center gap-2 font-bold text-gray-300">
                            <Globe className="h-4 w-4" />
                            <span>Español (NI)</span>
                            <span className="text-gray-700">|</span>
                            <span>USD ($)</span>
                        </div>

                        <button
                            onClick={irArriba}
                            className="group flex items-center gap-2 font-bold text-gray-400 transition-colors hover:text-white"
                        >
                            <span>Arriba</span>
                            <ArrowUpCircle className="h-4 w-4 text-bugambilia-500 transition-transform group-hover:scale-110" />
                        </button>
                    </div>
                </div>
            </div>
        </footer>
    );
};
