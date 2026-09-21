<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Repository\Models\Usuarios\SocialAccount;

interface SocialAccountRepositorioInterface
{
    public function buscarPorProvider(string $provider, string $providerId): ?SocialAccount;

    public function actualizarAvatar(SocialAccount $cuentaSocial, string $avatar): void;

    /**
     * @param  array<string, mixed>  $providerData
     */
    public function vincular(
        int $userId,
        string $provider,
        string $providerId,
        ?string $email,
        ?string $avatar,
        array $providerData
    ): SocialAccount;
}
