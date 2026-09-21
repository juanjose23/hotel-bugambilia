import { router } from '@inertiajs/react';
import {
    BedDouble,
    KeyRound,
    LayoutDashboard,
    LogIn,
    LogOut,
    Menu,
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
import type { EnlaceNavegacion } from './HeaderNavegacion';

interface HeaderMenuMovilProps {
    usuario: UsuarioAuth | null | undefined;
    iniciales: string;
    listaEnlaces: EnlaceNavegacion[];
}

export const HeaderMenuMovil = ({
    usuario,
    iniciales,
    listaEnlaces,
}: HeaderMenuMovilProps) => {
    return (
        <div className="md:hidden">
            <DropdownMenu>
                <DropdownMenuTrigger
                    className={buttonVariants({
                        variant: 'outline',
                        size: 'icon',
                        className: 'cursor-pointer rounded-full',
                    })}
                    aria-label="Abrir menú"
                >
                    <Menu className="size-5" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-60 p-2 font-sans">
                    {usuario && (
                        <>
                            <DropdownMenuLabel className="rounded-lg bg-muted/40 p-2.5">
                                <div className="flex items-center gap-2.5">
                                    <div className="flex size-7 items-center justify-center rounded-full bg-gradient-to-tr from-primary to-rose-400 text-[10px] font-black text-white">
                                        {iniciales}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-xs font-bold text-foreground">
                                            {usuario.name || 'Huésped'}
                                        </p>
                                        <p className="truncate text-[10px] text-muted-foreground">
                                            {usuario.email}
                                        </p>
                                    </div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator className="my-1.5" />
                        </>
                    )}

                    {listaEnlaces.map((item) => (
                        <DropdownMenuItem
                            key={item.nombre}
                            onClick={() => router.visit(item.href)}
                            className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-foreground"
                        >
                            {item.nombre}
                        </DropdownMenuItem>
                    ))}

                    <div className="my-1 border-t border-border/60" />

                    {usuario ? (
                        <>
                            {usuario.is_admin && (
                                <DropdownMenuItem
                                    onClick={() => router.visit('/admin')}
                                    className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-primary dark:text-rose-400"
                                >
                                    <LayoutDashboard className="size-3.5" />
                                    <span>Panel Admin</span>
                                </DropdownMenuItem>
                            )}

                            <DropdownMenuItem
                                onClick={() => router.visit('/portal')}
                                className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-primary"
                            >
                                <LayoutDashboard className="size-3.5" />
                                <span>Portal Huéspedes</span>
                            </DropdownMenuItem>

                            <DropdownMenuItem
                                onClick={() => router.visit('/portal/reservas')}
                                className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium text-foreground"
                            >
                                <BedDouble className="size-3.5 text-muted-foreground" />
                                <span>Mis Reservas</span>
                            </DropdownMenuItem>

                            <DropdownMenuItem
                                onClick={() =>
                                    router.visit('/auth/cambiar-contrasena')
                                }
                                className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium text-foreground"
                            >
                                <KeyRound className="size-3.5 text-muted-foreground" />
                                <span>Cambiar Contraseña</span>
                            </DropdownMenuItem>

                            <DropdownMenuSeparator className="my-1.5" />

                            <DropdownMenuItem
                                variant="destructive"
                                onClick={() => router.post('/auth/logout')}
                                className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-destructive"
                            >
                                <LogOut className="size-3.5" />
                                <span>Cerrar Sesión</span>
                            </DropdownMenuItem>
                        </>
                    ) : (
                        <DropdownMenuItem
                            onClick={() => router.visit('/auth/login')}
                            className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-foreground"
                        >
                            <LogIn className="size-3.5" />
                            <span>Iniciar Sesión / Registro</span>
                        </DropdownMenuItem>
                    )}

                    <DropdownMenuItem
                        onClick={() => router.visit('/habitaciones')}
                        className="mt-1.5 cursor-pointer rounded-lg bg-primary py-2 text-center text-xs font-black text-primary-foreground focus:bg-primary/90"
                    >
                        Ver Habitaciones
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
};

export default HeaderMenuMovil;
