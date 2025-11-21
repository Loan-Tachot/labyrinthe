<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$db = new SQLite3('labyrinthe.db');

// Initialisation des variables de session
if (!isset($_SESSION['ancien_id'])) $_SESSION['ancien_id'] = -1;
if (!isset($_SESSION['cles'])) $_SESSION['cles'] = 0;
if (!isset($_SESSION['deplacements'])) $_SESSION['deplacements'] = 0;
if (!isset($_SESSION['orientation'])) $_SESSION['orientation'] = 'N'; // Orientation initiale

// ID du couloir actuel et ID d'où il vient
$id = isset($_GET['id']) ? intval($_GET['id']) : $db->querySingle("SELECT id FROM couloir WHERE type='depart'");
$from = isset($_GET['from']) ? intval($_GET['from']) : null;

// Fonctions pour orientation relative
$axes = ['N','E','S','O']; // ordre horaire

function directionRelative($orientationActuelle, $directionAbsolue) {
    global $axes;
    $index = array_search($orientationActuelle, $axes);
    $diff = array_search($directionAbsolue, $axes) - $index;
    $diff = ($diff + 4) % 4;
    if ($diff == 0) return 'Avancer';
    if ($diff == 1) return 'Droite';
    if ($diff == 2) return 'Reculer';
    if ($diff == 3) return 'Gauche';
}

function nouvelleOrientation($orientationActuelle, $directionAbsolue) {
    global $axes;
    $index = array_search($orientationActuelle, $axes);
    $diff = array_search($directionAbsolue, $axes) - $index;
    $diff = ($diff + 4) % 4;
    return $axes[($index + $diff) % 4];
}

// Mettre à jour l'orientation si on vient d'un autre couloir
if ($from) {
    $passage = $db->querySingle("SELECT * FROM passage WHERE 
        (couloir1=$from AND couloir2=$id) OR (couloir1=$id AND couloir2=$from)", true);
    if ($passage) {
        if ($passage['couloir1'] == $from) {
            $posAbsolue = $passage['position2'];
        } else {
            $posAbsolue = $passage['position1'];
        }
        $_SESSION['orientation'] = nouvelleOrientation($_SESSION['orientation'], $posAbsolue);
    }
}

// Récupération du couloir
$couloir = $db->querySingle("SELECT * FROM couloir WHERE id=$id", true);
if (!$couloir) die("Erreur : Couloir introuvable.");

// Fin de partie
if ($couloir['type'] === 'sortie') {
    $_SESSION['score_final'] = $_SESSION['deplacements'];
    header("Location: fin.php");
    exit;
}

// Déplacements
if ($_SESSION['ancien_id'] != $id) $_SESSION['deplacements']++;
$_SESSION['ancien_id'] = $id;

// Gestion des clés
$message = "";
if ($couloir['type'] === 'cle') {
    $_SESSION['cles']++;
    $db->exec("UPDATE couloir SET type='vide' WHERE id=$id");
    $message = "Vous avez trouvé une clé !";
}

// Récupération des passages
$passages = $db->query("SELECT * FROM passage WHERE couloir1=$id OR couloir2=$id");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Couloir <?= $id ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Couloir <?= $id ?> (<?= $couloir['type'] ?>)</h1>

<div class="infos">
    <p>🔑 Clés : <strong><?= $_SESSION['cles'] ?></strong></p>
    <p>🚶 Déplacements : <strong><?= $_SESSION['deplacements'] ?></strong></p>
    <p>🧭 Orientation : <strong><?= $_SESSION['orientation'] ?></strong></p>
</div>

<?php if ($message): ?>
<p class="message"><?= $message ?></p>
<?php endif; ?>

<h2>Passages disponibles :</h2>
<ul>
<?php
while ($p = $passages->fetchArray(SQLITE3_ASSOC)) {
    if ($p['couloir1'] == $id) {
        $prochain = $p['couloir2'];
        $posAbsolue = $p['position2'];
    } else {
        $prochain = $p['couloir1'];
        $posAbsolue = $p['position1'];
    }

    $dirRel = directionRelative($_SESSION['orientation'], $posAbsolue);

    if ($p['type'] === 'libre' || $p['type'] === 'secret') {
        $lien = "jeu.php?id=$prochain&from=$id";
        echo "<li><a href='$lien'>$dirRel</a></li>";
    } elseif ($p['type'] === 'grille') {
        if ($_SESSION['cles'] > 0) {
            $lien = "ouvrir.php?id=$id&vers=$prochain&from=$id";
            echo "<li><a href='$lien'>$dirRel (1 clé)</a></li>";
        } else {
            echo "<li>$dirRel ❌ (clé requise)</li>";
        }
    }
}
?>
</ul>
</body>
</html>
