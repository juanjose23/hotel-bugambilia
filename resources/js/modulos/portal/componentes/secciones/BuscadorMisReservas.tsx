import { Search, X } from 'lucide-react';
import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesBuscadorMisReservas {
    searchTerm: string;
    onSearchChange: (value: string) => void;
}

export const BuscadorMisReservas = ({
    searchTerm,
    onSearchChange,
}: PropiedadesBuscadorMisReservas) => {
    return (
        <div className="relative w-full font-sans">
            <Search className="absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input
                type="text"
                placeholder="Buscar por código de reserva (ej: RES-2026-X89), nombre o detalles..."
                value={searchTerm}
                onChange={(e) => onSearchChange(e.target.value)}
                className="h-12 w-full rounded-2xl border-border/80 bg-card pr-10 pl-11 text-xs font-semibold shadow-xs focus:border-bugambilia-500 md:text-sm"
            />
            {searchTerm && (
                <button
                    type="button"
                    onClick={() => onSearchChange('')}
                    className="absolute top-1/2 right-4 -translate-y-1/2 cursor-pointer text-muted-foreground hover:text-foreground"
                >
                    <X className="size-4" />
                </button>
            )}
        </div>
    );
};

export default BuscadorMisReservas;
