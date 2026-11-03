<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;

final class RegistrarValoracionEstanciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:50'],
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function estrellas(): int
    {
        $estrellas = $this->validated('estrellas');

        return is_numeric($estrellas) ? (int) $estrellas : 5;
    }

    public function comentario(): ?string
    {
        $comentario = $this->validated('comentario');

        return is_string($comentario) && trim($comentario) !== '' ? trim($comentario) : null;
    }

    public function codigo(): ?string
    {
        $codigo = $this->validated('codigo');

        return is_string($codigo) && trim($codigo) !== '' ? trim($codigo) : null;
    }
}
