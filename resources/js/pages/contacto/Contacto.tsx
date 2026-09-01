import { Head } from '@inertiajs/react';
import { ContactoFaq } from '@/modules/contacto/components/ContactoFaq';
import { ContactoForm } from '@/modules/contacto/components/ContactoForm';
import { ContactoHero } from '@/modules/contacto/components/ContactoHero';
import { ContactoInfoCards } from '@/modules/contacto/components/ContactoInfoCards';
import { ContactoMapa } from '@/modules/contacto/components/ContactoMapa';

export const Contacto = () => {
    return (
        <>
            <Head>
                <title>Contacto & Ubicación — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Comunícate con recepción de Hotel Bugambilias en Estelí, Nicaragua. Consulta disponibilidad de habitaciones, salones de eventos y cómo llegar."
                />
            </Head>

            <div className="flex flex-col">
                <ContactoHero />
                <ContactoInfoCards />

                {/* Formulario y Mapa en 2 Columnas */}
                <section className="bg-background py-8 md:py-12">
                    <div className="container mx-auto px-4 sm:px-6">
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">
                            <div className="lg:col-span-7">
                                <ContactoForm />
                            </div>
                            <div className="lg:col-span-5">
                                <ContactoMapa />
                            </div>
                        </div>
                    </div>
                </section>

                <ContactoFaq />
            </div>
        </>
    );
};

export default Contacto;
