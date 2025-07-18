<?php
require_once 'includes/auth.php';

// Recherche et filtrage
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$genre = isset($_GET['genre']) ? sanitize_input($_GET['genre']) : '';

// Requête pour récupérer les livres
$sql = "SELECT * FROM livres WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (titre LIKE ? OR auteur LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($genre)) {
    $sql .= " AND genre = ?";
    $params[] = $genre;
}

$sql .= " ORDER BY titre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les genres pour le filtre
$stmt = $pdo->prepare("SELECT DISTINCT genre FROM livres WHERE genre IS NOT NULL ORDER BY genre");
$stmt->execute();
$genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue des livres - Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">📚 Bibliothèque</div>
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
            <h1>Catalogue des livres</h1>
            
            <!-- Formulaire de recherche et filtrage -->
            <form method="GET" style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <input type="text" name="search" placeholder="Rechercher un livre, auteur..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select name="genre">
                        <option value="">Tous les genres</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo htmlspecialchars($g); ?>" 
                                    <?php echo $genre === $g ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <a href="livres.php" class="btn btn-secondary">Réinitialiser</a>
            </form>
            
            <?php if (empty($livres)): ?>
                <p>Aucun livre trouvé.</p>
            <?php else: ?>
                <p><?php echo count($livres); ?> livre(s) trouvé(s)</p>
                
                <div class="books-grid">
                    <?php foreach ($livres as $livre): ?>
                        <div class="book-card">
                            <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
                            <p><strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre']); ?></p>
                            <?php if (!empty($livre['isbn'])): ?>
                                <p><strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($livre['description']); ?></p>
                            
                            <div class="book-info">
                                <span class="stock-info <?php echo $livre['stock_disponible'] > 0 ? 'stock-available' : 'stock-unavailable'; ?>">
                                    <?php echo $livre['stock_disponible']; ?> / <?php echo $livre['stock_total']; ?> disponible(s)
                                </span>
                                
                                <?php if (is_logged_in()): ?>
                                    <?php if ($livre['stock_disponible'] > 0): ?>
                                        <a href="reserver.php?id=<?php echo $livre['id']; ?>" class="btn btn-primary">Réserver</a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary" style="cursor: not-allowed;">Indisponible</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-warning">Connectez-vous pour réserver</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
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