<?php

declare(strict_types=1);

return [
    'version' => 1,
    'paginas' => [
        'App\\Filament\\Pages\\Restaurante\\ControlCocina' => [
            'id' => 'cocina-control',
            'titulo' => 'Control diario de cocina',
            'pasos' => [
                [
                    'titulo' => 'Control diario de cocina',
                    'descripcion' => 'Empieza por revisar pendientes, existencias y avisos del día. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="cocina-indicadores"]',
                    'titulo' => 'Qué está pendiente',
                    'descripcion' => 'Comandas activas, preparaciones sin confirmar, buffets abiertos e insumos por reponer.',
                ],
                [
                    'selector' => '[data-guia="cocina-tareas"]',
                    'titulo' => 'Elige el proceso',
                    'descripcion' => 'Porciona un insumo, elabora una receta o atiende un buffet. Cada proceso tiene su propia guía.',
                ],
                [
                    'selector' => '[data-guia="cocina-alertas"]',
                    'titulo' => 'Revisa lotes y unidades',
                    'descripcion' => 'Comprueba vencimientos y reposiciones. Separa lotes vencidos; no los uses para preparar.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Abre el proceso que necesitas y revisa su guía antes de registrar cantidades.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Restaurante\\MateriaPrimaCocina' => [
            'id' => 'cocina-preparacion',
            'titulo' => 'Preparar y porcionar materias primas',
            'pasos' => [
                [
                    'titulo' => 'Preparar y porcionar materias primas',
                    'descripcion' => 'Registra de qué insumo sale cada corte, porción o bebida. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="preparacion-formulario"]',
                    'titulo' => 'Origen y ubicación',
                    'descripcion' => 'Elige la bodega, producto y presentación de origen. La cantidad usa la unidad mostrada; un kilogramo no equivale a una unidad.',
                ],
                [
                    'selector' => '[role="tablist"]',
                    'titulo' => 'Trabaja por etapas',
                    'descripcion' => 'Completa origen, resultados y revisión. Puedes consultar existencias y movimientos sin confundirlos con una nueva preparación.',
                ],
                [
                    'selector' => '[data-guia="preparacion-resumen"]',
                    'titulo' => 'Resultado y merma',
                    'descripcion' => 'Revisa lo utilizado, lo obtenido y la pérdida real. Las presentaciones conservan sus unidades y lotes.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Confirma únicamente después de revisar cantidades y merma. Consulta el movimiento generado para comprobar la trazabilidad.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Restaurante\\CocinaPedidos' => [
            'id' => 'cocina-comandas',
            'titulo' => 'Preparar y despachar comandas',
            'pasos' => [
                [
                    'titulo' => 'Preparar y despachar comandas',
                    'descripcion' => 'Revisa el pedido y el área responsable antes de iniciar. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="comandas-areas"]',
                    'titulo' => 'Selecciona tu área',
                    'descripcion' => 'Cocina, barra, postres o parrilla. La asignación de la partida prevalece sobre la del platillo.',
                ],
                [
                    'selector' => '[data-guia="comandas-cola"]',
                    'titulo' => 'Pendientes y faltantes',
                    'descripcion' => 'Un bloqueo por stock requiere resolver el faltante o autorizar una sustitución válida. Revisa receta y presentación.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Herramientas de cocina',
                    'descripcion' => 'Las opciones de abastecimiento y merma están disponibles según tu permiso.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Inicia la preparación, marca listo cuando esté terminado y servido al entregarlo. Una anulación no borra el consumo físico ya realizado.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Restaurante\\GestionMesas' => [
            'id' => 'restaurante-mesas',
            'titulo' => 'Mesas y comandas',
            'pasos' => [
                [
                    'titulo' => 'Mesas y comandas',
                    'descripcion' => 'Selecciona la mesa correcta antes de abrir o mover un pedido. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="mesas-mapa"]',
                    'titulo' => 'Disponibilidad y ocupación',
                    'descripcion' => 'Distingue mesas disponibles, reservadas, ocupadas y pendientes de limpieza. Un pedido listo continúa activo.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Abre, une o reasigna mesas mediante las opciones autorizadas.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba mesa, cuenta y comanda antes de cobrar o transferir un consumo.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Restaurante\\PantallaPedidos' => [
            'id' => 'restaurante-despacho',
            'titulo' => 'Entregar pedidos listos',
            'pasos' => [
                [
                    'titulo' => 'Entregar pedidos listos',
                    'descripcion' => 'Distingue lo que sigue en preparación de lo que se puede entregar. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Activa el aviso sonoro cuando lo necesites.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Marca servido únicamente después de entregar el producto. Los pedidos prepagados también necesitan preparación y entrega.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Restaurante\\BuffetsCocina' => [
            'id' => 'cocina-buffet',
            'titulo' => 'Buffets y eventos',
            'pasos' => [
                [
                    'titulo' => 'Buffets y eventos',
                    'descripcion' => 'Organiza menú, modalidad de cobro y quién sirve: la persona, un mesero o ambos. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="buffet-flujo"]',
                    'titulo' => 'Tres momentos del servicio',
                    'descripcion' => 'Organizar, enviar o reponer y medir el resultado. El cobro y el control físico se registran por separado.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Crea el servicio y abre sus acciones: menú, envío, reposición, cierre y cargo.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Mide asistentes, sobrantes y desperdicios antes del cierre; registra el cargo una sola vez en la cuenta correcta.',
                ],
            ],
            'dialogos' => [
                'buffet-crear' => [
                    'id' => 'buffet-crear',
                    'titulo' => 'Organizar buffet o evento',
                    'pasos' => [
                        [
                            'titulo' => 'Organizar buffet o evento',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-nombre"]',
                            'titulo' => 'Servicio y fecha',
                            'descripcion' => 'Identifica el evento que vas a atender.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-modalidad"]',
                            'titulo' => 'Cómo se cobra',
                            'descripcion' => 'Distingue por persona, paquete o modalidad mixta. La forma de servir se configura por separado.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-servicio"]',
                            'titulo' => 'Quién sirve',
                            'descripcion' => 'Define autoservicio, mesero o mixto según el servicio real.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Revisa moneda, asistentes previstos y precios antes de crear el servicio.',
                        ],
                    ],
                ],
                'buffet-menu' => [
                    'id' => 'buffet-menu',
                    'titulo' => 'Organizar el menú',
                    'pasos' => [
                        [
                            'titulo' => 'Organizar el menú',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-menu"]',
                            'titulo' => 'Platillos y producto terminado',
                            'descripcion' => 'Relaciona cada platillo con la presentación que saldrá de cocina y sus porciones previstas.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Comprueba receta, presentación y unidad; prepara el producto antes de enviarlo al servicio.',
                        ],
                    ],
                ],
                'buffet-abastecer' => [
                    'id' => 'buffet-abastecer',
                    'titulo' => 'Enviar o reponer alimentos',
                    'pasos' => [
                        [
                            'titulo' => 'Enviar o reponer alimentos',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-stock"]',
                            'titulo' => 'Existencia de origen',
                            'descripcion' => 'Elige el producto terminado y revisa ubicación, presentación y cantidad disponible.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-cantidad"]',
                            'titulo' => 'Cantidad realmente enviada',
                            'descripcion' => 'Usa la unidad indicada: kg, litros o unidades según la presentación. Este envío no vuelve a consumir ingredientes.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Revisa origen y cantidad antes de confirmar; registra cada reposición como una entrega real.',
                        ],
                    ],
                ],
                'buffet-cerrar' => [
                    'id' => 'buffet-cerrar',
                    'titulo' => 'Cerrar el servicio',
                    'pasos' => [
                        [
                            'titulo' => 'Cerrar el servicio',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-comensales"]',
                            'titulo' => 'Personas atendidas',
                            'descripcion' => 'Registra la asistencia real, independiente de lo previsto.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-mediciones"]',
                            'titulo' => 'Sobrantes y desperdicios',
                            'descripcion' => 'Mide cada producto por separado. Escribe cero cuando no haya sobrante o pérdida.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Comprueba que lo enviado se pueda explicar entre servido, sobrante y merma antes de cerrar.',
                        ],
                    ],
                ],
                'buffet-cargar' => [
                    'id' => 'buffet-cargar',
                    'titulo' => 'Registrar el cargo del evento',
                    'pasos' => [
                        [
                            'titulo' => 'Registrar el cargo del evento',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-cuenta"]',
                            'titulo' => 'Cuenta correcta',
                            'descripcion' => 'Selecciona una cuenta abierta en la misma moneda del servicio.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-personas"]',
                            'titulo' => 'Personas fuera del paquete',
                            'descripcion' => 'No vuelvas a incluir las personas ya cubiertas por un paquete.',
                        ],
                        [
                            'selector' => '[data-guia="buffet-paquetes"]',
                            'titulo' => 'Paquetes cobrados',
                            'descripcion' => 'Registra la cantidad de paquetes que realmente corresponde cobrar.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Revisa el detalle y registra el cargo una sola vez. Consulta después el estado de cuenta.',
                        ],
                    ],
                ],
            ],
        ],
        'App\\Filament\\Pages\\Restaurante\\ConciliacionRecetasCocina' => [
            'id' => 'cocina-conciliacion',
            'titulo' => 'Conciliar recetas y consumo',
            'pasos' => [
                [
                    'titulo' => 'Conciliar recetas y consumo',
                    'descripcion' => 'Compara lo previsto en la receta con los movimientos reales. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Filtra el periodo o proceso que necesitas revisar.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Investiga la causa de las diferencias y conserva la justificación; no ajustes existencias para ocultarlas.',
                ],
            ],
        ],
        'App\\Filament\\Clusters\\FacturacionConfig\\FacturacionConfigCluster' => [
            'id' => 'facturacion-inicio',
            'titulo' => 'Facturación y caja',
            'pasos' => [
                [
                    'titulo' => 'Facturación y caja',
                    'descripcion' => 'Todos los procesos financieros se encuentran en este acceso. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="facturacion-accesos"]',
                    'titulo' => 'Elige el área',
                    'descripcion' => 'Cobros y documentos, caja y cierres, fondos y bancos y configuración fiscal. Solo se muestran tus opciones autorizadas.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Empieza por tu turno para operar efectivo, por cuentas para revisar deuda o por comprobantes para validar transferencias.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\MiTurnoCaja' => [
            'id' => 'caja-turno',
            'titulo' => 'Operar tu turno de caja',
            'pasos' => [
                [
                    'titulo' => 'Operar tu turno de caja',
                    'descripcion' => 'Primero cuenta el fondo recibido; después registra los cobros y movimientos. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="caja-saldos"]',
                    'titulo' => 'Esperado y contado',
                    'descripcion' => 'Distingue fondo recibido, saldo esperado, efectivo contado y diferencia. Cada moneda se conserva por separado.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Abre tu turno, cobra cuentas, registra ingresos o egresos y realiza el arqueo al terminar. Cada formulario tiene una guía propia.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Cuenta el efectivo por moneda. Un faltante o sobrante se explica y se somete a revisión; no se corrige inventando movimientos.',
                ],
            ],
            'dialogos' => [
                'caja-apertura' => [
                    'id' => 'caja-apertura',
                    'titulo' => 'Abrir el turno',
                    'pasos' => [
                        [
                            'titulo' => 'Abrir el turno',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="caja-elegir"]',
                            'titulo' => 'Caja disponible',
                            'descripcion' => 'Selecciona la caja asignada. Revisa que no tenga un turno incompatible.',
                        ],
                        [
                            'selector' => '[data-guia="caja-fondo"]',
                            'titulo' => 'Fondo recibido',
                            'descripcion' => 'Cuenta el efectivo realmente recibido en cada moneda. Comprueba el origen o entrega del fondo.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Compara lo registrado con el efectivo antes de confirmar la apertura.',
                        ],
                    ],
                ],
                'caja-cobrar' => [
                    'id' => 'caja-cobrar',
                    'titulo' => 'Cobrar una cuenta',
                    'pasos' => [
                        [
                            'titulo' => 'Cobrar una cuenta',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="cobro-cuenta"]',
                            'titulo' => 'Cuenta y saldo',
                            'descripcion' => 'Selecciona la cuenta que vas a liquidar. Revisa el número, cliente y moneda.',
                        ],
                        [
                            'selector' => '[data-guia="cobro-recibido"]',
                            'titulo' => 'Efectivo recibido',
                            'descripcion' => 'Escribe lo que entregó la persona. El sistema distingue lo recibido, lo aplicado y el vuelto.',
                        ],
                        [
                            'selector' => '[data-guia="cobro-factura"]',
                            'titulo' => 'Documento fiscal',
                            'descripcion' => 'Elige emisión únicamente cuando corresponda. Revisa receptor y serie antes de confirmar.',
                        ],
                        [
                            'selector' => '[data-guia="cobro-serie"]',
                            'titulo' => 'Serie de emisión',
                            'descripcion' => 'Selecciona una serie vigente y con autorización disponible.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Verifica cuenta, importe, moneda y vuelto antes de confirmar. Después revisa el pago y el documento generado.',
                        ],
                    ],
                ],
                'caja-cierre' => [
                    'id' => 'caja-cierre',
                    'titulo' => 'Arqueo y cierre',
                    'pasos' => [
                        [
                            'titulo' => 'Arqueo y cierre',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="caja-conteo"]',
                            'titulo' => 'Cuenta el efectivo físico',
                            'descripcion' => 'Registra lo contado en cada moneda, independientemente del saldo esperado.',
                        ],
                        [
                            'selector' => '[data-guia="caja-motivo"]',
                            'titulo' => 'Faltante o sobrante',
                            'descripcion' => 'Explica cualquier diferencia. No cambies el conteo para que coincida artificialmente.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Confirma el conteo real. Una diferencia queda pendiente de revisión por otra persona autorizada.',
                        ],
                    ],
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\GestionCajas' => [
            'id' => 'caja-gestion',
            'titulo' => 'Gestionar cajas',
            'pasos' => [
                [
                    'titulo' => 'Gestionar cajas',
                    'descripcion' => 'Configura las cajas autorizadas y sus responsables. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Crea o administra una caja según tus permisos.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Revisa caja, ubicación y monedas antes de habilitarla.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\HistorialCajas' => [
            'id' => 'caja-cierres',
            'titulo' => 'Arqueos y cierres',
            'pasos' => [
                [
                    'titulo' => 'Arqueos y cierres',
                    'descripcion' => 'Consulta la apertura, movimientos, conteo y diferencias de cada turno. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Filtra por estado y abre el detalle del cierre.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'La revisión de una diferencia debe conservar el motivo y la autorización correspondiente.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\ComprobantesTransferencia' => [
            'id' => 'caja-comprobantes',
            'titulo' => 'Revisar transferencias y depósitos',
            'pasos' => [
                [
                    'titulo' => 'Revisar transferencias y depósitos',
                    'descripcion' => 'Una imagen del comprobante todavía no acredita el dinero. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Abre las evidencias y después la revisión autorizada.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Contrasta cuenta, referencia, fecha, moneda e importe con un movimiento bancario real antes de aprobar.',
                ],
            ],
            'dialogos' => [
                'comprobante-revision' => [
                    'id' => 'comprobante-revision',
                    'titulo' => 'Decidir sobre un comprobante',
                    'pasos' => [
                        [
                            'titulo' => 'Decidir sobre un comprobante',
                            'descripcion' => 'Revisa este formulario antes de confirmar la operación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                        ],
                        [
                            'selector' => '[data-guia="comprobante-decision"]',
                            'titulo' => 'Elige la decisión',
                            'descripcion' => 'Puedes aprobar contra el banco, rechazar o pedir nueva evidencia.',
                        ],
                        [
                            'selector' => '[data-guia="comprobante-banco"]',
                            'titulo' => 'Movimiento bancario real',
                            'descripcion' => 'Contrasta cuenta, fecha, referencia, moneda e importe. La imagen sola no acredita dinero.',
                        ],
                        [
                            'selector' => '[data-guia="comprobante-motivo"]',
                            'titulo' => 'Motivo verificable',
                            'descripcion' => 'Documenta qué comprobaste y por qué tomas la decisión.',
                        ],
                        [
                            'titulo' => 'Antes de confirmar',
                            'descripcion' => 'Aprobar puede aplicar un pago y emitir el documento solicitado. Revisa la decisión y los importes antes de confirmar.',
                        ],
                    ],
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\ConciliacionBancaria' => [
            'id' => 'bancos-conciliacion',
            'titulo' => 'Conciliar movimientos bancarios',
            'pasos' => [
                [
                    'titulo' => 'Conciliar movimientos bancarios',
                    'descripcion' => 'Compara el extracto bancario con los registros del sistema. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Importa o selecciona los movimientos y revisa cada coincidencia.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Separa diferencias, comisiones y referencias repetidas; conserva el respaldo de la conciliación.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\CajaChica' => [
            'id' => 'caja-chica',
            'titulo' => 'Controlar caja chica',
            'pasos' => [
                [
                    'titulo' => 'Controlar caja chica',
                    'descripcion' => 'Entrega, liquida y repón vales con respaldo. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Registra el vale y revisa su entrega o liquidación.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Distingue dinero entregado, gasto respaldado y devolución. Cada paso conserva responsable y comprobante.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\FondosTesoreria' => [
            'id' => 'bancos-fondos',
            'titulo' => 'Fondos y bancos',
            'pasos' => [
                [
                    'titulo' => 'Fondos y bancos',
                    'descripcion' => 'Identifica la caja, banco, moneda y saldo del fondo. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Administra los fondos autorizados.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Selecciona el fondo y la moneda correctos; conserva el respaldo de cada movimiento.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\MovimientosFondos' => [
            'id' => 'bancos-libro',
            'titulo' => 'Libro de fondos',
            'pasos' => [
                [
                    'titulo' => 'Libro de fondos',
                    'descripcion' => 'Revisa entradas, salidas y referencias por fondo. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Filtra el periodo y la cuenta de fondo.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Sigue la referencia hasta su operación de origen antes de investigar diferencias.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\FondosTurnoCaja' => [
            'id' => 'caja-fondos-turno',
            'titulo' => 'Abastecer un turno',
            'pasos' => [
                [
                    'titulo' => 'Abastecer un turno',
                    'descripcion' => 'Distingue el fondo de tesorería del efectivo entregado al cajero. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Registra la entrega, recepción y revisión que te corresponda.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba responsable, turno y moneda. La recepción debe coincidir con el efectivo contado.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\EntregasCaja' => [
            'id' => 'caja-entregas',
            'titulo' => 'Entregas y depósitos',
            'pasos' => [
                [
                    'titulo' => 'Entregas y depósitos',
                    'descripcion' => 'Documenta el efectivo que sale del turno hacia su destino. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Solicita o revisa la entrega mediante su acción autorizada.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'La persona que recibe verifica el importe y conserva la evidencia.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Tesoreria\\DevolucionesCaja' => [
            'id' => 'caja-devoluciones',
            'titulo' => 'Devolver efectivo',
            'pasos' => [
                [
                    'titulo' => 'Devolver efectivo',
                    'descripcion' => 'Localiza el pago original y revisa el motivo de devolución. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Solicita o autoriza según tu responsabilidad.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba pago, importe, moneda y autorización antes de entregar dinero; resuelve también el documento fiscal cuando corresponda.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Reservas\\CheckInPage' => [
            'id' => 'recepcion-entrada',
            'titulo' => 'Registrar la entrada',
            'pasos' => [
                [
                    'titulo' => 'Registrar la entrada',
                    'descripcion' => 'Revisa reserva, alojamiento y huéspedes antes de entregar el servicio. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="checkin-contenido"]',
                    'titulo' => 'Reserva y requisitos',
                    'descripcion' => 'Comprueba fechas, ocupantes, documentos y condiciones de pago o garantía.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Selecciona la reserva',
                    'descripcion' => 'Busca el registro correcto y revisa su estado antes de registrar la entrada.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones de entrada',
                    'descripcion' => 'Utiliza únicamente las opciones habilitadas para esa reserva.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Confirma alojamiento, huésped titular, llaves y garantía. Comprueba el estado resultante antes de entregar la habitación.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Reservas\\CheckOutPage' => [
            'id' => 'recepcion-salida',
            'titulo' => 'Registrar la salida',
            'pasos' => [
                [
                    'titulo' => 'Registrar la salida',
                    'descripcion' => 'Revisa consumos y saldo de la estancia antes de finalizar. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '[data-guia="checkout-contenido"]',
                    'titulo' => 'Estado de cuenta',
                    'descripcion' => 'Verifica alojamiento, consumos, pagos y saldo pendiente.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Selecciona la estancia',
                    'descripcion' => 'Asegúrate de trabajar sobre la estancia que realmente finaliza.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Cobro y cierre',
                    'descripcion' => 'Las acciones de pago, documento y salida tienen responsabilidades distintas.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Liquida o resuelve el saldo autorizado, revisa el documento y confirma la salida; después comprueba la limpieza y disponibilidad.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Restaurante\\ProcesoCocinaResource\\ProcesoCocinaResource' => [
            'id' => 'cocina-recetas',
            'titulo' => 'Elaborar una receta',
            'pasos' => [
                [
                    'titulo' => 'Elaborar una receta',
                    'descripcion' => 'Selecciona la receta y planifica el rendimiento esperado. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba ingredientes, presentación y unidad antes de iniciar. Registra el resultado real y la merma; un borrador no equivale a un consumo confirmado.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Restaurante\\PlatoResource\\PlatoResource' => [
            'id' => 'restaurante-platillos',
            'titulo' => 'Platillos y recetas',
            'pasos' => [
                [
                    'titulo' => 'Platillos y recetas',
                    'descripcion' => 'Relaciona cada platillo con su receta, área y condiciones de venta. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Revisa costo, rendimiento y unidades. Los precios de venta y el consumo de ingredientes representan datos distintos.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Cuentas\\CuentaResource' => [
            'id' => 'cuentas',
            'titulo' => 'Administrar una cuenta',
            'pasos' => [
                [
                    'titulo' => 'Administrar una cuenta',
                    'descripcion' => 'Revisa titular, origen, moneda y estado de la cuenta. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Revisa cargos y pagos; identifica la deuda pendiente antes de cobrar o cerrar. Un comprobante pendiente no equivale a un pago aplicado.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Facturacion\\FacturaResource\\FacturaResource' => [
            'id' => 'facturas',
            'titulo' => 'Documentos fiscales',
            'pasos' => [
                [
                    'titulo' => 'Documentos fiscales',
                    'descripcion' => 'Comprueba receptor, venta, moneda y estado del documento. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Emisión, impresión y anulación son acciones distintas. Conserva el consecutivo y el documento original; explica la anulación cuando esté autorizada.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Cuentas\\VentaResource\\VentaResource' => [
            'id' => 'ventas',
            'titulo' => 'Ventas cerradas',
            'pasos' => [
                [
                    'titulo' => 'Ventas cerradas',
                    'descripcion' => 'Revisa la operación cerrada y su vínculo con la cuenta. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba importes, pagos y factura vinculada. Una venta y una factura no son el mismo registro.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Facturacion\\FacturaSerieResource\\FacturaSerieResource' => [
            'id' => 'series-fiscales',
            'titulo' => 'Series y consecutivos',
            'pasos' => [
                [
                    'titulo' => 'Series y consecutivos',
                    'descripcion' => 'Revisa la serie que corresponde a cada emisión. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba vigencia y rango autorizado antes de emitir; no reutilices un consecutivo anulado.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Facturacion\\FacturaAutorizacionDgiResource\\FacturaAutorizacionDgiResource' => [
            'id' => 'autorizaciones-fiscales',
            'titulo' => 'Autorizaciones DGI',
            'pasos' => [
                [
                    'titulo' => 'Autorizaciones DGI',
                    'descripcion' => 'Registra los datos oficiales de autorización y su vigencia. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Contrasta número, serie, rango y fechas con la autorización oficial.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Reservas\\ReservaResource' => [
            'id' => 'reservas',
            'titulo' => 'Reservar varios servicios',
            'pasos' => [
                [
                    'titulo' => 'Reservar varios servicios',
                    'descripcion' => 'Identifica al solicitante, fechas, recursos y condiciones de pago. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Una reserva puede tener varios alojamientos o espacios. Asigna los huéspedes al detalle correcto y revisa disponibilidad antes de confirmar.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Compras\\Solicitudes\\SolicitudResource' => [
            'id' => 'compras-solicitudes',
            'titulo' => 'Solicitar abastecimiento',
            'pasos' => [
                [
                    'titulo' => 'Solicitar abastecimiento',
                    'descripcion' => 'Define qué necesitas, cuánto y para qué ubicación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Revisa producto, presentación, unidad, motivo y aprobación antes de continuar con la compra.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Compras\\Cotizaciones\\CotizacionResource' => [
            'id' => 'compras-cotizaciones',
            'titulo' => 'Comparar cotizaciones',
            'pasos' => [
                [
                    'titulo' => 'Comparar cotizaciones',
                    'descripcion' => 'Relaciona las ofertas con la solicitud y el proveedor. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Compara presentación, cantidad, moneda, impuestos y condiciones; no compares importes de unidades distintas como equivalentes.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Compras\\OrdenesCompra\\OrdenCompraResource' => [
            'id' => 'compras-ordenes',
            'titulo' => 'Ordenar una compra',
            'pasos' => [
                [
                    'titulo' => 'Ordenar una compra',
                    'descripcion' => 'Revisa proveedor, artículos y aprobación. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Comprueba cantidades, precios y moneda antes de autorizar; la recepción física se registra por separado.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Compras\\Recepciones\\RecepcionResource' => [
            'id' => 'compras-recepciones',
            'titulo' => 'Recibir mercadería',
            'pasos' => [
                [
                    'titulo' => 'Recibir mercadería',
                    'descripcion' => 'Relaciona la entrega física con su orden de compra. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Registra lo recibido, lotes, vencimientos, ubicación y diferencias reales antes de confirmar la entrada.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Inventario\\InventarioFisico\\InventarioFisicoResource' => [
            'id' => 'inventario-conteo',
            'titulo' => 'Contar inventario físico',
            'pasos' => [
                [
                    'titulo' => 'Contar inventario físico',
                    'descripcion' => 'Selecciona la ubicación y organiza el conteo. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Registra lo contado por producto, presentación y lote. Justifica diferencias y conserva su revisión antes del ajuste.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Inventario\\StockResource\\StockResource' => [
            'id' => 'inventario-existencias',
            'titulo' => 'Consultar existencias',
            'pasos' => [
                [
                    'titulo' => 'Consultar existencias',
                    'descripcion' => 'Revisa ubicación, presentación, lote y unidad de cada existencia. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Sigue el movimiento de origen para investigar una diferencia; las existencias no se editan para sustituir un movimiento documentado.',
                ],
            ],
        ],
        'App\\Filament\\Resources\\Inventario\\Lote\\LoteResource' => [
            'id' => 'inventario-lotes',
            'titulo' => 'Controlar lotes',
            'pasos' => [
                [
                    'titulo' => 'Controlar lotes',
                    'descripcion' => 'Revisa entrada, cantidad disponible y vencimiento. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Acciones del proceso',
                    'descripcion' => 'Crear, confirmar, emitir o anular solo aparece cuando tu permiso y el estado del registro lo permiten.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra el registro',
                    'descripcion' => 'Busca y filtra antes de abrir el documento.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Registros y estados',
                    'descripcion' => 'Revisa el registro y su historial antes de continuar.',
                ],
                [
                    'selector' => '.fi-sc-form, form[wire\\:submit="save"], form[wire\\:submit="create"], [data-guia="preparacion-formulario"]',
                    'titulo' => 'Completa y revisa',
                    'descripcion' => 'Recorre los campos del formulario, conserva las unidades y verifica las validaciones antes de guardar.',
                ],
                [
                    'selector' => '.fi-section',
                    'titulo' => 'Detalle del documento',
                    'descripcion' => 'Comprueba los datos del registro y las relaciones que corresponden a la operación.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Distingue producto, presentación y lote; verifica su estado antes de utilizarlo o trasladarlo.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Limpieza\\TableroLimpieza' => [
            'id' => 'limpieza-tablero',
            'titulo' => 'Coordinar limpieza',
            'pasos' => [
                [
                    'titulo' => 'Coordinar limpieza',
                    'descripcion' => 'Revisa pendientes, ubicación y responsables antes de registrar el trabajo. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Utiliza las opciones de ejecución y revisión que correspondan a tu función.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Asigna responsable y verifica la ejecución antes de liberar un alojamiento.',
                ],
            ],
        ],
        'App\\Filament\\Pages\\Limpieza\\ControlLavanderia' => [
            'id' => 'limpieza-lavanderia',
            'titulo' => 'Controlar lavandería',
            'pasos' => [
                [
                    'titulo' => 'Controlar lavandería',
                    'descripcion' => 'Revisa pendientes, ubicación y responsables antes de registrar el trabajo. Durante el recorrido revisas los controles; al cerrar puedes trabajar en ellos.',
                ],
                [
                    'selector' => '.fi-header-actions-ctn > .fi-ac',
                    'titulo' => 'Opciones disponibles',
                    'descripcion' => 'Utiliza las opciones de ejecución y revisión que correspondan a tu función.',
                ],
                [
                    'selector' => '.fi-ta-header-toolbar',
                    'titulo' => 'Encuentra la operación',
                    'descripcion' => 'Busca por código, fecha o estado. Los filtros ayudan a localizar el registro correcto.',
                ],
                [
                    'selector' => '.fi-ta',
                    'titulo' => 'Revisa el registro',
                    'descripcion' => 'Comprueba los datos antes de abrir una operación. Las opciones visibles dependen de tus permisos.',
                ],
                [
                    'titulo' => 'Antes de confirmar',
                    'descripcion' => 'Distingue prendas enviadas, devueltas y pérdidas; conserva la referencia de cada movimiento.',
                ],
            ],
        ],
    ],
];
