<?php
session_start();
$db = new SQLite3('labyrinthe.db');

// Récupération du couloir de départ
$depart = $db->querySingle("SELECT id FROM couloir WHERE type='depart'");
if (!$depart) {
    die("Erreur : Aucun couloir de type 'depart' trouvé.");
}

// Réinitialisation de la session au démarrage d'une partie
$_SESSION = []; // réinitialise toute la session
$_SESSION['ancien_id'] = $depart; // pour éviter d'incrémenter deplacements au départ
$_SESSION['deplacements'] = 0;
$_SESSION['cles'] = 0;
$_SESSION['orientation'] = 'N';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Labyrinthe Web</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Bienvenue dans le Labyrinthe</h1>
<p>Votre but est de sortir du labyrinthe. Cliquez sur le bouton correspondant à la direction dans laquelle vous souhaitez aller.</p>
<p>Bonne Chance !</p>
<p>Cliquez pour commencer :</p>
<a href="jeu.php?id=<?= $depart ?>">Commencer la partie</a>
</body>
</html>


<?php
