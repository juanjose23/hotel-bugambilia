import { Link, router } from '@inertiajs/react';
import { LogOut, Sun, Moon } from 'lucide-react';
import type { ReactNode } from 'react';
import { Toast } from '@/modules/shared/components/Toast';
import { Button } from '@/modules/shared/components/ui/button';
import { useTema } from '@/modules/shared/hooks/useTema';
import type { ClienteProfile } from '../../types';
import { PortalBottomNav } from './PortalBottomNav';
import { PortalSidebar } from './PortalSidebar';

export interface PortalLayoutProps {
    children: ReactNode;
    cliente?: ClienteProfile;
}

export const PortalLayout = ({ children, cliente }: PortalLayoutProps) => {
    const { tema, alternarTema } = useTema();

    const iniciales = cliente?.nombre
        ? cliente.nombre
              .split(' ')
              .map((n) => n[0])
              .slice(0, 2)
              .join('')
              .toUpperCase()
        : 'HB';

    return (
        <div className="flex min-h-screen bg-background font-sans text-foreground selection:bg-primary selection:text-white">
            <Toast />
            <PortalSidebar cliente={cliente} />

            <div className="flex min-w-0 flex-1 flex-col pb-20 lg:pb-0">
                {/* Header Superior Móvil */}
                <header className="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-border/80 bg-background/95 px-4 backdrop-blur-lg lg:hidden">
                    <Link href="/portal" className="flex items-center gap-2">
                        <img
                            src="/images/logo-dark.webp"
                            alt="Hotel Bugambilias"
                            className="hidden h-8 w-auto object-contain dark:block"
                        />
                        <img
                            src="/images/logo-white.webp"
                            alt="Hotel Bugambilias"
                            className="block h-8 w-auto object-contain dark:hidden"
                        />
                    </Link>

                    <div className="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            onClick={alternarTema}
                            className="size-8 rounded-full"
                        >
                            {tema === 'dark' ? (
                                <Sun className="size-4 text-amber-400" />
                            ) : (
                                <Moon className="size-4 text-muted-foreground" />
                            )}
                        </Button>

                        <Link
                            href="/portal/perfil"
                            className="flex size-8 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/80 text-xs font-bold text-white shadow-xs"
                            title="Ver Perfil"
                        >
                            {iniciales}
                        </Link>

                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            onClick={() => router.post('/auth/logout')}
                            className="size-8 rounded-full text-destructive hover:bg-destructive/10 hover:text-destructive"
                            title="Cerrar sesión"
                        >
                            <LogOut className="size-4" />
                        </Button>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto">{children}</main>
            </div>

            <PortalBottomNav />
        </div>
    );
};

export default PortalLayout;
