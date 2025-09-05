<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Auth\JwtAuth;

class AuthController
{
    private Admin $adminModel;
    private JwtAuth $auth;

    public function __construct()
    {
        $this->adminModel = new Admin();
        $this->auth = new JwtAuth();
    }

    /**
     * Connexion administrateur
     */
    public function login(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ]);
                return;
            }

            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';

            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Nom d\'utilisateur et mot de passe requis'
                ]);
                return;
            }

            $admin = $this->adminModel->authenticate($username, $password);

            if (!$admin) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ]);
                return;
            }

            $token = $this->auth->generateToken([
                'admin_id' => $admin['id'],
                'username' => $admin['username']
            ]);

            // SEULEMENT LE JSON - rien d'autre
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username']
                ]
            ]);

        } catch (\Exception $e) {
            error_log('Erreur connexion admin: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la connexion'
            ]);
        }
    }

    /**
     * Vérifier le token (pour valider la session côté client)
     */
    public function verifyToken(): void
    {
        try {
            $userData = $this->auth->requireAuth();

            echo json_encode([
                'success' => true,
                'message' => 'Token valide',
                'data' => $userData
            ]);

        } catch (\Exception $e) {
            // L'erreur est déjà gérée par requireAuth()
            // Cette méthode ne sera exécutée que si le token est valide
        }
    }

    /**
     * Déconnexion (côté serveur, invalide le token)
     * Note: Avec JWT, la déconnexion est principalement côté client
     */
    public function logout(): void
    {
        // Vérifier que l'utilisateur est connecté
        $userData = $this->auth->requireAuth();

        // Pour un vrai système, on pourrait ajouter le token à une blacklist
        // Ici, on confirme juste la déconnexion
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Rafraîchir le token
     */
    public function refreshToken(): void
    {
        try {
            $userData = $this->auth->requireAuth();

            // Générer un nouveau token avec les mêmes données
            $newToken = $this->auth->generateToken($userData);

            echo json_encode([
                'success' => true,
                'message' => 'Token rafraîchi',
                'token' => $newToken
            ]);

        } catch (\Exception $e) {
            error_log('Erreur rafraîchissement token: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du token'
            ]);
        }
    }

    /**
     * Changer le mot de passe (ADMIN ONLY)
     */
    public function changePassword(): void
    {
        try {
            $userData = $this->auth->requireAuth();

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ]);
                return;
            }

            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';

            // Validation
            if (empty($currentPassword) || empty($newPassword)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mot de passe actuel et nouveau mot de passe requis'
                ]);
                return;
            }

            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Le nouveau mot de passe doit contenir au moins 6 caractères'
                ]);
                return;
            }

            // Vérifier le mot de passe actuel
            $admin = $this->adminModel->authenticate($userData['username'], $currentPassword);

            if (!$admin) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mot de passe actuel incorrect'
                ]);
                return;
            }

            // Changer le mot de passe
            $result = $this->adminModel->changePassword($userData['admin_id'], $newPassword);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Mot de passe modifié avec succès'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la modification du mot de passe'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Erreur changement mot de passe: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe'
            ]);
        }
    }
}