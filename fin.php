<?php
session_start();
$score = $_SESSION['score_final'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fin du labyrinthe</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Félicitations ! Vous avez trouvé la sortie !</h1>
date_default_timezone_set("Europe/Paris")
$date = date("m-d")
if $date > "12-22" and $date < "12-25" echo(<p>joyeux Noël</p>)
if $date > "12-29" and $date < "12-31" echo(<p>bonne nouvelle année</p>)
<p>Votre score (déplacements) : <strong><?= htmlspecialchars($score) ?></strong></p>
<a href="index.php">Recommencer</a>
</body>
</html>
