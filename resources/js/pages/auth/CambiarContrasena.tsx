import { useForm } from '@inertiajs/react';
import { Lock, KeyRound } from 'lucide-react';
import { LayoutPublico } from '@/modules/shared/components/layouts/LayoutPublico';

export default function CambiarContrasena() {
    const { data, setData, post, processing, errors } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    return (
        <LayoutPublico>
            <div className="flex min-h-screen items-center justify-center bg-background p-4">
                <div className="w-full max-w-md rounded-3xl border border-border bg-card p-8 shadow-xl">
                    <div className="mb-8 text-center">
                        <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-bugambilia-500/10">
                            <KeyRound className="h-7 w-7 text-bugambilia-600" />
                        </div>
                        <h1 className="text-2xl font-black tracking-tight">
                            Cambiar contraseña
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Por seguridad, debe cambiar su contraseña temporal
                            antes de continuar.
                        </p>
                    </div>

                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            post('/cambiar-contrasena');
                        }}
                        className="space-y-4"
                    >
                        <div>
                            <label className="mb-1.5 block text-xs font-extrabold tracking-wider uppercase">
                                Contraseña actual
                            </label>
                            <div className="relative">
                                <Lock className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                <input
                                    type="password"
                                    placeholder="Contraseña temporal"
                                    required
                                    value={data.current_password}
                                    onChange={(e) =>
                                        setData(
                                            'current_password',
                                            e.target.value,
                                        )
                                    }
                                    className="w-full rounded-2xl border border-border bg-background py-3 pr-4 pl-10 text-sm transition-colors outline-none focus:border-bugambilia-500"
                                />
                            </div>
                            {errors.current_password && (
                                <p className="mt-1 text-xs font-semibold text-rose-500">
                                    {errors.current_password}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-1.5 block text-xs font-extrabold tracking-wider uppercase">
                                Nueva contraseña
                            </label>
                            <div className="relative">
                                <Lock className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                <input
                                    type="password"
                                    placeholder="Mínimo 8 caracteres"
                                    required
                                    value={data.password}
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    className="w-full rounded-2xl border border-border bg-background py-3 pr-4 pl-10 text-sm transition-colors outline-none focus:border-bugambilia-500"
                                />
                            </div>
                            {errors.password && (
                                <p className="mt-1 text-xs font-semibold text-rose-500">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-1.5 block text-xs font-extrabold tracking-wider uppercase">
                                Confirmar contraseña
                            </label>
                            <div className="relative">
                                <Lock className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                <input
                                    type="password"
                                    placeholder="Repita la contraseña"
                                    required
                                    value={data.password_confirmation}
                                    onChange={(e) =>
                                        setData(
                                            'password_confirmation',
                                            e.target.value,
                                        )
                                    }
                                    className="w-full rounded-2xl border border-border bg-background py-3 pr-4 pl-10 text-sm transition-colors outline-none focus:border-bugambilia-500"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-full bg-bugambilia-600 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-all hover:bg-bugambilia-700 disabled:opacity-50"
                        >
                            {processing ? 'Cambiando...' : 'Cambiar contraseña'}
                        </button>
                    </form>
                </div>
            </div>
        </LayoutPublico>
    );
}
