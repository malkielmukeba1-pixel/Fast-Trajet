<?php

// ======================================================
// FAST TRAJET V2
// RÉCUPÉRER LES POSITIONS D'UNE COURSE
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";

// ======================================================
// UTILISATEUR
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
// COURSE
// ======================================================

$id_course = intval(
    $_GET["id_course"]
    ?? $_POST["id_course"]
    ?? 0
);

if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide."
    );
}

// ======================================================
// RÉCUPÉRER LA COURSE
// ======================================================

$sqlCourse = "
    SELECT
        id_course,
        id_client,
        id_chauffeur,
        latitude_destination,
        longitude_destination,
        latitude_client_actuelle,
        longitude_client_actuelle,
        latitude_chauffeur_actuelle,
        longitude_chauffeur_actuelle,
        statut_course

    FROM course

    WHERE id_course = ?

    LIMIT 1
";

$stmtCourse =
    $connexion->prepare($sqlCourse);

if (!$stmtCourse) {

    reponseErreur(
        "Erreur lors de la récupération de la course."
    );
}

$stmtCourse->bind_param(
    "i",
    $id_course
);

$stmtCourse->execute();

$resultatCourse =
    $stmtCourse->get_result();

if ($resultatCourse->num_rows === 0) {

    $stmtCourse->close();

    reponseErreur(
        "Course introuvable.",
        404
    );
}

$course =
    $resultatCourse->fetch_assoc();

$stmtCourse->close();

// ======================================================
// AUTORISATION CLIENT
// ======================================================

if ($estClient) {

    $id_client =
        intval($_SESSION["id_client"]);

    if (
        (int)$course["id_client"]
        !== $id_client
    ) {

        reponseErreur(
            "Vous n'avez pas accès à cette course.",
            403
        );
    }
}

// ======================================================
// AUTORISATION CHAUFFEUR
// ======================================================

if ($estChauffeur) {

    $id_chauffeur =
        intval($_SESSION["id_chauffeur"]);

    if (
        $course["id_chauffeur"] === null ||
        (int)$course["id_chauffeur"]
        !== $id_chauffeur
    ) {

        reponseErreur(
            "Vous n'avez pas accès à cette course.",
            403
        );
    }
}

// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "id_course" =>
        (int)$course["id_course"],

    "statut_course" =>
        $course["statut_course"],

    "latitude_client" =>
        $course["latitude_client_actuelle"],

    "longitude_client" =>
        $course["longitude_client_actuelle"],

    "latitude_chauffeur" =>
        $course["latitude_chauffeur_actuelle"],

    "longitude_chauffeur" =>
        $course["longitude_chauffeur_actuelle"],

    "latitude_destination" =>
        $course["latitude_destination"],

    "longitude_destination" =>
        $course["longitude_destination"]

]);

?>