import type { ReactNode } from 'react';
import Footer from '@/modules/shared/components/Footer';
import Header from '@/modules/shared/components/Header';

interface LayoutPublicoProps {
    children: ReactNode;
}

export default function LayoutPublico({ children }: LayoutPublicoProps) {
    return (
        <div className="flex min-h-screen flex-col justify-between bg-background font-sans selection:bg-bugambilia-500 selection:text-white">
            <Header />
            <main className="flex-grow">{children}</main>
            <Footer />
        </div>
    );
}
