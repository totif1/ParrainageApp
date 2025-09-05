<?php

// debug_auth.php

require_once __DIR__ . '/vendor/autoload.php';
use App\Models\Admin;
use App\Controllers\AuthController;
use App\Auth\JwtAuth;

try {
    echo "=== DEBUG AUTHENTIFICATION ===\n\n";

    // 1. Vérifier la connexion à la base
    echo "1. Test connexion base de données:\n";
    $config = require __DIR__ . '/config/config.php';

    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}",
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );
    echo "✅ Connexion réussie\n\n";

    // 2. Vérifier la table admins
    echo "2. Contenu de la table admins:\n";
    $stmt = $pdo->query("SELECT id, username, LEFT(password_hash, 20) as hash_preview FROM admins");
    $admins = $stmt->fetchAll();

    foreach ($admins as $admin) {
        echo "- ID: {$admin['id']}, Username: {$admin['username']}, Hash: {$admin['hash_preview']}...\n";
    }
    echo "\n";

    // 3. Test du modèle Admin
    echo "3. Test du modèle Admin:\n";


    $adminModel = new Admin();

    $testAuth = $adminModel->authenticate('admin', 'admin123');
    if ($testAuth) {
        echo "✅ Authentification réussie: " . json_encode($testAuth) . "\n";
    } else {
        echo "❌ Authentification échouée\n";
    }
    echo "\n";

    // 4. Test JWT
    echo "4. Test JWT:\n";


    $jwtAuth = new JwtAuth();

    if ($testAuth) {
        $token = $jwtAuth->generateToken([
            'admin_id' => $testAuth['id'],
            'username' => $testAuth['username']
        ]);
        echo "✅ Token généré: " . substr($token, 0, 50) . "...\n";

        $decoded = $jwtAuth->validateToken($token);
        if ($decoded) {
            echo "✅ Token validé: " . json_encode($decoded) . "\n";
        } else {
            echo "❌ Token invalide\n";
        }
    }
    echo "\n";

    // 5. Test du contrôleur
    echo "5. Test du contrôleur AuthController:\n";

    // Simuler une requête POST
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $testData = json_encode(['username' => 'admin', 'password' => 'admin123']);

    // Mock de file_get_contents('php://input')
    file_put_contents('php://memory', $testData);


    $authController = new AuthController();

    echo "Contrôleur instancié avec succès\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}