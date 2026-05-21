<?php

declare(strict_types=1);

namespace App\Notifications\Inventario;

use App\Models\Inventario\Lote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CaducidadProxima extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Lote $lote,
        private readonly int $dias,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Caducidad próxima: {$this->lote->codigo_lote}")
            ->line("El lote {$this->lote->codigo_lote} vencerá en {$this->dias} días.")
            ->line("Producto ID: {$this->lote->producto_id}")
            ->line("Cantidad: {$this->lote->cantidad_disponible}")
            ->action('Ver inventario', url('/admin/inventario/lotes'));
    }
}
