<?php

// ======================================================
// FAST TRAJET V2
// LISTE DES NOTIFICATIONS
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// IDENTIFIER L'UTILISATEUR
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
// REQUÊTE SELON LE TYPE D'UTILISATEUR
// ======================================================

if ($estClient) {

    $id_client =
        intval($_SESSION["id_client"]);

    $sql = "
        SELECT
            id_notification,
            id_course,
            type_notification,
            titre,
            contenu,
            lu,
            date_creation,
            date_lecture

        FROM notification

        WHERE id_client = ?

        ORDER BY date_creation DESC
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération des notifications."
        );
    }

    $stmt->bind_param(
        "i",
        $id_client
    );
}


elseif ($estChauffeur) {

    $id_chauffeur =
        intval($_SESSION["id_chauffeur"]);

    $sql = "
        SELECT
            id_notification,
            id_course,
            type_notification,
            titre,
            contenu,
            lu,
            date_creation,
            date_lecture

        FROM notification

        WHERE id_chauffeur = ?

        ORDER BY date_creation DESC
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération des notifications."
        );
    }

    $stmt->bind_param(
        "i",
        $id_chauffeur
    );
}


else {

    $id_administrateur =
        intval($_SESSION["id_administrateur"]);

    $sql = "
        SELECT
            id_notification,
            id_course,
            type_notification,
            titre,
            contenu,
            lu,
            date_creation,
            date_lecture

        FROM notification

        WHERE id_administrateur = ?

        ORDER BY date_creation DESC
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération des notifications."
        );
    }

    $stmt->bind_param(
        "i",
        $id_administrateur
    );
}


// ======================================================
// EXÉCUTION
// ======================================================

$stmt->execute();

$resultat =
    $stmt->get_result();


$notifications = [];


while (
    $notification =
    $resultat->fetch_assoc()
) {

    $notification["id_notification"] =
        (int)$notification["id_notification"];

    if (
        $notification["id_course"] !== null
    ) {

        $notification["id_course"] =
            (int)$notification["id_course"];
    }

    $notification["lu"] =
        (int)$notification["lu"];

    $notifications[] =
        $notification;
}


$stmt->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "nombre_notifications" =>
        count($notifications),

    "notifications" =>
        $notifications

]);

?>