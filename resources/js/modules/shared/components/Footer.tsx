import { Link } from '@inertiajs/react';
import { MapPin, Phone, Mail, Star } from 'lucide-react';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const Footer = () => {
    const { hotel } = usePropiedadesPagina();
    const nombre = hotel?.nombre || hotel?.name || 'Hotel Bugambilias';
    const telefono = hotel?.telefono || '+505 8713 6805';
    const email = hotel?.email || 'recepcion@bugambiliashotel.com';
    const direccion =
        hotel?.direccion ||
        'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, Estelí, Nicaragua';

    return (
        <footer className="border-t border-border/60 bg-gray-950 font-sans text-white">
            <div className="container mx-auto px-4 py-12 sm:px-6">
                <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
                    {/* Marca */}
                    <div>
                        <Link href="/" className="inline-block">
                            <span className="text-xl font-black tracking-tight text-white">
                                HOTEL{' '}
                                <span className="text-bugambilia-500">
                                    BUGAMBILIAS
                                </span>
                            </span>
                            <span className="flex items-center gap-1 text-[10px] font-bold text-amber-400">
                                <Star className="size-3 fill-amber-400" />
                                <Star className="size-3 fill-amber-400" />
                                <Star className="size-3 fill-amber-400" />
                                <Star className="size-3 fill-amber-400" />
                                <Star className="size-3 fill-amber-400" />
                                <span className="ml-1 text-gray-300">
                                    Estelí, Nicaragua
                                </span>
                            </span>
                        </Link>
                        <p className="mt-3 max-w-sm text-xs leading-relaxed text-gray-400">
                            Hospitalidad exclusiva, confort y tradición en el
                            corazón de Estelí.
                        </p>
                    </div>

                    {/* Enlaces */}
                    <div>
                        <h4 className="text-xs font-black tracking-wider text-bugambilia-400 uppercase">
                            Navegación
                        </h4>
                        <ul className="mt-3 space-y-2 text-xs">
                            <li>
                                <Link
                                    href="/"
                                    className="text-gray-400 transition-colors hover:text-white"
                                >
                                    Inicio
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/habitaciones"
                                    className="text-gray-400 transition-colors hover:text-white"
                                >
                                    Habitaciones & Suites
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/promociones"
                                    className="text-gray-400 transition-colors hover:text-white"
                                >
                                    Promociones & Paquetes
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/espacios"
                                    className="text-gray-400 transition-colors hover:text-white"
                                >
                                    Eventos & Espacios
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/servicios"
                                    className="text-gray-400 transition-colors hover:text-white"
                                >
                                    Servicios & Experiencias
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/acerca-de"
                                    className="text-gray-400 transition-colors hover:text-white"
                                >
                                    Nosotros
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Contacto */}
                    <div>
                        <h4 className="text-xs font-black tracking-wider text-bugambilia-400 uppercase">
                            Contacto
                        </h4>
                        <ul className="mt-3 space-y-2 text-xs text-gray-400">
                            <li className="flex items-center gap-2">
                                <MapPin className="size-3.5 shrink-0 text-bugambilia-500" />
                                <span>{direccion}</span>
                            </li>
                            <li className="flex items-center gap-2">
                                <Phone className="size-3.5 shrink-0 text-bugambilia-500" />
                                <a
                                    href={`tel:${telefono}`}
                                    className="hover:text-white"
                                >
                                    {telefono}
                                </a>
                            </li>
                            <li className="flex items-center gap-2">
                                <Mail className="size-3.5 shrink-0 text-bugambilia-500" />
                                <a
                                    href={`mailto:${email}`}
                                    className="hover:text-white"
                                >
                                    {email}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="mt-8 border-t border-gray-900 pt-6 text-center text-xs text-gray-500">
                    © {new Date().getFullYear()} {nombre}. Todos los derechos
                    reservados.
                </div>
            </div>
        </footer>
    );
};

export default Footer;
