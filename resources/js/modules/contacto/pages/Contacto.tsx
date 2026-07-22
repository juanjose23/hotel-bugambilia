import ContactForm from '@/modules/contacto/components/ContactForm';
import ContactHero from '@/modules/contacto/components/ContactHero';
import ContactInfo from '@/modules/contacto/components/ContactInfo';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

export default function Contacto() {
    return (
        <LayoutPublico>
            <ContactHero />
            <section className="bg-background py-16 md:py-24">
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="grid gap-8 lg:grid-cols-5 lg:gap-12">
                        <div className="lg:col-span-3">
                            <ContactForm />
                        </div>
                        <div className="lg:col-span-2">
                            <ContactInfo />
                        </div>
                    </div>
                </div>
            </section>
        </LayoutPublico>
    );
}
