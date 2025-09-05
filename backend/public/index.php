<?php
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

use App\Controllers\AuthController;
use App\Controllers\InscriptionController;

// Récupérer l'URI et supprimer le préfixe /api si présent
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Supprimer le préfixe /api
if (strpos($uri, '/api') === 0) {
    $uri = substr($uri, 4); // Enlever "/api"
}

// Si l'URI est vide après suppression du préfixe, rediriger vers /health
if (empty($uri) || $uri === '/') {
    $uri = '/health';
}

error_log("Request: $method $uri (original: " . $_SERVER['REQUEST_URI'] . ")");

try {
    switch (true) {
        case $uri === '/auth/login':
            if ($method === 'POST') {
                $controller = new AuthController();
                $controller->login();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;

        case $uri === '/auth/logout':
            if ($method === 'POST') {
                $controller = new AuthController();
                $controller->logout();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;

        case $uri === '/inscriptions':
            if ($method === 'POST') {
                $controller = new InscriptionController();
                $controller->create();
            } elseif ($method === 'GET') {
                $controller = new InscriptionController();
                $controller->getAll();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;

        case preg_match('/^\/inscriptions\/(\d+)$/', $uri, $matches):
            $id = (int)$matches[1];
            if ($method === 'GET') {
                $controller = new InscriptionController();
                $controller->getById($id);
            } elseif ($method === 'DELETE') {
                $controller = new InscriptionController();
                $controller->delete($id);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;

        case $uri === '/inscriptions/stats':
            if ($method === 'GET') {
                $controller = new InscriptionController();
                $controller->getStatistics();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;

        case $uri === '/inscriptions/export':
            if ($method === 'GET') {
                $controller = new InscriptionController();
                $controller->exportCsv();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;

        case $uri === '/health':
            echo json_encode([
                'success' => true,
                'message' => 'API Parrainage BUT Informatique',
                'version' => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s'),
                'endpoints' => [
                    'POST /api/auth/login' => 'Connexion admin',
                    'POST /api/inscriptions' => 'Créer une inscription',
                    'GET /api/inscriptions' => 'Récupérer les inscriptions (admin)',
                    'GET /api/inscriptions/{id}' => 'Récupérer une inscription (admin)',
                    'DELETE /api/inscriptions/{id}' => 'Supprimer une inscription (admin)',
                    'GET /api/inscriptions/stats' => 'Statistiques (admin)',
                    'GET /api/inscriptions/export' => 'Export CSV (admin)'
                ]
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint non trouvé',
                'requested_uri' => $uri,
                'original_uri' => $_SERVER['REQUEST_URI']
            ]);
            break;
    }
} catch (Exception $e) {
    error_log('Erreur router: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur interne'
    ]);
}