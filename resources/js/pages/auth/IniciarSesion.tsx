import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AuthLayout } from '@/modules/auth/components/AuthLayout';
import { LoginForm } from '@/modules/auth/components/LoginForm';

export const IniciarSesion = () => {
    return (
        <AuthLayout
            titulo="Iniciar Sesión"
            subtitulo="Ingresa tus credenciales para acceder a tus reservas y beneficios exclusivos en Hotel Bugambilias."
            imagenFondo="/images/hero-secondary.webp"
        >
            <Head>
                <title>Iniciar Sesión — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Accede a tu cuenta de huésped en Hotel Bugambilias Estelí para gestionar tus reservas y acceder a tarifas preferenciales."
                />
            </Head>

            <LoginForm />
        </AuthLayout>
    );
};

// Layout independiente sin Header/Footer general
IniciarSesion.layout = (page: ReactNode) => page;

export default IniciarSesion;
