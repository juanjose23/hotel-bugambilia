import { Link } from '@inertiajs/react';
import { Heart } from 'lucide-react';
const Favoritos = () => {
    return (
        <>
            <div className="container mx-auto px-4 py-32">
                <div className="mx-auto max-w-2xl text-center">
                    <div className="shadow-airbnb mx-auto mb-8 flex h-24 w-24 rotate-3 transform items-center justify-center rounded-3xl bg-gray-50 dark:bg-gray-900">
                        <Heart className="h-10 w-10 text-gray-300 dark:text-gray-700" />
                    </div>
                    <h1 className="mb-4 text-4xl font-black tracking-tighter text-gray-900 md:text-5xl dark:text-white">
                        Aún no tienes favoritos
                    </h1>
                    <p className="mb-12 text-xl font-medium text-gray-500">
                        Guarda las habitaciones que más te gusten para
                        encontrarlas fácilmente después.
                    </p>
                    <Link
                        href="/habitaciones"
                        className="transition-airbnb shadow-airbnb inline-flex h-14 items-center rounded-2xl bg-black px-10 text-[10px] font-black tracking-widest text-white uppercase hover:scale-105 dark:bg-white dark:text-black"
                    >
                        Explorar habitaciones
                    </Link>
                </div>
            </div>
        </>
    );
};
export default Favoritos;
