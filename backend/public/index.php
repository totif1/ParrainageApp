<?php

// Chargement de l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\InscriptionController;
use App\Controllers\AuthController;

// Configuration des headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs en production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Créer le dossier de logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

try {
    // Récupérer l'URI et la méthode
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Enlever le préfixe /api s'il existe
    $requestUri = preg_replace('#^/api#', '', $requestUri);

    // Router simple
    switch ($requestMethod) {
        case 'GET':
            handleGetRequest($requestUri);
            break;

        case 'POST':
            handlePostRequest($requestUri);
            break;

        case 'PUT':
            handlePutRequest($requestUri);
            break;

        case 'DELETE':
            handleDeleteRequest($requestUri);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log('Erreur générale API: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}

/**
 * Gérer les requêtes GET
 */
function handleGetRequest(string $uri): void
{
    $inscriptionController = new InscriptionController();

    switch ($uri) {
        case '/':
            // Route racine - API info
            echo json_encode([
                'success' => true,
                'message' => 'API Parrainage BUT Informatique',
                'version' => '1.0.0',
                'endpoints' => [
                    'POST /inscriptions' => 'Créer une inscription',
                    'GET /inscriptions' => 'Récupérer les inscriptions (admin)',
                    'GET /inscriptions/stats' => 'Statistiques (admin)',
                    'POST /auth/login' => 'Connexion admin',
                    'POST /auth/logout' => 'Déconnexion admin'
                ]
            ]);
            break;

        case '/inscriptions':
            $inscriptionController->getAll();
            break;

        case '/inscriptions/stats':
            $inscriptionController->getStatistics();
            break;

        case '/inscriptions/export':
            $inscriptionController->exportCsv();
            break;

        default:
            // Vérifier si c'est une route avec ID
            if (preg_match('#^/inscriptions/(\d+)$#', $uri, $matches)) {
                $inscriptionController->getById((int)$matches[1]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Endpoint non trouvé'
                ]);
            }
            break;
    }
}

/**
 * Gérer les requêtes POST
 */
function handlePostRequest(string $uri): void
{
    $inscriptionController = new InscriptionController();
    $authController = new AuthController();

    switch ($uri) {
        case '/inscriptions':
            $inscriptionController->create();
            break;

        case '/auth/login':
            $authController->login();
            break;

        case '/auth/logout':
            $authController->logout();
            break;

        case '/auth/verify':
            $authController->verifyToken();
            break;

        case '/auth/refresh':
            $authController->refreshToken();
            break;

        case '/auth/change-password':
            $authController->changePassword();
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint non trouvé'
            ]);
            break;
    }
}

/**
 * Gérer les requêtes PUT
 */
function handlePutRequest(string $uri): void
{
    // Pour les futures fonctionnalités de mise à jour
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Endpoint non trouvé'
    ]);
}

/**
 * Gérer les requêtes DELETE
 */
function handleDeleteRequest(string $uri): void
{
    $inscriptionController = new InscriptionController();

    // Route pour supprimer une inscription
    if (preg_match('#^/inscriptions/(\d+)$#', $uri, $matches)) {
        $inscriptionController->delete((int)$matches[1]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint non trouvé'
        ]);
    }
}

/**
 * Logger les requêtes pour le debug
 */
function logRequest(): void
{
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];

    error_log('API Request: ' . json_encode($logData));
}

// Logger la requête en mode debug
if (isset($_GET['debug'])) {
    logRequest();
}