import { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';

type Theme = 'light' | 'dark' | 'system';

type ThemeContextType = {
    theme: Theme;
    setTheme: (theme: Theme) => void;
    toggleTheme: () => void;
};

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

function getSystemTheme(): 'light' | 'dark' {
    if (typeof window === 'undefined') {
        return 'light';
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
}

function getStoredTheme(): Theme | null {
    try {
        const stored = localStorage.getItem('theme');

        if (stored === 'light' || stored === 'dark' || stored === 'system') {
            return stored;
        }
    } catch {
        // localStorage not available
    }

    return null;
}

function applyTheme(theme: Theme) {
    const resolved = theme === 'system' ? getSystemTheme() : theme;
    document.documentElement.classList.toggle('dark', resolved === 'dark');
}

function ThemeProvider({
    children,
    defaultTheme = 'system',
}: {
    children: ReactNode;
    defaultTheme?: Theme;
}) {
    const [theme, setThemeState] = useState<Theme>(() => {
        return getStoredTheme() ?? defaultTheme;
    });

    const setTheme = (newTheme: Theme) => {
        setThemeState(newTheme);

        try {
            localStorage.setItem('theme', newTheme);
        } catch {
            // localStorage not available
        }

        applyTheme(newTheme);
    };

    const toggleTheme = () => {
        setTheme(theme === 'dark' ? 'light' : 'dark');
    };

    useEffect(() => {
        applyTheme(theme);
    }, [theme]);

    useEffect(() => {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = () => {
            if (theme === 'system') {
                applyTheme('system');
            }
        };
        mediaQuery.addEventListener('change', handler);

        return () => mediaQuery.removeEventListener('change', handler);
    }, [theme]);

    return (
        <ThemeContext.Provider value={{ theme, setTheme, toggleTheme }}>
            {children}
        </ThemeContext.Provider>
    );
}

function useTheme(): ThemeContextType {
    const context = useContext(ThemeContext);

    if (!context) {
        throw new Error('useTheme debe usarse dentro de un ThemeProvider');
    }

    return context;
}

export { ThemeProvider, useTheme };
