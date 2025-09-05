<?php

require_once __DIR__ . '/vendor/autoload.php';

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
    
    // 3. Test direct du hash
    echo "3. Test direct du hash:\n";
    $testPassword = 'admin123';
    $storedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    echo "Password: $testPassword\n";
    echo "Hash: $storedHash\n";
    echo "Vérification: " . (password_verify($testPassword, $storedHash) ? 'VALIDE' : 'INVALIDE') . "\n\n";
    
    // 4. Test avec le vrai hash de la base
    if (!empty($admins)) {
        $realHash = $pdo->query("SELECT password_hash FROM admins WHERE username = 'admin'")->fetchColumn();
        echo "4. Test avec le hash réel de la base:\n";
        echo "Hash réel: " . substr($realHash, 0, 30) . "...\n";
        echo "Vérification: " . (password_verify($testPassword, $realHash) ? 'VALIDE' : 'INVALIDE') . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
