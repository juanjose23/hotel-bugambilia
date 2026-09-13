import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { RestauranteAmbientes } from '@/modules/restaurante/components/RestauranteAmbientes';
import { RestauranteCarritoFlotante } from '@/modules/restaurante/components/RestauranteCarritoFlotante';
import { RestauranteCarritoSheet } from '@/modules/restaurante/components/RestauranteCarritoSheet';
import { RestauranteCta } from '@/modules/restaurante/components/RestauranteCta';
import { RestauranteHero } from '@/modules/restaurante/components/RestauranteHero';
import { RestauranteHorarios } from '@/modules/restaurante/components/RestauranteHorarios';
import { RestauranteMenu } from '@/modules/restaurante/components/RestauranteMenu';
import { RestauranteReservaMesaModal } from '@/modules/restaurante/components/RestauranteReservaMesaModal';
import { crearRestaurantePorDefecto } from '@/modules/restaurante/constants';
import { useCarritoRestaurante } from '@/modules/restaurante/hooks/useCarritoRestaurante';
import type { RestaurantePageProps } from '@/modules/restaurante/types';

export const Restaurante = ({
    restaurante,
    ambientes = [],
    mesas = [],
    menu = [],
}: RestaurantePageProps) => {
    const [modalReservaAbierto, setModalReservaAbierto] = useState(false);

    const datosRestaurante = restaurante || crearRestaurantePorDefecto();

    // Hook para el carrito de compras a domicilio
    const carrito = useCarritoRestaurante({
        costoDelivery: datosRestaurante.costo_delivery ?? 50,
        pedidoMinimo: datosRestaurante.pedido_minimo ?? 0,
    });

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>{`${datosRestaurante.nombre} — Sabores y Alta Cocina en Estelí`}</title>
                <meta
                    name="description"
                    content={`Disfruta de la mejor experiencia gastronómica en ${datosRestaurante.nombre}. Desayunos buffet, almuerzos a la carta y cenas en terraza climatizada con servicio a domicilio y reservación de mesas en Estelí.`}
                />
            </Head>

            {/* Hero Principal con imagen real */}
            <RestauranteHero restaurante={datosRestaurante} />

            {/* Horarios de Servicio */}
            <RestauranteHorarios restaurante={datosRestaurante} />

            {/* Ambientes del Restaurante y Plano Visual de Mesas */}
            <RestauranteAmbientes
                ambientes={ambientes}
                mesas={mesas}
                alAbrirReserva={() => setModalReservaAbierto(true)}
            />

            {/* Menú a la Carta con Carrito de Delivery */}
            <RestauranteMenu
                menu={menu}
                carrito={carrito}
                permiteDelivery={datosRestaurante.permite_delivery ?? true}
            />

            {/* Bloque de Reserva y Contacto */}
            <RestauranteCta restaurante={datosRestaurante} />

            {/* Botón Flotante del Carrito */}
            {datosRestaurante.permite_delivery && (
                <RestauranteCarritoFlotante carrito={carrito} />
            )}

            {/* Drawer Lateral del Carrito y Envío por WhatsApp */}
            {datosRestaurante.permite_delivery && (
                <RestauranteCarritoSheet
                    carrito={carrito}
                    whatsappRestaurante={datosRestaurante.whatsapp_reservas}
                    nombreRestaurante={datosRestaurante.nombre}
                />
            )}

            {/* Modal / Drawer de Reservación de Mesas por Horario */}
            <RestauranteReservaMesaModal
                abierto={modalReservaAbierto}
                alCambiarAbierto={setModalReservaAbierto}
                restaurante={datosRestaurante}
                mesas={mesas}
            />
        </div>
    );
};

export default Restaurante;
