<?php

namespace App\Services;

use App\Models\Client;

/**
 * Client business actions. Admin CRUD (create, update, delete) can live here too.
 */
class ClientService
{
    /** The client a view code belongs to — only if that client is active. */
    public function findByViewCode(string $code): ?Client
    {
        return Client::query()
            ->where('view_code', $code)
            ->where('active', true)
            ->first();
    }
}
