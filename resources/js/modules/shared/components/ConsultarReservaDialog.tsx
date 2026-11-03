import { Search } from 'lucide-react';
import { useState } from 'react';
import { useAccesoCodigoForm } from '@/modules/clientes/hooks/useAccesoCodigoForm';
import { Button } from '@/modules/shared/components/ui/button';
import { Field, FieldError, FieldGroup, FieldLabel } from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/modules/shared/components/ui/sheet';

export const ConsultarReservaDialog = () => {
    const [abierto, setAbierto] = useState(false);

    // useAccesoCodigoForm returns { form, register, setValue, handleSubmit, isSubmitting, errors }
    // handleSubmit is already form.handleSubmit(onSubmit) — use as form onSubmit
    // isSubmitting is form.formState.isSubmitting
    const { form, handleSubmit, isSubmitting, errors } = useAccesoCodigoForm({
        onSuccessCallback: () => setAbierto(false),
    });

    return (
        <>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => setAbierto(true)}
                className="hidden items-center gap-1.5 rounded-full px-3 text-xs font-bold text-muted-foreground hover:text-foreground sm:inline-flex"
            >
                <Search className="size-3.5" />
                <span>Mi Reserva</span>
            </Button>

            <Sheet open={abierto} onOpenChange={setAbierto}>
                <SheetContent side="bottom" className="mx-auto max-w-sm rounded-t-2xl p-6">
                    <SheetHeader className="p-0 pb-4">
                        <SheetTitle className="text-sm font-black">Consultar mi Reserva</SheetTitle>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Ingresa tu código de confirmación para ver el estado de tu reserva.
                        </SheetDescription>
                    </SheetHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <FieldGroup>
                            <Field>
                                <FieldLabel>Código de Reserva</FieldLabel>
                                <Input
                                    {...form.register('codigo')}
                                    placeholder="Ej: RES-2024-001"
                                    className="font-mono uppercase"
                                    autoFocus
                                />
                                {errors.codigo && (
                                    <FieldError>{errors.codigo.message}</FieldError>
                                )}
                            </Field>
                        </FieldGroup>
                        <Button
                            type="submit"
                            className="w-full rounded-xl text-xs font-bold"
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Buscando...' : 'Ver mi Reserva'}
                        </Button>
                    </form>
                </SheetContent>
            </Sheet>
        </>
    );
};

export default ConsultarReservaDialog;
