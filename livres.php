<?php
require_once 'auth.php';

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header moderne */
        header {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.3)); }
            to { filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.6)); }
        }

        .nav-links a {
            position: relative;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-links a:hover::after {
            width: 80%;
        }

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        /* Hero Section */
        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2c3e50, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .hero-section p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* Search Section */
        .search-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 1rem;
            align-items: end;
        }

        .search-group {
            position: relative;
        }

        .search-group input,
        .search-group select {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-group input:focus,
        .search-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-group i {
            position: absolute;
            left: 1rem;
            top: 1rem;
            color: #95a5a6;
            transition: color 0.3s ease;
        }

        .search-group input:focus + i,
        .search-group select:focus + i {
            color: #667eea;
        }

        .btn-search {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-reset {
            padding: 1rem 2rem;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid rgba(102, 126, 234, 0.3);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-reset:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        /* Results Section */
        .results-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e1e8ed;
        }

        .results-count {
            font-size: 1.1rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        .results-count strong {
            color: #667eea;
            font-size: 1.2rem;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .book-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
            overflow: hidden;
        }

        .book-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .book-card h3 {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .book-info-grid {
            display: grid;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .book-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .book-info-item strong {
            color: #2c3e50;
            min-width: 60px;
        }

        .book-description {
            color: #7f8c8d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .book-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e1e8ed;
        }

        .stock-info {
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stock-available {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .stock-unavailable {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .btn-reserve {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-reserve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-disabled {
            padding: 0.75rem 1.5rem;
            background: #95a5a6;
            color: white;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-login-required {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-login-required:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        /* Footer moderne */
        footer {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .results-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 1.5rem;
            }

            .hero-section h1 {
                font-size: 1.5rem;
            }

            .search-section,
            .results-section {
                padding: 1.5rem;
            }

            .book-card {
                padding: 1rem;
            }
        }

        /* Animations pour les cartes */
        .book-card:nth-child(1) { animation: slideIn 0.6s ease-out 0.1s both; }
        .book-card:nth-child(2) { animation: slideIn 0.6s ease-out 0.2s both; }
        .book-card:nth-child(3) { animation: slideIn 0.6s ease-out 0.3s both; }
        .book-card:nth-child(4) { animation: slideIn 0.6s ease-out 0.4s both; }
        .book-card:nth-child(5) { animation: slideIn 0.6s ease-out 0.5s both; }
        .book-card:nth-child(6) { animation: slideIn 0.6s ease-out 0.6s both; }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">📚 Dadtou Bibliothèque</div>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="livres.php"><i class="fas fa-book"></i> Livres</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="profil.php"><i class="fas fa-user"></i> Mon Profil</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="admin/reservations.php"><i class="fas fa-cog"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                    <li><a href="register.php"><i class="fas fa-user-plus"></i> Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1><i class="fas fa-books"></i> Catalogue des livres</h1>
            <p>Découvrez notre collection complète de livres et trouvez votre prochaine lecture</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Rechercher un livre, auteur..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <i class="fas fa-search"></i>
                </div>
                <div class="search-group">
                    <select name="genre">
                        <option value="">Tous les genres</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo htmlspecialchars($g); ?>" 
                                    <?php echo $genre === $g ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-filter"></i>
                </div>
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Rechercher
                </button>
                <a href="livres.php" class="btn-reset">
                    <i class="fas fa-undo"></i> Réinitialiser
                </a>
            </form>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-book-open"></i>
                    <strong><?php echo count($livres); ?></strong> livre(s) trouvé(s)
                </div>
            </div>
            
            <?php if (empty($livres)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Aucun livre trouvé</h3>
                    <p>Essayez de modifier vos critères de recherche ou consultez notre catalogue complet.</p>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($livres as $livre): ?>
                        <div class="book-card">
                            <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                            
                            <div class="book-info-grid">
                                <div class="book-info-item">
                                    <i class="fas fa-user"></i>
                                    <strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?>
                                </div>
                                <div class="book-info-item">
                                    <i class="fas fa-tag"></i>
                                    <strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre']); ?>
                                </div>
                                <?php if (!empty($livre['isbn'])): ?>
                                <div class="book-info-item">
                                    <i class="fas fa-barcode"></i>
                                    <strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="book-description">
                                <i class="fas fa-quote-left"></i>
                                <?php echo htmlspecialchars($livre['description']); ?>
                            </div>
                            
                            <div class="book-actions">
                                <span class="stock-info <?php echo $livre['stock_disponible'] > 0 ? 'stock-available' : 'stock-unavailable'; ?>">
                                    <i class="fas fa-<?php echo $livre['stock_disponible'] > 0 ? 'check' : 'times'; ?>"></i>
                                    <?php echo $livre['stock_disponible']; ?> / <?php echo $livre['stock_total']; ?> disponible(s)
                                </span>
                                
                                <?php if (is_logged_in()): ?>
                                    <?php if ($livre['stock_disponible'] > 0): ?>
                                        <a href="reserver.php?id=<?php echo $livre['id']; ?>" class="btn-reserve">
                                            <i class="fas fa-bookmark"></i> Réserver
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-disabled">
                                            <i class="fas fa-times"></i> Indisponible
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn-login-required">
                                        <i class="fas fa-sign-in-alt"></i> Connectez-vous
                                    </a>
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