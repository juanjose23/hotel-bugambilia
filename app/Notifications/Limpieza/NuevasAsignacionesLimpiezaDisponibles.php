<?php

declare(strict_types=1);

namespace App\Notifications\Limpieza;

use App\Models\Limpieza\Turno;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevasAsignacionesLimpiezaDisponibles extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Turno $turno,
        public readonly int $cantidad
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $turnoNombre = (string) ($this->turno->nombre ?? 'Turno sin nombre');

        return (new MailMessage)
            ->subject("Nuevas Limpiezas Disponibles en: {$turnoNombre}")
            ->greeting('Estimado equipo,')
            ->line("Se han materializado {$this->cantidad} nuevas ejecuciones de limpieza sin asignar en el turno [{$turnoNombre}].")
            ->line('El líder del turno puede asignar las tareas o cualquier miembro del equipo puede auto-asignarse y reclamar una habitación, espacio o ubicación.')
            ->action('Ver Tablero de Limpieza', url('/admin/tablero-limpieza'))
            ->line('Por favor, organícense para iniciar los trabajos correspondientes.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $turnoNombre = (string) ($this->turno->nombre ?? 'Turno sin nombre');

        return [
            'turno_id' => $this->turno->id,
            'cantidad' => $this->cantidad,
            'title' => 'Nuevas Limpiezas Disponibles',
            'message' => "Se han programado {$this->cantidad} ejecuciones sin asignar en el turno {$turnoNombre}.",
        ];
    }
}
