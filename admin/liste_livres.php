<?php
require '../includes/db.php';
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Accès refusé");

$stmt = $conn->query("SELECT * FROM livres");
echo "<h2>Liste des livres</h2><table border='1'>";
echo "<tr><th>Titre</th><th>Auteur</th><th>Année</th><th>Catégorie</th><th>Exemplaires</th><th>Actions</th></tr>";
while ($row = $stmt->fetch()) {
    echo "<tr>
        <td>{$row['titre']}</td>
        <td>{$row['auteur']}</td>
        <td>{$row['annee']}</td>
        <td>{$row['categorie']}</td>
        <td>{$row['nb_exemplaires']}</td>
        <td>
            <a href='modifier_livre.php?id={$row['id']}'>Modifier</a> |
            <a href='supprimer_livre.php?id={$row['id']}'>Supprimer</a>
        </td>
    </tr>";
}
echo "</table>";
?>
