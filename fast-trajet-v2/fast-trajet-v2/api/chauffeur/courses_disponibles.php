<?php

// ======================================================
// FAST TRAJET V2
// COURSES DISPONIBLES POUR LE CHAUFFEUR
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// CHAUFFEUR CONNECTÉ
// ======================================================

$id_chauffeur = exigerChauffeur();


// ======================================================
// VÉRIFIER LE STATUT DU CHAUFFEUR
// ======================================================

$sqlChauffeur = "
    SELECT
        disponibilite,
        statut
    FROM chauffeur
    WHERE id_chauffeur = ?
    LIMIT 1
";

$stmtChauffeur =
    $connexion->prepare($sqlChauffeur);

if (!$stmtChauffeur) {

    reponseErreur(
        "Erreur lors de la vérification du chauffeur."
    );
}

$stmtChauffeur->bind_param(
    "i",
    $id_chauffeur
);

$stmtChauffeur->execute();

$resultatChauffeur =
    $stmtChauffeur->get_result();

if ($resultatChauffeur->num_rows === 0) {

    $stmtChauffeur->close();

    reponseErreur(
        "Chauffeur introuvable.",
        404
    );
}

$chauffeur =
    $resultatChauffeur->fetch_assoc();

$stmtChauffeur->close();


// ======================================================
// CHAUFFEUR ACTIF
// ======================================================

if ($chauffeur["statut"] !== "actif") {

    reponseErreur(
        "Votre compte chauffeur n'est pas actif.",
        403
    );
}


// ======================================================
// CHAUFFEUR INDISPONIBLE
// ======================================================

if ((int)$chauffeur["disponibilite"] !== 1) {

    reponseSucces([
        "disponibilite" => 0,
        "nombre_courses" => 0,
        "courses" => []
    ]);
}


// ======================================================
// RECHERCHER LES COURSES EN ATTENTE
// ======================================================

$sqlCourses = "
    SELECT

        c.id_course,
        c.id_client,

        c.lieu_depart,
        c.latitude_depart,
        c.longitude_depart,

        c.lieu_destination,
        c.latitude_destination,
        c.longitude_destination,

        c.distance,
        c.duree_estimee,

        c.prix_initial,
        c.prix_actuel,
        c.prix_accepte,

        c.statut_prix,
        c.statut_course,

        c.date_creation

    FROM course c

    WHERE c.id_chauffeur IS NULL
      AND c.statut_course = 'en_attente'

    ORDER BY c.date_creation ASC
";

$stmtCourses =
    $connexion->prepare($sqlCourses);

if (!$stmtCourses) {

    reponseErreur(
        "Erreur lors de la recherche des courses."
    );
}

$stmtCourses->execute();

$resultatCourses =
    $stmtCourses->get_result();


// ======================================================
// CONSTRUIRE LE TABLEAU
// ======================================================

$courses = [];

while (
    $course =
    $resultatCourses->fetch_assoc()
) {

    $course["id_course"] =
        (int)$course["id_course"];

    $course["id_client"] =
        (int)$course["id_client"];

    $course["distance"] =
        $course["distance"] !== null
        ? (float)$course["distance"]
        : null;

    $course["duree_estimee"] =
        $course["duree_estimee"] !== null
        ? (int)$course["duree_estimee"]
        : null;

    $courses[] =
        $course;
}

$stmtCourses->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "disponibilite" => 1,

    "nombre_courses" =>
        count($courses),

    "courses" =>
        $courses

]);

?>