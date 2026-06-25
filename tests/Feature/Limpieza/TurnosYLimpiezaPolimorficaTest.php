<?php

declare(strict_types=1);

namespace Tests\Feature\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\LimpiezaHorario;
use App\Models\Limpieza\Turno;
use App\Models\User;
use App\Notifications\Limpieza\RecordatorioLimpiezaPendiente;
use Carbon\Carbon;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Ejecutar seeders necesarios
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $this->ubicacionPadre = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    // Crear ubicaciones de prueba
    $this->zonaPrincipal = Ubicacion::create([
        'tipo' => 'zona',
        'nombre' => 'Zona A - Lobby',
        'orden' => 90,
        'estado' => 1,
    ]);

    $this->zonaSecundaria = Ubicacion::create([
        'tipo' => 'zona',
        'nombre' => 'Zona B - Pasillo Principal',
        'orden' => 91,
        'estado' => 1,
    ]);

    $this->carritoBodega = Ubicacion::create([
        'tipo' => 'almacen',
        'nombre' => 'Carrito Limpieza #1',
        'orden' => 92,
        'estado' => 1,
    ]);

    // Colaboradores
    $this->lider = Colaborador::factory()->create();
    $this->apoyo = Colaborador::factory()->create();

    // Entidades Limpiables (Habitación, Espacio, Ubicación)
    $this->habitacion = Habitacion::create([
        'codigo' => 'HAB-1001',
        'numero' => 1001,
        'slug' => 'habitacion-1001',
        'nombre' => 'Habitación 1001 Deluxe',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacionPadre->id,
        'estado' => EstadoHabitacion::Sucia,
    ]);

    $this->espacio = Espacio::create([
        'codigo' => 'ESP-GYM',
        'nombre' => 'Gimnasio Principal',
        'tipo' => TipoEspacio::GYM,
        'capacidad_personas' => 20,
        'estado' => EstadoEspacio::Disponible,
    ]);
});

describe('Módulo de Turnos de Limpieza (limp_horario_turnos)', function () {
    it('crea un turno con líder, apoyo y múltiples carritos', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Matutino A',
            'lider_id' => $this->lider->id,
            'apoyo_id' => $this->apoyo->id,
            'carritos_ids' => [$this->carritoBodega->id],
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => true,
        ]);

        expect($turno->nombre)->toBe('Turno Matutino A')
            ->and($turno->lider->id)->toBe($this->lider->id)
            ->and($turno->apoyo->id)->toBe($this->apoyo->id)
            ->and($turno->carritos_ids)->toBeArray()
            ->and($turno->carritos_ids)->toContain($this->carritoBodega->id)
            ->and($turno->carritos->first()->nombre)->toBe('Carrito Limpieza #1');
    });
});

describe('Planificación de Horarios Polimórficos Cabecera-Detalle (limp_horarios)', function () {
    it('permite planificar limpieza para habitación, espacio y ubicación general', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Vespertino',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '15:00:00',
            'hora_fin' => '23:00:00',
        ]);

        // 1. Horario para Habitación
        $horarioHab = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '16:00:00',
            'frecuencia' => 'diaria',
            'checklist' => ['Tender cama', 'Barrer'],
        ]);
        $horarioHab->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        // 2. Horario para Espacio
        $horarioEsp = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '17:00:00',
            'frecuencia' => 'semanal',
            'dia_semana' => 'lunes',
        ]);
        $horarioEsp->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        // 3. Horario para Ubicación Física
        $horarioUbi = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '18:00:00',
            'frecuencia' => 'diaria',
        ]);
        $horarioUbi->detalles()->create([
            'limpiable_type' => Ubicacion::class,
            'limpiable_id' => $this->zonaPrincipal->id,
        ]);

        expect($horarioHab->detalles->first()->limpiable)->toBeInstanceOf(Habitacion::class)
            ->and($horarioEsp->detalles->first()->limpiable)->toBeInstanceOf(Espacio::class)
            ->and($horarioUbi->detalles->first()->limpiable)->toBeInstanceOf(Ubicacion::class)
            ->and($this->habitacion->horariosLimpieza)->toHaveCount(1)
            ->and($this->espacio->horariosLimpieza)->toHaveCount(1)
            ->and($this->zonaPrincipal->horariosLimpieza)->toHaveCount(1);
    });
});

describe('Comando de Materialización Diaria (limpieza:materializar-ejecuciones)', function () {
    it('materializa horarios correctos para el día de la semana', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Matutino',
            'lider_id' => $this->lider->id,
            'apoyo_id' => $this->apoyo->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => true,
        ]);

        // Diaria
        $h1 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
            'checklist' => ['Tarea 1'],
        ]);
        $h1->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        // Semanal los lunes (2026-06-22 es Lunes)
        $h2 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '10:00:00',
            'frecuencia' => 'semanal',
            'dia_semana' => 'lunes',
        ]);
        $h2->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        // Semanal los miércoles
        $h3 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '10:00:00',
            'frecuencia' => 'semanal',
            'dia_semana' => 'miercoles',
        ]);
        $h3->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        // Forzar fecha a un Lunes (ej: 2026-06-22 es Lunes)
        $lunes = '2026-06-22';
        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => $lunes]);

        // Deberían materializarse: la diaria y la semanal del lunes (2 ejecuciones)
        $ejecuciones = LimpiezaEjecucion::whereDate('fecha', $lunes)->get();
        expect($ejecuciones)->toHaveCount(2);

        // Validar colaborador preasignado (debería ser null por requerimiento, sin asignar)
        expect($ejecuciones->first()->colaborador_id)->toBeNull();

        // Validar que el checklist se cargó con el valor inicial false
        expect($ejecuciones->first()->detalles_checklist)->toBeArray()
            ->and($ejecuciones->first()->detalles_checklist['Tarea 1'])->toBeFalse();

        // Ejecutar de nuevo para el mismo lunes no debe duplicar registros
        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => $lunes]);
        expect(LimpiezaEjecucion::whereDate('fecha', $lunes)->count())->toBe(2);
    });

    it('materializa con colaborador nulo (sin asignar) si no hay colaborador de apoyo', function () {
        $turnoSinApoyo = Turno::create([
            'nombre' => 'Turno Solo Lider',
            'lider_id' => $this->lider->id,
            'apoyo_id' => null,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $h = LimpiezaHorario::create([
            'turno_id' => $turnoSinApoyo->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
        ]);
        $h->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => '2026-06-22']);

        $ejecucion = LimpiezaEjecucion::whereDate('fecha', '2026-06-22')->first();
        expect($ejecucion)->not->toBeNull()
            ->and($ejecucion->colaborador_id)->toBeNull();
    });
});

