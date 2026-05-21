<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Hotel Bugambilias</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        [v-cloak] { display: none !important; }
        
        /* Font Styles */
        body {
            font-family: 'Outfit', sans-serif;
        }
        .serif-font {
            font-family: 'Playfair Display', serif;
        }

        /* Glassmorphism Premium */
        .premium-glass {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 30px 70px rgba(113, 28, 55, 0.08), 
                        inset 0 0 0 1px rgba(255, 255, 255, 0.3);
        }
        .dark .premium-glass {
            background: rgba(15, 12, 22, 0.65) !important;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(231, 124, 142, 0.15) !important;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.6), 
                        inset 0 1px 0 0 rgba(255, 255, 255, 0.05) !important;
        }

        /* Animated Mesh Background inspired by summer flowers (Magenta/Pink/Gold/Emerald) */
        .mesh-gradient-bg {
            background-color: #fff5f6;
            background-image: 
                radial-gradient(at 0% 0%, rgba(231, 124, 142, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(253, 242, 244, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.08) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(245, 158, 11, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(113, 28, 55, 0.05) 0px, transparent 50%);
            background-size: 200% 200%;
            animation: waveGradient 20s ease infinite;
        }
        
        .dark .mesh-gradient-bg {
            background-color: #0b070f !important;
            background-image: 
                radial-gradient(at 0% 0%, rgba(113, 28, 55, 0.35) 0px, transparent 55%),
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(113, 28, 55, 0.25) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(245, 158, 11, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 12, 22, 0.8) 0px, transparent 50%) !important;
            background-size: 200% 200%;
            animation: waveGradient 20s ease infinite;
        }

        @keyframes waveGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Gentle Floating Animation for Petals */
        @keyframes floatPetal {
            0% {
                transform: translateY(0px) rotate(0deg) translateX(0px);
                opacity: 0;
            }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% {
                transform: translateY(100vh) rotate(360deg) translateX(80px);
                opacity: 0;
            }
        }

        .petal {
            position: absolute;
            pointer-events: none;
            z-index: 1;
            fill: #e77c8e;
            opacity: 0.6;
            animation: floatPetal 15s linear infinite;
        }
        .dark .petal {
            fill: #711c37 !important;
        }

        /* Entry Transitions */
        .fade-in-up {
            animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pulsing Glow representing the vibrant floral essence */
        .glow-floral {
            box-shadow: 0 0 40px rgba(231, 124, 142, 0.2);
            animation: slowGlow 4s ease-in-out infinite alternate;
        }
        .dark .glow-floral {
            background-color: rgba(15, 12, 22, 0.95) !important;
            border-color: rgba(231, 124, 142, 0.15) !important;
            box-shadow: 0 0 40px rgba(113, 28, 55, 0.4) !important;
            animation: slowGlow 4s ease-in-out infinite alternate;
        }
        @keyframes slowGlow {
            0% { transform: scale(1); opacity: 0.9; }
            100% { transform: scale(1.03); opacity: 1; }
        }

        /* Bulletproof Dark Mode CSS overrides for Text, Borders and Backgrounds (Tailwind V4 Bypass) */
        .dark .text-slate-900 {
            color: #ffffff !important;
        }
        .dark .text-slate-700 {
            color: #cbd5e1 !important;
        }
        .dark .text-slate-500 {
            color: #94a3b8 !important;
        }
        .dark .text-slate-800 {
            color: #e2e8f0 !important;
        }
        .dark .text-primary-600 {
            color: #f1a9b9 !important;
        }
        .dark .text-primary-950 {
            color: #ffffff !important;
        }
        .dark .text-slate-600 {
            color: #94a3b8 !important;
        }
        .dark .border-slate-200 {
            border-color: rgba(231, 124, 142, 0.15) !important;
        }
        .dark .border-slate-200\/80 {
            border-color: rgba(231, 124, 142, 0.1) !important;
        }
        
        /* Sun / Moon Icons state transitions */
        #sunIcon {
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s ease;
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
        #moonIcon {
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s ease;
            opacity: 0;
            transform: scale(0) rotate(90deg);
        }
        
        .dark #sunIcon {
            opacity: 0 !important;
            transform: scale(0) rotate(90deg) !important;
        }
        .dark #moonIcon {
            opacity: 1 !important;
            transform: scale(1) rotate(0deg) !important;
        }

        .dark #themeToggleBtn {
            background-color: rgba(15, 12, 22, 0.8) !important;
            border-color: rgba(231, 124, 142, 0.2) !important;
            color: #f1a9b9 !important;
        }
        .dark #themeToggleBtn:hover {
            color: #e77c8e !important;
            box-shadow: 0 0 20px rgba(231, 124, 142, 0.2) !important;
        }

        .dark #backBtn {
            color: #f1a9b9 !important;
            border-color: rgba(231, 124, 142, 0.2) !important;
        }
        .dark #backBtn:hover {
            background-color: rgba(231, 124, 142, 0.1) !important;
            border-color: rgba(231, 124, 142, 0.3) !important;
        }
    </style>
