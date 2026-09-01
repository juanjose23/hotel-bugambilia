import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AuthLayout } from '@/modules/auth/components/AuthLayout';
import { CambiarContrasenaForm } from '@/modules/auth/components/CambiarContrasenaForm';

export const CambiarContrasena = () => {
    return (
        <AuthLayout
            titulo="Actualizar Contraseña"
            subtitulo="Por motivos de seguridad y privacidad, debes actualizar tu contraseña antes de continuar utilizando la plataforma."
            imagenFondo="/images/hero-secondary.webp"
        >
            <Head>
                <title>Actualizar Contraseña — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Actualiza tu contraseña de acceso en Hotel Bugambilias Estelí."
                />
            </Head>

            <CambiarContrasenaForm />
        </AuthLayout>
    );
};

// Layout independiente sin Header/Footer general
CambiarContrasena.layout = (page: ReactNode) => page;

export default CambiarContrasena;
