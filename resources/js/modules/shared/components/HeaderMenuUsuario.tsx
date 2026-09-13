import { Link, router } from '@inertiajs/react';
import {
    BedDouble,
    ChevronDown,
    KeyRound,
    LayoutDashboard,
    LogIn,
    LogOut,
    Shield,
    Sparkles,
} from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/modules/shared/components/ui/dropdown-menu';
import type { UsuarioAuth } from '@/modules/shared/hooks/usePropiedadesPagina';

interface HeaderMenuUsuarioProps {
    usuario: UsuarioAuth | null | undefined;
    iniciales: string;
}

export const HeaderMenuUsuario = ({
    usuario,
    iniciales,
}: HeaderMenuUsuarioProps) => {
    if (!usuario) {
        return (
            <Link
                href="/auth/login"
                className={buttonVariants({
                    variant: 'ghost',
                    size: 'sm',
                    className:
                        'hidden items-center gap-1.5 rounded-full px-3 text-xs font-bold text-muted-foreground hover:text-foreground sm:inline-flex',
                })}
            >
                <LogIn className="size-3.5" />
                <span>Iniciar Sesión</span>
            </Link>
        );
    }

    return (
        <div className="hidden sm:block">
            <DropdownMenu>
                <DropdownMenuTrigger className="group inline-flex cursor-pointer items-center gap-2.5 rounded-full border border-border/80 bg-card/90 py-1 pr-3 pl-1.5 shadow-xs transition-all hover:border-primary/40 hover:bg-card focus:outline-none">
                    <div className="relative flex size-7 items-center justify-center rounded-full bg-gradient-to-tr from-primary to-rose-400 text-[11px] font-black text-white shadow-xs">
                        {iniciales}
                        <span className="absolute -right-0.5 -bottom-0.5 size-2.5 rounded-full bg-emerald-500 ring-2 ring-card" />
                    </div>
                    <div className="flex flex-col text-left">
                        <span className="max-w-28 truncate text-xs font-bold text-foreground">
                            {(usuario.name || 'Huésped').split(' ')[0]}
                        </span>
                        <span className="text-[9px] font-black tracking-wider text-primary uppercase dark:text-rose-400">
                            {usuario.is_admin ? 'Admin' : 'VIP'}
                        </span>
                    </div>
                    <ChevronDown className="size-3 text-muted-foreground transition-transform group-hover:translate-y-0.5" />
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    align="end"
                    className="w-64 border-border/80 p-2 font-sans shadow-xl"
                >
                    <DropdownMenuLabel className="rounded-xl bg-muted/40 p-3">
                        <div className="flex items-center gap-3">
                            <div className="flex size-9 items-center justify-center rounded-full bg-gradient-to-tr from-primary to-rose-400 text-xs font-black text-white shadow-xs">
                                {iniciales}
                            </div>
                            <div className="min-w-0 flex-1">
                                <div className="flex items-center gap-1.5">
                                    <p className="truncate text-xs font-bold text-foreground">
                                        {usuario.name || 'Huésped'}
                                    </p>
                                </div>
                                <p className="truncate text-[11px] text-muted-foreground">
                                    {usuario.email}
                                </p>
                                <span className="mt-1 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[9px] font-black text-primary dark:text-rose-300">
                                    {usuario.is_admin ? (
                                        <>
                                            <Shield className="size-2.5" />
                                            <span>Administrador</span>
                                        </>
                                    ) : (
                                        <>
                                            <Sparkles className="size-2.5" />
                                            <span>Huésped VIP</span>
                                        </>
                                    )}
                                </span>
                            </div>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator className="my-1.5" />

                    {usuario.is_admin && (
                        <DropdownMenuItem
                            onClick={() => router.visit('/admin')}
                            className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-bold text-primary focus:bg-primary/10 dark:text-rose-400"
                        >
                            <LayoutDashboard className="size-4" />
                            <span>Panel de Control Admin</span>
                        </DropdownMenuItem>
                    )}

                    <DropdownMenuItem
                        onClick={() => router.visit('/portal')}
                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-bold text-primary focus:bg-primary/10"
                    >
                        <LayoutDashboard className="size-4 text-primary" />
                        <span>Portal de Huéspedes</span>
                    </DropdownMenuItem>

                    <DropdownMenuItem
                        onClick={() => router.visit('/portal/reservas')}
                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-medium text-foreground focus:bg-muted"
                    >
                        <BedDouble className="size-4 text-muted-foreground" />
                        <span>Mis Reservas & Estancias</span>
                    </DropdownMenuItem>

                    <DropdownMenuItem
                        onClick={() => router.visit('/auth/cambiar-contrasena')}
                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-medium text-foreground focus:bg-muted"
                    >
                        <KeyRound className="size-4 text-muted-foreground" />
                        <span>Seguridad & Contraseña</span>
                    </DropdownMenuItem>

                    <DropdownMenuSeparator className="my-1.5" />

                    <DropdownMenuItem
                        variant="destructive"
                        onClick={() => router.post('/auth/logout')}
                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-bold text-destructive focus:bg-destructive/10"
                    >
                        <LogOut className="size-4" />
                        <span>Cerrar Sesión</span>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
};

export default HeaderMenuUsuario;
