<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Non autorisé']));
}

header('Content-Type: application/json');

$siteId = $_GET['site_id'] ?? null;
if (!$siteId) {
    http_response_code(400);
    exit(json_encode(['error' => 'ID du site manquant']));
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM objectives 
                WHERE site_id = ? 
                ORDER BY deadline ASC, created_at DESC
            ");
            $stmt->execute([$siteId]);
            $objectives = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($objectives);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'POST':
        if (!checkRole('editor')) {
            http_response_code(403);
            exit(json_encode(['error' => 'Permission refusée']));
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['title']) || !isset($data['description'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Données manquantes']));
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO objectives (title, description, deadline, site_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['deadline'] ?? null,
                $siteId
            ]);
            
            echo json_encode(['message' => 'Objectif ajouté avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'PUT':
        if (!checkRole('editor')) {
            http_response_code(403);
            exit(json_encode(['error' => 'Permission refusée']));
        }

        $objectiveId = $_GET['id'] ?? null;
        if (!$objectiveId) {
            http_response_code(400);
            exit(json_encode(['error' => 'ID de l\'objectif manquant']));
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['status'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Statut manquant']));
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE objectives 
                SET status = ? 
                WHERE id = ? AND site_id = ?
            ");
            $stmt->execute([$data['status'], $objectiveId, $siteId]);
            echo json_encode(['message' => 'Objectif mis à jour avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'DELETE':
        if (!checkRole('admin')) {
            http_response_code(403);
            exit(json_encode(['error' => 'Permission refusée']));
        }

        $objectiveId = $_GET['id'] ?? null;
        if (!$objectiveId) {
            http_response_code(400);
            exit(json_encode(['error' => 'ID de l\'objectif manquant']));
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM objectives WHERE id = ? AND site_id = ?");
            $stmt->execute([$objectiveId, $siteId]);
            echo json_encode(['message' => 'Objectif supprimé avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;
}