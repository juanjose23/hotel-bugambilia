import { usePage } from '@inertiajs/react';
import { Calendar, Phone } from 'lucide-react';
import { Button } from '@/modules/shared/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/modules/shared/ui/card';
import { Input } from '@/modules/shared/ui/input';
import { Label } from '@/modules/shared/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/ui/select';

export default function BookingSection() {
    const { hotel } = usePage().props;

    return (
        <section className="bg-gradient-to-b from-bugambilia-50/30 to-gray-50 py-16 dark:from-bugambilia-900/10 dark:to-gray-800">
            <div className="container mx-auto px-4">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-12 text-center">
                        <h2 className="mb-4 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white">
                            Reserva tu Habitación
                            <span className="mt-2 block text-lg text-bugambilia-600 dark:text-bugambilia-400">
                                Proceso rápido y seguro
                            </span>
                        </h2>
                        <p className="text-lg text-gray-600 dark:text-gray-300">
                            Completa el formulario o llámanos directamente para
                            hacer tu reserva
                        </p>
                    </div>

                    <div className="grid gap-8 lg:grid-cols-3">
                        <div className="lg:col-span-2">
                            <Card className="petal-shadow bg-white dark:bg-gray-800">
                                <CardHeader>
                                    <CardTitle className="text-xl text-gray-900 dark:text-white">
                                        Formulario de Reserva
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <Label
                                                htmlFor="checkin"
                                                className="text-gray-700 dark:text-gray-300"
                                            >
                                                Fecha de llegada
                                            </Label>
                                            <div className="relative">
                                                <Input
                                                    id="checkin"
                                                    type="date"
                                                    className="border-gray-300 pl-10 dark:border-gray-600"
                                                />
                                                <Calendar className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                            </div>
                                        </div>
                                        <div>
                                            <Label
                                                htmlFor="checkout"
                                                className="text-gray-700 dark:text-gray-300"
                                            >
                                                Fecha de salida
                                            </Label>
                                            <div className="relative">
                                                <Input
                                                    id="checkout"
                                                    type="date"
                                                    className="border-gray-300 pl-10 dark:border-gray-600"
                                                />
                                                <Calendar className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <Label
                                                htmlFor="guests"
                                                className="text-gray-700 dark:text-gray-300"
                                            >
                                                Número de huéspedes
                                            </Label>
                                            <Select>
                                                <SelectTrigger className="border-gray-300 dark:border-gray-600">
                                                    <SelectValue placeholder="Seleccionar" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="1">
                                                        1 huésped
                                                    </SelectItem>
                                                    <SelectItem value="2">
                                                        2 huéspedes
                                                    </SelectItem>
                                                    <SelectItem value="3">
                                                        3 huéspedes
                                                    </SelectItem>
                                                    <SelectItem value="4">
                                                        4 huéspedes
                                                    </SelectItem>
                                                    <SelectItem value="5">
                                                        5+ huéspedes
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Label
                                                htmlFor="room-type"
                                                className="text-gray-700 dark:text-gray-300"
                                            >
                                                Tipo de habitación
                                            </Label>
                                            <Select>
                                                <SelectTrigger className="border-gray-300 dark:border-gray-600">
                                                    <SelectValue placeholder="Seleccionar" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="economica">
                                                        Habitación Económica
                                                    </SelectItem>
                                                    <SelectItem value="principal">
                                                        Habitación Principal
                                                    </SelectItem>
                                                    <SelectItem value="grupal">
                                                        Habitación Grupal
                                                    </SelectItem>
                                                    <SelectItem value="suite">
                                                        Suite Especial
                                                    </SelectItem>
                                                    <SelectItem value="familiar">
                                                        Habitación Familiar
                                                    </SelectItem>
                                                    <SelectItem value="presidencial">
                                                        Suite Presidencial
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <Label
                                                htmlFor="name"
                                                className="text-gray-700 dark:text-gray-300"
                                            >
                                                Nombre completo
                                            </Label>
                                            <Input
                                                id="name"
                                                placeholder="Tu nombre completo"
                                                className="border-gray-300 dark:border-gray-600"
                                            />
                                        </div>
                                        <div>
                                            <Label
                                                htmlFor="email"
                                                className="text-gray-700 dark:text-gray-300"
                                            >
                                                Correo electrónico
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                placeholder="tu@email.com"
                                                className="border-gray-300 dark:border-gray-600"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <Label
                                            htmlFor="phone"
                                            className="text-gray-700 dark:text-gray-300"
                                        >
                                            Teléfono
                                        </Label>
                                        <Input
                                            id="phone"
                                            placeholder="+505 1234 5678"
                                            className="border-gray-300 dark:border-gray-600"
                                        />
                                    </div>

                                    <Button className="bugambilia-primary petal-shadow w-full transition-all duration-300 hover:scale-105">
                                        Confirmar Reserva
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>

                        <div className="space-y-6">
                            <Card className="petal-shadow bg-white dark:bg-gray-800">
                                <CardContent className="p-6">
                                    <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                        Reserva por Teléfono
                                    </h3>
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-3">
                                            <Phone className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                            <div>
                                                <p className="font-medium text-gray-900 dark:text-white">
                                                    {hotel.telefono}
                                                </p>
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    Disponible 24/7
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="petal-shadow border-bugambilia-200 bg-bugambilia-50 dark:border-bugambilia-700 dark:bg-bugambilia-900/20">
                                <CardContent className="p-6">
                                    <h3 className="mb-4 text-lg font-semibold text-bugambilia-700 dark:text-bugambilia-300">
                                        Oferta Especial
                                    </h3>
                                    <p className="mb-3 text-sm text-bugambilia-600 dark:text-bugambilia-400">
                                        Reserva directamente con nosotros y
                                        accede a tarifas preferenciales.
                                    </p>
                                    <div className="text-xs text-bugambilia-500 dark:text-bugambilia-500">
                                        * Válido para estadías de 2 noches o más
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="petal-shadow bg-white dark:bg-gray-800">
                                <CardContent className="p-6">
                                    <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                        Políticas de Reserva
                                    </h3>
                                    <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>{`• Entrada: ${hotel.checkin} hrs`}</li>
                                        <li>{`• Salida: ${hotel.checkout} hrs`}</li>
                                        <li>
                                            • Cancelación gratuita hasta 24h
                                            antes
                                        </li>
                                        <li>• Se requiere depósito del 50%</li>
                                        <li>• Aceptamos efectivo y tarjetas</li>
                                    </ul>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
