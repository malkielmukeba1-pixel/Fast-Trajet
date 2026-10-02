<?php

// ======================================================
// FAST TRAJET V2
// MARQUER LES NOTIFICATIONS COMME LUES
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// POST UNIQUEMENT
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    reponseErreur(
        "Méthode non autorisée.",
        405
    );
}


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
// MODE
//
// tout = 1
// marque toutes les notifications de l'utilisateur.
//
// Sinon :
// id_notification est obligatoire.
// ======================================================

$tout =
    isset($_POST["tout"]) &&
    $_POST["tout"] == "1";


$id_notification =
    (int) (
        $_POST["id_notification"] ?? 0
    );


// ======================================================
// CLIENT
// ======================================================

if ($estClient) {

    $id_utilisateur =
        (int) $_SESSION["id_client"];


    if ($tout) {

        $sql = "
            UPDATE notification

            SET
                lu = 1,
                date_lecture = NOW()

            WHERE
                id_client = ?
                AND lu = 0
        ";


        $stmt =
            $connexion->prepare($sql);


        $stmt->bind_param(
            "i",
            $id_utilisateur
        );

    } else {

        if ($id_notification <= 0) {

            reponseErreur(
                "Identifiant de notification invalide.",
                400
            );
        }


        $sql = "
            UPDATE notification

            SET
                lu = 1,
                date_lecture = NOW()

            WHERE
                id_notification = ?
                AND id_client = ?
                AND lu = 0
        ";


        $stmt =
            $connexion->prepare($sql);


        $stmt->bind_param(
            "ii",
            $id_notification,
            $id_utilisateur
        );
    }
}


// ======================================================
// CHAUFFEUR
// ======================================================

elseif ($estChauffeur) {

    $id_utilisateur =
        (int) $_SESSION["id_chauffeur"];


    if ($tout) {

        $sql = "
            UPDATE notification

            SET
                lu = 1,
                date_lecture = NOW()

            WHERE
                id_chauffeur = ?
                AND lu = 0
        ";


        $stmt =
            $connexion->prepare($sql);


        $stmt->bind_param(
            "i",
            $id_utilisateur
        );

    } else {

        if ($id_notification <= 0) {

            reponseErreur(
                "Identifiant de notification invalide.",
                400
            );
        }


        $sql = "
            UPDATE notification

            SET
                lu = 1,
                date_lecture = NOW()

            WHERE
                id_notification = ?
                AND id_chauffeur = ?
                AND lu = 0
        ";


        $stmt =
            $connexion->prepare($sql);


        $stmt->bind_param(
            "ii",
            $id_notification,
            $id_utilisateur
        );
    }
}


// ======================================================
// ADMINISTRATEUR
// ======================================================

else {

    $id_utilisateur =
        (int) $_SESSION["id_administrateur"];


    if ($tout) {

        $sql = "
            UPDATE notification

            SET
                lu = 1,
                date_lecture = NOW()

            WHERE
                id_administrateur = ?
                AND lu = 0
        ";


        $stmt =
            $connexion->prepare($sql);


        $stmt->bind_param(
            "i",
            $id_utilisateur
        );

    } else {

        if ($id_notification <= 0) {

            reponseErreur(
                "Identifiant de notification invalide.",
                400
            );
        }


        $sql = "
            UPDATE notification

            SET
                lu = 1,
                date_lecture = NOW()

            WHERE
                id_notification = ?
                AND id_administrateur = ?
                AND lu = 0
        ";


        $stmt =
            $connexion->prepare($sql);


        $stmt->bind_param(
            "ii",
            $id_notification,
            $id_utilisateur
        );
    }
}


// ======================================================
// VÉRIFIER LA REQUÊTE
// ======================================================

if (!$stmt) {

    reponseErreur(
        "Impossible de préparer la requête.",
        500
    );
}


// ======================================================
// EXÉCUTER
// ======================================================

if (!$stmt->execute()) {

    $stmt->close();

    reponseErreur(
        "Impossible de mettre à jour les notifications.",
        500
    );
}


$nombreModifie =
    $stmt->affected_rows;


$stmt->close();


// ======================================================
// SUCCÈS
// ======================================================

reponseSucces([

    "message" =>
        $nombreModifie > 0
            ? "Notifications marquées comme lues."
            : "Aucune nouvelle notification à marquer.",

    "nombre_modifie" =>
        $nombreModifie

]);

?>