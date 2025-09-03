<?php

namespace App\Models;

use App\Database\Connection;
use PDO;
use PDOException;

class Inscription
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Créer une nouvelle inscription
     */
    public function create(array $data): array
    {
        // Validation des données
        $this->validateInscriptionData($data);

        try {
            $sql = "INSERT INTO inscriptions (nom, prenom, email, classe, motivation) 
                    VALUES (:nom, :prenom, :email, :classe, :motivation)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':classe' => $data['classe'],
                ':motivation' => $data['motivation'] ?? ''
            ]);

            if (!$result) {
                throw new \Exception('Erreur lors de l\'insertion');
            }

            $inscriptionId = $this->db->lastInsertId();
            return $this->findById($inscriptionId);

        } catch (PDOException $e) {
            // Gestion de l'erreur d'email unique
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'email') !== false) {
                throw new \Exception('Cette adresse email est déjà utilisée');
            }
            error_log('Erreur SQL lors de la création d\'inscription: ' . $e->getMessage());
            throw new \Exception('Erreur lors de l\'enregistrement');
        }
    }

    /**
     * Récupérer toutes les inscriptions
     */
    public function findAll(array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM inscriptions";
            $params = [];
            $conditions = [];

            // Filtrage par classe
            if (!empty($filters['classe']) && in_array($filters['classe'], ['BUT1', 'BUT2', 'BUT3'])) {
                $conditions[] = "classe = :classe";
                $params[':classe'] = $filters['classe'];
            }

            // Filtrage par email (recherche partielle)
            if (!empty($filters['email'])) {
                $conditions[] = "email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $sql .= " ORDER BY date_inscription DESC";

            // Pagination
            $limit = min((int)($filters['limit'] ?? 50), 100);
            $offset = max((int)($filters['offset'] ?? 0), 0);
            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de la récupération des inscriptions: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération des données');
        }
    }

    /**
     * Récupérer une inscription par ID
     */
    public function findById(int $id): array
    {
        try {
            $sql = "SELECT * FROM inscriptions WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $inscription = $stmt->fetch();
            if (!$inscription) {
                throw new \Exception('Inscription non trouvée');
            }

            return $inscription;

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de la récupération de l\'inscription: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération');
        }
    }

    /**
     * Récupérer les statistiques
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Nombre total d'inscriptions
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM inscriptions");
            $stats['total'] = $stmt->fetch()['total'];

            // Répartition par classe
            $stmt = $this->db->query("
                SELECT classe, COUNT(*) as count 
                FROM inscriptions 
                GROUP BY classe 
                ORDER BY classe
            ");
            $stats['by_class'] = $stmt->fetchAll();

            // Inscriptions récentes (dernières 24h)
            $stmt = $this->db->query("
                SELECT COUNT(*) as recent 
                FROM inscriptions 
                WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stats['recent'] = $stmt->fetch()['recent'];

            return $stats;

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de la récupération des statistiques: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération des statistiques');
        }
    }

    /**
     * Valider les données d'inscription
     */
    private function validateInscriptionData(array $data): void
    {
        $errors = [];

        // Nom requis
        if (empty($data['nom']) || strlen(trim($data['nom'])) < 2) {
            $errors[] = 'Le nom est requis (minimum 2 caractères)';
        }

        // Prénom requis
        if (empty($data['prenom']) || strlen(trim($data['prenom'])) < 2) {
            $errors[] = 'Le prénom est requis (minimum 2 caractères)';
        }

        // Email requis et valide
        if (empty($data['email'])) {
            $errors[] = 'L\'email est requis';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide';
        }

        // Classe valide
        if (empty($data['classe']) || !in_array($data['classe'], ['BUT1', 'BUT2', 'BUT3'])) {
            $errors[] = 'La classe doit être BUT1, BUT2 ou BUT3';
        }

        // Longueur de la motivation
        if (!empty($data['motivation']) && strlen($data['motivation']) > 1000) {
            $errors[] = 'La motivation ne peut pas dépasser 1000 caractères';
        }

        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
    }

    /**
     * Supprimer une inscription (pour l'administration)
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM inscriptions WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);

            return $result && $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log('Erreur SQL lors de la suppression: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la suppression');
        }
    }
}