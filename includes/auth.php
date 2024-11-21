<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkRole($requiredRole) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $roles = [
        'admin' => 3,
        'editor' => 2,
        'reader' => 1
    ];
    
    return $roles[$_SESSION['user_role']] >= $roles[$requiredRole];
}

function login($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}