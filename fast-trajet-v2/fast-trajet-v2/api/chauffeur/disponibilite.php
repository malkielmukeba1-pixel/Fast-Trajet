<?php

// ======================================================
// FAST TRAJET V2
// DISPONIBILITÉ DU CHAUFFEUR
// GET  : lire la disponibilité
// POST : changer la disponibilité
// ======================================================


// ======================================================
// FICHIERS DE CONFIGURATION
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/response.php";


// ======================================================
// VÉRIFIER LA SESSION CHAUFFEUR
// ======================================================

if (
    !isset($_SESSION["chauffeur_connecte"]) ||
    $_SESSION["chauffeur_connecte"] !== true
) {

    reponseErreur(
        "Session chauffeur non valide.",
        401
    );
}


// ======================================================
// RÉCUPÉRER L'ID DU CHAUFFEUR
// ======================================================

$idChauffeur =
    $_SESSION["id_chauffeur"] ?? null;


if (!$idChauffeur) {

    reponseErreur(
        "Identifiant du chauffeur introuvable.",
        401
    );
}


$idChauffeur =
    (int) $idChauffeur;


// ======================================================
// MÉTHODE GET
// LIRE LA DISPONIBILITÉ
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $sql = "
        SELECT disponibilite
        FROM chauffeur
        WHERE id_chauffeur = ?
        LIMIT 1
    ";


    $stmt =
        $connexion->prepare($sql);


    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la préparation de la requête."
        );
    }


    $stmt->bind_param(
        "i",
        $idChauffeur
    );


    if (!$stmt->execute()) {

        $stmt->close();

        reponseErreur(
            "Impossible de récupérer la disponibilité."
        );
    }


    $resultat =
        $stmt->get_result();


    if ($resultat->num_rows === 0) {

        $stmt->close();

        reponseErreur(
            "Chauffeur introuvable.",
            404
        );
    }


    $chauffeur =
        $resultat->fetch_assoc();


    $stmt->close();


    reponseSucces([
        "message" =>
            "Disponibilité récupérée avec succès.",

        "disponibilite" =>
            (int) $chauffeur["disponibilite"]
    ]);
}


// ======================================================
// MÉTHODE POST
// CHANGER LA DISPONIBILITÉ
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ----------------------------------------------
    // RÉCUPÉRER LA DISPONIBILITÉ ACTUELLE
    // ----------------------------------------------

    $sqlLecture = "
        SELECT disponibilite
        FROM chauffeur
        WHERE id_chauffeur = ?
        LIMIT 1
    ";


    $stmt =
        $connexion->prepare($sqlLecture);


    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la préparation de la requête."
        );
    }


    $stmt->bind_param(
        "i",
        $idChauffeur
    );


    if (!$stmt->execute()) {

        $stmt->close();

        reponseErreur(
            "Impossible de récupérer la disponibilité."
        );
    }


    $resultat =
        $stmt->get_result();


    if ($resultat->num_rows === 0) {

        $stmt->close();

        reponseErreur(
            "Chauffeur introuvable.",
            404
        );
    }


    $chauffeur =
        $resultat->fetch_assoc();


    $stmt->close();


    // ----------------------------------------------
    // INVERSER LA DISPONIBILITÉ
    //
    // 0 devient 1
    // 1 devient 0
    // ----------------------------------------------

    $nouvelleDisponibilite =
        ((int) $chauffeur["disponibilite"] === 1)
            ? 0
            : 1;


    // ----------------------------------------------
    // METTRE À JOUR
    // ----------------------------------------------

    $sqlModification = "
        UPDATE chauffeur
        SET disponibilite = ?
        WHERE id_chauffeur = ?
    ";


    $stmt =
        $connexion->prepare(
            $sqlModification
        );


    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la préparation de la modification."
        );
    }


    $stmt->bind_param(
        "ii",
        $nouvelleDisponibilite,
        $idChauffeur
    );


    if (!$stmt->execute()) {

        $stmt->close();

        reponseErreur(
            "Impossible de modifier la disponibilité."
        );
    }


    $stmt->close();


    // ----------------------------------------------
    // SUCCÈS
    // ----------------------------------------------

    reponseSucces([
        "message" =>
            $nouvelleDisponibilite === 1
                ? "Vous êtes maintenant disponible."
                : "Vous êtes maintenant indisponible.",

        "disponibilite" =>
            $nouvelleDisponibilite
    ]);
}


// ======================================================
// AUTRE MÉTHODE NON AUTORISÉE
// ======================================================

reponseErreur(
    "Méthode non autorisée.",
    405
);

?>