describe('Transiciones y Registro de Ejecución (limp_ejecuciones)', function () {
    it('flujo de estados de una ejecución de limpieza', function () {
        $turno = Turno::create([
            'nombre' => 'Turno Matutino A',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $ejecucion = LimpiezaEjecucion::create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
            'turno_id' => $turno->id,
            'colaborador_id' => $this->lider->id,
            'fecha' => '2026-06-22',
            'estado' => EstadoLimpieza::Pendiente,
        ]);

        // Iniciar
        $ejecucion->update([
            'estado' => EstadoLimpieza::EnProgreso,
            'hora_inicio' => '08:05:00',
        ]);

        expect($ejecucion->fresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
            ->and($ejecucion->fresh()->hora_inicio)->toBe('08:05:00');

        // Completar con checklist
        $ejecucion->update([
            'estado' => EstadoLimpieza::Completada,
            'hora_fin' => '08:45:00',
            'detalles_checklist' => [
                'limpieza_cama' => true,
                'aspirado' => true,
                'reposicion_toallas' => true,
            ],
            'observaciones' => 'Sin novedades',
        ]);

        $fresh = $ejecucion->fresh();
        expect($fresh->estado)->toBe(EstadoLimpieza::Completada)
            ->and($fresh->hora_fin)->toBe('08:45:00')
            ->and($fresh->detalles_checklist)->toBeArray()
            ->and($fresh->detalles_checklist['aspirado'])->toBeTrue()
            ->and($fresh->observaciones)->toBe('Sin novedades');
    });
});

describe('Nuevas características del Módulo de Limpieza (Equipos, Horarios Nullable, Recordatorios)', function () {
    it('permite crear un horario sin asignar turno', function () {
        $horarioSinTurno = LimpiezaHorario::create([
            'turno_id' => null,
            'hora_estimada' => '11:00:00',
            'frecuencia' => 'diaria',
        ]);
        $horarioSinTurno->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        expect($horarioSinTurno->turno_id)->toBeNull()
            ->and($horarioSinTurno->detalles->first()->limpiable)->toBeInstanceOf(Habitacion::class);
    });

    it('ignora horarios sin turno asignado durante la materialización', function () {
        // Horario con turno
        $turno = Turno::create([
            'nombre' => 'Turno Activo',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        $h1 = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
        ]);
        $h1->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        // Horario sin turno
        $h2 = LimpiezaHorario::create([
            'turno_id' => null,
            'hora_estimada' => '09:00:00',
            'frecuencia' => 'diaria',
        ]);
        $h2->detalles()->create([
            'limpiable_type' => Espacio::class,
            'limpiable_id' => $this->espacio->id,
        ]);

        Artisan::call('limpieza:materializar-ejecuciones', ['fecha' => '2026-06-22']);

        // Sólo debe existir 1 ejecución de limpieza (la de la habitación con turno)
        $ejecuciones = LimpiezaEjecucion::whereDate('fecha', '2026-06-22')->get();
        expect($ejecuciones)->toHaveCount(1)
            ->and($ejecuciones->first()->limpiable_type)->toBe(Habitacion::class);
    });

    it('envía recordatorios de limpieza pendientes cuando pasa la hora estimada', function () {
        // Creamos una ejecución pendiente para hoy
        $turno = Turno::create([
            'nombre' => 'Turno Alertas',
            'lider_id' => $this->lider->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);

        // Aseguramos que el líder tenga usuario asociado para recibir notificaciones
        $userLider = User::factory()->create(['persona_id' => $this->lider->persona_id]);

        $horario = LimpiezaHorario::create([
            'turno_id' => $turno->id,
            'hora_estimada' => '08:00:00',
            'frecuencia' => 'diaria',
        ]);
        $horario->detalles()->create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
        ]);

        $ejecucion = LimpiezaEjecucion::create([
            'horario_id' => $horario->id,
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
            'turno_id' => $turno->id,
            'colaborador_id' => $this->lider->id,
            'fecha' => now()->toDateString(),
            'estado' => EstadoLimpieza::Pendiente,
        ]);

        // Cambiar la hora actual a las 09:00 para simular que ya venció la hora estimada (08:00)
        Carbon::setTestNow(now()->startOfDay()->addHours(9));

        Notification::fake();

        Artisan::call('limpieza:enviar-recordatorios');

        // Verificar que se haya enviado la notificación al usuario del líder
        Notification::assertSentTo(
            $userLider,
            RecordatorioLimpiezaPendiente::class
        );

        // Verificar trazabilidad
        expect($ejecucion->fresh()->recordatorio_enviado_at)->not->toBeNull();

        Carbon::setTestNow(); // reset
    });
});
