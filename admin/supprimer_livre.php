<?php
require('../db.php');
require('../auth.php');

// Vérifier si l'utilisateur est admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$message = '';
$livre = null;

// Récupérer l'ID du livre à supprimer
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

// Traitement de la suppression
if ($_POST && isset($_POST['confirmer']) && $livre) {
    $id = $livre['id'];
    
    // Vérifier s'il y a des réservations en cours
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE livre_id = ? AND statut IN ('en_attente', 'validee')");
    $stmt->execute([$id]);
    $reservations_actives = $stmt->fetchColumn();
    
    if ($reservations_actives > 0) {
        $message = "Impossible de supprimer ce livre car il y a des réservations en cours.";
    } else {
        try {
            // Commencer une transaction
            $pdo->beginTransaction();
            
            // Supprimer toutes les réservations liées (historique)
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE livre_id = ?");
            $stmt->execute([$id]);
            
            // Supprimer le livre
            $stmt = $pdo->prepare("DELETE FROM livres WHERE id = ?");
            $stmt->execute([$id]);
            
            // Valider la transaction
            $pdo->commit();
            
            // Rediriger vers la liste des livres
            header("Location: livres.php?message=Livre supprimé avec succès");
            exit();
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            $message = "Erreur lors de la suppression du livre : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un livre - Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Supprimer un livre</h1>
            <nav>
                <a href="dashboard.php">Tableau de bord</a>
                <a href="livres.php">Livres</a>
                <a href="logout.php">Déconnexion</a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="alert error">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($livre): ?>
                <div class="livre-details">
                    <h2>Êtes-vous sûr de vouloir supprimer ce livre ?</h2>
                    
                    <div class="livre-info">
                        <p><strong>Titre :</strong> <?= htmlspecialchars($livre['titre']) ?></p>
                        <p><strong>Auteur :</strong> <?= htmlspecialchars($livre['auteur']) ?></p>
                        <p><strong>ISBN :</strong> <?= htmlspecialchars($livre['isbn']) ?></p>
                        <p><strong>Genre :</strong> <?= htmlspecialchars($livre['genre']) ?></p>
                        <p><strong>Année :</strong> <?= $livre['annee_publication'] ?></p>
                        <p><strong>Exemplaires :</strong> <?= $livre['exemplaires_disponibles'] ?>/<?= $livre['exemplaires_total'] ?></p>
                    </div>

                    <?php
                    // Vérifier les réservations en cours
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as total,
                               SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                               SUM(CASE WHEN statut = 'validee' THEN 1 ELSE 0 END) as validee
                        FROM reservations 
                        WHERE livre_id = ?
                    ");
                    $stmt->execute([$livre['id']]);
                    $reservations = $stmt->fetch();
                    ?>

                    <div class="reservations-info">
                        <h3>Réservations liées :</h3>
                        <p>Total des réservations : <?= $reservations['total'] ?></p>
                        <p>En attente : <?= $reservations['en_attente'] ?></p>
                        <p>Validées : <?= $reservations['validee'] ?></p>
                    </div>

                    <?php if ($reservations['en_attente'] > 0 || $reservations['validee'] > 0): ?>
                        <div class="alert warning">
                            <strong>Attention !</strong> Ce livre a des réservations en cours. Vous ne pouvez pas le supprimer.
                        </div>
                        <div class="form-actions">
                            <a href="livres.php" class="btn btn-primary">Retour à la liste</a>
                        </div>
                    <?php else: ?>
                        <div class="alert warning">
                            <strong>Attention !</strong> Cette action est irréversible. Toutes les données liées à ce livre seront supprimées définitivement.
                        </div>

                        <form method="POST" class="form-suppression">
                            <div class="form-actions">
                                <button type="submit" name="confirmer" class="btn btn-danger" onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer ce livre ?');">
                                    Confirmer la suppression
                                </button>
                                <a href="livres.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>Livre non trouvé ou ID manquant.</p>
                <a href="livres.php" class="btn btn-primary">Retour à la liste</a>
            <?php endif; ?>
        </main>
    </div>

    <style>
        .livre-details {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .livre-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .livre-info p {
            margin: 10px 0;
        }
        
        .reservations-info {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ffeaa7;
        }
        
        .form-suppression {
            margin-top: 30px;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .alert.warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
    </style>
</body>
</html>