import { Moon, Sun } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTema } from '@/modules/shared/hooks/useTema';
import { Button } from '@/modules/shared/ui/boton';
export const CambioTema = () => {
    const { tema, definirTema } = useTema();
    const [montado, setMontado] = useState(false);
    const yaMonto = useRef(false);
    useEffect(() => {
        if (!yaMonto.current) {
            yaMonto.current = true;
            setMontado(true);
        }
    }, []);
    const esOscuro = tema === 'dark';

    if (!montado) {
        return (
            <Button
                variant="ghost"
                size="icon"
                className="relative h-9 w-9 rounded-full transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                aria-label="Cambiar tema"
                disabled
            >
                <Sun className="h-4 w-4" />
            </Button>
        );
    }

    return (
        <Button
            variant="ghost"
            size="icon"
            onClick={() => definirTema(esOscuro ? 'light' : 'dark')}
            className="relative h-9 w-9 rounded-full transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
            aria-label={
                esOscuro ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'
            }
        >
            <Sun className="h-4 w-4 scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90" />
            <Moon className="absolute h-4 w-4 scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0" />
        </Button>
    );
};
