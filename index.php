<?php
require_once 'includes/auth.php';

// Récupérer les derniers livres ajoutés
$stmt = $pdo->prepare("SELECT * FROM livres ORDER BY date_ajout DESC LIMIT 6");
$stmt->execute();
$derniers_livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM livres");
$stmt->execute();
$total_livres = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'utilisateur'");
$stmt->execute();
$total_utilisateurs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE statut = 'en_attente'");
$stmt->execute();
$reservations_en_attente = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque - Accueil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">📚 Dadtou Bibliothèque</div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="livres.php">Livres</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="profil.php">Mon Profil</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="admin/reservations.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="card">
            <h1>Bienvenue à la Bibliothèque</h1>
            <?php if (is_logged_in()): ?>
                <p>Bonjour <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong> ! Découvrez notre collection de livres et faites vos réservations.</p>
            <?php else: ?>
                <p>Découvrez notre collection de livres et créez un compte pour faire des réservations.</p>
                <a href="register.php" class="btn btn-primary">S'inscrire</a>
                <a href="login.php" class="btn btn-secondary">Se connecter</a>
            <?php endif; ?>
        </div>

        <!-- Statistiques -->
        <div class="card">
            <h2>Statistiques</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <div style="text-align: center; padding: 1rem; background: #3498db; color: white; border-radius: 8px;">
                    <h3><?php echo $total_livres; ?></h3>
                    <p>Livres disponibles</p>
                </div>
                <div style="text-align: center; padding: 1rem; background: #27ae60; color: white; border-radius: 8px;">
                    <h3><?php echo $total_utilisateurs; ?></h3>
                    <p>Utilisateurs inscrits</p>
                </div>
                <?php if (is_admin()): ?>
                <div style="text-align: center; padding: 1rem; background: #f39c12; color: white; border-radius: 8px;">
                    <h3><?php echo $reservations_en_attente; ?></h3>
                    <p>Réservations en attente</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Derniers livres ajoutés -->
        <div class="card">
            <h2>Derniers livres ajoutés</h2>
            <div class="books-grid">
                <?php foreach ($derniers_livres as $livre): ?>
                    <div class="book-card">
                        <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                        <p><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre']); ?></p>
                        <p><?php echo htmlspecialchars(substr($livre['description'], 0, 100)); ?>...</p>
                        <div class="book-info">
                            <span class="stock-info <?php echo $livre['stock_disponible'] > 0 ? 'stock-available' : 'stock-unavailable'; ?>">
                                <?php echo $livre['stock_disponible']; ?> / <?php echo $livre['stock_total']; ?> disponible(s)
                            </span>
                            <?php if (is_logged_in() && $livre['stock_disponible'] > 0): ?>
                                <a href="reserver.php?id=<?php echo $livre['id']; ?>" class="btn btn-primary">Réserver</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="livres.php" class="btn btn-primary">Voir tous les livres</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Bibliothèque. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>