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
                SELECT n.*, u.name as author_name 
                FROM notes n 
                JOIN users u ON n.user_id = u.id 
                WHERE n.site_id = ? 
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$siteId]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($notes);
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
        
        if (!isset($data['content'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Contenu manquant']));
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO notes (title, content, user_id, site_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['title'] ?? null,
                $data['content'],
                $_SESSION['user_id'],
                $siteId
            ]);
            
            echo json_encode(['message' => 'Note ajoutée avec succès']);
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

        $noteId = $_GET['id'] ?? null;
        if (!$noteId) {
            http_response_code(400);
            exit(json_encode(['error' => 'ID de la note manquant']));
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND site_id = ?");
            $stmt->execute([$noteId, $siteId]);
            echo json_encode(['message' => 'Note supprimée avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;
}