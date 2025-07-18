<?php
require_once 'includes/auth.php';
require_login();

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
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">📚 Dadtou Bibliothèque</div>
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
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="profile-section">
            <!-- Informations du profil -->
            <div class="card">
                <h2>Mon Profil</h2>
                <p><strong>Nom:</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Rôle:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                
                <?php if (is_admin()): ?>
                    <div style="margin-top: 1rem;">
                        <a href="admin/ajouter_livre.php" class="btn btn-primary">Ajouter un livre</a>
                        <a href="admin/reservations.php" class="btn btn-secondary">Gérer les réservations</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistiques utilisateur -->
            <div class="card">
                <h2>Mes Statistiques</h2>
                <?php
                $stats = [
                    'total' => count($reservations),
                    'en_attente' => count(array_filter($reservations, fn($r) => $r['statut'] === 'en_attente')),
                    'validee' => count(array_filter($reservations, fn($r) => $r['statut'] === 'validee')),
                    'terminee' => count(array_filter($reservations, fn($r) => $r['statut'] === 'terminee')),
                    'annulee' => count(array_filter($reservations, fn($r) => $r['statut'] === 'annulee'))
                ];
                ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div style="text-align: center; padding: 1rem; background: #3498db; color: white; border-radius: 8px;">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total réservations</p>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f39c12; color: white; border-radius: 8px;">
                        <h3><?php echo $stats['en_attente']; ?></h3>
                        <p>En attente</p>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #27ae60; color: white; border-radius: 8px;">
                        <h3><?php echo $stats['validee']; ?></h3>
                        <p>Validées</p>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #95a5a6; color: white; border-radius: 8px;">
                        <h3><?php echo $stats['terminee']; ?></h3>
                        <p>Terminées</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mes réservations -->
        <div class="card">
            <h2>Mes Réservations</h2>
            
            <?php if (empty($reservations)): ?>
                <p>Vous n'avez aucune réservation.</p>
                <a href="livres.php" class="btn btn-primary">Parcourir les livres</a>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Auteur</th>
                            <th>Date de réservation</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['titre']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['auteur']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_reservation'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['statut']; ?>">
                                        <?php 
                                        echo match($reservation['statut']) {
                                            'en_attente' => 'En attente',
                                            'validee' => 'Validée',
                                            'annulee' => 'Annulée',
                                            'terminee' => 'Terminée'
                                        };
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reservation['statut'] === 'en_attente'): ?>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" name="annuler_reservation" class="btn btn-danger">
                                                Annuler
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="btn btn-secondary" style="cursor: not-allowed;">
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