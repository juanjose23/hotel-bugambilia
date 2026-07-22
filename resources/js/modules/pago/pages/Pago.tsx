import PaymentProcess from '@/modules/pago/components/PaymentProcess';
import Footer from '@/modules/shared/components/Footer';
import Header from '@/modules/shared/components/Header';

export default function Pago() {
    return (
        <main className="min-h-screen bg-background">
            <Header />
            <PaymentProcess />
            <Footer />
        </main>
    );
}
