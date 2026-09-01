import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AuthLayout } from '@/modules/auth/components/AuthLayout';
import { RegistroForm } from '@/modules/auth/components/RegistroForm';

export const Registro = () => {
    return (
        <AuthLayout
            titulo="Crear Cuenta"
            subtitulo="Únete al Club de Huéspedes de Hotel Bugambilias y accede a beneficios exclusivos, historial de reservas y tarifas preferenciales."
            imagenFondo="/images/hero-secondary.webp"
        >
            <Head>
                <title>Crear Cuenta — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Regístrate como huésped en Hotel Bugambilias Estelí para reservar más rápido y recibir beneficios exclusivos."
                />
            </Head>

            <RegistroForm />
        </AuthLayout>
    );
};

// Layout independiente sin Header/Footer general
Registro.layout = (page: ReactNode) => page;

export default Registro;
