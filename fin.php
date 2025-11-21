<?php
session_start();
$score = $_SESSION['score_final'] ?? 0;

date_default_timezone_set("Europe/Paris");
$date = date("m-d");
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

<?php
if ($date >= "12-22" && $date <= "12-25") {
    echo "<p>🎄 Joyeux Noël !</p>";
}

if ($date >= "12-28" && $date <= "12-31") {
    echo "<p>🎆 Bonne nouvelle année !</p>";
}
if ($date >= "12-25" && $date<= "12-29"){
    echo "<p>🛏️ Noubliez pas de vous reposez !</p>";
}
?>

<p>Votre score (déplacements) : <strong><?= htmlspecialchars($score) ?></strong></p>
<a href="index.php">Recommencer</a>

</body>
</html>

