<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $statePath = $getStatePath();
        $key = $getLivewireKey() . '.' . $statePath;
        $height = $getHeight() ?? '680px';
        $minHeight = $getMinHeight() ?? '540px';
        $isReadOnly = $field->isDisabled();
    @endphp

    <link rel="stylesheet" href="{{ asset('vendor/x-spreadsheet/xspreadsheet.css') }}">
    <script src="{{ asset('vendor/sheetjs/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('vendor/x-spreadsheet/xspreadsheet.js') }}"></script>

    <div
        x-data="excelSpreadsheetField({
            state: $wire.entangle('{{ $statePath }}'),
            readOnly: {{ $isReadOnly ? 'true' : 'false' }},
            height: '{{ $height }}'
        })"
        wire:ignore
        wire:key="{{ $key }}"
        :class="isFullscreen ? 'fixed inset-0 z-50 p-4 bg-gray-950/95 flex flex-col backdrop-blur-md' : 'flex flex-col w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden'"
    >
        {{-- Input oculto para importación de Excel --}}
        <input
            type="file"
            x-ref="fileInput"
            @change="importarArchivoExcel($event)"
            accept=".xlsx, .xls, .csv"
            class="hidden"
        />

        {{-- Barra Superior de Herramientas, KPIs y Acciones de Excel --}}
        <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 flex flex-wrap items-center justify-between gap-3 text-xs">
            {{-- Insignia, Buscador Rápido y Contadores de Discrepancias --}}
            <div class="flex items-center flex-wrap gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-semibold text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Libro Excel de Conciliación</span>
                </span>

                {{-- Buscador Rápido en Hoja --}}
                <div class="relative">
                    <input
                        type="text"
                        x-model="searchTerm"
                        @input="buscarEnHoja()"
                        placeholder="Buscar producto o código..."
                        class="text-[11px] pl-7 pr-3 py-1 w-44 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-hidden focus:ring-1 focus:ring-primary-500 placeholder-gray-400"
                    />
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono text-[11px] border border-gray-300 dark:border-gray-700">
                        Total: <strong x-text="stats.total">0</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-mono text-[11px] border border-emerald-300 dark:border-emerald-800/60">
                        Coincidentes: <strong x-text="stats.conciliados">0</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 font-mono text-[11px] border border-rose-300 dark:border-rose-800/60">
                        Faltantes: <strong x-text="stats.faltantes">0</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 font-mono text-[11px] border border-amber-300 dark:border-amber-800/60">
                        Sobrantes: <strong x-text="stats.sobrantes">0</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 font-mono text-[11px] border border-blue-300 dark:border-blue-800/60">
                        Avance: <strong x-text="progresoContado + '%'">0%</strong>
                    </span>
                </div>
            </div>

            {{-- Botonera de Acciones Avanzadas de Hoja de Cálculo --}}
            <div class="flex items-center flex-wrap gap-1.5">
                <template x-if="!readOnly">
                    <div class="flex items-center gap-1.5">
                        {{-- Autocompletar --}}
                        <button
                            type="button"
                            @click="rellenarTodo()"
                            title="Establecer Cantidad Física = Stock Sistema para agilizar conteos donde no hay faltantes"
                            class="px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-600 hover:bg-emerald-500 text-white shadow-2xs transition flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Autocompletar (Físico = Sistema)</span>
                        </button>

                        {{-- Conteo Ciego --}}
                        <button
                            type="button"
                            @click="limpiarConteo()"
                            title="Limpiar cantidades físicas para realizar conteo ciego en físico"
                            class="px-2.5 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Conteo Ciego</span>
                        </button>

                        {{-- Importar desde Excel --}}
                        <button
                            type="button"
                            @click="$refs.fileInput.click()"
                            title="Cargar conteos desde un archivo Excel (.xlsx / .csv)"
                            class="px-2.5 py-1 text-xs font-medium rounded-md bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 border border-primary-200 dark:border-primary-800/60 transition flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span>Importar Excel</span>
                        </button>
                    </div>
                </template>

                {{-- Descargar Excel (.xlsx) Nativo --}}
                <button
                    type="button"
                    @click="exportarExcelXlsx()"
                    title="Descargar libro en formato nativo Microsoft Excel (.xlsx)"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-700 hover:bg-emerald-600 text-white shadow-2xs transition flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1.8 14.5-1.5-2.5 1.5-2.5h-1.6l-.8 1.6-.8-1.6H11l1.5 2.5-1.5 2.5h1.6l.8-1.7.8 1.7h1.6zM13 9V3.5L18.5 9H13z"/>
                    </svg>
                    <span>Descargar Excel (.xlsx)</span>
                </button>

                {{-- Descargar CSV --}}
                <button
                    type="button"
                    @click="exportarCsv()"
                    title="Exportar archivo CSV simple"
                    class="px-2 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>CSV</span>
                </button>

                {{-- Imprimir Planilla --}}
                <button
                    type="button"
                    @click="imprimirHoja()"
                    title="Imprimir planilla de inventario para conteo físico en papel"
                    class="px-2 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Imprimir</span>
                </button>

                {{-- Alternar Pantalla Completa --}}
                <button
                    type="button"
                    @click="toggleFullscreen()"
                    :title="isFullscreen ? 'Salir de pantalla completa' : 'Expandir hoja a pantalla completa'"
                    class="px-2 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition flex items-center gap-1"
                >
                    <template x-if="!isFullscreen">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </template>
                    <template x-if="isFullscreen">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </template>
                    <span x-text="isFullscreen ? 'Cerrar Pantalla Completa' : 'Maximizar'"></span>
                </button>
            </div>
        </div>

        {{-- Contenedor Canvas del Libro Excel --}}
        <div
            x-ref="excelContainer"
            class="w-full flex-1 bg-white text-gray-900 overflow-hidden"
            :style="isFullscreen ? 'height: calc(100vh - 100px); min-height: 500px;' : 'min-height: {{ $minHeight }}; height: {{ $height }};'"
        ></div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            // Configurar traducción al español de x-spreadsheet
            if (typeof window.x_spreadsheet !== 'undefined' && typeof window.x_spreadsheet.locale === 'function') {
                window.x_spreadsheet.locale('es', {
                    toolbar: {
                        undo: 'Deshacer (Ctrl+Z)',
                        redo: 'Rehacer (Ctrl+Y)',
                        print: 'Imprimir planilla',
                        paintformat: 'Copiar formato',
                        clearformat: 'Borrar formato',
                        format: 'Formato de número',
                        fontName: 'Tipografía',
                        fontSize: 'Tamaño de fuente',
                        fontBold: 'Negrita (Ctrl+B)',
                        fontItalic: 'Cursiva (Ctrl+I)',
                        underline: 'Subrayado (Ctrl+U)',
                        strike: 'Tachado',
                        color: 'Color de texto',
                        bgcolor: 'Color de relleno',
                        border: 'Bordes de celda',
                        merge: 'Combinar celdas',
                        align: 'Alineación horizontal',
                        valign: 'Alineación vertical',
                        textwrap: 'Ajuste de texto',
                        freeze: 'Inmovilizar paneles',
                        autofilter: 'Filtro automático',
                        formula: 'Funciones y Fórmulas',
                        more: 'Más herramientas'
                    },
                    contextmenu: {
                        copy: 'Copiar (Ctrl+C)',
                        truncate: 'Cortar (Ctrl+X)',
                        paste: 'Pegar (Ctrl+V)',
                        pasteValue: 'Pegar solo valores',
                        pasteFormat: 'Pegar solo formato',
                        insertRow: 'Insertar fila arriba',
                        insertColumn: 'Insertar columna a la izquierda',
                        deleteSheet: 'Eliminar hoja',
                        deleteRow: 'Eliminar fila',
                        deleteColumn: 'Eliminar columna',
                        deleteCell: 'Eliminar celda',
                        deleteCellText: 'Limpiar contenido de celda',
                        validation: 'Validación de datos',
                        cellprintable: 'Habilitar exportación',
                        cellnonprintable: 'Deshabilitar exportación',
                        celleditable: 'Habilitar edición',
                        cellnoneditable: 'Proteger celda'
                    },
                    format: {
                        normal: 'Normal',
                        text: 'Texto sin formato',
                        number: 'Número (1,000.12)',
                        percent: 'Porcentaje (10.12%)',
                        rmb: 'Córdoba (C$ 10.00)',
                        usd: 'Dólar ($10.00)',
                        eur: 'Euro (€10.00)',
                        date: 'Fecha (DD/MM/AAAA)',
                        time: 'Hora (HH:MM:SS)',
                        datetime: 'Fecha y hora',
                        duration: 'Duración'
                    },
                    formula: {
                        sum: 'SUMA',
                        average: 'PROMEDIO',
                        max: 'MAX',
                        min: 'MIN',
                        concat: 'CONCAT'
                    }
                });
            }

            Alpine.data('excelSpreadsheetField', (config) => ({
                state: config.state,
                readOnly: config.readOnly ?? false,
                height: config.height ?? '680px',
                isFullscreen: false,
                searchTerm: '',
                xs: null,
                stats: { total: 0, conciliados: 0, faltantes: 0, sobrantes: 0 },
                _syncingFromSheet: false,
                _timer: null,

                init() {
                    this.$nextTick(() => {
                        this.initSheet();
                    });

                    this.$watch('state', (newVal) => {
                        if (this._syncingFromSheet) return;
                        this.loadDataIntoSheet(newVal);
                    });
                },

                get progresoContado() {
                    if (this.stats.total === 0) return 0;
                    const contados = this.stats.conciliados + this.stats.faltantes + this.stats.sobrantes;
                    return Math.round((contados / this.stats.total) * 100);
                },

                toggleFullscreen() {
                    this.isFullscreen = !this.isFullscreen;
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initSheet();
                        }, 60);
                    });
                },

                initSheet() {
                    const container = this.$refs.excelContainer;
                    if (!container || typeof window.x_spreadsheet !== 'function') {
                        setTimeout(() => this.initSheet(), 100);
                        return;
                    }

                    container.innerHTML = '';

                    const w = container.clientWidth || 900;
                    const h = this.isFullscreen ? (window.innerHeight - 100) : (parseInt(this.height, 10) || 550);

                    this.xs = window.x_spreadsheet(container, {
                        showToolbar: !this.readOnly,
                        showGrid: true,
                        showContextmenu: !this.readOnly,
                        view: {
                            height: () => h,
                            width: () => container.clientWidth || w,
                        },
                        row: { len: 120, height: 26 },
                        col: { len: 10, width: 130, indexWidth: 50, minWidth: 60 }
                    });

                    this.loadDataIntoSheet(this.state);

                    this.xs.change((cdata) => {
                        this.recalcularStatsYGuardar(cdata);
                    });
                },

                loadDataIntoSheet(data) {
                    if (!this.xs) return;

                    let rawCellData = null;
                    if (data && typeof data === 'object') {
                        if (data.sheets) {
                            const firstSheet = Object.values(data.sheets)[0];
                            if (firstSheet?.cellData) rawCellData = firstSheet.cellData;
                        } else if (data.cellData) {
                            rawCellData = data.cellData;
                        }
                    }

                    const rows = {
                        0: {
                            cells: {
                                0: { text: 'ID Lote', style: 0 },
                                1: { text: 'Código Lote', style: 0 },
                                2: { text: 'Producto', style: 0 },
                                3: { text: 'Ubicación', style: 0 },
                                4: { text: 'Stock Sistema', style: 0 },
                                5: { text: 'Cantidad Física', style: 0 },
                                6: { text: 'Diferencia (Fórm.)', style: 0 },
                                7: { text: 'Observaciones / Justificación', style: 0 },
                            }
                        }
                    };

                    let total = 0;
                    let conciliados = 0;
                    let faltantes = 0;
                    let sobrantes = 0;
                    let sequentialIdx = 1;

                    if (rawCellData && typeof rawCellData === 'object') {
                        const keys = Object.keys(rawCellData);
                        for (const rKey of keys) {
                            if (String(rKey) === '0') continue;
                            const r = rawCellData[rKey];
                            if (!r) continue;

                            const loteId = r[0]?.v ?? r['0']?.v ?? '';
                            if (!loteId) continue;

                            const codigo = r[1]?.v ?? r['1']?.v ?? '';
                            const prod = r[2]?.v ?? r['2']?.v ?? '';
                            const ubi = r[3]?.v ?? r['3']?.v ?? '';
                            const stock = parseFloat(r[4]?.v ?? r['4']?.v ?? 0) || 0;
                            const fis = r[5]?.v ?? r['5']?.v;
                            const nota = r[7]?.v ?? r['7']?.v ?? '';

                            const rIdx = sequentialIdx++;
                            const excelRowNum = rIdx + 1; // 1-based para fórmulas de Excel (Fila 2, 3, 4...)

                            let fisVal = (fis === '' || fis === null || fis === undefined) ? '' : String(fis);
                            let formula = `=F${excelRowNum}-E${excelRowNum}`;

                            let diff = 0;
                            let styleDiff = 4; // pendiente por defecto

                            if (fisVal !== '') {
                                diff = (parseFloat(fisVal) || 0) - stock;
                                diff = Math.round(diff * 1000) / 1000;
                                if (diff === 0) {
                                    conciliados++;
                                    styleDiff = 1; // Verde
                                } else if (diff < 0) {
                                    faltantes++;
                                    styleDiff = 2; // Rojo
                                } else {
                                    sobrantes++;
                                    styleDiff = 3; // Amarillo
                                }
                            }
                            total++;

                            rows[rIdx] = {
                                cells: {
                                    0: { text: String(loteId), editable: false },
                                    1: { text: String(codigo), editable: false },
                                    2: { text: String(prod), editable: false },
                                    3: { text: String(ubi), editable: false },
                                    4: { text: String(stock), editable: false },
                                    5: { text: fisVal },
                                    6: { text: formula, style: styleDiff, editable: false },
                                    7: { text: String(nota) },
                                }
                            };
                        }
                    }

                    this.stats = { total, conciliados, faltantes, sobrantes };

                    const sheetData = {
                        name: 'Físico vs Sistema',
                        freeze: 'C2', // Inmovilizar encabezado (fila 1) y columnas A y B (ID y Código)
                        styles: [
                            { font: { bold: true }, bgcolor: '#1e293b', color: '#ffffff', align: 'center' }, // 0: Cabecera
                            { bgcolor: '#ecfdf5', color: '#047857', font: { bold: true }, align: 'center' }, // 1: Verde (Coincidente)
                            { bgcolor: '#fef2f2', color: '#b91c1c', font: { bold: true }, align: 'center' }, // 2: Rojo (Faltante)
                            { bgcolor: '#fffbeb', color: '#b45309', font: { bold: true }, align: 'center' }, // 3: Amarillo (Sobrante)
                            { bgcolor: '#f8fafc', color: '#64748b', align: 'center' },                        // 4: Pendiente
                        ],
                        cols: {
                            0: { width: 75 },
                            1: { width: 175 },
                            2: { width: 260 },
                            3: { width: 180 },
                            4: { width: 110 },
                            5: { width: 120 },
                            6: { width: 125 },
                            7: { width: 260 }
                        },
                        rows: rows
                    };

                    this.xs.loadData([sheetData]);
                },

                buscarEnHoja() {
                    if (!this.xs || !this.searchTerm.trim()) return;
                    const sheet = this.xs.getData()[0];
                    if (!sheet || !sheet.rows) return;

                    const term = this.searchTerm.toLowerCase().trim();
                    const rowKeys = Object.keys(sheet.rows);

                    for (const rKey of rowKeys) {
                        if (String(rKey) === '0') continue;
                        const r = sheet.rows[rKey];
                        if (!r || !r.cells) continue;

                        const cod = (r.cells[1]?.text ?? '').toLowerCase();
                        const prod = (r.cells[2]?.text ?? '').toLowerCase();
                        const ubi = (r.cells[3]?.text ?? '').toLowerCase();

                        if (cod.includes(term) || prod.includes(term) || ubi.includes(term)) {
                            const rIdx = parseInt(rKey, 10);
                            // Seleccionar la celda de cantidad física del producto encontrado
                            this.xs.cellSelected(rIdx, 5);
                            break;
                        }
                    }
                },

                recalcularStatsYGuardar(cdata) {
                    if (!cdata || !cdata.rows) return;

                    const rows = cdata.rows;
                    let total = 0;
                    let conciliados = 0;
                    let faltantes = 0;
                    let sobrantes = 0;

                    const cellData = {
                        0: {
                            0: { v: 'ID Lote', s: { bl: 1 } },
                            1: { v: 'Código Lote', s: { bl: 1 } },
                            2: { v: 'Producto', s: { bl: 1 } },
                            3: { v: 'Ubicación', s: { bl: 1 } },
                            4: { v: 'Stock Sistema', s: { bl: 1 } },
                            5: { v: 'Cantidad Física', s: { bl: 1 } },
                            6: { v: 'Diferencia (Fórm.)', s: { bl: 1 } },
                            7: { v: 'Notas / Observaciones', s: { bl: 1 } },
                        }
                    };

                    const rowKeys = Object.keys(rows);
                    for (const rKey of rowKeys) {
                        if (String(rKey) === '0') continue;
                        const r = rows[rKey];
                        if (!r || !r.cells) continue;

                        const loteId = r.cells[0]?.text;
                        if (!loteId) continue;

                        const codigo = r.cells[1]?.text ?? '';
                        const prod = r.cells[2]?.text ?? '';
                        const ubi = r.cells[3]?.text ?? '';
                        const stock = parseFloat(r.cells[4]?.text ?? 0) || 0;
                        const fis = r.cells[5]?.text;
                        const nota = r.cells[7]?.text ?? '';

                        const rIdx = parseInt(rKey, 10);
                        const excelRowNum = rIdx + 1;

                        let fisVal = (fis === '' || fis === null || fis === undefined) ? '' : (parseFloat(fis) || 0);

                        let diff = 0;
                        if (fisVal !== '') {
                            diff = (parseFloat(fisVal) || 0) - stock;
                            diff = Math.round(diff * 1000) / 1000;
                            if (diff === 0) conciliados++;
                            else if (diff < 0) faltantes++;
                            else sobrantes++;
                        }
                        total++;

                        cellData[String(rKey)] = {
                            0: { v: isNaN(Number(loteId)) ? loteId : Number(loteId) },
                            1: { v: codigo },
                            2: { v: prod },
                            3: { v: ubi },
                            4: { v: stock },
                            5: { v: fisVal },
                            6: { f: `=F${excelRowNum}-E${excelRowNum}`, v: fisVal !== '' ? diff : '' },
                            7: { v: nota },
                        };
                    }

                    this.stats = { total, conciliados, faltantes, sobrantes };

                    if (this._timer) clearTimeout(this._timer);
                    this._timer = setTimeout(() => {
                        this._syncingFromSheet = true;
                        this.state = {
                            id: 'workbook-inventario-' + Date.now(),
                            sheetOrder: ['sheet-1'],
                            sheets: {
                                'sheet-1': {
                                    id: 'sheet-1',
                                    name: 'Físico vs Sistema',
                                    rowCount: Math.max(100, total + 20),
                                    columnCount: 10,
                                    cellData: cellData
                                }
                            }
                        };
                        setTimeout(() => { this._syncingFromSheet = false; }, 100);
                    }, 350);
                },

                rellenarTodo() {
                    if (!this.xs) return;
                    const sheet = this.xs.getData()[0];
                    if (!sheet || !sheet.rows) return;

                    const rowKeys = Object.keys(sheet.rows);
                    for (const rKey of rowKeys) {
                        if (String(rKey) === '0') continue;
                        const r = sheet.rows[rKey];
                        if (!r || !r.cells) continue;
                        const stock = r.cells[4]?.text ?? '0';
                        if (!r.cells[5]) r.cells[5] = {};
                        r.cells[5].text = stock;
                    }

                    this.xs.loadData([sheet]);
                    this.recalcularStatsYGuardar(sheet);
                },

                limpiarConteo() {
                    if (!this.xs) return;
                    const sheet = this.xs.getData()[0];
                    if (!sheet || !sheet.rows) return;

                    const rowKeys = Object.keys(sheet.rows);
                    for (const rKey of rowKeys) {
                        if (String(rKey) === '0') continue;
                        const r = sheet.rows[rKey];
                        if (!r || !r.cells) continue;
                        if (!r.cells[5]) r.cells[5] = {};
                        r.cells[5].text = '';
                    }

                    this.xs.loadData([sheet]);
                    this.recalcularStatsYGuardar(sheet);
                },

                exportarExcelXlsx() {
                    if (!this.xs || typeof window.XLSX === 'undefined') {
                        this.exportarCsv();
                        return;
                    }

                    const sheet = this.xs.getData()[0];
                    if (!sheet || !sheet.rows) return;

                    const dataAoa = [
                        ['ID Lote', 'Código Lote', 'Producto', 'Ubicación', 'Stock Sistema', 'Cantidad Física', 'Diferencia', 'Observaciones / Justificación']
                    ];

                    const rowKeys = Object.keys(sheet.rows);
                    for (const rKey of rowKeys) {
                        if (String(rKey) === '0') continue;
                        const r = sheet.rows[rKey];
                        if (!r || !r.cells) continue;

                        const id = isNaN(Number(r.cells[0]?.text)) ? r.cells[0]?.text : Number(r.cells[0]?.text);
                        const cod = r.cells[1]?.text ?? '';
                        const prod = r.cells[2]?.text ?? '';
                        const ubi = r.cells[3]?.text ?? '';
                        const stock = parseFloat(r.cells[4]?.text ?? 0) || 0;
                        const fis = r.cells[5]?.text !== '' && r.cells[5]?.text !== undefined ? (parseFloat(r.cells[5]?.text) || 0) : '';
                        const diff = fis !== '' ? (fis - stock) : '';
                        const nota = r.cells[7]?.text ?? '';

                        dataAoa.push([id, cod, prod, ubi, stock, fis, diff, nota]);
                    }

                    const wb = window.XLSX.utils.book_new();
                    const ws = window.XLSX.utils.aoa_to_sheet(dataAoa);

                    ws['!cols'] = [
                        { wch: 10 },
                        { wch: 22 },
                        { wch: 32 },
                        { wch: 22 },
                        { wch: 14 },
                        { wch: 15 },
                        { wch: 14 },
                        { wch: 35 }
                    ];

                    window.XLSX.utils.book_append_sheet(wb, ws, 'Físico vs Sistema');
                    window.XLSX.writeFile(wb, `toma_inventario_${new Date().toISOString().slice(0, 10)}.xlsx`);
                },

                exportarCsv() {
                    if (!this.xs) return;
                    const sheet = this.xs.getData()[0];
                    if (!sheet || !sheet.rows) return;

                    let csv = 'ID Lote,Código Lote,Producto,Ubicación,Stock Sistema,Cantidad Física,Diferencia,Notas\n';
                    const rowKeys = Object.keys(sheet.rows);
                    for (const rKey of rowKeys) {
                        if (String(rKey) === '0') continue;
                        const r = sheet.rows[rKey];
                        if (!r || !r.cells) continue;

                        const id = r.cells[0]?.text ?? '';
                        const cod = r.cells[1]?.text ?? '';
                        const prod = `"${(r.cells[2]?.text ?? '').replace(/"/g, '""')}"`;
                        const ubi = `"${(r.cells[3]?.text ?? '').replace(/"/g, '""')}"`;
                        const stock = r.cells[4]?.text ?? '';
                        const fis = r.cells[5]?.text ?? '';
                        const diff = r.cells[6]?.text ?? '';
                        const nota = `"${(r.cells[7]?.text ?? '').replace(/"/g, '""')}"`;

                        csv += `${id},${cod},${prod},${ubi},${stock},${fis},${diff},${nota}\n`;
                    }

                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.setAttribute('download', `toma_inventario_${Date.now()}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                importarArchivoExcel(event) {
                    const file = event.target?.files?.[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        try {
                            const data = new Uint8Array(e.target.result);
                            const workbook = window.XLSX.read(data, { type: 'array' });
                            const firstSheetName = workbook.SheetNames[0];
                            const worksheet = workbook.Sheets[firstSheetName];
                            const rowsAoa = window.XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                            if (!rowsAoa || rowsAoa.length < 2) {
                                alert('El archivo no contiene filas de datos válidas.');
                                return;
                            }

                            const currentSheet = this.xs.getData()[0];
                            if (!currentSheet || !currentSheet.rows) return;

                            const importMap = new Map();
                            for (let i = 1; i < rowsAoa.length; i++) {
                                const row = rowsAoa[i];
                                if (!row || row.length === 0) continue;
                                const idKey = String(row[0] ?? '').trim();
                                const codKey = String(row[1] ?? '').trim();
                                const cantFis = row[5] !== undefined ? String(row[5]).trim() : '';
                                const nota = row[7] !== undefined ? String(row[7]).trim() : '';

                                if (idKey) importMap.set(idKey, { cantFis, nota });
                                if (codKey) importMap.set(codKey, { cantFis, nota });
                            }

                            const rowKeys = Object.keys(currentSheet.rows);
                            let matched = 0;

                            for (const rKey of rowKeys) {
                                if (String(rKey) === '0') continue;
                                const r = currentSheet.rows[rKey];
                                if (!r || !r.cells) continue;

                                const currentId = String(r.cells[0]?.text ?? '').trim();
                                const currentCod = String(r.cells[1]?.text ?? '').trim();

                                const match = importMap.get(currentId) || importMap.get(currentCod);
                                if (match) {
                                    if (!r.cells[5]) r.cells[5] = {};
                                    r.cells[5].text = match.cantFis;
                                    if (match.nota) {
                                        if (!r.cells[7]) r.cells[7] = {};
                                        r.cells[7].text = match.nota;
                                    }
                                    matched++;
                                }
                            }

                            this.xs.loadData([currentSheet]);
                            this.recalcularStatsYGuardar(currentSheet);
                            alert(`Importación completada: se actualizaron ${matched} lotes desde el archivo Excel.`);
                        } catch (err) {
                            alert('Error al leer el archivo Excel: ' + (err.message || 'Formato no soportado.'));
                        } finally {
                            event.target.value = '';
                        }
                    };

                    reader.readAsArrayBuffer(file);
                },

                imprimirHoja() {
                    window.print();
                }
            }));
        });
    </script>
</x-dynamic-component>
