<?php

// ======================================================
// FAST TRAJET V2
// NOTIFICATIONS NON LUES
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// IDENTIFIER UTILISATEUR
// ======================================================

$estClient =
    isset($_SESSION["client_connecte"]) &&
    $_SESSION["client_connecte"] === true;

$estChauffeur =
    isset($_SESSION["chauffeur_connecte"]) &&
    $_SESSION["chauffeur_connecte"] === true;

$estAdmin =
    isset($_SESSION["administrateur_connecte"]) &&
    $_SESSION["administrateur_connecte"] === true;


if (
    !$estClient &&
    !$estChauffeur &&
    !$estAdmin
) {

    reponseErreur(
        "Utilisateur non connecté.",
        401
    );
}


// ======================================================
// REQUÊTE
// ======================================================

if ($estClient) {

    $id_client =
        intval($_SESSION["id_client"]);

    $sql = "
        SELECT COUNT(*) AS total
        FROM notification
        WHERE id_client = ?
        AND lu = 0
    ";

    $stmt =
        $connexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_client
    );
}

elseif ($estChauffeur) {

    $id_chauffeur =
        intval($_SESSION["id_chauffeur"]);

    $sql = "
        SELECT COUNT(*) AS total
        FROM notification
        WHERE id_chauffeur = ?
        AND lu = 0
    ";

    $stmt =
        $connexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_chauffeur
    );
}

else {

    $id_administrateur =
        intval($_SESSION["id_administrateur"]);

    $sql = "
        SELECT COUNT(*) AS total
        FROM notification
        WHERE id_administrateur = ?
        AND lu = 0
    ";

    $stmt =
        $connexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_administrateur
    );
}


// ======================================================
// EXÉCUTER
// ======================================================

if (!$stmt->execute()) {

    $stmt->close();

    reponseErreur(
        "Impossible de récupérer le compteur."
    );
}

$resultat =
    $stmt->get_result();

$ligne =
    $resultat->fetch_assoc();

$stmt->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "non_lues" =>
        (int)$ligne["total"]

]);

?>