<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Hotel Bugambilias</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        [v-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.6);
        }
        .error-glow {
            filter: drop-shadow(0 0 30px rgba(113, 28, 55, 0.3));
        }
        .bg-mesh {
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(113, 28, 55, 0.05) 0px, transparent 50%),
                              radial-gradient(at 100% 0%, rgba(184, 20, 70, 0.05) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(113, 28, 55, 0.05) 0px, transparent 50%),
                              radial-gradient(at 0% 100%, rgba(113, 28, 55, 0.05) 0px, transparent 50%);
        }
        .dark .bg-mesh {
            background-color: #020617;
            background-image: radial-gradient(at 0% 0%, rgba(113, 28, 55, 0.1) 0px, transparent 50%),
                              radial-gradient(at 100% 0%, rgba(184, 20, 70, 0.1) 0px, transparent 50%);
        }
    </style>
</head>
<body class="h-full antialiased bg-mesh transition-colors duration-500">
    
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 opacity-30 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/20 rounded-full blur-[120px]"></div>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="glass-panel border border-white/20 dark:border-white/10 sm:rounded-3xl shadow-2xl overflow-hidden">
                <div class="px-6 py-12 sm:p-16 text-center">
                    <!-- Brand Section (Logo) -->
                    <div class="mb-10 flex justify-center">
                        <div class="relative group">
                            <div class="absolute -inset-4 bg-gradient-to-r from-primary-500/20 to-primary-700/20 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition duration-1000"></div>
                            <div class="relative p-2 bg-white dark:bg-slate-900 rounded-full shadow-xl ring-1 ring-slate-900/5 transition-transform duration-500 group-hover:scale-105">
                                <img src="{{ asset('img/hotel-icon.png') }}" alt="Hotel Bugambilias Logo" class="h-24 w-auto object-contain">
                            </div>
                        </div>
                    </div>

                    <!-- Error Code & Header -->
                    <div class="space-y-4">
                        <span class="inline-flex items-center px-4 py-1 rounded-full text-xs font-bold tracking-[0.2em] uppercase bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-500/20">
                            HTTP STATUS @yield('code')
                        </span>
                        
                        <h1 class="text-4xl sm:text-5xl font-black text-slate-950 dark:text-white tracking-tight leading-tight">
                            @yield('title')
                        </h1>
                    </div>

                    <!-- Message Section -->
                    <div class="mt-8">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                            </div>
                            <div class="relative flex justify-center text-sm uppercase tracking-widest">
                                <span class="px-3 bg-white dark:bg-slate-900/0 text-slate-600 dark:text-slate-400 font-bold">Detalles del Error</span>
                            </div>
                        </div>
                        
                        <p class="mt-6 text-lg text-slate-800 dark:text-slate-300 leading-relaxed font-semibold max-w-lg mx-auto">
                            @yield('message')
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ url('/') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-sm font-bold tracking-wide text-white bg-primary-600 rounded-2xl hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all shadow-xl shadow-primary-500/25 active:scale-95">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Volver al Inicio
                        </a>
                        
                        <button onclick="window.history.back()" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-sm font-bold tracking-wide text-primary-700 dark:text-primary-400 border-2 border-primary-100 dark:border-primary-900/50 rounded-2xl hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-all active:scale-95 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Regresar a la Página Anterior
                        </button>
                    </div>
                </div>

                <!-- Footer Visual -->
                <div class="h-2 bg-gradient-to-r from-primary-600 via-primary-400 to-primary-700 opacity-80"></div>
            </div>
            
            <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-600 font-medium tracking-wide uppercase">
                &copy; {{ date('Y') }} Hotel Bugambilias 
            </p>
        </div>
    </div>

</body>
</html>
