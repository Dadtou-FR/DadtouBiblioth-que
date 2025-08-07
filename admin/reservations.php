<?php
// Activation de l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../db.php');
require('../auth.php');

// Test de connexion à la base de données
if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données");
}

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Traitement de l'action d'accepter ou annuler
if (isset($_GET['action'], $_GET['id'])) {
    $reservation_id = (int)$_GET['id'];
    if ($_GET['action'] === 'accepter') {
        // Accepter la réservation
        $stmt = $pdo->prepare("UPDATE reservations SET statut = 'validee', date_validation = NOW() WHERE id = ? AND statut = 'en_attente'");
        $stmt->execute([$reservation_id]);
    } elseif ($_GET['action'] === 'annuler') {
        // Annuler la réservation
        // On remet le livre en stock si la réservation était en attente
        $stmt = $pdo->prepare("SELECT livre_id FROM reservations WHERE id = ? AND statut = 'en_attente'");
        $stmt->execute([$reservation_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?");
            $stmt->execute([$reservation_id]);
            $stmt = $pdo->prepare("UPDATE livres SET stock_disponible = stock_disponible + 1 WHERE id = ?");
            $stmt->execute([$row['livre_id']]);
            $pdo->commit();
        }
    }
    // Rafraîchir la page pour éviter la double soumission
    header('Location: reservations.php');
    exit();
}

// Récupérer toutes les réservations avec infos utilisateur et livre
$stmt = $pdo->query("SELECT r.id, u.nom, u.email, l.titre, r.date_reservation, r.statut FROM reservations r JOIN utilisateurs u ON r.utilisateur_id = u.id JOIN livres l ON r.livre_id = l.id ORDER BY r.date_reservation DESC");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$total_reservations = count($reservations);
$en_attente = count(array_filter($reservations, function($r) { return $r['statut'] === 'en_attente'; }));
$validees = count(array_filter($reservations, function($r) { return $r['statut'] === 'validee'; }));
$annulees = count(array_filter($reservations, function($r) { return $r['statut'] === 'annulee'; }));
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des réservations - Administration</title>
    <link rel='stylesheet' href='../CSS/style.css'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header moderne */
        .admin-header {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .admin-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
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

        .btn-back {
            padding: 0.75rem 1.5rem;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            background: rgba(102, 126, 234, 0.2);
            color: #764ba2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
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

        /* Stats Section */
        .stats-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
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

        /* Table Section */
        .table-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e1e8ed;
        }

        .table-title {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reservations-count {
            font-size: 1.1rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        .reservations-count strong {
            color: #667eea;
            font-size: 1.2rem;
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
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #e1e8ed;
            color: #2c3e50;
            vertical-align: middle;
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
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-en_attente {
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
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-accept {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-cancel {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
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
            font-size: 0.8rem;
            font-weight: 500;
            cursor: not-allowed;
            opacity: 0.7;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* User info */
        .user-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .user-email {
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .book-title {
            font-weight: 600;
            color: #2c3e50;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reservation-date {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-header .container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .table {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .table-header {
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

            .stats-section,
            .table-section {
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 0.7rem;
            }

            .book-title {
                max-width: 150px;
            }
        }

        /* Animations pour les lignes */
        .table tbody tr:nth-child(1) { animation: slideIn 0.6s ease-out 0.1s both; }
        .table tbody tr:nth-child(2) { animation: slideIn 0.6s ease-out 0.2s both; }
        .table tbody tr:nth-child(3) { animation: slideIn 0.6s ease-out 0.3s both; }
        .table tbody tr:nth-child(4) { animation: slideIn 0.6s ease-out 0.4s both; }
        .table tbody tr:nth-child(5) { animation: slideIn 0.6s ease-out 0.5s both; }
        .table tbody tr:nth-child(6) { animation: slideIn 0.6s ease-out 0.6s both; }

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
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="admin-title">
                <i class="fas fa-cog"></i> Administration
            </div>
            <a href="../profil.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour au profil
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1><i class="fas fa-bookmark"></i> Gestion des réservations</h1>
            <p>Administrez et gérez toutes les demandes de réservation des utilisateurs</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-list"></i>
                    <h3><?= $total_reservations ?></h3>
                    <p>Total réservations</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?= $en_attente ?></h3>
                    <p>En attente</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check"></i>
                    <h3><?= $validees ?></h3>
                    <p>Validées</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-times"></i>
                    <h3><?= $annulees ?></h3>
                    <p>Annulées</p>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-table"></i> Liste des réservations
                </div>
                <div class="reservations-count">
                    <i class="fas fa-book-open"></i>
                    <strong><?= $total_reservations ?></strong> réservation(s) au total
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Utilisateur</th>
                        <th><i class="fas fa-book"></i> Livre</th>
                        <th><i class="fas fa-calendar"></i> Date de réservation</th>
                        <th><i class="fas fa-info-circle"></i> Statut</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-name"><?= htmlspecialchars($r['nom']) ?></div>
                                    <div class="user-email"><?= htmlspecialchars($r['email']) ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="book-title"><?= htmlspecialchars($r['titre']) ?></div>
                            </td>
                            <td>
                                <div class="reservation-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?= date('d/m/Y H:i', strtotime($r['date_reservation'])) ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $r['statut'] ?>">
                                    <i class="fas fa-<?php 
                                        switch($r['statut']) {
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
                                    <?= ucfirst($r['statut']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($r['statut'] === 'en_attente'): ?>
                                        <a href="?action=accepter&id=<?= $r['id'] ?>" 
                                           class="btn-accept" 
                                           onclick="return confirm('Accepter cette réservation ?')">
                                            <i class="fas fa-check"></i> Accepter
                                        </a>
                                        <a href="?action=annuler&id=<?= $r['id'] ?>" 
                                           class="btn-cancel" 
                                           onclick="return confirm('Annuler cette réservation ?')">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-disabled">
                                            <i class="fas fa-ban"></i> Aucune action
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
