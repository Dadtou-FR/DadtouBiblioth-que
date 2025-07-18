<?php
require '../includes/db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();}

$res = $conn->query("SELECT r.id, u.nom, l.titre, r.date_reservation, r.statut
                    FROM reservations r
                    JOIN users u ON r.id_user = u.id
                    JOIN livres l ON r.id_livre = l.id");

echo "<h2>Réservations</h2><table border='1'>";
echo "<tr><th>Utilisateur</th><th>Livre</th><th>Date</th><th>Statut</th><th>Actions</th></tr>";
while ($r = $res->fetch()) {
    echo "<tr>
        <td>{$r['nom']}</td>
        <td>{$r['titre']}</td>
        <td>{$r['date_reservation']}</td>
        <td>{$r['statut']}</td>
        <td>
            <a href='valider_reservation.php?id={$r['id']}'>Valider</a> |
            <a href='annuler_reservation.php?id={$r['id']}'>Annuler</a>
        </td>
    </tr>";
}
echo "</table>";
?>
