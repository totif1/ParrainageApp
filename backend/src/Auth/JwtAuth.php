<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtAuth
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    /**
     * Générer un token JWT
     */
    public function generateToken(array $payload): string
    {
        $now = time();

        $tokenPayload = [
            'iat' => $now,                                    // Issued at
            'exp' => $now + $this->config['jwt']['expiration'], // Expiration
            'iss' => 'parrainage-but-api',                   // Issuer
            'data' => $payload                               // Données utilisateur
        ];

        return JWT::encode(
            $tokenPayload,
            $this->config['jwt']['secret'],
            $this->config['jwt']['algorithm']
        );
    }

    /**
     * Valider et décoder un token JWT
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(
                    $this->config['jwt']['secret'],
                    $this->config['jwt']['algorithm']
                )
            );

            return (array) $decoded->data;

        } catch (ExpiredException $e) {
            error_log('Token expiré: ' . $e->getMessage());
            return null;
        } catch (SignatureInvalidException $e) {
            error_log('Signature JWT invalide: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('Erreur JWT: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extraire le token depuis les headers HTTP
     */
    public function extractTokenFromHeaders(): ?string
    {
        $headers = getallheaders();

        if (!$headers) {
            return null;
        }

        // Rechercher l'en-tête Authorization
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            return null;
        }

        // Format attendu: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Middleware pour vérifier l'authentification
     */
    public function requireAuth(): ?array
    {
        $token = $this->extractTokenFromHeaders();

        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token d\'authentification manquant'
            ]);
            exit;
        }

        $userData = $this->validateToken($token);

        if (!$userData) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token d\'authentification invalide ou expiré'
            ]);
            exit;
        }

        return $userData;
    }

    /**
     * Vérifier si un token est valide (sans exit)
     */
    public function isValidToken(): bool
    {
        $token = $this->extractTokenFromHeaders();

        if (!$token) {
            return false;
        }

        return $this->validateToken($token) !== null;
    }
}