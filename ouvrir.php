<?php
session_start();
$db = new SQLite3('labyrinthe.db');

$idActuel = intval($_GET['id']);
$vers = intval($_GET['vers']);
$from = isset($_GET['from']) ? intval($_GET['from']) : $idActuel;

$passage = $db->querySingle("SELECT * FROM passage WHERE 
    ((couloir1=$idActuel AND couloir2=$vers) OR (couloir1=$vers AND couloir2=$idActuel))", true);

if (!$passage) die("Aucun passage entre ces couloirs.");

// Initialisation des variables de session si nécessaire
if (!isset($_SESSION['cles'])) $_SESSION['cles'] = 0;
if (!isset($_SESSION['grille_ouverte'])) $_SESSION['grille_ouverte'] = [];

if ($passage['type'] === 'grille') {
    if ($_SESSION['cles'] <= 0) {
        die("Vous n'avez pas de clé pour ouvrir cette grille !");
    }

    // Retirer une clé
    $_SESSION['cles']--;

    // Marquer la grille comme ouverte
    $_SESSION['grille_ouverte'][] = $passage['couloir1'];
}

// Redirection
header("Location: jeu.php?id=$vers&from=$idActuel");
exit;
