<!DOCTYPE html>
<html lang="es" class="dark h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotel Bugambilias • Turnos Restaurante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-950 text-slate-100 font-sans antialiased select-none overflow-hidden flex flex-col justify-between">
    
    {{-- Header estilo Display de Hotel / Tablero Informativo --}}
    <header class="bg-slate-900/90 border-b border-slate-800 px-4 sm:px-8 py-3 sm:py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-lg">
        <div class="flex items-center gap-2 sm:gap-4">
            <div class="p-2 sm:p-3 bg-[rgba(107,0,62,0.25)] text-[#e87faa] rounded-lg sm:rounded-xl border border-[rgba(107,0,62,0.4)]">
                <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-2xl font-black tracking-wider uppercase text-white">
                    HOTEL BUGAMBILIAS
                </h1>
                <p class="text-[10px] sm:text-xs text-slate-400 font-semibold tracking-wide uppercase">
                    Restaurante • Turnos de Atención
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-6 w-full sm:w-auto justify-between sm:justify-end">
            <div class="flex items-center gap-2 px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-lg bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 font-mono text-[10px] sm:text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                EN VIVO
            </div>
            <div class="font-mono text-sm sm:text-xl font-bold text-white" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit', second:'2-digit'}), 1000)" x-text="time || '{{ now()->format('H:i:s') }}'">
            </div>
        </div>
    </header>

    {{-- Main Container con Livewire PantallaPedidos --}}
    <main class="flex-1 p-4 sm:p-8 overflow-auto">
        @livewire(\App\Filament\Pages\Restaurante\PantallaPedidos::class)
    </main>

    {{-- Footer Sobrio Informativo --}}
    <footer class="bg-slate-900/80 border-t border-slate-800 px-4 sm:px-8 py-2 sm:py-3 flex flex-col sm:flex-row items-center justify-between gap-1 sm:gap-0 text-[10px] sm:text-xs text-slate-500 font-medium">
        <div class="flex items-center gap-2">
            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Conserve su comanda al ser llamado.</span>
        </div>
        <div>
            <span>Hotel Bugambilias POS</span>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
