import { usePage, Link } from '@inertiajs/react';
import {
    CreditCard,
    ShieldCheck,
    Lock,
    CheckCircle,
    MapPin,
    Calendar,
    Users,
    ChevronLeft,
    Star,
    HelpCircle,
    Shield,
    Clock,
    Briefcase,
    Smartphone,
    Info,
    Gift,
    Car,
    HeartPulse,
} from 'lucide-react';
import { useState, useEffect } from 'react';
import { Button } from '@/modules/shared/ui/button';
import { Checkbox } from '@/modules/shared/ui/checkbox';
import { Input } from '@/modules/shared/ui/input';
import { Label } from '@/modules/shared/ui/label';

const steps = [
    { id: 1, title: 'Detalles' },
    { id: 2, title: 'Servicios' },
    { id: 3, title: 'Pago' },
    { id: 4, title: 'Reserva' },
];

const EXTRAS = [
    {
        id: 'romantic',
        name: 'Pack Romántico',
        description:
            'Pétalos, espumoso y chocolates artesanales en tu habitación.',
        price: 45,
        icon: Gift,
        image: '/images/terrace.jpg',
    },
    {
        id: 'transfer',
        name: 'Traslado Privado',
        description:
            'Transporte exclusivo desde el Aeropuerto de Managua (MGA).',
        price: 85,
        icon: Car,
        image: '/images/service-pool.png',
    },
    {
        id: 'massage',
        name: 'Masaje de Bugambilia',
        description: 'Sesión de 60 min con aceites esenciales orgánicos.',
        price: 60,
        icon: HeartPulse,
        image: '/images/pool-scaled.jpg',
    },
    {
        id: 'tour',
        name: 'Tour del Tabaco',
        description:
            'Recorrido privado por las fábricas de puros más icónicas.',
        price: 35,
        icon: Briefcase,
        image: '/images/pool-front-view.jpg',
    },
];

