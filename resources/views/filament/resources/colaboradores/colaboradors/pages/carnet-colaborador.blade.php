<x-filament-panels::page>
    <div class="mx-auto flex max-w-7xl flex-col gap-8 print:gap-0">
        <!-- Cabecera de Control (Oculta al imprimir) -->
        <div
            class="flex flex-col gap-4 rounded-[2.5rem] border border-gray-200 bg-white p-8 shadow-sm lg:flex-row lg:items-center lg:justify-between dark:bg-gray-900 dark:border-white/10 print:hidden">
            <div class="flex items-center gap-6">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-4xl bg-rose-50 p-3 dark:bg-rose-500/10 shadow-inner">
                    <img src="{{ asset(config('hotel.icon')) }}" alt="Logo" class="h-14 w-auto object-contain">
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.4em] text-[#711C37] dark:text-rose-400">{{ config('hotel.name') }}</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white leading-none mt-1">
                        Identidad Corporativa</h1>
                </div>
            </div>

        </div>

        <!-- Contenedor del Carnet -->
        <div id="carnet-container"
            class="flex flex-wrap items-start justify-center gap-16 py-12 print:gap-0 print:py-0 print:justify-start">

            <!-- LADO FRONTAL -->
            <div id="carnet-frontal"
                class="relative h-[539.8px] w-85 shrink-0 overflow-hidden rounded-[2.8rem] bg-white shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)] ring-1 ring-gray-900/5 print:m-4 print:shadow-none print:ring-1 print:ring-gray-300">
                <!-- Sección de Marca Superior -->
                <div class="relative h-60 bg-linear-to-br from-[#711C37] via-[#8b2344] to-[#5a162c]">
                    <div class="absolute inset-0 opacity-15"
                        style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;">
                    </div>

                    <div class="relative flex h-full flex-col items-center pt-10 px-8 text-center">
                        <img src="{{ asset(config('hotel.logo')) }}" alt="{{ config('hotel.name') }}"
                             class="h-14 w-auto brightness-0 invert drop-shadow-lg">
                        <div
                            class="mt-4 inline-flex rounded-full border border-white/30 bg-white/10 px-5 py-1.5 backdrop-blur-xl shadow-lg">
                            <span class="text-[9px] font-black uppercase tracking-[0.5em] text-white">Colaborador</span>
                        </div>
                    </div>

                    <div class="absolute -bottom-1 left-0 w-full overflow-hidden leading-0">
                        <svg class="relative block h-14 w-full" viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path
                                d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,35.26,69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                fill="#ffffff"></path>
                        </svg>
                    </div>
                </div>

                <!-- Sección de Perfil -->
                <div class="relative z-10 -mt-20 flex flex-col items-center px-10">
                    <div class="relative group">
                        <div
                            class="h-48 w-40 overflow-hidden rounded-[2.5rem] border-8 border-white bg-gray-50 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.2)] ring-1 ring-gray-900/10">
                            <img src="{{ $carnetData['url_foto'] }}" alt="Foto"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </div>
                        <div
                            class="absolute -bottom-2 -right-2 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400 border-4 border-white shadow-xl transform rotate-12 group-hover:rotate-0 transition-transform duration-300">
                            <img src="{{ asset(config('hotel.icon')) }}" class="h-8 w-8">
                        </div>
                    </div>

                    <div class="mt-8 text-center w-full">
                        <h2 class="text-[24px] font-black leading-tight tracking-tighter text-gray-900 uppercase px-2">
                            {{ $carnetData['nombre_completo'] }}
                        </h2>
                        <div
                            class="mt-3 inline-flex items-center rounded-2xl bg-rose-50 px-5 py-2 border border-rose-100 shadow-sm">
                            <span class="text-[11px] font-black uppercase tracking-[0.2em] text-[#711C37]">
                                {{ $carnetData['cargo'] }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-10 grid w-full grid-cols-2 border-t-2 border-gray-50 pt-8 gap-4">
                        <div class="flex flex-col">
                            <span
                                class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Departamento</span>
                            <span
                                class="mt-1.5 text-[13px] font-black text-gray-800 leading-tight">{{ $carnetData['departamento'] }}</span>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Código
                                Empleado</span>
                            <span
                                class="mt-1.5 text-xl font-black leading-none text-[#711C37]">{{ $carnetData['codigo'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-0 h-5 w-full bg-linear-to-r from-amber-500 via-[#711C37] to-amber-600">
                </div>
            </div>

            <!-- LADO POSTERIOR -->
            <div id="carnet-reverso"
                class="relative h-[539.8px] w-85 shrink-0 overflow-hidden rounded-[2.8rem] bg-[#0f0f0f] shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)] print:m-4 print:shadow-none print:ring-1 print:ring-gray-800">
                <div class="absolute inset-0 flex items-center justify-center opacity-[0.04]">
                    <img src="{{ asset(config('hotel.icon')) }}" class="w-80 grayscale scale-150">
                </div>

                <div class="relative z-10 flex h-full flex-col p-10 text-white">
                    <div class="flex justify-center py-2">
                        <img src="{{ asset(config('hotel.logo')) }}" alt="{{ config('hotel.name') }}"
                             class="h-10 w-auto brightness-0 invert opacity-30">
                    </div>

                    <div class="mt-6 space-y-6">
                        <div
                            class="rounded-[2.5rem] border border-white/10 bg-white/5 p-6 backdrop-blur-2xl shadow-inner">
                            <div class="flex items-center gap-3 mb-5">
                                <div
                                    class="h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse shadow-[0_0_15px_rgba(251,191,36,0.8)]">
                                </div>
                                <span class="text-[11px] font-black uppercase tracking-[0.4em] text-amber-400">Datos de
                                    Seguridad</span>
                            </div>

                            <div class="grid grid-cols-2 gap-8">
                                <div class="flex flex-col">
                                    <span class="text-[9px] uppercase tracking-widest text-gray-500 font-bold">Tipo
                                        Sangre</span>
                                    <span
                                        class="mt-1 text-4xl font-black text-rose-500 drop-shadow-sm">{{ $carnetData['tipo_sangre'] }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] uppercase tracking-widest text-gray-500 font-bold">Ref.
                                        Interna</span>
                                    <span
                                        class="mt-2 text-base font-black text-gray-200 tracking-tight">{{ $carnetData['codigo'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-4xl border border-white/5 bg-white/2 p-6">
                            <span class="text-[10px] font-black uppercase tracking-[0.4em] text-amber-400/80">Domicilio
                                Oficial</span>
                            <p class="mt-3 text-xs leading-relaxed text-gray-400 font-medium italic line-clamp-2">
                                {{ $carnetData['direccion'] }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-auto mb-2">
                        <div class="rounded-4xl bg-white p-4 shadow-2xl flex flex-col items-center overflow-hidden">
                            <div
                                class="w-full flex justify-center max-h-16 overflow-hidden [&>svg]:w-full [&>svg]:h-auto">
                                {!! $carnetData['barcode_svg'] !!}
                            </div>
                            <p class="mt-3 text-[11px] font-black tracking-[0.6em] text-gray-900 ml-2 uppercase">
                                {{ $carnetData['codigo'] }}</p>
                        </div>
                    </div>

                    <div class="pt-6 text-center">
                        <p class="text-[9px] leading-relaxed text-gray-600 font-medium px-4">
                            Propiedad exclusiva de <span class="text-gray-400 font-bold">{{ config('hotel.name') }}</span>.
                            Documento intransferible. En caso de hallazgo, devolver a RRHH.
                        </p>
                    </div>
                </div>

                <div class="absolute bottom-0 h-5 w-full bg-linear-to-r from-amber-600 via-[#711C37] to-amber-500">
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
