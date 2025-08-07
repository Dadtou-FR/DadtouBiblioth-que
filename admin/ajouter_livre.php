<?php
require('../db.php');
require('../auth.php');
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = sanitize_input($_POST['titre']);
    $auteur = sanitize_input($_POST['auteur']);
    $isbn = sanitize_input($_POST['isbn']);
    $genre = sanitize_input($_POST['genre']);
    $description = sanitize_input($_POST['description']);
    $stock_total = (int)$_POST['stock_total'];
    
    if (empty($titre) || empty($auteur) || empty($genre) || $stock_total < 1) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } else {
        // Vérifier l'unicité de l'ISBN s'il est fourni
        if (!empty($isbn)) {
            $stmt = $pdo->prepare("SELECT id FROM livres WHERE isbn = ?");
            $stmt->execute([$isbn]);
            if ($stmt->fetch()) {
                $error = 'Un livre avec cet ISBN existe déjà';
            }
        }
        
        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO livres (titre, auteur, isbn, genre, description, stock_total, stock_disponible) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$titre, $auteur, $isbn ?: null, $genre, $description, $stock_total, $stock_total]);
                
                $success = 'Livre ajouté avec succès !';
                
                // Réinitialiser le formulaire
                $_POST = [];
                
            } catch (Exception $e) {
                $error = 'Erreur lors de l\'ajout du livre : ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un livre - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">📚 Bibliothèque - Admin</div>
            <ul class="nav-links">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="../livres.php">Livres</a></li>
                <li><a href="../profil.php">Mon Profil</a></li>
                <li><a href="reservations.php">Réservations</a></li>
                <li><a href="ajouter_livre.php">Ajouter Livre</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h2>Ajouter un nouveau livre</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group
                    <label for="titre">Titre:</label>
                    <input type="text" id="titre" name="titre" required 
                           value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="auteur">Auteur:</label>
                    <input type="text" id="auteur" name="auteur" required 
                           value="<?php echo isset($_POST['auteur']) ? htmlspecialchars($_POST['auteur']) : ''; ?>">
                </div>
                <div class="form-group
                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" 
                           value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>">
                </div>
                <div class="form-group
                    <label for="genre">Genre:</label>
                    <input type="text" id="genre" name="genre" required 
                           value="<?php echo isset($_POST['genre']) ? htmlspecialchars($_POST['genre']) : ''; ?>">
                </div>
                <div class="form-group
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                <div class="form-group
                    <label for="stock_total">Stock total:</label>
                    <input type="number" id="stock_total" name="stock_total" required min="1" 
                           value="<?php echo isset($_POST['stock_total']) ? htmlspecialchars($_POST['stock_total']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Ajouter le livre</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                <a href="reservations.php">Voir les réservations</a>
            </p>
        </div>
    </main> 
    