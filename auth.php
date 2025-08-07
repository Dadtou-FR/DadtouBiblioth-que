<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Vérifie si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Vérifie si l'utilisateur est un administrateur
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirection simple
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fonction pour vérifier l'authentification
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

// Fonction pour vérifier les droits admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect('index.php');
    }
}
// auth.php
function get_user_info() {
    global $pdo; // Make sure $pdo is your database connection
    
    // Check if user is logged in (you should have this from your auth system)
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Fonction de connexion
function login_user($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    return false;
}

// Fonction d'inscription
function register_user($nom, $email, $password) {
    global $pdo;
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false;
    }
    
    // Créer le compte
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
    return $stmt->execute([$nom, $email, $hashed_password]);
}

// Fonction de déconnexion
function logout_user() {
    session_destroy();
    redirect('login.php');
}
