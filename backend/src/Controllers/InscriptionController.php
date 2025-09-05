<?php

namespace App\Controllers;

use App\Models\Inscription;
use App\Auth\JwtAuth;

class InscriptionController
{
    private Inscription $inscriptionModel;
    private JwtAuth $auth;

    public function __construct()
    {
        $this->inscriptionModel = new Inscription();
        $this->auth = new JwtAuth();
    }

    /**
     * Créer une nouvelle inscription (PUBLIC)
     */
    public function create(): void
    {
        try {
            // Récupérer les données JSON
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Données JSON invalides'
                ]);
                return;
            }

            // Nettoyer les données
            $data = [
                'nom' => trim($input['nom'] ?? ''),
                'prenom' => trim($input['prenom'] ?? ''),
                'email' => trim(strtolower($input['email'] ?? '')),
                'classe' => $input['classe'] ?? '',
                'motivation' => trim($input['motivation'] ?? ''),
                'discord' => trim($input['discord'] ?? ''),
                'insta' => trim($input['insta'] ?? '')
            ];

            // Créer l'inscription
            $inscription = $this->inscriptionModel->create($data);

            // Réponse de succès
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Inscription créée avec succès',
                'data' => $inscription
            ]);

        } catch (\Exception $e) {
            error_log('Erreur création inscription: ' . $e->getMessage());

            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer toutes les inscriptions (ADMIN ONLY)
     */
    public function getAll(): void
    {
        // Vérifier l'authentification admin
        $this->auth->requireAuth();

        try {
            // Récupérer les paramètres de filtrage
            $filters = [
                'classe' => $_GET['classe'] ?? '',
                'email' => $_GET['email'] ?? '',
                'limit' => min((int)($_GET['limit'] ?? 50), 100),
                'offset' => max((int)($_GET['offset'] ?? 0), 0)
            ];

            // Nettoyer les filtres vides
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== 0;
            });

            $inscriptions = $this->inscriptionModel->findAll($filters);

            echo json_encode([
                'success' => true,
                'message' => 'Inscriptions récupérées avec succès',
                'data' => $inscriptions,
                'count' => count($inscriptions)
            ]);

        } catch (\Exception $e) {
            error_log('Erreur récupération inscriptions: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions'
            ]);
        }
    }

    /**
     * Récupérer une inscription par ID (ADMIN ONLY)
     */
    public function getById(int $id): void
    {
        // Vérifier l'authentification admin
        $this->auth->requireAuth();

        try {
            $inscription = $this->inscriptionModel->findById($id);

            echo json_encode([
                'success' => true,
                'message' => 'Inscription trouvée',
                'data' => $inscription
            ]);

        } catch (\Exception $e) {
            error_log('Erreur récupération inscription: ' . $e->getMessage());

            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer les statistiques (ADMIN ONLY)
     */
    public function getStatistics(): void
    {
        // Vérifier l'authentification admin
        $this->auth->requireAuth();

        try {
            $stats = $this->inscriptionModel->getStatistics();

            echo json_encode([
                'success' => true,
                'message' => 'Statistiques récupérées',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            error_log('Erreur récupération statistiques: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ]);
        }
    }

    /**
     * Supprimer une inscription (ADMIN ONLY)
     */
    public function delete(int $id): void
    {
        // Vérifier l'authentification admin
        $this->auth->requireAuth();

        try {
            $result = $this->inscriptionModel->delete($id);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Inscription supprimée avec succès'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Inscription non trouvée'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Erreur suppression inscription: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ]);
        }
    }

    /**
     * Exporter les inscriptions en CSV (ADMIN ONLY)
     */
    public function exportCsv(): void
    {
        // Vérifier l'authentification admin
        $this->auth->requireAuth();

        try {
            $inscriptions = $this->inscriptionModel->findAll();

            // Headers pour le téléchargement CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="inscriptions_parrainage_' . date('Y-m-d') . '.csv"');

            // Créer le flux de sortie
            $output = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes CSV
            fputcsv($output, [
                'ID',
                'Nom',
                'Prénom',
                'Email',
                'Classe',
                'Motivation',
                'Date d\'inscription'
            ], ';');

            // Données
            foreach ($inscriptions as $inscription) {
                fputcsv($output, [
                    $inscription['id'],
                    $inscription['nom'],
                    $inscription['prenom'],
                    $inscription['email'],
                    $inscription['classe'],
                    $inscription['motivation'],
                    $inscription['date_inscription']
                ], ';');
            }

            fclose($output);

        } catch (\Exception $e) {
            error_log('Erreur export CSV: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'export'
            ]);
        }
    }
}