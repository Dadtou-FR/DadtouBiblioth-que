<?php
session_start();

// Fonctions d'authentification
function is_logged_in() {
    return isset($_SESSION['user_id']); // ou 'nom', selon ce que tu as défini à la connexion
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

require_once 'db.php'; // ou ton fichier de connexion à la BDD

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
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header */
header {
    background: #2c3e50;
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 1.5rem;
    font-weight: bold;
}

.nav-links {
    display: flex;
    list-style: none;
    gap: 2rem;
}

.nav-links a {
    color: white;
    text-decoration: none;
    transition: color 0.3s;
}

.nav-links a:hover {
    color: #3498db;
}

/* Main Content */
main {
    padding: 2rem 0;
}

/* Cards */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

/* Forms */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #555;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

/* Buttons */
.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #3498db;
    color: white;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-success:hover {
    background-color: #219a52;
}

.btn-danger {
    background-color: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background-color: #c0392b;
}

.btn-warning {
    background-color: #f39c12;
    color: white;
}

.btn-warning:hover {
    background-color: #d68910;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

/* Tables */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #2c3e50;
}

.table tr:hover {
    background-color: #f8f9fa;
}

/* Book Grid */
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.book-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1.5rem;
    transition: transform 0.3s;
}

.book-card:hover {
    transform: translateY(-5px);
}

.book-card h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.book-card p {
    color: #666;
    margin-bottom: 0.5rem;
}

.book-info {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
}

.stock-info {
    font-weight: bold;
}

.stock-available {
    color: #27ae60;
}

.stock-unavailable {
    color: #e74c3c;
}

/* Alerts */
.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

/* Profile Section */
.profile-section {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .nav-links {
        flex-direction: column;
        gap: 1rem;
    }
    
    .books-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-section {
        grid-template-columns: 1fr;
    }
    
    .book-info {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Footer */
footer {
    background: #2c3e50;
    color: white;
    text-align: center;
    padding: 1rem 0;
    margin-top: 2rem;
}

/* Status badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: bold;
}

.status-en-attente {
    background-color: #fff3cd;
    color: #856404;
}

.status-validee {
    background-color: #d4edda;
    color: #155724;
}

.status-annulee {
    background-color: #f8d7da;
    color: #721c24;
}

.status-terminee {
    background-color: #e2e3e5;
    color: #383d41;
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

        /* Hero Section */
        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem 0;
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
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2c3e50, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .hero-section p {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-hero-secondary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid rgba(102, 126, 234, 0.3);
        }

        .btn-hero-secondary:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-3px);
        }

        /* Statistiques modernes */
        .stats-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            transition: all 0.3s ease;
        }

        .stat-card:hover::before {
            transform: rotate(45deg) translate(50%, 50%);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .stat-card p {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .stat-card i {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            opacity: 0.3;
        }

        /* Livres section */
        .books-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .books-section h2 {
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .book-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .book-card h3 {
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .book-card p {
            color: #7f8c8d;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .book-card strong {
            color: #2c3e50;
        }

        .book-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e1e8ed;
        }

        .stock-info {
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
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
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-reserve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .view-all-books {
            text-align: center;
            margin-top: 2rem;
        }

        .btn-view-all {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-view-all:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
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
            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .stat-card h3 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 2rem 1rem;
            }

            .hero-section h1 {
                font-size: 1.5rem;
            }

            .stat-card {
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
            <h1>Bienvenue à la Bibliothèque</h1>
            <?php if (is_logged_in()): ?>
                <p>Bonjour <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong> ! Découvrez notre collection de livres et faites vos réservations.</p>
            <?php else: ?>
                <p>Découvrez notre collection de livres et créez un compte pour faire des réservations.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-hero btn-hero-primary">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </a>
                    <a href="login.php" class="btn-hero btn-hero-secondary">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistiques -->
        <div class="stats-section">
            <h2><i class="fas fa-chart-bar"></i> Statistiques</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $total_livres; ?></h3>
                    <p>Livres disponibles</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $total_utilisateurs; ?></h3>
                    <p>Utilisateurs inscrits</p>
                </div>
                <?php if (is_admin()): ?>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo $reservations_en_attente; ?></h3>
                    <p>Réservations en attente</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Derniers livres ajoutés -->
        <div class="books-section">
            <h2><i class="fas fa-star"></i> Derniers livres ajoutés</h2>
            <div class="books-grid">
                <?php foreach ($derniers_livres as $livre): ?>
                    <div class="book-card">
                        <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                        <p><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre']); ?></p>
                        <p><?php echo htmlspecialchars(substr($livre['description'], 0, 100)); ?>...</p>
                        <div class="book-info">
                            <span class="stock-info <?php echo $livre['stock_disponible'] > 0 ? 'stock-available' : 'stock-unavailable'; ?>">
                                <i class="fas fa-<?php echo $livre['stock_disponible'] > 0 ? 'check' : 'times'; ?>"></i>
                                <?php echo $livre['stock_disponible']; ?> / <?php echo $livre['stock_total']; ?> disponible(s)
                            </span>
                            <?php if (is_logged_in() && $livre['stock_disponible'] > 0): ?>
                                <a href="reserver.php?id=<?php echo $livre['id']; ?>" class="btn-reserve">
                                    <i class="fas fa-bookmark"></i> Réserver
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all-books">
                <a href="livres.php" class="btn-view-all">
                    <i class="fas fa-th-list"></i> Voir tous les livres
                </a>
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