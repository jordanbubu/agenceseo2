<?php
require_once 'config/database.php';

// Vérifier si un administrateur existe déjà
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$adminExists = $stmt->fetchColumn() > 0;

if ($adminExists) {
    die("Un administrateur existe déjà. Par sécurité, ce script ne peut pas être exécuté à nouveau.");
}

// Informations de l'administrateur par défaut
$adminUser = [
    'name' => 'Administrateur',
    'email' => 'admin@example.com',
    'password' => 'admin123', // À changer immédiatement après la première connexion
    'role' => 'admin'
];

try {
    // Créer l'utilisateur administrateur
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $adminUser['name'],
        $adminUser['email'],
        password_hash($adminUser['password'], PASSWORD_DEFAULT),
        $adminUser['role']
    ]);
    
    echo "L'administrateur a été créé avec succès !<br>";
    echo "Email: " . htmlspecialchars($adminUser['email']) . "<br>";
    echo "Mot de passe: " . htmlspecialchars($adminUser['password']) . "<br>";
    echo "<strong>IMPORTANT : Veuillez changer ce mot de passe dès votre première connexion !</strong><br>";
    echo "<a href='login.php'>Se connecter</a>";
    
} catch(PDOException $e) {
    die("Erreur lors de la création de l'administrateur : " . $e->getMessage());
}
?>