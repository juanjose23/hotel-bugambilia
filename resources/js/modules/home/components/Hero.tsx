import {
    Search,
    Calendar,
    Users,
    MapPin,
    Loader2,
    Hotel,
    BedDouble,
} from 'lucide-react';
import { useRef } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/components/ui/select';
import type { CategoriaFiltro } from '@/modules/shared/types';
import { useHomeBusquedaForm } from '../hooks/useHomeBusquedaForm';

interface HeroProps {
    categorias?: CategoriaFiltro[];
}

export const Hero = ({ categorias = [] }: HeroProps) => {
    const { register, handleSubmit, setValue, watch, isSubmitting } =
        useHomeBusquedaForm();

    const inputCheckInRef = useRef<HTMLInputElement | null>(null);
    const inputCheckOutRef = useRef<HTMLInputElement | null>(null);

    const categoriaSeleccionada = watch('categoria') || 'todas';
    const checkIn = watch('check_in');
    const checkOut = watch('check_out');
    const personas = watch('personas') || '2';

    // Registro de RHF con ref combinada
    const { ref: rhfCheckInRef, ...checkInProps } = register('check_in');
    const { ref: rhfCheckOutRef, ...checkOutProps } = register('check_out');

    // Función para formatear fechas de manera amigable
    const formatearFechaVista = (fechaStr?: string) => {
        if (!fechaStr) {
            return null;
        }

        try {
            const [year, month, day] = fechaStr.split('-').map(Number);

            if (!year || !month || !day) {
                return null;
            }

            const d = new Date(year, month - 1, day);

            return d.toLocaleDateString('es-NI', {
                day: 'numeric',
                month: 'short',
            });
        } catch {
            return null;
        }
    };

    const textoCheckIn = formatearFechaVista(checkIn);
    const textoCheckOut = formatearFechaVista(checkOut);

    const abrirPickerLlegada = () => {
        try {
            const el = inputCheckInRef.current;

            if (el) {
                if ('showPicker' in el && typeof el.showPicker === 'function') {
                    el.showPicker();
                } else {
                    el.focus();
                }
            }
        } catch {
            inputCheckInRef.current?.focus();
        }
    };

    const abrirPickerSalida = () => {
        try {
            const el = inputCheckOutRef.current;

            if (el) {
                if ('showPicker' in el && typeof el.showPicker === 'function') {
                    el.showPicker();
                } else {
                    el.focus();
                }
            }
        } catch {
            inputCheckOutRef.current?.focus();
        }
    };

    return (
        <section
            aria-label="Portada de Bienvenida y Búsqueda"
            className="relative overflow-hidden font-sans"
        >
            {/* Banner Fotográfico Principal de Hotel Bugambilias */}
            <div className="relative h-[360px] w-full sm:h-[420px] lg:h-[460px]">
                <img
                    src="/images/hero-main.webp"
                    alt="Patios y arquitectura colonial de Hotel Bugambilias Estelí"
                    className="h-full w-full object-cover brightness-[0.80] dark:brightness-[0.55]"
                    loading="eager"
                />
                <div
                    aria-hidden="true"
                    className="absolute inset-0 bg-gradient-to-t from-background via-black/30 to-black/60"
                />

                {/* Texto Central */}
                <div className="absolute inset-0 container mx-auto flex flex-col items-center justify-center px-4 text-center text-white sm:px-6">
                    {/* Badge Institucional */}
                    <div className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1 text-[11px] font-black tracking-wider text-rose-200 uppercase backdrop-blur-md">
                        <Hotel className="size-3.5" aria-hidden="true" />
                        <span>Hotel Boutique en Estelí, Nicaragua</span>
                    </div>

                    <h1 className="mt-4 max-w-2xl text-2xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl lg:leading-tight">
                        Tu estancia inolvidable en{' '}
                        <span className="text-rose-300 dark:text-rose-400">
                            Hotel Bugambilias
                        </span>
                    </h1>

                    <p className="mt-2.5 max-w-md text-xs font-medium text-white/90 sm:text-sm">
                        Confort superior, piscina tropical y la mejor
                        hospitalidad en el norte de Nicaragua.
                    </p>
                </div>
            </div>

            {/* Cápsula de Búsqueda Flotante */}
            <div className="relative z-20 container mx-auto -mt-10 px-4 sm:-mt-12 sm:px-6 lg:max-w-4xl">
                <form
                    onSubmit={handleSubmit}
                    className="rounded-3xl border border-border/80 bg-card/95 p-3.5 shadow-2xl backdrop-blur-xl transition-all duration-300 sm:rounded-full sm:p-2 sm:pr-2.5"
                >
                    {/* En Móvil: Cuadrícula organizada 2x2. En Desktop: Barra horizontal */}
                    <div className="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-0">
                        {/* 1. Ubicación (Desktop) */}
                        <div className="hidden flex-1 items-center gap-2.5 px-3 py-1.5 sm:flex">
                            <MapPin className="size-4 shrink-0 text-primary dark:text-rose-400" />
                            <div className="flex flex-col text-left">
                                <span className="text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                                    Destino
                                </span>
                                <span className="text-xs font-bold text-foreground">
                                    Estelí, Nicaragua
                                </span>
                            </div>
                        </div>

                        <div className="hidden h-6 w-px bg-border sm:block" />

                        {/* 2. Selector de Llegada */}
                        <div
                            onClick={abrirPickerLlegada}
                            role="button"
                            tabIndex={0}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    abrirPickerLlegada();
                                }
                            }}
                            className="relative col-span-1 flex flex-1 cursor-pointer items-center gap-2 rounded-2xl border border-border/60 bg-background/50 px-3 py-2 transition-colors hover:border-primary/40 sm:rounded-full sm:border-none sm:bg-transparent sm:py-1"
                        >
                            <Calendar className="size-4 shrink-0 text-primary dark:text-rose-400" />
                            <div className="flex w-full flex-col text-left">
                                <span className="text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                                    Llegada
                                </span>
                                <span className="truncate text-xs font-bold text-foreground">
                                    {textoCheckIn || 'Seleccionar'}
                                </span>
                            </div>
                            <input
                                type="date"
                                aria-label="Fecha de llegada"
                                {...checkInProps}
                                ref={(el) => {
                                    rhfCheckInRef(el);
                                    inputCheckInRef.current = el;
                                }}
                                className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            />
                        </div>

                        <div className="hidden h-6 w-px bg-border sm:block" />

                        {/* 3. Selector de Salida */}
                        <div
                            onClick={abrirPickerSalida}
                            role="button"
                            tabIndex={0}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    abrirPickerSalida();
                                }
                            }}
                            className="relative col-span-1 flex flex-1 cursor-pointer items-center gap-2 rounded-2xl border border-border/60 bg-background/50 px-3 py-2 transition-colors hover:border-primary/40 sm:rounded-full sm:border-none sm:bg-transparent sm:py-1"
                        >
                            <Calendar className="size-4 shrink-0 text-primary dark:text-rose-400" />
                            <div className="flex w-full flex-col text-left">
                                <span className="text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                                    Salida
                                </span>
                                <span className="truncate text-xs font-bold text-foreground">
                                    {textoCheckOut || 'Seleccionar'}
                                </span>
                            </div>
                            <input
                                type="date"
                                aria-label="Fecha de salida"
                                {...checkOutProps}
                                ref={(el) => {
                                    rhfCheckOutRef(el);
                                    inputCheckOutRef.current = el;
                                }}
                                className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            />
                        </div>

                        <div className="hidden h-6 w-px bg-border sm:block" />

                        {/* 4. Selector de Categoría */}
                        <div className="col-span-1 flex flex-1 items-center gap-2 rounded-2xl border border-border/60 bg-background/50 px-3 py-1.5 sm:rounded-full sm:border-none sm:bg-transparent sm:py-1">
                            <BedDouble className="size-4 shrink-0 text-primary dark:text-rose-400" />
                            <div className="flex w-full flex-col text-left">
                                <span className="text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                                    Categoría
                                </span>
                                <Select
                                    value={categoriaSeleccionada}
                                    onValueChange={(val) =>
                                        setValue(
                                            'categoria',
                                            val === 'todas' ? '' : val,
                                        )
                                    }
                                >
                                    <SelectTrigger className="h-5 border-none bg-transparent p-0 text-xs font-bold text-foreground shadow-none focus:ring-0">
                                        <SelectValue placeholder="Todas" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="todas">
                                            Todas las suites
                                        </SelectItem>
                                        {categorias.map((cat) => (
                                            <SelectItem
                                                key={cat.id}
                                                value={cat.slug || cat.nombre}
                                            >
                                                {cat.nombre}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="hidden h-6 w-px bg-border sm:block" />

                        {/* 5. Selector de Huéspedes */}
                        <div className="col-span-1 flex flex-1 items-center gap-2 rounded-2xl border border-border/60 bg-background/50 px-3 py-1.5 sm:rounded-full sm:border-none sm:bg-transparent sm:py-1">
                            <Users className="size-4 shrink-0 text-primary dark:text-rose-400" />
                            <div className="flex w-full flex-col text-left">
                                <span className="text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                                    Huéspedes
                                </span>
                                <Select
                                    value={personas}
                                    onValueChange={(val) =>
                                        setValue('personas', val)
                                    }
                                >
                                    <SelectTrigger className="h-5 border-none bg-transparent p-0 text-xs font-bold text-foreground shadow-none focus:ring-0">
                                        <SelectValue placeholder="Personas" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">
                                            1 persona
                                        </SelectItem>
                                        <SelectItem value="2">
                                            2 personas
                                        </SelectItem>
                                        <SelectItem value="3">
                                            3 personas
                                        </SelectItem>
                                        <SelectItem value="4">
                                            4+ personas
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* 6. Botón de Búsqueda */}
                        <div className="col-span-2 sm:col-span-1 sm:pl-2">
                            <Button
                                type="submit"
                                disabled={isSubmitting}
                                className="w-full cursor-pointer rounded-2xl bg-primary py-3 text-xs font-black text-primary-foreground shadow-md transition-all hover:bg-primary/90 active:scale-95 sm:w-auto sm:rounded-full sm:px-6 sm:py-2.5"
                            >
                                {isSubmitting ? (
                                    <>
                                        <Loader2 className="size-4 animate-spin" />
                                        <span>Buscando...</span>
                                    </>
                                ) : (
                                    <>
                                        <Search className="size-4" />
                                        <span>Buscar</span>
                                    </>
                                )}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    );
};

export default Hero;
