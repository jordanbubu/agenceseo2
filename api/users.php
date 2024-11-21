<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !checkRole('admin')) {
    http_response_code(401);
    exit(json_encode(['error' => 'Non autorisé']));
}

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY name ASC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Données manquantes']));
        }

        // Vérification de l'email unique
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(400);
                exit(json_encode(['error' => 'Cet email est déjà utilisé']));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            exit(json_encode(['error' => 'Erreur serveur']));
        }

        // Hashage du mot de passe
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $hashedPassword,
                $data['role']
            ]);
            
            echo json_encode(['message' => 'Utilisateur ajouté avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'PUT':
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            exit(json_encode(['error' => 'ID de l\'utilisateur manquant']));
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['role'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Rôle manquant']));
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$data['role'], $userId]);
            echo json_encode(['message' => 'Rôle mis à jour avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;

    case 'DELETE':
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            exit(json_encode(['error' => 'ID de l\'utilisateur manquant']));
        }

        // Empêcher la suppression de son propre compte
        if ($userId == $_SESSION['user_id']) {
            http_response_code(400);
            exit(json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']));
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        break;
}