</head>
<body class="h-full antialiased mesh-gradient-bg transition-colors duration-500 overflow-x-hidden relative">
    
    <!-- Animated Falling Bugambilia Petals -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <!-- We dynamically generate gorgeous stylized bougainvillea petals using inline SVG -->
        <svg class="petal" style="left: 10%; top: -5%; width: 28px; height: 28px; animation-delay: 0s; animation-duration: 14s;" viewBox="0 0 24 24">
            <path d="M12,2 C15,7 22,9 22,14 C22,19 18,22 12,22 C6,22 2,19 2,14 C2,9 9,7 12,2 Z"/>
        </svg>
        <svg class="petal" style="left: 30%; top: -5%; width: 20px; height: 20px; animation-delay: 3s; animation-duration: 18s; fill: #f1a9b9;" viewBox="0 0 24 24">
            <path d="M12,2 C15,7 22,9 22,14 C22,19 18,22 12,22 C6,22 2,19 2,14 C2,9 9,7 12,2 Z"/>
        </svg>
        <svg class="petal" style="left: 55%; top: -5%; width: 32px; height: 32px; animation-delay: 1s; animation-duration: 22s; fill: #711c37;" viewBox="0 0 24 24">
            <path d="M12,2 C15,7 22,9 22,14 C22,19 18,22 12,22 C6,22 2,19 2,14 C2,9 9,7 12,2 Z"/>
        </svg>
        <svg class="petal" style="left: 75%; top: -5%; width: 24px; height: 24px; animation-delay: 5s; animation-duration: 16s;" viewBox="0 0 24 24">
            <path d="M12,2 C15,7 22,9 22,14 C22,19 18,22 12,22 C6,22 2,19 2,14 C2,9 9,7 12,2 Z"/>
        </svg>
        <svg class="petal" style="left: 90%; top: -5%; width: 22px; height: 22px; animation-delay: 2s; animation-duration: 20s; fill: #e77c8e;" viewBox="0 0 24 24">
            <path d="M12,2 C15,7 22,9 22,14 C22,19 18,22 12,22 C6,22 2,19 2,14 C2,9 9,7 12,2 Z"/>
        </svg>
    </div>

    <!-- Header Navigation & Elegant Theme Toggle -->
    <header class="absolute top-0 w-full z-20 px-6 py-6 sm:px-12 flex justify-between items-center pointer-events-auto">
        <!-- Floral Logo Indicator -->
        <div class="flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-primary-500/10 text-primary-600 border border-primary-500/20 backdrop-blur-md">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <!-- Elegant flower path representing bugambilia -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
            </span>
            <span class="text-sm font-black tracking-[0.25em] uppercase text-primary-950">Hotel Bugambilias</span>
        </div>

        <!-- Master Theme Switcher -->
        <button id="themeToggleBtn" aria-label="Toggle Dark Mode" class="p-3 rounded-2xl bg-white/80 border border-slate-200/50 shadow-lg backdrop-blur-md text-slate-700 hover:text-primary-600 transition-all duration-300 active:scale-95 group relative overflow-hidden">
            <div class="relative w-5 h-5 flex items-center justify-center">
                <!-- Sun Icon -->
                <svg id="sunIcon" class="w-5 h-5 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
                <!-- Moon Icon -->
                <svg id="moonIcon" class="w-5 h-5 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </div>
        </button>
    </header>

    <!-- Main Container -->
    <div class="min-h-full flex flex-col justify-center py-20 px-4 sm:px-6 lg:px-8 relative z-10 overflow-hidden">
        
        <!-- Elegant Background Lights -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-7xl h-full -z-10 opacity-40 pointer-events-none">
            <div class="absolute top-[20%] left-[10%] w-[450px] h-[450px] bg-primary-300/30 rounded-full blur-[140px] animate-pulse"></div>
            <div class="absolute bottom-[20%] right-[10%] w-[450px] h-[450px] bg-emerald-300/20 rounded-full blur-[140px] animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-2xl fade-in-up">
            <!-- Premium Glass Card container -->
            <div class="premium-glass sm:rounded-[36px] overflow-hidden shadow-2xl relative">
                
                <!-- Vibrant Summer Blooming Branch Top Graphic -->
                <div class="absolute top-0 left-0 w-full h-[6px] bg-gradient-to-r from-primary-400 via-primary-500 to-primary-300"></div>

                <div class="px-6 py-14 sm:p-16 text-center">
                    
                    <!-- Floral Blooming Bugambilia Crown / Brand Emblem -->
                    <div class="mb-10 flex justify-center">
                        <div class="relative group">
                            <!-- Soft glowing pulse behind -->
                            <div class="absolute -inset-6 bg-gradient-to-r from-primary-400/30 to-primary-600/30 rounded-full blur-3xl opacity-80 group-hover:opacity-100 transition duration-700 animate-pulse"></div>
                            
                            <!-- Master Floral Crest Outer Circle -->
                            <div class="relative p-5 bg-white/95 rounded-full shadow-[0_15px_40px_rgba(113,28,55,0.12)] border border-primary-100 transition-all duration-500 group-hover:scale-105 glow-floral flex items-center justify-center">
                                <!-- Hand-crafted premium bugambilia floral illustration -->
                                <svg class="h-20 w-20 text-primary-500" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <!-- Bugambilia blooming center -->
                                    <circle cx="32" cy="32" r="6" fill="currentColor" opacity="0.3"/>
                                    <!-- Petal 1 (Top) -->
                                    <path d="M32 26 C36 12, 46 16, 32 6 C18 16, 28 12, 32 26 Z" fill="currentColor"/>
                                    <!-- Petal 2 (Bottom) -->
                                    <path d="M32 38 C28 52, 18 48, 32 58 C46 48, 36 52, 32 38 Z" fill="currentColor"/>
                                    <!-- Petal 3 (Right) -->
                                    <path d="M38 32 C52 28, 48 18, 58 32 C48 46, 52 36, 38 32 Z" fill="currentColor"/>
                                    <!-- Petal 4 (Left) -->
                                    <path d="M26 32 C12 36, 16 46, 6 32 C16 18, 12 28, 26 32 Z" fill="currentColor"/>
                                    <!-- Decorative summer details / Leaf accents -->
                                    <path d="M43 21 C48 15, 52 23, 43 21 Z" fill="#10B981" stroke="#10B981" opacity="0.8"/>
                                    <path d="M21 43 C16 49, 12 41, 21 43 Z" fill="#10B981" stroke="#10B981" opacity="0.8"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Error Code & Header -->
                    <div class="space-y-4">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold tracking-[0.25em] uppercase bg-primary-500/10 text-primary-600 border border-primary-500/20 backdrop-blur-md">
                            CÓDIGO DE ESTADO @yield('code')
                        </span>
                        
                        <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight serif-font">
                            @yield('title')
                        </h1>
                    </div>

                    <!-- Message Section -->
                    <div class="mt-8">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-slate-200/80"></div>
                            </div>
                            <div class="relative flex justify-center text-xs uppercase tracking-[0.3em] font-black">
                                <span class="px-5 bg-white/0 text-primary-600">Detalles del Error</span>
                            </div>
                        </div>
                        
                        <p class="mt-6 text-[1.125rem] text-slate-700 leading-relaxed font-medium max-w-lg mx-auto">
                            @yield('message')
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4 sm:px-6">
                        <!-- Primary Home Button -->
                        <a href="{{ url('/') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4.5 text-sm font-bold tracking-wider text-white bg-primary-500 rounded-2xl hover:bg-primary-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 shadow-[0_12px_30px_rgba(113,28,55,0.25)] hover:shadow-[0_15px_35px_rgba(113,28,55,0.35)] active:scale-[0.98]">
                            <svg class="w-5 h-5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Volver al Inicio
                        </a>
                        
                        <!-- Back Button -->
                        <button id="backBtn" onclick="window.history.back()" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4.5 text-sm font-bold tracking-wider text-slate-800 border-2 border-slate-200 rounded-2xl hover:bg-slate-50 transition-all duration-300 active:scale-[0.98] shadow-sm">
                            <svg class="w-5 h-5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Página Anterior
                        </button>
                    </div>
                </div>

                <!-- Footer Visual (Blooming bugambilia gradient) -->
                <div class="h-2.5 bg-gradient-to-r from-primary-600 via-primary-400 to-primary-500"></div>
            </div>
            
            <!-- Dynamic Footer Copyright -->
            <p class="mt-8 text-center text-[0.75rem] font-bold text-slate-500 tracking-[0.25em] uppercase">
                &copy; {{ date('Y') }} HOTEL BUGAMBILIAS &bull; TODOS LOS DERECHOS RESERVADOS
            </p>
        </div>
    </div>

    <!-- Smooth Dark Mode Initialization & Control JS -->
    <script>
        const html = document.documentElement;
        const themeToggleBtn = document.getElementById('themeToggleBtn');

        // Check active mode or default to system preferences
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        themeToggleBtn.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                html.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });
    </script>
</body>
</html>
