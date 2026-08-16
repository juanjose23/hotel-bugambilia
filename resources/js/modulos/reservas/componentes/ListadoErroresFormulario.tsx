interface PropiedadesListadoErroresFormulario {
    errores: Record<string, string>;
}
export const ListadoErroresFormulario = ({
    errores,
}: PropiedadesListadoErroresFormulario) => {
    if (Object.keys(errores).length === 0) {
        return null;
    }

    return (
        <div
            role="alert"
            className="space-y-1 rounded-2xl bg-destructive/10 p-4 text-xs font-semibold text-destructive"
        >
            {Object.entries(errores).map(([campo, mensaje]) => (
                <p key={campo}>{mensaje}</p>
            ))}
        </div>
    );
};
