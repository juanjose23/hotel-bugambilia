import { Head } from '@inertiajs/react';
import FormularioContacto from '@/modules/contact/components/FormularioContacto';
import InformacionContacto from '@/modules/contact/components/InformacionContacto';
import PortadaContacto from '@/modules/contact/components/PortadaContacto';
const Contacto = () => {
    return (
        <>
            <Head>
                <title>Contacto — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Contacta al Hotel Bugambilias Estelí — Reservaciones, ubicación y atención al cliente. Estamos para servirte."
                />
            </Head>
            <PortadaContacto />
            <section className="bg-background py-16 md:py-24">
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="grid gap-8 lg:grid-cols-5 lg:gap-12">
                        <div className="lg:col-span-3">
                            <FormularioContacto />
                        </div>
                        <div className="lg:col-span-2">
                            <InformacionContacto />
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
};
export default Contacto;
