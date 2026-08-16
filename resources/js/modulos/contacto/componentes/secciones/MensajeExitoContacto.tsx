import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesMensajeExitoContacto {
    onReiniciar: () => void;
}

export function MensajeExitoContacto({
    onReiniciar,
}: PropiedadesMensajeExitoContacto) {
    return (
        <Card className="rounded-3xl border-border/80 bg-card p-8 text-center font-sans sm:p-12">
            <CardContent className="flex flex-col items-center p-0">
                <div className="mb-4 flex size-14 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                    <CheckCircle2 className="size-8 text-emerald-500" />
                </div>
                <h3 className="mb-2 text-2xl font-black text-foreground">
                    Mensaje Enviado con Éxito
                </h3>
                <p className="mx-auto mb-6 max-w-md text-xs leading-relaxed text-muted-foreground sm:text-sm">
                    Gracias por comunicarse con nuestro equipo. Nos pondremos en
                    contacto con usted en un plazo máximo de 2 horas.
                </p>
                <Button
                    onClick={onReiniciar}
                    size="lg"
                    className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700"
                >
                    Enviar Otro Mensaje
                </Button>
            </CardContent>
        </Card>
    );
}
