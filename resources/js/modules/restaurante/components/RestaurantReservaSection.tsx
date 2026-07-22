import { Link } from '@inertiajs/react';
import {
    MessageCircle,
    Phone,
    Sparkles,
    MapPin,
    Users,
    Calendar,
} from 'lucide-react';
import { useState } from 'react';
import type { AmbienteData } from '@/modules/restaurante/types';

interface RestaurantReservaSectionProps {
    ambientes: AmbienteData[];
    selectedAmbienteNombre?: string;
    whatsappNumber?: string;
}

export default function RestaurantReservaSection({
    ambientes,
    selectedAmbienteNombre,
    whatsappNumber,
}: RestaurantReservaSectionProps) {
    const [ambienteSeleccionado, setAmbienteSeleccionado] = useState<string>(
        selectedAmbienteNombre ||
            ambientes[0]?.nombre ||
            'Salón Principal Bugambilias',
    );
    const [cantidadPersonas, setCantidadPersonas] = useState<number>(2);
    const [fechaReserva] = useState<string>('');
    const [horaReserva, setHoraReserva] = useState<string>('19:00');

    const cleanPhone = whatsappNumber
        ? whatsappNumber.replace(/[^0-9]/g, '')
        : '';

    const handleWhatsAppReserva = () => {
        const mensaje = encodeURIComponent(
            `Hola Restaurante Bugambilias, me gustaría solicitar una reserva:\n\n` +
                `📍 Ambiente: ${ambienteSeleccionado}\n` +
                `👥 Personas: ${cantidadPersonas}\n` +
                `📅 Fecha: ${fechaReserva || 'Próximos días'}\n` +
                `⏰ Hora: ${horaReserva}\n\n` +
                `¿Tienen disponibilidad?`,
        );
        window.open(`https://wa.me/${cleanPhone}?text=${mensaje}`, '_blank');
    };

    return (
        <section id="reserva-section" className="bg-background py-20 font-sans">
            <div className="container mx-auto max-w-5xl px-4">
                <div className="relative overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 via-zinc-950 to-black p-8 text-white shadow-2xl md:p-12">
                    {/* Decorative glows */}
                    <div className="pointer-events-none absolute top-0 right-0 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl" />
                    <div className="pointer-events-none absolute bottom-0 left-0 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl" />

                    <div className="relative z-10 grid items-center gap-8 md:grid-cols-12">
                        {/* Callout Header */}
                        <div className="space-y-4 md:col-span-6">
                            <div className="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-500/20 px-3.5 py-1.5 text-xs font-black tracking-widest text-amber-300 uppercase">
                                <Sparkles className="h-3.5 w-3.5" />
                                Reserva Instantánea
                            </div>

                            <h2 className="text-3xl leading-tight font-black tracking-tight text-white md:text-4xl">
                                Reserve su mesa o ambiente preferido
                            </h2>

                            <p className="text-sm leading-relaxed text-zinc-400 md:text-base">
                                Garantice su espacio en la terraza, salón VIP o
                                barra. Atendemos reservas de parejas, familias y
                                eventos grupales.
                            </p>

                            <div className="flex flex-col items-stretch gap-3 pt-4 sm:flex-row sm:items-center">
                                <Link
                                    href="/contacto"
                                    className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-6 py-3 text-sm font-bold text-white transition-all hover:bg-white/20"
                                >
                                    <Phone className="h-4 w-4 text-amber-400" />
                                    Formulario de Contacto
                                </Link>
                            </div>
                        </div>

                        {/* Interactive WhatsApp Form Card */}
                        <div className="space-y-4 rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl md:col-span-6">
                            <div>
                                <label className="mb-2 block flex items-center gap-1.5 text-xs font-extrabold tracking-wider text-amber-300 uppercase">
                                    <MapPin className="h-3.5 w-3.5" />
                                    Ambiente Deseado
                                </label>
                                <select
                                    value={ambienteSeleccionado}
                                    onChange={(e) =>
                                        setAmbienteSeleccionado(e.target.value)
                                    }
                                    className="w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm font-semibold text-white focus:border-amber-400 focus:outline-none"
                                >
                                    {ambientes.map((amb) => (
                                        <option key={amb.id} value={amb.nombre}>
                                            {amb.nombre} (
                                            {amb.zona.toUpperCase()})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="mb-2 block flex items-center gap-1.5 text-xs font-extrabold tracking-wider text-amber-300 uppercase">
                                        <Users className="h-3.5 w-3.5" />
                                        Personas
                                    </label>
                                    <select
                                        value={cantidadPersonas}
                                        onChange={(e) =>
                                            setCantidadPersonas(
                                                Number(e.target.value),
                                            )
                                        }
                                        className="w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm font-semibold text-white focus:border-amber-400 focus:outline-none"
                                    >
                                        {[1, 2, 3, 4, 5, 6, 8, 10, 15, 20].map(
                                            (num) => (
                                                <option key={num} value={num}>
                                                    {num}{' '}
                                                    {num === 1
                                                        ? 'Persona'
                                                        : 'Personas'}
                                                </option>
                                            ),
                                        )}
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-2 block flex items-center gap-1.5 text-xs font-extrabold tracking-wider text-amber-300 uppercase">
                                        <Calendar className="h-3.5 w-3.5" />
                                        Hora
                                    </label>
                                    <input
                                        type="time"
                                        value={horaReserva}
                                        onChange={(e) =>
                                            setHoraReserva(e.target.value)
                                        }
                                        className="w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm font-semibold text-white focus:border-amber-400 focus:outline-none"
                                    />
                                </div>
                            </div>

                            <button
                                onClick={handleWhatsAppReserva}
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-emerald-500 py-4 text-sm font-black text-zinc-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] hover:bg-emerald-600"
                            >
                                <MessageCircle className="h-5 w-5" />
                                Confirmar Reserva por WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
