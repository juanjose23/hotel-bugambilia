import { Skeleton } from '../ui/skeleton';

export type SkeletonLayout = 'public' | 'portal' | 'auth';
type SkeletonVariant =
    'home' | 'catalog' | 'detail' | 'form' | 'dashboard' | 'history' | 'info';

function variantFor(href: string, layout: SkeletonLayout): SkeletonVariant {
    const path =
        new URL(href, 'http://inertia.local').pathname.replace(/\/$/, '') ||
        '/';

    if (
        layout === 'auth' ||
        /\/(auth|pago|contacto)(\/|$)/.test(path) ||
        /reservar|checkout|perfil|acompanantes|solicitar/.test(path)
    ) {
        return 'form';
    }

    if (layout === 'portal') {
        return path === '/portal' ? 'dashboard' : 'history';
    }

    if (path === '/') {
        return 'home';
    }

    if (
        /^\/(habitaciones|espacios|servicios|promociones|restaurante)$/.test(
            path,
        )
    ) {
        return 'catalog';
    }

    if (/^\/(habitaciones|espacios|servicios)\/.+/.test(path)) {
        return 'detail';
    }

    return 'info';
}

function HeadingSkeleton() {
    return (
        <div className="space-y-3">
            <Skeleton className="h-4 w-24" />
            <Skeleton className="h-9 w-3/4 max-w-96" />
            <Skeleton className="h-4 w-full max-w-lg" />
        </div>
    );
}

function FieldsSkeleton({ count = 4 }: { count?: number }) {
    return (
        <div className="space-y-5">
            {Array.from({ length: count }, (_, index) => (
                <div key={index} className="space-y-2">
                    <Skeleton className="h-3 w-28" />
                    <Skeleton className="h-11 w-full rounded-xl" />
                </div>
            ))}
            <Skeleton className="h-11 w-full rounded-full" />
        </div>
    );
}

