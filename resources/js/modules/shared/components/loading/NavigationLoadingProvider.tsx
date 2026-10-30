import { router } from '@inertiajs/react';
import { createContext, useContext, useEffect, useState } from 'react';
import type { PropsWithChildren } from 'react';
import { PageSkeleton } from '../skeletons/PageSkeleton';
import type { SkeletonLayout } from '../skeletons/PageSkeleton';

const NavigationLoadingContext = createContext<string | null>(null);

export function NavigationLoadingProvider({ children }: PropsWithChildren) {
    const [href, setHref] = useState<string | null>(null);

    useEffect(() => {
        let activeVisit: object | null = null;
        let timer: ReturnType<typeof setTimeout> | undefined;
        const clear = () => {
            clearTimeout(timer);
            timer = undefined;
            activeVisit = null;
            setHref(null);
        };
        const stopStart = router.on('start', ({ detail: { visit } }) => {
            if (
                visit.method !== 'get' ||
                visit.async ||
                visit.prefetch ||
                visit.only.length > 0 ||
                visit.except.length > 0 ||
                (visit.url.pathname === window.location.pathname &&
                    visit.preserveState === true)
            ) {
                return;
            }

            clearTimeout(timer);
            activeVisit = visit;
            // Short visits keep their content; there is no minimum loading time.
            timer = setTimeout(() => setHref(visit.url.href), 120);
        });
        const stopFinish = router.on('finish', ({ detail: { visit } }) => {
            if (visit === activeVisit) {
                clear();
            }
        });

        return () => {
            clearTimeout(timer);
            stopStart();
            stopFinish();
        };
    }, []);

    return (
        <NavigationLoadingContext.Provider value={href}>
            {children}
        </NavigationLoadingContext.Provider>
    );
}

export function PageLoadingBoundary({
    children,
    layout = 'public',
}: PropsWithChildren<{ layout?: SkeletonLayout }>) {
    const href = useContext(NavigationLoadingContext);

    return (
        <div className="min-w-0" data-page-loading={href ? 'true' : 'false'}>
            <div hidden={href !== null} inert={href !== null}>
                {children}
            </div>
            {href !== null && <PageSkeleton href={href} layout={layout} />}
        </div>
    );
}
