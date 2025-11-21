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
    $passage = $db->querySingle("SELECT * FROM passage WHERE (couloir1=$from AND couloir2=$id) OR (couloir1=$id AND couloir2=$from)", true);
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
<h1>Couloir ??? </h1>


<?php 
$aléa_couloirs = random_int(1,10);
switch ($aléa_couloirs) 
{
    case 1:
        echo "<p>Une salle parfaitement carrée dont les murs sont recouverts de symboles luminescents. Au centre, un léger voile de brume violette flotte au ras du sol, diffusant une douce lumière surnaturelle qui semble pulser au rythme d’un battement invisible.</p>";
        break;
    case 2:
        echo "<p>La pièce, entièrement carrée, est composée de panneaux métalliques lisses parcourus de fines lignes lumineuses bleu cyan. Des hologrammes statiques flottent à quelques centimètres du sol, projetant des données incompréhensibles dans un silence mécanique.</p>";
        break;
    case 3:
        echo "<p>Les murs en pierre brute forment un carré parfait, décoré de tapisseries anciennes aux couleurs délavées. La lumière des torches danse sur les surfaces rugueuses, faisant ressortir chaque fissure et chaque trace du temps.</p>";
        break;
    case 4:
        echo "<p>La pièce carrée baigne dans une semi-obscurité ; les murs sont marqués de taches d’humidité sombres, et une odeur métallique se mêle à un léger courant d’air froid. Le sol semble un peu trop collant par endroits, sans qu’on comprenne pourquoi.</p>";
        break;
    case 5:
        echo "<p>Dans cette salle carrée aux tons beige clair, le sol est recouvert de tatamis parfaitement alignés. Une petite fontaine murale diffuse un filet d'eau paisible, tandis qu’un parfum délicat de bois de cèdre imprègne l’air.</p>";
        break;
    case 6:
        echo "<p>La pièce, rigoureusement carrée, est décorée d’engrenages en cuivre fixés aux murs et de tubes transparents où circulent de petites bulles de vapeur. Un mécanisme central émet un tic-tac régulier qui résonne doucement.</p>";
        break;
    case 7:
        echo "<p>Cette salle carrée est ornée de moulures dorées qui encadrent des fresques au plafond. Un tapis pourpre parfaitement centré couvre une partie du sol en marbre, reflétant la lumière chaleureuse d’un grand lustre en cristal.</p>";
        break;
    case 8:
        echo "<p>Les murs carrés sont couverts de fissures et de peinture écaillée. Le sol poussiéreux porte les marques d’anciens meubles, et quelques feuilles mortes ont été poussées dans un coin par un courant d’air errant.</p>";
        break;
    case 9:
        echo "<p>Dans cette pièce carrée, la nature a repris ses droits : des racines serpentent le long des murs et un tapis de mousse recouvre le sol. Des éclats de lumière verte filtrent à travers des feuillages suspendus comme un plafond vivant.</p>";
        break;
    case 10:
        echo "<p>Une salle carrée immaculée, aux murs blancs lisses et au sol parfaitement net. Des écrans d’analyse affichent des données en continu, tandis qu’un léger bourdonnement électronique remplit l’air stérile.</p>";
        break;
}    
?>

<!DOCTYPE html>
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
    } else if ($p['type'] === 'grille') {
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
