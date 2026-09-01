import { Head, Link } from '@inertiajs/react';
import {
    Utensils,
    Clock,
    Sparkles,
    Coffee,
    Sun,
    Moon,
    MessageSquare,
    PhoneCall,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { buttonVariants } from '@/modules/shared/components/ui/button';

interface RestauranteData {
    id: number;
    nombre: string;
    descripcion: string;
    capacidad: number;
    imagenes: string[];
    tipo_cocina: string;
    tipo_servicio: string;
    horario_desayuno: string;
    horario_almuerzo: string;
    horario_cena: string;
}

interface AmbienteData {
    id: number;
    codigo: string;
    nombre: string;
    tipo: string;
    capacidad: number;
    descripcion: string;
    zona: string;
    caracteristicas: string[];
    imagenes: string[];
    mesas_count: number;
}

interface MenuItemData {
    id: number;
    nombre: string;
    descripcion: string;
    categoria: string;
    categoria_codigo: string;
    precio: number | null;
    moneda: string;
    imagen: string;
    etiquetas: string[];
    tiempo_preparacion: string;
    disponible: boolean;
}

interface RestaurantePageProps {
    restaurante?: RestauranteData | null;
    ambientes?: AmbienteData[];
    menu?: MenuItemData[];
}

export const Restaurante = ({
    restaurante,
    ambientes = [],
    menu = [],
}: RestaurantePageProps) => {
    const [categoriaMenu, setCategoriaMenu] = useState<string>('todos');

    const categoriasDisponibles = useMemo(() => {
        const setCats = new Set<string>();

        menu.forEach((item) => {
            if (item.categoria) {
                setCats.add(item.categoria);
            }
        });

        return Array.from(setCats);
    }, [menu]);

    const menuFiltrado = useMemo(() => {
        if (categoriaMenu === 'todos') {
            return menu;
        }

        return menu.filter((item) => item.categoria === categoriaMenu);
    }, [menu, categoriaMenu]);

    const infoRestaurante = restaurante || {
        id: 1,
        nombre: 'Restaurante & Bar Bugambilias',
        descripcion:
            'Una experiencia gastronómica de alta cocina nicaragüense e internacional, combinando ingredientes frescos de temporada en ambientes acogedores y exclusivos.',
        capacidad: 40,
        imagenes: ['/images/service-kitchen.png', '/images/terrace.jpg'],
        tipo_cocina: 'Nicaragüense Gourmet & Fusión Internacional',
        tipo_servicio: 'A la carta / Menú Degustación',
        horario_desayuno: '07:00 AM - 10:30 AM',
        horario_almuerzo: '12:00 PM - 03:30 PM',
        horario_cena: '06:00 PM - 10:00 PM',
    };

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>
                    Restaurante & Bar Bugambilias — Sabores de Alta Cocina en
                    Estelí
                </title>
                <meta
                    name="description"
                    content="Disfruta de la mejor gastronomía en el Restaurante de Hotel Bugambilias. Menú a la carta, coctelería de autor, desayunos y cenas en terraza."
                />
            </Head>

            {/* Hero Principal */}
            <section className="relative overflow-hidden bg-zinc-950 py-24 text-white lg:py-32">
                <div className="absolute inset-0 z-0">
                    <img
                        src={
                            infoRestaurante.imagenes[0] ||
                            '/images/service-kitchen.png'
                        }
                        alt={infoRestaurante.nombre}
                        className="size-full object-cover opacity-25 blur-xs filter"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-background via-background/60 to-transparent" />
                </div>

                <div className="relative z-10 container mx-auto px-4 sm:px-6">
                    <div className="mx-auto max-w-3xl text-center">
                        <span className="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold tracking-widest text-amber-400 uppercase">
                            <Utensils className="size-3.5" />
                            Gastronomía Exclusiva
                        </span>

                        <h1 className="mt-6 text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                            {infoRestaurante.nombre}
                        </h1>

                        <p className="mt-6 text-base leading-relaxed text-zinc-300 sm:text-lg">
                            {infoRestaurante.descripcion}
                        </p>

                        <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                            <a
                                href="https://wa.me/50588888888?text=Hola,%20deseo%20reservar%20una%20mesa%20en%20el%20Restaurante"
                                target="_blank"
                                rel="noopener noreferrer"
                                className={buttonVariants({
                                    size: 'lg',
                                    className:
                                        'bg-bugambilia-600 font-bold text-white hover:bg-bugambilia-700',
                                })}
                            >
                                <MessageSquare className="size-4" />
                                Reservar Mesa por WhatsApp
                            </a>
                            <Link
                                href="/contacto"
                                className={buttonVariants({
                                    variant: 'outline',
                                    size: 'lg',
                                    className:
                                        'border-white/20 bg-white/5 text-white hover:bg-white/10',
                                })}
                            >
                                <PhoneCall className="size-4" />
                                Contactar Recepción
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Horarios & Tiempos de Servicio */}
            <section className="container mx-auto -mt-10 px-4 sm:px-6">
                <div className="grid grid-cols-1 gap-4 rounded-3xl border border-border bg-card p-6 shadow-xl sm:grid-cols-3 sm:gap-6 sm:p-8">
                    <div className="flex items-start gap-4 rounded-2xl bg-muted/40 p-4">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                            <Coffee className="size-6" />
                        </div>
                        <div>
                            <h3 className="text-sm font-bold text-foreground">
                                Desayunos Buffet & Carta
                            </h3>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {infoRestaurante.horario_desayuno}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-start gap-4 rounded-2xl bg-muted/40 p-4">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-500">
                            <Sun className="size-6" />
                        </div>
                        <div>
                            <h3 className="text-sm font-bold text-foreground">
                                Almuerzos Ejecutivos
                            </h3>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {infoRestaurante.horario_almuerzo}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-start gap-4 rounded-2xl bg-muted/40 p-4">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500">
                            <Moon className="size-6" />
                        </div>
                        <div>
                            <h3 className="text-sm font-bold text-foreground">
                                Cenas Gourmet & Bar
                            </h3>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {infoRestaurante.horario_cena}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Ambientes del Restaurante */}
            {ambientes.length > 0 && (
                <section className="container mx-auto px-4 py-16 sm:px-6">
                    <div className="text-center">
                        <span className="text-xs font-bold tracking-widest text-bugambilia-600 uppercase">
                            Nuestros Espacios
                        </span>
                        <h2 className="mt-2 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                            Ambientes Diseñados para Cada Momento
                        </h2>
                        <p className="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                            Elige el ambiente ideal para tu velada: desde la
                            elegancia climatizada hasta la frescura tropical de
                            nuestra terraza.
                        </p>
                    </div>

                    <div className="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        {ambientes.map((ambiente) => (
                            <div
                                key={ambiente.id || ambiente.codigo}
                                className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition hover:shadow-md"
                            >
                                <div className="relative h-48 w-full overflow-hidden bg-muted">
                                    <img
                                        src={
                                            ambiente.imagenes[0] ||
                                            '/images/terrace.jpg'
                                        }
                                        alt={ambiente.nombre}
                                        className="size-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                    <div className="absolute top-3 right-3 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-xs">
                                        {ambiente.capacidad} pers.
                                    </div>
                                </div>
                                <div className="flex grow flex-col p-5">
                                    <h3 className="text-base font-bold text-foreground">
                                        {ambiente.nombre}
                                    </h3>
                                    <p className="mt-2 grow text-xs leading-relaxed text-muted-foreground">
                                        {ambiente.descripcion}
                                    </p>

                                    {ambiente.caracteristicas &&
                                        ambiente.caracteristicas.length > 0 && (
                                            <div className="mt-4 flex flex-wrap gap-1.5">
                                                {ambiente.caracteristicas
                                                    .slice(0, 3)
                                                    .map((c, idx) => (
                                                        <span
                                                            key={idx}
                                                            className="rounded-md bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                                                        >
                                                            {c}
                                                        </span>
                                                    ))}
                                            </div>
                                        )}
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            {/* Menú a la Carta */}
            <section className="border-t border-border bg-muted/20 py-16">
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="text-center">
                        <span className="text-xs font-bold tracking-widest text-bugambilia-600 uppercase">
                            Alta Cocina
                        </span>
                        <h2 className="mt-2 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                            Menú a la Carta
                        </h2>
                        <p className="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                            Platos preparados al momento por nuestros chefs con
                            ingredientes de los valles de Estelí y recetas
                            internacionales.
                        </p>
                    </div>

                    {/* Filtros de Categorías de Menú */}
                    {categoriasDisponibles.length > 0 && (
                        <div className="mt-8 flex flex-wrap items-center justify-center gap-2">
                            <button
                                type="button"
                                onClick={() => setCategoriaMenu('todos')}
                                className={`rounded-full px-4 py-2 text-xs font-bold transition ${
                                    categoriaMenu === 'todos'
                                        ? 'bg-bugambilia-600 text-white shadow-sm'
                                        : 'bg-card text-muted-foreground hover:bg-muted'
                                }`}
                            >
                                Todas las Categorías
                            </button>
                            {categoriasDisponibles.map((cat) => (
                                <button
                                    key={cat}
                                    type="button"
                                    onClick={() => setCategoriaMenu(cat)}
                                    className={`rounded-full px-4 py-2 text-xs font-bold transition ${
                                        categoriaMenu === cat
                                            ? 'bg-bugambilia-600 text-white shadow-sm'
                                            : 'bg-card text-muted-foreground hover:bg-muted'
                                    }`}
                                >
                                    {cat}
                                </button>
                            ))}
                        </div>
                    )}

                    {/* Grid de Platos */}
                    <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {menuFiltrado.map((plato) => (
                            <div
                                key={plato.id}
                                className="group flex overflow-hidden rounded-2xl border border-border bg-card p-4 shadow-sm transition hover:shadow-md"
                            >
                                <div className="size-24 shrink-0 overflow-hidden rounded-xl bg-muted sm:size-28">
                                    <img
                                        src={
                                            plato.imagen ||
                                            '/images/service-kitchen.png'
                                        }
                                        alt={plato.nombre}
                                        className="size-full object-cover transition group-hover:scale-105"
                                    />
                                </div>
                                <div className="ml-4 flex grow flex-col justify-between">
                                    <div>
                                        <div className="flex items-start justify-between gap-2">
                                            <h3 className="text-sm font-bold text-foreground">
                                                {plato.nombre}
                                            </h3>
                                            {plato.precio !== null && (
                                                <span className="shrink-0 text-sm font-black text-bugambilia-600">
                                                    {plato.moneda}{' '}
                                                    {Number(
                                                        plato.precio,
                                                    ).toFixed(2)}
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">
                                            {plato.descripcion}
                                        </p>
                                    </div>

                                    <div className="mt-3 flex items-center justify-between text-[11px] text-muted-foreground">
                                        <span className="inline-flex items-center gap-1">
                                            <Clock className="size-3" />
                                            {plato.tiempo_preparacion}
                                        </span>
                                        {plato.etiquetas &&
                                            plato.etiquetas.length > 0 && (
                                                <span className="inline-flex items-center gap-1 font-medium text-amber-500">
                                                    <Sparkles className="size-3" />
                                                    {plato.etiquetas[0]}
                                                </span>
                                            )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>
        </div>
    );
};

export default Restaurante;
