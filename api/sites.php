<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Non autorisé']));
}

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->prepare("
                SELECT s.*, 
                    (SELECT COUNT(*) FROM objectives o WHERE o.site_id = s.id) as total_objectives,
                    (SELECT COUNT(*) FROM objectives o WHERE o.site_id = s.id AND o.status = 'completed') as completed_objectives,
                    (SELECT MAX(created_at) FROM notes n WHERE n.site_id = s.id) as last_note_date
                FROM sites s 
                WHERE s.agency_id = ?
                ORDER BY s.url ASC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($sites);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'POST':
        if (!checkRole('admin')) {
            http_response_code(403);
            exit(json_encode(['error' => 'Permission refusée']));
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['url'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'URL manquante']));
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO sites (url, name, agency_id) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['url'],
                $data['url'],
                $_SESSION['user_id']
            ]);
            
            $siteId = $pdo->lastInsertId();
            echo json_encode(['id' => $siteId, 'message' => 'Site ajouté avec succès']);
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

        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            exit(json_encode(['error' => 'ID manquant']));
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM sites WHERE id = ? AND agency_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            echo json_encode(['message' => 'Site supprimé avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;
}