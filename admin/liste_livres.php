<?php
require('../db.php');
require('../auth.php');

// Vérifier si l'utilisateur est admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$stmt = $pdo->query("SELECT * FROM livres ORDER BY titre");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des livres - Administration</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <div class="container">
        <h2>Liste des livres</h2>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <tr>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Genre</th>
                <th>Stock total</th>
                <th>Stock disponible</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['titre']) ?></td>
                    <td><?= htmlspecialchars($row['auteur']) ?></td>
                    <td><?= htmlspecialchars($row['genre']) ?></td>
                    <td><?= $row['stock_total'] ?></td>
                    <td><?= $row['stock_disponible'] ?></td>
                    <td>
                        <a href="modifier_livre.php?id=<?= $row['id'] ?>">Modifier</a> |
                        <a href="supprimer_livre.php?id=<?= $row['id'] ?>" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <p><a href="../profil.php">Retour au profil</a></p>
    </div>
</body>
</html>