export default function PaymentProcess() {
    const { hotel } = usePage().props;
    const [currentStep, setCurrentStep] = useState(1);
    const [paymentMethod, setPaymentMethod] = useState('card');
    const [selectedServices, setSelectedServices] = useState<string[]>([]);

    const toggleService = (id: string) => {
        setSelectedServices((prev) =>
            prev.includes(id) ? prev.filter((s) => s !== id) : [...prev, id],
        );
    };

    const bookingData = {
        room: 'Habitación Doble Deluxe',
        location: 'Estelí, Nicaragua',
        checkIn: '22 May 2024',
        checkOut: '24 May 2024',
        nights: 2,
        guests: 2,
        roomPrice: 390.0,
        subtotal: 780.0,
        taxes: 124.8,
        serviceFee: 45.0,
        total: 949.8,
        image: '/images/group-room.jpg',
        rating: 4.98,
    };

    const extrasTotal = selectedServices.reduce((sum, id) => {
        const extra = EXTRAS.find((e) => e.id === id);

        return sum + (extra?.price || 0);
    }, 0);

    const finalTotal = bookingData.total + extrasTotal;

    useEffect(() => {
        window.scrollTo(0, 0);
    }, [currentStep]);

    return (
        <main className="min-h-screen overflow-x-hidden bg-gray-50/50 pb-32 dark:bg-gray-950">
            <header className="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/80">
                <div className="container mx-auto flex h-20 items-center justify-between px-4 md:px-8">
                    <Link href="/" className="group flex items-center gap-2">
                        <div className="transition-airbnb flex h-8 w-8 items-center justify-center rounded-lg bg-bugambilia-600 text-xs font-black text-white group-hover:scale-110">
                            {hotel.name
                                .split(' ')
                                .map((w) => w[0])
                                .join('')}
                        </div>
                        <span className="hidden text-xl font-black tracking-tighter text-gray-900 sm:block dark:text-white">
                            {hotel.name.replace('Hotel ', '')}
                        </span>
                    </Link>

                    <div className="xs:gap-2 flex items-center gap-1.5 md:gap-10">
                        {steps.map((step, idx) => {
                            const isActive = currentStep === step.id;
                            const isCompleted = currentStep > step.id;

                            return (
                                <div
                                    key={step.id}
                                    className="xs:gap-2 flex items-center gap-1"
                                >
                                    <div
                                        className={`xs:w-7 xs:h-7 xs:text-[10px] transition-airbnb flex h-6 w-6 items-center justify-center rounded-full text-[9px] font-black ${
                                            isActive || isCompleted
                                                ? 'bg-black text-white dark:bg-white dark:text-black'
                                                : 'bg-gray-100 text-gray-400 dark:bg-gray-800'
                                        }`}
                                    >
                                        {isCompleted ? (
                                            <CheckCircle className="xs:w-3.5 xs:h-3.5 h-3 w-3" />
                                        ) : (
                                            step.id
                                        )}
                                    </div>
                                    <span
                                        className={`xs:text-[10px] transition-airbnb hidden text-[9px] font-black tracking-widest uppercase sm:inline ${isActive ? 'text-black dark:text-white' : 'text-gray-400'}`}
                                    >
                                        {step.title}
                                    </span>
                                    {idx < steps.length - 1 && (
                                        <div className="hidden h-[1px] w-4 bg-gray-200 sm:block dark:bg-gray-700" />
                                    )}
                                </div>
                            );
                        })}
                    </div>

                    <div className="hidden items-center gap-4 lg:flex">
                        <div className="flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-emerald-600 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <Lock className="h-3 w-3" />
                            <span className="text-[9px] font-black tracking-widest uppercase">
                                Pago Seguro
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5 text-gray-400">
                            <ShieldCheck className="h-4 w-4" />
                            <span className="text-[9px] font-black tracking-widest uppercase">
                                Reserva Garantizada
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <div className="container mx-auto px-4 pt-12 md:px-8">
                <div className="mx-auto max-w-6xl">
                    {currentStep < 4 && (
                        <button
                            onClick={() =>
                                currentStep > 1 &&
                                setCurrentStep(currentStep - 1)
                            }
                            className="group transition-airbnb mb-10 flex items-center gap-2 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase hover:text-black dark:hover:text-white"
                        >
                            <div className="transition-airbnb flex h-8 w-8 items-center justify-center rounded-full border border-gray-100 group-hover:border-black">
                                <ChevronLeft className="h-4 w-4" />
                            </div>
                            {currentStep === 1
                                ? 'Volver a la habitación'
                                : 'Paso anterior'}
                        </button>
                    )}

                    <div className="grid gap-12 lg:grid-cols-12 xl:gap-20">
                        <div
                            className={`lg:col-span-7 ${currentStep === 3 ? 'lg:col-span-12' : ''}`}
                        >
                            {currentStep === 1 && (
                                <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
                                    <header className="mb-12">
                                        <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                                            Tu reserva casi está{' '}
                                            <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                                                lista
                                            </span>
                                        </h1>
                                        <p className="text-lg font-medium text-gray-500">
                                            Revisa los detalles antes de
                                            confirmar.
                                        </p>
                                    </header>

                                    <section className="space-y-12">
                                        <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                                            <div className="mb-8 flex items-center justify-between">
                                                <h2 className="text-xl font-black tracking-tight text-gray-900 dark:text-white">
                                                    Tu viaje
                                                </h2>
                                                <button className="text-[10px] font-black tracking-widest text-bugambilia-600 uppercase underline underline-offset-4 hover:opacity-70">
                                                    Cambiar
                                                </button>
                                            </div>

                                            <div className="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                                <div>
                                                    <p className="mb-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                        Fechas
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <Calendar className="h-4 w-4 text-bugambilia-600" />
                                                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                                                            {
                                                                bookingData.checkIn
                                                            }{' '}
                                                            –{' '}
                                                            {
                                                                bookingData.checkOut
                                                            }
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p className="mb-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                        Huéspedes
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <Users className="h-4 w-4 text-bugambilia-600" />
                                                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                                                            {bookingData.guests}{' '}
                                                            Personas
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                                            <h2 className="mb-8 text-xl font-black tracking-tight text-gray-900 dark:text-white">
                                                Información de contacto
                                            </h2>
                                            <div className="grid gap-6 md:grid-cols-2">
                                                <div className="space-y-2.5">
                                                    <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                        Nombre
                                                    </Label>
                                                    <Input
                                                        className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                        placeholder="Juan"
                                                    />
                                                </div>
                                                <div className="space-y-2.5">
                                                    <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                        Apellido
                                                    </Label>
                                                    <Input
                                                        className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                        placeholder="Rodríguez"
                                                    />
                                                </div>
                                                <div className="space-y-2.5 md:col-span-2">
                                                    <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                        Correo electrónico
                                                    </Label>
                                                    <Input
                                                        className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                        placeholder="juan@ejemplo.com"
                                                    />
                                                </div>
                                                <div className="space-y-2.5 md:col-span-2">
                                                    <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                        Teléfono
                                                    </Label>
                                                    <Input
                                                        className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                        placeholder="+505 0000 0000"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                                            <div className="mb-6 flex items-center gap-2">
                                                <Briefcase className="h-5 w-5 text-gray-400" />
                                                <h2 className="text-xl font-black tracking-tight text-gray-900 dark:text-white">
                                                    Peticiones especiales
                                                </h2>
                                            </div>
                                            <p className="mb-6 border-l-2 border-bugambilia-600 py-1 pl-4 text-xs font-medium tracking-widest text-gray-400 uppercase">
                                                ¿Llegas tarde? ¿Eres alérgico a
                                                algo? Cuéntanos.
                                            </p>
                                            <textarea
                                                className="transition-airbnb h-32 w-full resize-none rounded-3xl border-gray-100 bg-gray-50 p-6 text-sm placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                placeholder="Ej. Prefiero una habitación en la planta alta..."
                                            ></textarea>
                                        </div>

                                        <div className="flex flex-col items-center gap-6 pt-6 sm:flex-row">
                                            <Button
                                                onClick={() =>
                                                    setCurrentStep(2)
                                                }
                                                className="transition-airbnb h-16 w-full rounded-2xl bg-black px-12 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl hover:scale-105 active:scale-95 sm:w-auto dark:bg-white dark:text-black"
                                            >
                                                Continuar a servicios
                                            </Button>
                                            <div className="flex items-center gap-2 text-gray-400">
                                                <Lock className="h-3.5 w-3.5" />
                                                <span className="text-[9px] font-black tracking-widest uppercase">
                                                    Seguro y Privado
                                                </span>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            )}

                            {currentStep === 2 && (
                                <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
                                    <header className="mb-12">
                                        <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                                            Mejora tu{' '}
                                            <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                                                estancia
                                            </span>
                                        </h1>
                                        <p className="text-lg font-medium text-gray-500">
                                            Añade experiencias exclusivas a tu
                                            reserva.
                                        </p>
                                    </header>

                                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                        {EXTRAS.map((extra) => (
                                            <div
                                                key={extra.id}
                                                onClick={() =>
                                                    toggleService(extra.id)
                                                }
                                                className={`group transition-airbnb relative flex cursor-pointer flex-col overflow-hidden rounded-[2.5rem] border-2 p-6 ${
                                                    selectedServices.includes(
                                                        extra.id,
                                                    )
                                                        ? 'border-black bg-white shadow-xl dark:border-white dark:bg-gray-800'
                                                        : 'border-gray-100 bg-white/50 hover:border-gray-200 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700'
                                                }`}
                                            >
                                                <div className="mb-6 flex items-start justify-between">
                                                    <div
                                                        className={`transition-airbnb flex h-12 w-12 items-center justify-center rounded-2xl ${selectedServices.includes(extra.id) ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-400'}`}
                                                    >
                                                        <extra.icon className="h-6 w-6" />
                                                    </div>
                                                    <div
                                                        className={`transition-airbnb flex h-6 w-6 items-center justify-center rounded-full border-2 ${selectedServices.includes(extra.id) ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-gray-200 dark:border-gray-700'}`}
                                                    >
                                                        {selectedServices.includes(
                                                            extra.id,
                                                        ) && (
                                                            <CheckCircle className="h-4 w-4 fill-current" />
                                                        )}
                                                    </div>
                                                </div>

                                                <h3 className="mb-2 text-lg font-black text-gray-900 dark:text-white">
                                                    {extra.name}
                                                </h3>
                                                <p className="mb-6 text-xs leading-relaxed font-medium text-gray-500 dark:text-gray-300">
                                                    {extra.description}
                                                </p>

                                                <div className="mt-auto flex items-center justify-between">
                                                    <span className="text-sm font-black text-gray-900 dark:text-white">
                                                        ${extra.price}
                                                    </span>
                                                    <span className="text-[10px] font-black tracking-widest text-bugambilia-600 uppercase">
                                                        {selectedServices.includes(
                                                            extra.id,
                                                        )
                                                            ? 'Añadido'
                                                            : 'Añadir'}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex flex-col items-center gap-6 pt-12 sm:flex-row">
                                        <Button
                                            onClick={() => setCurrentStep(3)}
                                            className="bg-bugambilia-gradient transition-airbnb h-16 w-full rounded-2xl border-none px-12 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl hover:scale-105 active:scale-95 sm:w-auto"
                                        >
                                            Continuar al pago
                                        </Button>
                                        <button
                                            onClick={() => setCurrentStep(3)}
                                            className="text-[10px] font-black tracking-widest text-gray-400 uppercase transition-colors hover:text-black"
                                        >
                                            Saltar por ahora
                                        </button>
                                    </div>
                                </div>
                            )}

                            {currentStep === 3 && (
                                <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
                                    <header className="mb-12">
                                        <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                                            Detalles del{' '}
                                            <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                                                pago
                                            </span>
                                        </h1>
                                        <p className="text-lg font-medium text-gray-500">
                                            Transacción encriptada de 256 bits.
                                        </p>
                                    </header>

                                    <div className="space-y-10">
                                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            {['card', 'paypal'].map(
                                                (method) => (
                                                    <div
                                                        key={method}
                                                        onClick={() =>
                                                            setPaymentMethod(
                                                                method,
                                                            )
                                                        }
                                                        className={`group transition-airbnb relative cursor-pointer overflow-hidden rounded-[2rem] border-2 p-5 sm:p-8 ${
                                                            paymentMethod ===
                                                            method
                                                                ? 'border-black bg-white shadow-lg dark:border-white dark:bg-gray-800'
                                                                : 'border-gray-100 bg-white/50 hover:border-gray-200 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700'
                                                        }`}
                                                    >
                                                        {paymentMethod ===
                                                            method && (
                                                            <div className="animate-in zoom-in absolute top-4 right-4">
                                                                <CheckCircle className="h-4 w-4 fill-current text-black dark:text-white" />
                                                            </div>
                                                        )}
                                                        <div
                                                            className={`transition-airbnb mb-4 flex h-10 w-10 items-center justify-center rounded-xl ${paymentMethod === method ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'}`}
                                                        >
                                                            {method ===
                                                            'card' ? (
                                                                <CreditCard className="h-5 w-5" />
                                                            ) : (
                                                                <Smartphone className="h-5 w-5" />
                                                            )}
                                                        </div>
                                                        <span
                                                            className={`text-[10px] font-black tracking-widest uppercase ${paymentMethod === method ? 'text-black dark:text-white' : 'text-gray-400 dark:text-gray-500'}`}
                                                        >
                                                            {method === 'card'
                                                                ? 'Tarjeta'
                                                                : 'PayPal / Digital'}
                                                        </span>
                                                    </div>
                                                ),
                                            )}
                                        </div>

                                        {paymentMethod === 'card' && (
                                            <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                                                <div className="grid grid-cols-1 gap-6">
                                                    <div className="space-y-2.5">
                                                        <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                            Titular de la
                                                            tarjeta
                                                        </Label>
                                                        <Input
                                                            className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                            placeholder="JUAN PEREZ"
                                                        />
                                                    </div>
                                                    <div className="space-y-2.5">
                                                        <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                            Número de tarjeta
                                                        </Label>
                                                        <div className="relative">
                                                            <Input
                                                                className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 pr-12 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                                placeholder="0000 0000 0000 0000"
                                                            />
                                                            <div className="absolute top-1/2 right-4 flex -translate-y-1/2 gap-1 opacity-20 dark:opacity-40">
                                                                <CreditCard className="h-5 w-5 dark:text-white" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-6">
                                                        <div className="space-y-2.5">
                                                            <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                                Expiración
                                                            </Label>
                                                            <Input
                                                                className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                                placeholder="MM / AA"
                                                            />
                                                        </div>
                                                        <div className="space-y-2.5">
                                                            <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                                                                Cód. seg.
                                                            </Label>
                                                            <Input
                                                                className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                                                                placeholder="123"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        )}

                                        <div className="rounded-3xl border border-bugambilia-100 bg-bugambilia-50/30 p-8 dark:border-bugambilia-800/50 dark:bg-bugambilia-900/10">
                                            <div className="flex items-start gap-4">
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-bugambilia-100 bg-white shadow-sm dark:bg-gray-800">
                                                    <Clock className="h-5 w-5 text-bugambilia-600" />
                                                </div>
                                                <div>
                                                    <h4 className="mb-1 text-sm font-black tracking-tighter text-gray-900 uppercase dark:text-white">
                                                        Cancelación Gratuita
                                                    </h4>
                                                    <p className="text-xs leading-relaxed font-medium text-gray-500">
                                                        Flexibilidad total.
                                                        Puedes cancelar sin
                                                        cargos hasta 24 horas
                                                        antes de tu llegada.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="pt-6">
                                            <div className="mb-10 flex items-start gap-4">
                                                <Checkbox
                                                    id="terms_agree"
                                                    className="mt-1 border-gray-200"
                                                />
                                                <Label
                                                    htmlFor="terms_agree"
                                                    className="cursor-pointer text-[10px] leading-relaxed font-black tracking-widest text-gray-400 uppercase select-none"
                                                >
                                                    He leído y acepto los{' '}
                                                    <Link
                                                        href="#"
                                                        className="text-gray-900 underline dark:text-white"
                                                    >
                                                        términos de servicio
                                                    </Link>{' '}
                                                    y las políticas de
                                                    privacidad.
                                                </Label>
                                            </div>

                                            <Button
                                                onClick={() =>
                                                    setCurrentStep(4)
                                                }
                                                className="bg-bugambilia-gradient transition-airbnb h-20 w-full rounded-[2rem] px-16 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl hover:scale-105 active:scale-95 sm:w-auto"
                                            >
                                                Confirmar Reserva • $
                                                {finalTotal.toFixed(2)}
                                            </Button>

                                            <div className="mt-10 flex items-center gap-6 opacity-30">
                                                <div className="h-6 w-12 rounded-md bg-gray-400/20" />
                                                <div className="h-6 w-12 rounded-md bg-gray-400/20" />
                                                <div className="h-6 w-12 rounded-md bg-gray-400/20" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {currentStep === 4 && (
                                <div className="animate-in fade-in zoom-in-95 mx-auto max-w-3xl py-12 duration-1000">
                                    <div className="mb-16 text-center">
                                        <div className="relative mb-10 inline-block">
                                            <div className="transition-airbnb mx-auto flex h-28 w-28 transform items-center justify-center rounded-[2.5rem] bg-emerald-50 shadow-2xl hover:rotate-6 dark:bg-emerald-900/20">
                                                <CheckCircle className="h-14 w-14 text-emerald-600" />
                                            </div>
                                            <div className="absolute -right-2 -bottom-2 flex h-10 w-10 animate-bounce items-center justify-center rounded-2xl bg-bugambilia-600 text-white shadow-lg">
                                                <Star className="h-5 w-5 fill-current" />
                                            </div>
                                        </div>
                                        <h1 className="mb-6 text-5xl leading-none font-black tracking-tighter text-gray-900 md:text-7xl dark:text-white">
                                            ¡Tu refugio en Estelí{' '}
                                            <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                                                está listo!
                                            </span>
                                        </h1>
                                        <p className="mx-auto max-w-xl text-xl font-medium text-gray-500">
                                            Hemos enviado los detalles de tu
                                            reserva y el código de confirmación
                                            a tu correo electrónico.
                                        </p>
                                    </div>

                                    <div className="relative mb-12 overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white p-5 shadow-2xl sm:rounded-[3.5rem] sm:p-8 md:p-12 dark:border-gray-800 dark:bg-gray-900">
                                        <div className="pointer-events-none absolute top-0 right-0 h-32 w-32 rounded-bl-[100%] bg-bugambilia-50/50 dark:bg-bugambilia-900/10" />

                                        <div className="mb-12 flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
                                            <div>
                                                <p className="mb-2 text-[10px] font-black tracking-[0.3em] text-gray-400 uppercase">
                                                    Reserva Número
                                                </p>
                                                <h4 className="text-3xl font-black tracking-widest text-gray-900 dark:text-white">
                                                    HB-382910
                                                </h4>
                                            </div>
                                            <div className="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-6 py-3 text-emerald-600 dark:border-emerald-900 dark:bg-emerald-900/20">
                                                <Lock className="h-4 w-4" />
                                                <span className="text-xs font-black tracking-widest uppercase">
                                                    Reserva Confirmada
                                                </span>
                                            </div>
                                        </div>

                                        <div className="grid gap-10 md:grid-cols-2">
                                            <div className="space-y-8">
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Descripción
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <Calendar className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-base font-bold text-gray-900 dark:text-white">
                                                            {bookingData.room}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Entrada
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <Clock className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-base font-bold text-gray-900 dark:text-white">
                                                            22 May 2024, 15:00
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="space-y-8">
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Huéspedes
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <Users className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-base font-bold text-gray-900 dark:text-white">
                                                            {bookingData.guests}{' '}
                                                            Adultos
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Dirección
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <MapPin className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-sm leading-tight font-bold text-gray-900 dark:text-white">
                                                            {hotel.direccion}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="mt-12 flex flex-col items-center justify-between gap-6 border-t border-gray-50 pt-12 sm:flex-row dark:border-gray-800">
                                            <div className="flex items-center gap-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                <HelpCircle className="h-4 w-4" />
                                                {`¿Necesitas soporte? ${hotel.telefono}`}
                                            </div>
                                            <div className="flex w-full gap-4 sm:w-auto">
                                                <Button
                                                    onClick={() =>
                                                        window.print()
                                                    }
                                                    className="transition-airbnb h-14 flex-1 rounded-2xl bg-black px-8 text-[10px] font-black tracking-widest text-white uppercase shadow-lg sm:flex-none dark:bg-white dark:text-black"
                                                >
                                                    Imprimir Folio
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="text-center">
                                        <Button
                                            variant="ghost"
                                            className="transition-airbnb h-14 rounded-2xl px-10 text-[10px] font-black tracking-[0.4em] text-gray-500 uppercase hover:bg-white hover:text-black"
                                            asChild
                                        >
                                            <Link href="/">
                                                Volver al portal principal
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {currentStep < 4 && (
                            <aside className="lg:col-span-5">
                                <div className="animate-in fade-in slide-in-from-right-8 space-y-8 duration-700 lg:sticky lg:top-32">
                                    <div className="shadow-airbnb relative overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                                        <div className="xs:flex-row mb-10 flex flex-col gap-6 overflow-hidden">
                                            <div className="xs:w-32 xs:aspect-square relative aspect-video w-full flex-shrink-0 overflow-hidden rounded-3xl shadow-2xl">
                                                <img
                                                    src={bookingData.image}
                                                    alt={bookingData.room}
                                                    className="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110"
                                                />
                                            </div>
                                            <div className="flex flex-col justify-center">
                                                <div className="mb-2 flex items-center gap-1.5">
                                                    <Star className="h-3.5 w-3.5 fill-bugambilia-600 text-bugambilia-600" />
                                                    <span className="text-xs font-black tracking-tight">
                                                        {bookingData.rating}
                                                    </span>
                                                    <span className="text-[10px] font-bold text-gray-400">
                                                        • Mejor estancia
                                                    </span>
                                                </div>
                                                <h4 className="mb-1 max-w-[200px] truncate text-xl leading-[1.1] font-black tracking-tighter text-gray-900 sm:max-w-none dark:text-white">
                                                    {bookingData.room}
                                                </h4>
                                                <p className="text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                    {bookingData.location}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="mb-10 space-y-5 border-t border-gray-50 pt-8 dark:border-gray-800">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium text-gray-500 underline decoration-gray-100 decoration-2 underline-offset-4">
                                                    $
                                                    {bookingData.roomPrice.toFixed(
                                                        2,
                                                    )}{' '}
                                                    x {bookingData.nights}{' '}
                                                    noches
                                                </span>
                                                <span className="font-black text-gray-900 tabular-nums dark:text-white">
                                                    $
                                                    {bookingData.subtotal.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium text-gray-500 underline decoration-gray-100 decoration-2 underline-offset-4">
                                                    Impuestos de hospitalidad
                                                </span>
                                                <span className="font-black text-gray-900 tabular-nums dark:text-white">
                                                    $
                                                    {bookingData.taxes.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex items-center justify-between text-sm">
                                                <div className="group flex items-center gap-1 font-medium text-gray-500 underline decoration-gray-100 decoration-2 underline-offset-4">
                                                    Configuración y servicio
                                                    <Info className="h-3 w-3 text-gray-300 transition-colors group-hover:text-gray-900" />
                                                </div>
                                                <span className="font-black text-gray-900 tabular-nums dark:text-white">
                                                    $
                                                    {bookingData.serviceFee.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                            </div>

                                            {selectedServices.map((id) => {
                                                const extra = EXTRAS.find(
                                                    (e) => e.id === id,
                                                );

                                                if (!extra) {
                                                    return null;
                                                }

                                                return (
                                                    <div
                                                        key={id}
                                                        className="animate-in fade-in slide-in-from-right-4 flex items-center justify-between text-sm"
                                                    >
                                                        <span className="flex items-center gap-2 font-medium text-bugambilia-600 capitalize">
                                                            <extra.icon className="h-3 w-3" />
                                                            {extra.name}
                                                        </span>
                                                        <span className="font-black text-gray-900 tabular-nums dark:text-white">
                                                            $
                                                            {extra.price.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    </div>
                                                );
                                            })}
                                        </div>

                                        <div className="flex items-end justify-between border-t-2 border-dashed border-gray-100 pt-8 dark:border-gray-800">
                                            <div>
                                                <p className="mb-1 text-[10px] font-black tracking-[0.4em] text-gray-400 uppercase">
                                                    Total Reserva
                                                </p>
                                                <p className="text-4xl font-black tracking-tighter text-gray-900 tabular-nums dark:text-white">
                                                    ${finalTotal.toFixed(2)}
                                                </p>
                                            </div>
                                            <div className="mb-1 rounded-lg bg-bugambilia-50 px-3 py-1.5 text-[10px] font-black tracking-widest text-bugambilia-600 uppercase dark:bg-bugambilia-900/40">
                                                USD
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4 rounded-[2rem] border border-emerald-100 bg-emerald-50/50 p-8 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/10">
                                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm dark:bg-emerald-900/30">
                                            <Shield className="h-6 w-6" />
                                        </div>
                                        <div>
                                            <h5 className="mb-1 text-[10px] font-black tracking-widest text-emerald-900 uppercase dark:text-emerald-400">{`Garantía ${hotel.name.replace('Hotel ', '')}`}</h5>
                                            <p className="text-[11px] leading-relaxed font-medium text-emerald-700/70 dark:text-emerald-500/80">
                                                Mejor precio garantizado y
                                                soporte local 24/7 durante toda
                                                tu estancia.
                                            </p>
                                        </div>
                                    </div>

                                    <div className="text-center">
                                        <button className="transition-airbnb group inline-flex items-center gap-2 text-[10px] font-black tracking-widest text-gray-400 uppercase hover:text-black dark:hover:text-white">
                                            <HelpCircle className="h-4 w-4 transition-transform group-hover:rotate-12" />
                                            Preguntas sobre tu reserva
                                        </button>
                                    </div>
                                </div>
                            </aside>
                        )}
                    </div>
                </div>
            </div>
        </main>
    );
}
