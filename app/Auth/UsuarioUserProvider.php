<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class UsuarioUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return $this->createModel()
            ->where('usuario', $credentials['usuario'])
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $this->hasher->check(
            $credentials['password'],
            $user->getAuthPassword()
        );
    }
}