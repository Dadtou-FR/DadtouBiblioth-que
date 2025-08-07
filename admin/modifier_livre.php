<?php
require('../db.php');
require('../auth.php');

// Vérifier si l'utilisateur est admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$message = '';
$livre = null;

// Récupérer l'ID du livre à modifier
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Récupérer les informations du livre
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
    $stmt->execute([$id]);
    $livre = $stmt->fetch();
    
    if (!$livre) {
        $message = "Livre non trouvé.";
    }
}

// Traitement du formulaire de modification
if ($_POST && $livre) {
    $titre = trim($_POST['titre']);
    $auteur = trim($_POST['auteur']);
    $isbn = trim($_POST['isbn']);
    $genre = trim($_POST['genre']);
    $annee_publication = (int)$_POST['annee_publication'];
    $description = trim($_POST['description']);
    $exemplaires_total = (int)$_POST['exemplaires_total'];
    
    // Validation
    if (empty($titre) || empty($auteur) || empty($isbn)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérifier si l'ISBN existe déjà (sauf pour le livre actuel)
        $stmt = $pdo->prepare("SELECT id FROM livres WHERE isbn = ? AND id != ?");
        $stmt->execute([$isbn, $id]);
        if ($stmt->fetch()) {
            $message = "Un livre avec cet ISBN existe déjà.";
        } else {
            // Calculer les exemplaires disponibles
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE livre_id = ? AND statut = 'validee'");
            $stmt->execute([$id]);
            $exemplaires_reserves = $stmt->fetchColumn();
            
            $exemplaires_disponibles = max(0, $exemplaires_total - $exemplaires_reserves);
            
            // Mettre à jour le livre
            $stmt = $pdo->prepare("UPDATE livres SET titre = ?, auteur = ?, isbn = ?, genre = ?, annee_publication = ?, description = ?, exemplaires_total = ?, exemplaires_disponibles = ? WHERE id = ?");
            
            if ($stmt->execute([$titre, $auteur, $isbn, $genre, $annee_publication, $description, $exemplaires_total, $exemplaires_disponibles, $id])) {
                $message = "Livre modifié avec succès.";
                // Recharger les données du livre
                $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
                $stmt->execute([$id]);
                $livre = $stmt->fetch();
            } else {
                $message = "Erreur lors de la modification du livre.";
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
    <title>Modifier un livre - Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Modifier un livre</h1>
            <nav>
                <a href="dashboard.php">Tableau de bord</a>
                <a href="livres.php">Livres</a>
                <a href="logout.php">Déconnexion</a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($livre): ?>
                <form method="POST" class="form-livre">
                    <div class="form-group">
                        <label for="titre">Titre *</label>
                        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($livre['titre']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="auteur">Auteur *</label>
                        <input type="text" id="auteur" name="auteur" value="<?= htmlspecialchars($livre['auteur']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="isbn">ISBN *</label>
                        <input type="text" id="isbn" name="isbn" value="<?= htmlspecialchars($livre['isbn']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre">
                            <option value="">Sélectionner un genre</option>
                            <option value="Roman" <?= $livre['genre'] == 'Roman' ? 'selected' : '' ?>>Roman</option>
                            <option value="Science-Fiction" <?= $livre['genre'] == 'Science-Fiction' ? 'selected' : '' ?>>Science-Fiction</option>
                            <option value="Fantasy" <?= $livre['genre'] == 'Fantasy' ? 'selected' : '' ?>>Fantasy</option>
                            <option value="Thriller" <?= $livre['genre'] == 'Thriller' ? 'selected' : '' ?>>Thriller</option>
                            <option value="Histoire" <?= $livre['genre'] == 'Histoire' ? 'selected' : '' ?>>Histoire</option>
                            <option value="Biographie" <?= $livre['genre'] == 'Biographie' ? 'selected' : '' ?>>Biographie</option>
                            <option value="Essai" <?= $livre['genre'] == 'Essai' ? 'selected' : '' ?>>Essai</option>
                            <option value="Autre" <?= $livre['genre'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="annee_publication">Année de publication</label>
                        <input type="number" id="annee_publication" name="annee_publication" value="<?= $livre['annee_publication'] ?>" min="1000" max="<?= date('Y') ?>">
                    </div>

                    <div class="form-group">
                        <label for="exemplaires_total">Nombre d'exemplaires total</label>
                        <input type="number" id="exemplaires_total" name="exemplaires_total" value="<?= $livre['exemplaires_total'] ?>" min="0" required>
                        <small>Disponibles actuellement : <?= $livre['exemplaires_disponibles'] ?></small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($livre['description']) ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Modifier le livre</button>
                        <a href="livres.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            <?php else: ?>
                <p>Livre non trouvé ou ID manquant.</p>
                <a href="livres.php" class="btn btn-primary">Retour à la liste</a>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>