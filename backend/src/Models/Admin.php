<?php

namespace App\Models;

use App\Database\Connection;
use PDO;
use PDOException;

class Admin
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Authentifier un administrateur
     */
    public function authenticate(string $username, string $password): ?array
    {
        try {
            $sql = "SELECT id, username, password_hash FROM admins WHERE username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);

            $admin = $stmt->fetch();

            if (!$admin) {
                return null;
            }

            // Vérification du mot de passe
            if (password_verify($password, $admin['password_hash'])) {
                // Ne pas retourner le hash du mot de passe
                unset($admin['password_hash']);
                return $admin;
            }

            return null;

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de l\'authentification admin: ' . $e->getMessage());
            throw new \Exception('Erreur lors de l\'authentification');
        }
    }

    /**
     * Récupérer un admin par ID
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT id, username, created_at FROM admins WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            return $stmt->fetch() ?: null;

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de la récupération admin: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération');
        }
    }

    /**
     * Créer un nouvel administrateur
     */
    public function create(string $username, string $password): array
    {
        try {
            // Vérifier que le nom d'utilisateur n'existe pas déjà
            $stmt = $this->db->prepare("SELECT id FROM admins WHERE username = :username");
            $stmt->execute([':username' => $username]);

            if ($stmt->fetch()) {
                throw new \Exception('Ce nom d\'utilisateur existe déjà');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':username' => $username,
                ':password_hash' => $hashedPassword
            ]);

            if (!$result) {
                throw new \Exception('Erreur lors de la création');
            }

            $adminId = $this->db->lastInsertId();
            return $this->findById($adminId);

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de la création admin: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la création de l\'administrateur');
        }
    }

    /**
     * Changer le mot de passe d'un admin
     */
    public function changePassword(int $adminId, string $newPassword): bool
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE admins SET password_hash = :password_hash WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':password_hash' => $hashedPassword,
                ':id' => $adminId
            ]);

            return $result && $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log('Erreur SQL lors du changement de mot de passe: ' . $e->getMessage());
            throw new \Exception('Erreur lors du changement de mot de passe');
        }
    }
}