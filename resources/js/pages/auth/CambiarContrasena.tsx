import { FormularioCambiarContrasena } from '@/modulos/autenticacion/componentes/FormularioCambiarContrasena';
import { LayoutPublico } from '@/modulos/compartido/componentes/layouts/LayoutPublico';

export default function CambiarContrasenaPage() {
    return (
        <LayoutPublico>
            <FormularioCambiarContrasena />
        </LayoutPublico>
    );
}
