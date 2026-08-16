import type { PropiedadesTarjetaEspacio } from '../interfaces/espacioInterfaces';
import { TarjetaEspacioItem } from './secciones/TarjetaEspacioItem';

export const TarjetaEspacio = (props: PropiedadesTarjetaEspacio) => {
    return <TarjetaEspacioItem {...props} />;
};

export default TarjetaEspacio;
