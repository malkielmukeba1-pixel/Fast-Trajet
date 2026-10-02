<?php

// ======================================================
// FAST TRAJET V2
// HISTORIQUE DES COURSES
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// CLIENT
// ======================================================

if (
    isset($_SESSION["client_connecte"]) &&
    $_SESSION["client_connecte"] === true
) {

    $id_client =
        intval(
            $_SESSION["id_client"]
        );


    $sql = "
        SELECT
            id_course,
            id_client,
            id_chauffeur,
            lieu_depart,
            lieu_destination,
            distance,
            duree_estimee,
            prix_initial,
            prix_actuel,
            prix_accepte,
            statut_prix,
            statut_course,
            date_creation,
            date_fin

        FROM course

        WHERE id_client = ?

        ORDER BY date_creation DESC
    ";


    $stmt =
        $connexion->prepare($sql);


    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération de l'historique."
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

elseif (
    isset($_SESSION["chauffeur_connecte"]) &&
    $_SESSION["chauffeur_connecte"] === true
) {

    $id_chauffeur =
        intval(
            $_SESSION["id_chauffeur"]
        );


    $sql = "
        SELECT
            id_course,
            id_client,
            id_chauffeur,
            lieu_depart,
            lieu_destination,
            distance,
            duree_estimee,
            prix_initial,
            prix_actuel,
            prix_accepte,
            statut_prix,
            statut_course,
            date_creation,
            date_fin

        FROM course

        WHERE id_chauffeur = ?

        ORDER BY date_creation DESC
    ";


    $stmt =
        $connexion->prepare($sql);


    if (!$stmt) {

        reponseErreur(
            "Erreur lors de la récupération de l'historique."
        );
    }


    $stmt->bind_param(
        "i",
        $id_chauffeur
    );
}

else {

    reponseErreur(
        "Utilisateur non connecté.",
        401
    );
}


// ======================================================
// EXÉCUTION
// ======================================================

$stmt->execute();

$resultat =
    $stmt->get_result();


$courses = [];

while (
    $course =
    $resultat->fetch_assoc()
) {

    $courses[] =
        $course;
}


$stmt->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([
    "nombre_courses" =>
        count($courses),

    "courses" =>
        $courses
]);

?>