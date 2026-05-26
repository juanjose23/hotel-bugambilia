<div class="w-full max-w-full overflow-hidden space-y-4" wire:ignore>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/es.global.min.js"></script>

    <!-- Contenedor Principal del Calendario -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-6 shadow-sm overflow-x-auto overflow-y-hidden scrollbar-thin">
        <div id="calendar-native" class="fc-theme-custom w-full min-w-[700px] md:min-w-full max-w-full"></div>
    </div>

    <style>
        /* Forzar adaptabilidad total de tablas de FullCalendar */
        .fc-theme-custom,
        .fc-theme-custom .fc,
        .fc-theme-custom .fc-view-harness,
        .fc-theme-custom .fc-scrollgrid {
            width: 100% !important;
            max-width: 100% !important;
        }

        .fc-theme-custom {
            --fc-border-color: #e4e4e7; /* zinc-200 */
            --fc-page-bg-color: #ffffff;
            --fc-neutral-bg-color: #f4f4f5; /* zinc-100 */
            --fc-today-bg-color: #f4f4f5;
            
            font-family: inherit;
        }

        /* Botones de Navegación */
        .fc-theme-custom .fc-button {
            background-color: #ffffff !important;
            color: #3f3f46 !important; /* zinc-700 */
            border: 1px solid #e4e4e7 !important; /* zinc-200 */
            border-radius: 8px !important;
            padding: 8px 14px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.15s ease !important;
        }

        .fc-theme-custom .fc-button:hover {
            background-color: #f4f4f5 !important; /* zinc-100 */
            border-color: #d4d4d8 !important; /* zinc-300 */
            color: #18181b !important;
        }

        .fc-theme-custom .fc-button-active {
            background-color: #e4e4e7 !important; /* zinc-200 */
            color: #09090b !important;
            border-color: #d4d4d8 !important;
        }

        /* Título del Mes */
        .fc-theme-custom .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.025em;
            color: #18181b !important;
        }

        /* Cabecera de los Días de la Semana */
        .fc-theme-custom .fc-col-header-cell {
            background-color: #f4f4f5 !important; /* zinc-100 */
            padding: 10px 0 !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #71717a !important; /* zinc-500 */
            border-bottom: 1px solid #e4e4e7 !important;
        }

        /* Celdas del Calendario */
        .fc-theme-custom td, 
        .fc-theme-custom th {
            border-color: #f4f4f5 !important; /* zinc-100 */
        }

        .fc-theme-custom .fc-daygrid-day-number {
            font-size: 0.785rem !important;
            font-weight: 500 !important;
            color: #71717a !important;
            padding: 8px !important;
        }

        /* Día de hoy */
        .fc-theme-custom .fc-day-today {
            background-color: #f9fafb !important;
        }

        .fc-theme-custom .fc-day-today .fc-daygrid-day-number {
            background-color: var(--color-primary-600, #8B1A4B) !important;
            color: #ffffff !important;
            border-radius: 9999px !important;
            width: 24px !important;
            height: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 6px !important;
            padding: 0 !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }

        /* Eventos del Calendario */
        .fc-theme-custom .fc-event {
            border-radius: 6px !important;
            padding: 3.5px 8px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border: none !important;
            margin: 2px 4px !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            cursor: pointer !important;
            transition: transform 0.15s ease, filter 0.15s ease !important;
        }

        .fc-theme-custom .fc-event-title,
        .fc-theme-custom .fc-event-main,
        .fc-theme-custom .fc-event-title-container,
        .fc-theme-custom .fc-event-main-frame,
        .fc-theme-custom .fc-event {
            color: #ffffff !important;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        .fc-theme-custom .fc-event:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04) !important;
        }


        /* ─── SOPORTE MODO OSCURO (EXHAUSTIVO) ────────────────────────── */
        .dark .fc-theme-custom {
            --fc-border-color: rgba(255, 255, 255, 0.05);
            --fc-page-bg-color: #09090b; /* zinc-950 */
            --fc-neutral-bg-color: #18181b; /* zinc-900 */
            --fc-today-bg-color: rgba(255, 255, 255, 0.02);
        }

        /* Fondo de la Tarjeta */
        .dark .bg-white {
            background-color: #09090b !important; /* zinc-950 */
            border-color: #27272a !important; /* zinc-800 */
        }

        /* Título del Mes en Modo Oscuro */
        .dark .fc-theme-custom .fc-toolbar-title {
            color: #ffffff !important;
        }

        /* Botones en Modo Oscuro */
        .dark .fc-theme-custom .fc-button {
            background-color: #18181b !important; /* zinc-900 */
            color: #e4e4e7 !important; /* zinc-200 */
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .dark .fc-theme-custom .fc-button:hover {
            background-color: #27272a !important; /* zinc-800 */
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        .dark .fc-theme-custom .fc-button-active {
            background-color: #27272a !important; /* zinc-800 */
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        /* Cabecera de los Días en Modo Oscuro */
        .dark .fc-theme-custom .fc-col-header-cell {
            background-color: #18181b !important; /* zinc-900 */
            color: #a1a1aa !important; /* zinc-400 */
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Bordes del Grid en Modo Oscuro */
        .dark .fc-theme-custom td, 
        .dark .fc-theme-custom th {
            border-color: rgba(255, 255, 255, 0.04) !important;
        }

        /* Números de día en Modo Oscuro */
        .dark .fc-theme-custom .fc-daygrid-day-number {
            color: #a1a1aa !important;
        }

        .dark .fc-theme-custom .fc-day-today {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .dark .fc-theme-custom .fc-day-today .fc-daygrid-day-number {
            background-color: var(--color-primary-500, #8B1A4B) !important;
            color: #ffffff !important;
        }
        
        .dark .fc-theme-custom .fc-day-other {
            background-color: rgba(255, 255, 255, 0.01) !important;
        }

        /* Enlace "+ más" de eventos acumulados */
        .fc-theme-custom .fc-daygrid-more-link {
            color: var(--color-primary-500, #8B1A4B) !important;
            font-size: 0.725rem !important;
            font-weight: 700 !important;
            padding: 2px 4px !important;
            border-radius: 4px !important;
            transition: all 0.15s ease !important;
            text-decoration: none !important;
        }

        .fc-theme-custom .fc-daygrid-more-link:hover {
            background-color: rgba(139, 26, 75, 0.08) !important;
            color: var(--color-primary-600, #70123a) !important;
        }

        /* Popover flotante de "+ más" */
        .fc-theme-custom .fc-popover {
            border: 1px solid #e4e4e7 !important; /* zinc-200 */
            border-radius: 12px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            background-color: #ffffff !important;
            z-index: 1000 !important;
        }

        .fc-theme-custom .fc-popover-header {
            background-color: #f4f4f5 !important; /* zinc-100 */
            padding: 8px 12px !important;
            border-top-left-radius: 11px !important;
            border-top-right-radius: 11px !important;
            border-bottom: 1px solid #e4e4e7 !important;
            font-weight: 700 !important;
            font-size: 0.825rem !important;
            color: #18181b !important;
        }

        .fc-theme-custom .fc-popover-title {
            color: #18181b !important;
        }

        .fc-theme-custom .fc-popover-close {
            color: #71717a !important;
            opacity: 0.8 !important;
            transition: opacity 0.15s ease !important;
        }

        .fc-theme-custom .fc-popover-close:hover {
            opacity: 1 !important;
        }

        .fc-theme-custom .fc-popover-body {
            background-color: #ffffff !important;
            padding: 10px !important;
            border-bottom-left-radius: 11px !important;
            border-bottom-right-radius: 11px !important;
        }

        /* SOPORTE POP-OVER MODO OSCURO */
        .dark .fc-theme-custom .fc-popover {
            border-color: #27272a !important; /* zinc-800 */
            background-color: #18181b !important; /* zinc-900 */
        }

        .dark .fc-theme-custom .fc-popover-header {
            background-color: #27272a !important; /* zinc-800 */
            border-bottom-color: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        .dark .fc-theme-custom .fc-popover-title {
            color: #ffffff !important;
        }

        .dark .fc-theme-custom .fc-popover-body {
            background-color: #18181b !important;
        }

        .dark .fc-theme-custom .fc-daygrid-more-link:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
            color: var(--color-primary-400, #F472B6) !important;
        }

        /* ─── SOPORTE Y ADAPTABILIDAD RESPONSIVA (MÓVIL / TABLET) ──────── */
        @media (max-width: 768px) {
            .fc-theme-custom .fc-toolbar {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: center !important;
            }

            .fc-theme-custom .fc-toolbar-title {
                font-size: 1.05rem !important;
                text-align: center !important;
            }

            .fc-theme-custom .fc-button {
                padding: 5px 9px !important;
                font-size: 0.725rem !important;
            }

            .fc-theme-custom .fc-daygrid-day-number {
                font-size: 0.7rem !important;
                padding: 3px !important;
            }

            .fc-theme-custom .fc-col-header-cell {
                font-size: 0.65rem !important;
                padding: 5px 0 !important;
            }

            .fc-theme-custom .fc-event {
                padding: 2px 4px !important;
                font-size: 0.65rem !important;
                margin: 1px 2px !important;
            }
        }
    </style>

    <!-- Script de Inicialización Nativo del Calendario de JS -->
    <script>
        (function() {
            const calendarEl = document.getElementById('calendar-native');
            if (calendarEl) {
                // Determinar vista inicial en base al tamaño de pantalla
                var initialViewSetting = window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth';

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: initialViewSetting,
                    locale: 'es',
                    firstDay: 1, // Lunes
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listMonth'
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        list: 'Lista'
                    },
                    events: @js(json_decode($this->getEventsJson())),
                    eventClick: function(info) {
                        if (info.event.url) {
                            info.jsEvent.preventDefault();
                            window.open(info.event.url, '_self'); // Abre la edición en la misma pestaña
                        }
                    },
                    dayMaxEvents: true, // Limitar eventos visibles y mostrar popover "+ más"
                    height: 'auto',
                    fixedWeekCount: false, // Oculta filas extras vacías
                    showNonCurrentDates: true // Muestra días del mes anterior/siguiente de forma atenuada
                });
                
                calendar.render();
                
                // Forzar un recalculado de tamaño retardado inmediato para ajustarse perfectamente al ancho final del panel de Filament
                setTimeout(function() {
                    calendar.updateSize();
                }, 100);
                
                // Redimensionado y cambio de vista dinámico en resize
                window.addEventListener('resize', function() {
                    var responsiveView = window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth';
                    if (calendar.view.type !== responsiveView) {
                        calendar.changeView(responsiveView);
                    }
                    setTimeout(function() {
                        calendar.updateSize();
                    }, 50);
                });
                
                document.addEventListener('livewire:navigated', function() {
                    setTimeout(function() {
                        calendar.render();
                        calendar.updateSize();
                    }, 100);
                });

                // Escuchar clicks en botones que colapsen el sidebar de Filament y redimensionar tras la animación
                document.addEventListener('click', function(e) {
                    if (e.target.closest('button') || e.target.closest('a')) {
                        setTimeout(function() {
                            calendar.updateSize();
                        }, 350); 
                    }
                });
            }
        })();
    </script>
</div>
