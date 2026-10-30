import type { ReactNode } from 'react';
import { PageLoadingBoundary } from '@/modules/shared/components/loading/NavigationLoadingProvider';
import { Footer } from '../Footer';
import { Header } from '../Header';
import { MobileBottomNav } from '../MobileBottomNav';
import { Toast } from '../Toast';

export interface PropsLayout {
    children: ReactNode;
}

export const Layout = ({ children }: PropsLayout) => {
    return (
        <div className="flex min-h-screen flex-col justify-between bg-background font-sans text-foreground selection:bg-bugambilia-500 selection:text-white">
            <Toast />
            <Header />
            <main className="grow pb-20 md:pb-0">
                <PageLoadingBoundary>{children}</PageLoadingBoundary>
            </main>
            <Footer />
            <MobileBottomNav />
        </div>
    );
};

export default Layout;
