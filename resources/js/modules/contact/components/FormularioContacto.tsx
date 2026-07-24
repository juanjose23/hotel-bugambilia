import { CheckCircle2, ArrowRight, Sparkles } from 'lucide-react';
import type React from 'react';
import { useState } from 'react';
const FormularioContacto = () => {
    const [isSubmitted, setIsSubmitted] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [formData, setFormData] = useState({
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        subject: 'reserva',
        message: '',
    });
    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        await new Promise((resolve) => setTimeout(resolve, 1200));
        setIsLoading(false);
        setIsSubmitted(true);
    };

    if (isSubmitted) {
        return (
            <div className="shadow-airbnb rounded-3xl border border-border/80 bg-card p-8 text-center font-sans sm:p-12">
                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                    <CheckCircle2 className="h-8 w-8 text-emerald-500" />
                </div>
                <h3 className="mb-2 text-2xl font-black text-foreground">
                    Mensaje Enviado con Éxito
                </h3>
                <p className="mx-auto mb-6 max-w-md text-xs leading-relaxed text-muted-foreground sm:text-sm">
                    Gracias por comunicarse con nuestro equipo. Nos pondremos en
                    contacto con usted en un plazo máximo de 2 horas.
                </p>
                <button
                    onClick={() => setIsSubmitted(false)}
                    className="shadow-airbnb cursor-pointer rounded-full bg-bugambilia-600 px-8 py-3 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-200 hover:bg-bugambilia-700"
                >
                    Enviar Otro Mensaje
                </button>
            </div>
        );
    }

    return (
        <div className="shadow-airbnb-hover rounded-3xl border border-border/80 bg-card p-6 font-sans sm:p-10">
            <div className="mb-6">
                <div className="mb-2 inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                    <Sparkles className="h-3.5 w-3.5" />
                    Formulario Directo
                </div>
                <h2 className="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                    Envíenos un{' '}
                    <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                        Mensaje
                    </span>
                </h2>
                <p className="mt-1 text-xs text-muted-foreground">
                    Complete los campos y un representante de recepción le
                    responderá a la brevedad.
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            htmlFor="firstName"
                            className="mb-1 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                        >
                            Nombre *
                        </label>
                        <input
                            id="firstName"
                            type="text"
                            placeholder="Su nombre"
                            value={formData.firstName}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    firstName: e.target.value,
                                })
                            }
                            required
                            className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground outline-none focus:border-bugambilia-500"
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="lastName"
                            className="mb-1 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                        >
                            Apellido *
                        </label>
                        <input
                            id="lastName"
                            type="text"
                            placeholder="Su apellido"
                            value={formData.lastName}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    lastName: e.target.value,
                                })
                            }
                            required
                            className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground outline-none focus:border-bugambilia-500"
                        />
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            htmlFor="email"
                            className="mb-1 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                        >
                            Correo Electrónico *
                        </label>
                        <input
                            id="email"
                            type="email"
                            placeholder="ejemplo@correo.com"
                            value={formData.email}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    email: e.target.value,
                                })
                            }
                            required
                            className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground outline-none focus:border-bugambilia-500"
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="phone"
                            className="mb-1 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                        >
                            Teléfono / WhatsApp
                        </label>
                        <input
                            id="phone"
                            type="tel"
                            placeholder="+505 8713 6805"
                            value={formData.phone}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    phone: e.target.value,
                                })
                            }
                            className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground outline-none focus:border-bugambilia-500"
                        />
                    </div>
                </div>

                <div>
                    <label
                        htmlFor="subject"
                        className="mb-1 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                    >
                        Asunto de la Consulta
                    </label>
                    <select
                        id="subject"
                        value={formData.subject}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                subject: e.target.value,
                            })
                        }
                        className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground outline-none focus:border-bugambilia-500"
                    >
                        <option value="reserva">
                            Reservaciones de Habitaciones
                        </option>
                        <option value="servicios">
                            Servicios & Gastronomía
                        </option>
                        <option value="eventos">
                            Eventos, Bodas & Conferencias
                        </option>
                        <option value="transporte">Transporte Privado</option>
                        <option value="otro">Otro Requerimiento</option>
                    </select>
                </div>

                <div>
                    <label
                        htmlFor="message"
                        className="mb-1 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                    >
                        Mensaje *
                    </label>
                    <textarea
                        id="message"
                        rows={4}
                        placeholder="Escriba aquí los detalles de su consulta o fechas de interés..."
                        value={formData.message}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                message: e.target.value,
                            })
                        }
                        required
                        className="w-full resize-none rounded-2xl border border-border/80 bg-background p-4 text-xs text-foreground outline-none focus:border-bugambilia-500"
                    />
                </div>

                <button
                    type="submit"
                    disabled={isLoading}
                    className="shadow-airbnb hover:shadow-airbnb-hover mt-2 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-bugambilia-600 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-[1.02] hover:bg-bugambilia-700"
                >
                    {isLoading ? (
                        <span>Enviando mensaje...</span>
                    ) : (
                        <>
                            <span>Enviar Mensaje</span>
                            <ArrowRight className="h-4 w-4" />
                        </>
                    )}
                </button>
            </form>
        </div>
    );
};
export default FormularioContacto;
