<?php

// ======================================================
// FAST TRAJET V2
// MES ALERTES
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// IDENTIFIER
// ======================================================

$estClient =
    isset($_SESSION["client_connecte"]) &&
    $_SESSION["client_connecte"] === true;

$estChauffeur =
    isset($_SESSION["chauffeur_connecte"]) &&
    $_SESSION["chauffeur_connecte"] === true;


if (!$estClient && !$estChauffeur) {

    reponseErreur(
        "Utilisateur non connecté.",
        401
    );
}


// ======================================================
// CLIENT
// ======================================================

if ($estClient) {

    $id_client =
        intval($_SESSION["id_client"]);

    $sql = "
        SELECT
            id_alerte,
            id_course,
            type_alerte,
            niveau,
            description,
            latitude,
            longitude,
            statut,
            date_creation,
            date_resolution

        FROM alerte

        WHERE id_client = ?

        ORDER BY date_creation DESC
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération des alertes."
        );
    }

    $stmt->bind_param(
        "i",
        $id_client
    );
}


// ======================================================
// CHAUFFEUR
// ======================================================

else {

    $id_chauffeur =
        intval($_SESSION["id_chauffeur"]);

    $sql = "
        SELECT
            id_alerte,
            id_course,
            type_alerte,
            niveau,
            description,
            latitude,
            longitude,
            statut,
            date_creation,
            date_resolution

        FROM alerte

        WHERE id_chauffeur = ?

        ORDER BY date_creation DESC
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération des alertes."
        );
    }

    $stmt->bind_param(
        "i",
        $id_chauffeur
    );
}


// ======================================================
// EXÉCUTER
// ======================================================

$stmt->execute();

$resultat =
    $stmt->get_result();

$alertes = [];

while (
    $alerte =
    $resultat->fetch_assoc()
) {

    $alerte["id_alerte"] =
        (int)$alerte["id_alerte"];

    $alerte["id_course"] =
        $alerte["id_course"] !== null
        ? (int)$alerte["id_course"]
        : null;

    $alertes[] =
        $alerte;
}

$stmt->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "nombre_alertes" =>
        count($alertes),

    "alertes" =>
        $alertes

]);

?>