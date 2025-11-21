<?php
session_start();
$db = new SQLite3('labyrinthe.db');

$idActuel = intval($_GET['id']);
$vers = intval($_GET['vers']);
$from = isset($_GET['from']) ? intval($_GET['from']) : $idActuel;

$passage = $db->querySingle("SELECT * FROM passage WHERE 
    ((couloir1=$idActuel AND couloir2=$vers) OR (couloir1=$vers AND couloir2=$idActuel))", true);

if (!$passage) die("Aucun passage entre ces couloirs.");

// Si c'est une grille et qu'elle n'est pas déjà libre
if ($passage['type'] === 'grille') {
    if (!isset($_SESSION['cles']) || $_SESSION['cles'] <= 0) {
        die("Vous n'avez pas de clé pour ouvrir cette grille !");
    }
    $_SESSION['cles']--;
    $db->exec("UPDATE passage SET type='libre' WHERE 
        (couloir1=$idActuel AND couloir2=$vers) OR (couloir1=$vers AND couloir2=$idActuel)");
}

// Redirection vers le couloir ouvert en transmettant from pour orientation
header("Location: jeu.php?id=$vers&from=$idActuel");
exit;
