import { Eye, Building2 } from 'lucide-react';
import { useState } from 'react';
import { VisorGaleriaModal } from '@/modulos/compartido/componentes/VisorGaleriaModal';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesMosaicoGaleriaEspacio {
    imagenes: string[];
    nombre: string;
    tipoLabel?: string;
}

export const MosaicoGaleriaEspacio = ({
    imagenes = [],
    nombre,
    tipoLabel,
}: PropiedadesMosaicoGaleriaEspacio) => {
    const [indiceActivo, setIndiceActivo] = useState(0);
    const [visorAbierto, setVisorAbierto] = useState(false);

    const fotos =
        imagenes && imagenes.length > 0 ? imagenes : ['/images/terrace.webp'];
    const fotoPrincipal = fotos[0];
    const fotosSecundarias = fotos.slice(1, 5);

    const abrirVisor = (idx: number = 0) => {
        setIndiceActivo(idx);
        setVisorAbierto(true);
    };

    return (
        <div className="relative font-sans">
            {/* Desktop Mosaic Grid (Bento Box Airbnb Luxe Style) */}
            <div className="hidden grid-cols-4 gap-3 overflow-hidden rounded-3xl md:grid md:h-[420px] lg:h-[480px]">
                {/* Imagen Principal Grande (Ocupa 2 columnas y 2 filas) */}
                <div
                    onClick={() => abrirVisor(0)}
                    className="group relative col-span-2 row-span-2 cursor-pointer overflow-hidden bg-muted"
                >
                    <img
                        src={fotoPrincipal}
                        alt={nombre}
                        className="size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/10 opacity-60 transition-opacity group-hover:opacity-70" />

                    {tipoLabel && (
                        <div className="absolute top-4 left-4 z-10">
                            <Badge
                                variant="outline"
                                className="rounded-full border-white/30 bg-black/50 px-3.5 py-1 text-xs font-extrabold text-white backdrop-blur-md"
                            >
                                <Building2 className="mr-1.5 size-3.5 text-bugambilia-300" />
                                {tipoLabel}
                            </Badge>
                        </div>
                    )}
                </div>

                {/* Grilla Secundaria (4 fotos en columna) */}
                {fotosSecundarias.length > 0 ? (
                    fotosSecundarias.map((foto, idx) => (
                        <div
                            key={idx}
                            onClick={() => abrirVisor(idx + 1)}
                            className="group relative cursor-pointer overflow-hidden bg-muted"
                        >
                            <img
                                src={foto}
                                alt={`${nombre} ${idx + 2}`}
                                className="size-full object-cover transition-transform duration-500 group-hover:scale-110"
                            />
                            <div className="absolute inset-0 bg-black/20 opacity-0 transition-opacity group-hover:opacity-100" />
                        </div>
                    ))
                ) : (
                    // Relleno si hay menos de 5 fotos
                    <div
                        onClick={() => abrirVisor(0)}
                        className="group relative col-span-2 row-span-2 cursor-pointer overflow-hidden bg-muted/80"
                    >
                        <img
                            src={fotoPrincipal}
                            alt={nombre}
                            className="size-full object-cover brightness-90 transition-transform duration-500 group-hover:scale-105"
                        />
                    </div>
                )}

                {/* Botón Flotante Ver Todas las Fotos */}
                <button
                    type="button"
                    onClick={() => abrirVisor(0)}
                    className="absolute right-5 bottom-5 z-10 flex cursor-pointer items-center gap-2 rounded-full border border-white/30 bg-black/60 px-4 py-2 text-xs font-extrabold text-white shadow-lg backdrop-blur-md transition-all hover:scale-105 hover:bg-black/80 active:scale-95"
                >
                    <Eye className="size-4 text-bugambilia-300" />
                    <span>Ver todas las fotos ({fotos.length})</span>
                </button>
            </div>

            {/* Layout Móvil (Banner interactivo con badge counter) */}
            <div className="relative aspect-16/10 w-full overflow-hidden rounded-3xl bg-muted md:hidden">
                <img
                    src={fotos[indiceActivo] || fotoPrincipal}
                    alt={nombre}
                    className="size-full object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/20" />

                {tipoLabel && (
                    <div className="absolute top-3 left-3 z-10">
                        <Badge
                            variant="outline"
                            className="rounded-full border-white/30 bg-black/50 px-3 py-1 text-xs font-bold text-white backdrop-blur-md"
                        >
                            {tipoLabel}
                        </Badge>
                    </div>
                )}

                <button
                    type="button"
                    onClick={() => abrirVisor(indiceActivo)}
                    className="absolute right-3 bottom-3 z-10 flex items-center gap-1.5 rounded-full border border-white/30 bg-black/60 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-md"
                >
                    <Eye className="size-3.5" />
                    <span>{fotos.length} fotos</span>
                </button>
            </div>

            {/* Modal Pantalla Completa */}
            <VisorGaleriaModal
                estaAbierto={visorAbierto}
                alCerrar={() => setVisorAbierto(false)}
                imagenes={fotos}
                indiceImagenActiva={indiceActivo}
                alSeleccionarImagen={setIndiceActivo}
                titulo={nombre}
            />
        </div>
    );
};

export default MosaicoGaleriaEspacio;
