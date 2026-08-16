import { ArrowRight, BadgeCheck } from 'lucide-react';
import { Textarea } from '@/modulos/compartido/ui/area-texto';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Badge } from '@/modulos/compartido/ui/insignia';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/modulos/compartido/ui/selector';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { OPCIONES_ASUNTO_CONTACTO } from '../constantes/contactoConstantes';
import { useFormularioContacto } from '../hooks/useFormularioContacto';
import { MensajeExitoContacto } from './secciones/MensajeExitoContacto';

export default function FormularioContacto() {
    const {
        formData,
        errors,
        isLoading,
        isSubmitted,
        setFieldValue,
        enviarFormulario,
        reiniciarFormulario,
    } = useFormularioContacto();

    if (isSubmitted) {
        return <MensajeExitoContacto onReiniciar={reiniciarFormulario} />;
    }

    return (
        <Card className="rounded-3xl border-border/80 bg-card p-6 font-sans sm:p-10">
            <CardContent className="p-0">
                <div className="mb-6 flex flex-col gap-2">
                    <div>
                        <Badge
                            variant="outline"
                            className="border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                        >
                            <BadgeCheck
                                className="mr-1 size-3.5"
                                data-icon="inline-start"
                            />{' '}
                            Formulario Directo
                        </Badge>
                    </div>
                    <h2 className="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        Envíenos un{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            Mensaje
                        </span>
                    </h2>
                    <p className="text-xs text-muted-foreground">
                        Complete los campos y un representante de recepción le
                        responderá a la brevedad.
                    </p>
                </div>

                <form
                    onSubmit={enviarFormulario}
                    className="flex flex-col gap-4"
                >
                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="flex flex-col gap-1">
                            <label
                                htmlFor="firstName"
                                className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                            >
                                Nombre *
                            </label>
                            <Input
                                id="firstName"
                                type="text"
                                placeholder="Su nombre"
                                value={formData.firstName}
                                onChange={(e) =>
                                    setFieldValue('firstName', e.target.value)
                                }
                            />
                            {errors.firstName && (
                                <span className="text-xs text-destructive">
                                    {errors.firstName}
                                </span>
                            )}
                        </div>
                        <div className="flex flex-col gap-1">
                            <label
                                htmlFor="lastName"
                                className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                            >
                                Apellido *
                            </label>
                            <Input
                                id="lastName"
                                type="text"
                                placeholder="Su apellido"
                                value={formData.lastName}
                                onChange={(e) =>
                                    setFieldValue('lastName', e.target.value)
                                }
                            />
                            {errors.lastName && (
                                <span className="text-xs text-destructive">
                                    {errors.lastName}
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="flex flex-col gap-1">
                            <label
                                htmlFor="email"
                                className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                            >
                                Correo Electrónico *
                            </label>
                            <Input
                                id="email"
                                type="email"
                                placeholder="ejemplo@correo.com"
                                value={formData.email}
                                onChange={(e) =>
                                    setFieldValue('email', e.target.value)
                                }
                            />
                            {errors.email && (
                                <span className="text-xs text-destructive">
                                    {errors.email}
                                </span>
                            )}
                        </div>
                        <div className="flex flex-col gap-1">
                            <label
                                htmlFor="phone"
                                className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                            >
                                Teléfono / WhatsApp
                            </label>
                            <Input
                                id="phone"
                                type="tel"
                                placeholder="+505 8713 6805"
                                value={formData.phone || ''}
                                onChange={(e) =>
                                    setFieldValue('phone', e.target.value)
                                }
                            />
                        </div>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label
                            htmlFor="subject"
                            className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                        >
                            Asunto de la Consulta
                        </label>
                        <Select
                            value={formData.subject}
                            onValueChange={(val: string) =>
                                setFieldValue('subject', val)
                            }
                        >
                            <SelectTrigger
                                id="subject"
                                className="w-full rounded-2xl bg-background"
                            >
                                <SelectValue placeholder="Seleccione un asunto" />
                            </SelectTrigger>
                            <SelectContent>
                                {OPCIONES_ASUNTO_CONTACTO.map((opcion) => (
                                    <SelectItem
                                        key={opcion.value}
                                        value={opcion.value}
                                    >
                                        {opcion.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.subject && (
                            <span className="text-xs text-destructive">
                                {errors.subject}
                            </span>
                        )}
                    </div>

                    <div className="flex flex-col gap-1">
                        <label
                            htmlFor="message"
                            className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                        >
                            Mensaje *
                        </label>
                        <Textarea
                            id="message"
                            rows={4}
                            placeholder="Escriba aquí los detalles de su consulta o fechas de interés..."
                            value={formData.message}
                            onChange={(e) =>
                                setFieldValue('message', e.target.value)
                            }
                            className="resize-none"
                        />
                        {errors.message && (
                            <span className="text-xs text-destructive">
                                {errors.message}
                            </span>
                        )}
                    </div>

                    <Button
                        type="submit"
                        disabled={isLoading}
                        size="lg"
                        className="mt-2 w-full rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700"
                    >
                        {isLoading ? (
                            <span>Enviando mensaje...</span>
                        ) : (
                            <>
                                <span>Enviar Mensaje</span>
                                <ArrowRight
                                    className="size-4"
                                    data-icon="inline-end"
                                />
                            </>
                        )}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
