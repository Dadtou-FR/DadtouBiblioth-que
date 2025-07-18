<?php
require_once 'includes/auth.php';
require_login();

$user = get_user_info();
$error = '';
$success = '';

// Vérifier si l'ID du livre est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('livres.php');
}

$livre_id = (int)$_GET['id'];

// Récupérer les informations du livre
$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$livre_id]);
$livre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$livre) {
    redirect('livres.php');
}

// Vérifier si l'utilisateur a déjà une réservation active pour ce livre
$stmt = $pdo->prepare("
    SELECT * FROM reservations 
    WHERE utilisateur_id = ? AND livre_id = ? AND statut IN ('en_attente', 'validee')
");
$stmt->execute([$user['id'], $livre_id]);
$reservation_existante = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($reservation_existante) {
        $error = 'Vous avez déjà une réservation active pour ce livre';
    } elseif ($livre['stock_disponible'] <= 0) {
        $error = 'Ce livre n\'est plus disponible';
    } else {
        // Effectuer la réservation
        $pdo->beginTransaction();
        try {
            // Créer la réservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations (utilisateur_id, livre_id, statut) 
                VALUES (?, ?, 'en_attente')
            ");
            $stmt->execute([$user['id'], $livre_id]);
            
            // Décrémenter le stock
            $stmt = $pdo->prepare("
                UPDATE livres 
                SET stock_disponible = stock_disponible - 1 
                WHERE id = ?
            ");
            $stmt->execute([$livre_id]);
            
            $pdo->commit();
            $success = 'Réservation effectuée avec succès ! Elle sera validée par un administrateur.';
            
            // Actualiser les informations du livre
            $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
            $stmt->execute([$livre_id]);
            $livre = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de la réservation. Veuillez réessayer.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un livre - Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">📚 Bibliothèque</div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="livres.php">Livres</a></li>
                <li><a href="profil.php">Mon Profil</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="admin/reservations.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h2>Réserver un livre</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Informations du livre -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                <p><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre']); ?></p>
                <?php if (!empty($livre['isbn'])): ?>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
                <?php endif; ?>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($livre['description']); ?></p>
                <p><strong>Disponibilité:</strong> 
                    <span class="stock-info <?php echo $livre['stock_disponible'] > 0 ? 'stock-available' : 'stock-unavailable'; ?>">
                        <?php echo $livre['stock_disponible']; ?> / <?php echo $livre['stock_total']; ?> exemplaire(s) disponible(s)
                    </span>
                </p>
            </div>
            
            <?php if ($reservation_existante): ?>
                <div class="alert alert-warning">
                    Vous avez déjà une réservation <?php echo $reservation_existante['statut']; ?> pour ce livre.
                </div>
                <a href="profil.php" class="btn btn-primary">Voir mes réservations</a>
            <?php elseif ($livre['stock_disponible'] <= 0): ?>
                <div class="alert alert-error">
                    Ce livre n'est plus disponible actuellement.
                </div>
                <a href="livres.php" class="btn btn-primary">Retour aux livres</a>
            <?php elseif ($success): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="profil.php" class="btn btn-primary">Voir mes réservations</a>
                    <a href="livres.php" class="btn btn-secondary">Continuer à parcourir</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div style="background: #e8f5e8; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                        <h4>Conditions de réservation :</h4>
                        <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                            <li>La réservation doit être validée par un administrateur</li>
                            <li>Vous serez notifié par email une fois la réservation validée</li>
                            <li>Le livre sera réservé à votre nom pendant 7 jours</li>
                            <li>Vous pouvez annuler votre réservation avant validation</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center;">
                        <button type="submit" class="btn btn-primary" style="margin-right: 1rem;">
                            Confirmer la réservation
                        </button>
                        <a href="livres.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Bibliothèque. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>