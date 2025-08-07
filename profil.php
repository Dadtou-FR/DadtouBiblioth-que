<?php
require_once 'auth.php';
// Fonction de nettoyage des entrées utilisateur
function sanitize_input($data) {
    $data = trim($data); // Supprime les espaces en début et fin
    $data = stripslashes($data); // Supprime les antislashs
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Protège contre les XSS
    return $data;
}
require_login();

// Définir la fonction get_user_info si elle n'existe pas déjà
if (!function_exists('get_user_info')) {
    function get_user_info() {
        global $pdo;
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$user = get_user_info();

// Récupérer les réservations de l'utilisateur
$stmt = $pdo->prepare("
    SELECT r.*, l.titre, l.auteur, l.isbn 
    FROM reservations r
    JOIN livres l ON r.livre_id = l.id
    WHERE r.utilisateur_id = ?
    ORDER BY r.date_reservation DESC
");
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de l'annulation de réservation
if (isset($_POST['annuler_reservation'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    
    // Vérifier que la réservation appartient à l'utilisateur et qu'elle peut être annulée
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND utilisateur_id = ? AND statut = 'en_attente'");
    $stmt->execute([$reservation_id, $user['id']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reservation) {
        $pdo->beginTransaction();
        try {
            // Annuler la réservation
            $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?");
            $stmt->execute([$reservation_id]);
            
            // Remettre le livre en stock
            $stmt = $pdo->prepare("UPDATE livres SET stock_disponible = stock_disponible + 1 WHERE id = ?");
            $stmt->execute([$reservation['livre_id']]);
            
            $pdo->commit();
            $success = "Réservation annulée avec succès";
            
            // Recharger les réservations
            $stmt = $pdo->prepare("
                SELECT r.*, l.titre, l.auteur, l.isbn 
                FROM reservations r
                JOIN livres l ON r.livre_id = l.id
                WHERE r.utilisateur_id = ?
                ORDER BY r.date_reservation DESC
            ");
            $stmt->execute([$user['id']]);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de l'annulation de la réservation";
        }
    } else {
        $error = "Réservation non trouvée ou non modifiable";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Bibliothèque</title>
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

        /* Alertes modernes */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
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

        /* Profile Section */
        .profile-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .profile-card h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-info {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .profile-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .profile-item i {
            color: #667eea;
            font-size: 1.1rem;
            width: 20px;
        }

        .profile-item strong {
            color: #2c3e50;
            min-width: 80px;
        }

        .profile-item span {
            color: #7f8c8d;
        }

        .admin-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-admin {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
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
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .stat-card p {
            font-size: 0.9rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .stat-card i {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            font-size: 1.5rem;
            opacity: 0.3;
        }

        /* Reservations Section */
        .reservations-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .reservations-section h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .no-reservations {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }

        .no-reservations i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-reservations h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .btn-browse {
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

        .btn-browse:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        /* Table moderne */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e1e8ed;
            color: #2c3e50;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Status badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-en-attente {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }

        .status-validee {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .status-annulee {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .status-terminee {
            background: rgba(149, 165, 166, 0.1);
            color: #95a5a6;
        }

        /* Boutons d'action */
        .btn-cancel {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-disabled {
            padding: 0.5rem 1rem;
            background: #95a5a6;
            color: white;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: not-allowed;
            opacity: 0.7;
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
            .profile-section {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .table {
                font-size: 0.9rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 1.5rem;
            }

            .hero-section h1 {
                font-size: 1.5rem;
            }

            .profile-card,
            .reservations-section {
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-actions {
                flex-direction: column;
            }

            .table {
                font-size: 0.8rem;
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
                <li><a href="profil.php"><i class="fas fa-user"></i> Mon Profil</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="admin/reservations.php"><i class="fas fa-cog"></i> Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Hero Section -->
        <div class="hero-section">
            <h1><i class="fas fa-user-circle"></i> Mon Profil</h1>
            <p>Gérez vos informations personnelles et vos réservations</p>
        </div>
        
        <div class="profile-section">
            <!-- Informations du profil -->
            <div class="profile-card">
                <h2><i class="fas fa-user"></i> Informations personnelles</h2>
                <div class="profile-info">
                    <div class="profile-item">
                        <i class="fas fa-user"></i>
                        <strong>Nom:</strong>
                        <span><?php echo htmlspecialchars($user['nom']); ?></span>
                    </div>
                    <div class="profile-item">
                        <i class="fas fa-envelope"></i>
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="profile-item">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Rôle:</strong>
                        <span><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                </div>
                
                <?php if (is_admin()): ?>
                    <div class="admin-actions">
                        <a href="admin/ajouter_livre.php" class="btn-admin">
                            <i class="fas fa-plus"></i> Ajouter un livre
                        </a>
                        <a href="admin/reservations.php" class="btn-admin">
                            <i class="fas fa-cog"></i> Gérer les réservations
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistiques utilisateur -->
            <div class="profile-card">
                <h2><i class="fas fa-chart-bar"></i> Mes Statistiques</h2>
                <?php
                $stats = [
                    'total' => count($reservations),
                    'en_attente' => count(array_filter($reservations, function($r) { return $r['statut'] === 'en_attente'; })),
                    'validee' => count(array_filter($reservations, function($r) { return $r['statut'] === 'validee'; })),
                    'terminee' => count(array_filter($reservations, function($r) { return $r['statut'] === 'terminee'; })),
                    'annulee' => count(array_filter($reservations, function($r) { return $r['statut'] === 'annulee'; }))
                ];
                ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-book"></i>
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3><?php echo $stats['en_attente']; ?></h3>
                        <p>En attente</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check"></i>
                        <h3><?php echo $stats['validee']; ?></h3>
                        <p>Validées</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-double"></i>
                        <h3><?php echo $stats['terminee']; ?></h3>
                        <p>Terminées</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mes réservations -->
        <div class="reservations-section">
            <h2><i class="fas fa-bookmark"></i> Mes Réservations</h2>
            
            <?php if (empty($reservations)): ?>
                <div class="no-reservations">
                    <i class="fas fa-book-open"></i>
                    <h3>Aucune réservation</h3>
                    <p>Vous n'avez pas encore fait de réservations.</p>
                    <a href="livres.php" class="btn-browse">
                        <i class="fas fa-search"></i> Parcourir les livres
                    </a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-book"></i> Livre</th>
                            <th><i class="fas fa-user"></i> Auteur</th>
                            <th><i class="fas fa-calendar"></i> Date de réservation</th>
                            <th><i class="fas fa-info-circle"></i> Statut</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($reservation['titre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($reservation['auteur']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_reservation'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['statut']; ?>">
                                        <i class="fas fa-<?php 
                                            switch($reservation['statut']) {
                                                case 'en_attente':
                                                    echo 'clock';
                                                    break;
                                                case 'validee':
                                                    echo 'check';
                                                    break;
                                                case 'annulee':
                                                    echo 'times';
                                                    break;
                                                case 'terminee':
                                                    echo 'check-double';
                                                    break;
                                                default:
                                                    echo 'info';
                                            }
                                        ?>"></i>
                                        <?php 
                                        switch($reservation['statut']) {
                                            case 'en_attente':
                                                echo 'En attente';
                                                break;
                                            case 'validee':
                                                echo 'Validée';
                                                break;
                                            case 'annulee':
                                                echo 'Annulée';
                                                break;
                                            case 'terminee':
                                                echo 'Terminée';
                                                break;
                                            default:
                                                echo ucfirst($reservation['statut']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reservation['statut'] === 'en_attente'): ?>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" name="annuler_reservation" class="btn-cancel">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="btn-disabled">
                                            <i class="fas fa-<?php 
                                                switch($reservation['statut']) {
                                                    case 'validee':
                                                        echo 'check';
                                                        break;
                                                    case 'annulee':
                                                        echo 'times';
                                                        break;
                                                    case 'terminee':
                                                        echo 'check-double';
                                                        break;
                                                    default:
                                                        echo 'info';
                                                }
                                            ?>"></i>
                                            <?php echo ucfirst($reservation['statut']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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