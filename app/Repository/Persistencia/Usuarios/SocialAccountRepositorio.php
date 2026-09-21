<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Repository\Models\Usuarios\SocialAccount;

final class SocialAccountRepositorio implements SocialAccountRepositorioInterface
{
    public function buscarPorProvider(string $provider, string $providerId): ?SocialAccount
    {
        /** @var SocialAccount|null $cuenta */
        $cuenta = SocialAccount::query()
            ->with('user')
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        return $cuenta;
    }

    public function actualizarAvatar(SocialAccount $cuentaSocial, string $avatar): void
    {
        $cuentaSocial->update(['avatar' => $avatar]);
    }

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
    ): SocialAccount {
        /** @var SocialAccount $cuenta */
        $cuenta = SocialAccount::query()->create([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_id' => $providerId,
            'provider_email' => $email,
            'avatar' => $avatar,
            'provider_data' => $providerData,
        ]);

        return $cuenta;
    }
}
