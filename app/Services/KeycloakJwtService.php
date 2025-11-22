<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KeycloakJwtService
{
    /**
     * Decode dan verifikasi JWT dari Keycloak (RS256).
     */
    public function decodeAndVerify(string $jwt): object
    {
        $config = config('services.keycloak');

        // Ambil JWKS (public keys) dari Keycloak, cache 1 jam
        $jwks = Cache::remember('keycloak_jwks', 3600, function () use ($config) {
            $jwksUrl = rtrim($config['base_url'], '/') .
                '/realms/' . $config['realm'] . '/protocol/openid-connect/certs';

            $response = Http::get($jwksUrl);

            if ($response->failed()) {
                throw new RuntimeException('Cannot fetch Keycloak JWKS');
            }

            return $response->json();
        });

        try {
            // Parse JWKS and decode token (allowed algorithms are inferred from the key set)
            $decoded = JWT::decode(
                $jwt,
                JWK::parseKeySet($jwks)
            );
        } catch (\Throwable $e) {
            throw new RuntimeException('Invalid Keycloak token: ' . $e->getMessage(), 0, $e);
        }

        return $decoded;
    }
}