function CardsSkeleton() {
    return (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }, (_, index) => (
                <div
                    key={index}
                    className="overflow-hidden rounded-3xl border border-border bg-card"
                >
                    <Skeleton className="aspect-4/3 w-full rounded-none" />
                    <div className="space-y-4 p-5">
                        <Skeleton className="h-5 w-3/4" />
                        <Skeleton className="h-3 w-full" />
                        <Skeleton className="h-3 w-2/3" />
                        <div className="flex items-center justify-between gap-4">
                            <Skeleton className="h-7 w-24" />
                            <Skeleton className="h-10 w-28 rounded-full" />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}

export function PageSkeleton({
    href,
    layout = 'public',
    initial = false,
}: {
    href: string;
    layout?: SkeletonLayout;
    initial?: boolean;
}) {
    const variant = variantFor(href, layout);
    const compact = layout === 'auth';

    return (
        <section
            role="status"
            aria-live="polite"
            aria-label="Cargando página"
            data-page-skeleton={variant}
            className={
                compact
                    ? 'py-2'
                    : 'mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8'
            }
        >
            <span className="sr-only">
                Cargando contenido, espera un momento.
            </span>
            <div aria-hidden="true" className="min-w-0 space-y-8">
                {initial && (
                    <div className="flex items-center justify-between gap-8 border-b border-border pb-5">
                        <Skeleton className="h-10 w-36" />
                        <Skeleton className="h-9 w-24 rounded-full" />
                    </div>
                )}
                {!compact && <HeadingSkeleton />}
                {variant === 'home' && (
                    <>
                        <Skeleton className="h-64 w-full rounded-3xl sm:h-96" />
                        <div className="flex gap-3 overflow-hidden">
                            <Skeleton className="h-11 w-40 shrink-0 rounded-full" />
                            <Skeleton className="h-11 w-40 shrink-0 rounded-full" />
                            <Skeleton className="h-11 w-40 shrink-0 rounded-full" />
                        </div>
                        <CardsSkeleton />
                    </>
                )}
                {variant === 'catalog' && (
                    <>
                        <div className="flex flex-wrap gap-3">
                            <Skeleton className="h-11 w-64 max-w-full rounded-full" />
                            <Skeleton className="h-11 w-32 rounded-full" />
                            <Skeleton className="h-11 w-32 rounded-full" />
                        </div>
                        <CardsSkeleton />
                    </>
                )}
                {variant === 'detail' && (
                    <>
                        <div className="grid gap-4 md:grid-cols-3">
                            <Skeleton className="h-64 rounded-3xl md:col-span-2 md:h-96" />
                            <div className="hidden space-y-4 md:block">
                                <Skeleton className="h-44 rounded-2xl" />
                                <Skeleton className="h-44 rounded-2xl" />
                            </div>
                        </div>
                        <div className="grid gap-8 lg:grid-cols-3">
                            <div className="space-y-4 lg:col-span-2">
                                <Skeleton className="h-7 w-2/3" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-3/4" />
                            </div>
                            <div className="rounded-3xl border border-border p-6">
                                <FieldsSkeleton count={2} />
                            </div>
                        </div>
                    </>
                )}
                {variant === 'form' &&
                    (compact ? (
                        <FieldsSkeleton count={3} />
                    ) : (
                        <div className="grid gap-8 lg:grid-cols-3">
                            <div className="space-y-6 rounded-3xl border border-border bg-card p-5 sm:p-8 lg:col-span-2">
                                <Skeleton className="h-6 w-2/3" />
                                <FieldsSkeleton />
                            </div>
                            <div className="space-y-5 rounded-3xl border border-border bg-card p-6">
                                <Skeleton className="aspect-video w-full rounded-xl" />
                                <Skeleton className="h-5 w-3/4" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-9 w-1/2" />
                            </div>
                        </div>
                    ))}
                {variant === 'dashboard' && (
                    <>
                        <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                            {Array.from({ length: 4 }, (_, index) => (
                                <div
                                    key={index}
                                    className="space-y-4 rounded-2xl border border-border p-5"
                                >
                                    <Skeleton className="size-9 rounded-xl" />
                                    <Skeleton className="h-8 w-16" />
                                    <Skeleton className="h-3 w-3/4" />
                                </div>
                            ))}
                        </div>
                        <CardsSkeleton />
                    </>
                )}
                {variant === 'history' && (
                    <div className="space-y-5">
                        {Array.from({ length: 3 }, (_, index) => (
                            <div
                                key={index}
                                className="flex flex-col gap-5 rounded-3xl border border-border p-5 sm:flex-row"
                            >
                                <Skeleton className="h-36 w-full shrink-0 rounded-xl sm:w-44" />
                                <div className="flex-1 space-y-4">
                                    <Skeleton className="h-5 w-2/3" />
                                    <Skeleton className="h-4 w-full" />
                                    <Skeleton className="h-4 w-3/4" />
                                    <Skeleton className="h-10 w-32 rounded-full" />
                                </div>
                            </div>
                        ))}
                    </div>
                )}
                {variant === 'info' && (
                    <>
                        <Skeleton className="h-64 w-full rounded-3xl" />
                        <div className="space-y-4">
                            <Skeleton className="h-6 w-1/2" />
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-4/5" />
                        </div>
                    </>
                )}
            </div>
        </section>
    );
}

export function PaymentFormSkeleton() {
    return (
        <div
            role="status"
            aria-label="Cargando formulario de pago"
            className="space-y-5 rounded-2xl border border-border p-5"
        >
            <span className="sr-only">Cargando formulario de pago seguro.</span>
            <div aria-hidden="true">
                <FieldsSkeleton count={3} />
            </div>
        </div>
    );
}

export function PageLoadError() {
    return (
        <div
            role="alert"
            className="mx-auto flex min-h-64 max-w-lg flex-col items-center justify-center gap-5 p-6 text-center"
        >
            <p>No se pudo cargar la página.</p>
            <button
                type="button"
                onClick={() => window.location.reload()}
                className="rounded-full bg-primary px-6 py-3 text-primary-foreground"
            >
                Volver a intentar
            </button>
        </div>
    );
}

export function AvailabilitySkeleton() {
    return (
        <div role="status" aria-label="Consultando disponibilidad">
            <span className="sr-only">Consultando horarios disponibles.</span>
            <div
                aria-hidden="true"
                className="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6"
            >
                {Array.from({ length: 6 }, (_, index) => (
                    <Skeleton key={index} className="h-16 w-full rounded-xl" />
                ))}
            </div>
        </div>
    );
}
