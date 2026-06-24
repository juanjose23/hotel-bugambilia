<?php

declare(strict_types=1);

namespace App\Notifications\Limpieza;

use App\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecordatorioLimpiezaPendiente extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LimpiezaEjecucion $ejecucion
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
        $limpiable = $this->ejecucion->limpiable;
        $nombreAttr = $limpiable ? $limpiable->getAttribute('nombre') : 'Área sin nombre';
        $nombreArea = is_string($nombreAttr) ? $nombreAttr : 'Área sin nombre';

        $horario = $this->ejecucion->horario;
        $horaAttr = $horario ? $horario->getAttribute('hora_estimada') : '—';
        $horaEstimada = is_string($horaAttr) ? $horaAttr : '—';

        $turno = $this->ejecucion->turno;
        $turnoNombreAttr = $turno->getAttribute('nombre');
        $turnoNombre = is_string($turnoNombreAttr) ? $turnoNombreAttr : 'Turno sin nombre';

        return (new MailMessage)
            ->subject("Recordatorio de Limpieza Pendiente: {$nombreArea}")
            ->greeting('Estimado colaborador,')
            ->line("Le recordamos que la limpieza de la ubicación [{$nombreArea}] se encuentra PENDIENTE y ha superado su hora estimada de inicio ({$horaEstimada}) en el {$turnoNombre}.")
            ->action('Ver Tablero de Limpieza', url('/admin/tablero-limpieza'))
            ->line('Por favor, inicie la limpieza a la brevedad para mantener la calidad del servicio.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $limpiable = $this->ejecucion->limpiable;
        $nombreAttr = $limpiable ? $limpiable->getAttribute('nombre') : 'Área';
        $nombreArea = is_string($nombreAttr) ? $nombreAttr : 'Área';

        $horario = $this->ejecucion->horario;
        $horaAttr = $horario ? $horario->getAttribute('hora_estimada') : '—';
        $horaEstimada = is_string($horaAttr) ? $horaAttr : '—';

        return [
            'ejecucion_id' => $this->ejecucion->id,
            'limpiable_type' => $this->ejecucion->limpiable_type,
            'limpiable_id' => $this->ejecucion->limpiable_id,
            'nombre_area' => $nombreArea,
            'hora_estimada' => $horaEstimada,
            'title' => 'Limpieza Pendiente y Retrasada',
            'message' => "La limpieza de {$nombreArea} programada para las {$horaEstimada} aún no ha iniciado.",
        ];
    }
}
