<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use Afip;

class AfipFactory
{
    /**
     * Crea una instancia de Afip
     * @param string      $cuit
     * @param bool        $production
     * @param string      $cert
     * @param string      $key
     * @param string|null $accessToken
     * @return Afip
     */
    public static function create(
        string $cuit,
        bool $production,
        string $cert,
        string $key,
        ?string $accessToken = null
    ): Afip {
        $options = [
            'CUIT'       => $cuit,
            'production' => $production,
            'cert'       => $cert,
            'key'        => $key,
        ];

        if ($accessToken !== null) {
            $options['access_token'] = $accessToken;
        }

        return new Afip($options);
    }
}
