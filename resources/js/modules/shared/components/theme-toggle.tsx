import { Moon, Sun } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTheme } from '@/modules/shared/hooks/useTheme';
import { Button } from '@/modules/shared/ui/button';

export default function ThemeToggle() {
    const { theme, setTheme } = useTheme();
    const [mounted, setMounted] = useState(false);
    const didMount = useRef(false);

    useEffect(() => {
        if (!didMount.current) {
            didMount.current = true;
            setMounted(true);
        }
    }, []);

    const isDark = theme === 'dark';

    if (!mounted) {
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
            onClick={() => setTheme(isDark ? 'light' : 'dark')}
            className="relative h-9 w-9 rounded-full transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
            aria-label={
                isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'
            }
        >
            <Sun className="h-4 w-4 scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90" />
            <Moon className="absolute h-4 w-4 scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0" />
        </Button>
    );
}
