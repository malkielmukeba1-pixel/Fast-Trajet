<?php

// ======================================================
// FAST TRAJET V2
// ENREGISTRER LA POSITION GPS DU CHAUFFEUR
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
// CHAUFFEUR CONNECTÉ
// ======================================================

$id_chauffeur =
    (int) exigerChauffeur();


// ======================================================
// DONNÉES REÇUES
// ======================================================

$id_course =
    (int) (
        $_POST["id_course"] ?? 0
    );


$latitude =
    $_POST["latitude"] ?? null;


$longitude =
    $_POST["longitude"] ?? null;


// précision GPS facultative
$precision_gps =
    $_POST["precision_gps"] ?? null;


// ======================================================
// VÉRIFIER ID COURSE
// ======================================================

if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide.",
        400
    );
}


// ======================================================
// VÉRIFIER LATITUDE / LONGITUDE
// ======================================================

if (
    $latitude === null ||
    $longitude === null ||
    !is_numeric($latitude) ||
    !is_numeric($longitude)
) {

    reponseErreur(
        "Coordonnées GPS invalides.",
        400
    );
}


$latitude =
    (float) $latitude;


$longitude =
    (float) $longitude;


// ======================================================
// LIMITES GPS
// ======================================================

if (
    $latitude < -90 ||
    $latitude > 90
) {

    reponseErreur(
        "Latitude invalide.",
        400
    );
}


if (
    $longitude < -180 ||
    $longitude > 180
) {

    reponseErreur(
        "Longitude invalide.",
        400
    );
}


// ======================================================
// VÉRIFIER QUE LA COURSE APPARTIENT AU CHAUFFEUR
// ======================================================

$sqlCourse = "
    SELECT

        id_course,
        id_chauffeur,
        statut_course

    FROM course

    WHERE id_course = ?

    LIMIT 1
";


$stmtCourse =
    $connexion->prepare(
        $sqlCourse
    );


if (!$stmtCourse) {

    reponseErreur(
        "Impossible de vérifier la course.",
        500
    );
}


$stmtCourse->bind_param(
    "i",
    $id_course
);


$stmtCourse->execute();


$resultatCourse =
    $stmtCourse->get_result();


if (
    $resultatCourse->num_rows === 0
) {

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
// VÉRIFIER LE CHAUFFEUR
// ======================================================

if (
    $course["id_chauffeur"] === null ||
    (int) $course["id_chauffeur"] !==
    $id_chauffeur
) {

    reponseErreur(
        "Cette course ne vous est pas attribuée.",
        403
    );
}


// ======================================================
// GPS UNIQUEMENT PENDANT LA COURSE
// ======================================================

if (
    $course["statut_course"] !==
    "en_cours"
) {

    reponseErreur(
        "La course n'est pas actuellement en cours.",
        400
    );
}


// ======================================================
// METTRE À JOUR LA POSITION ACTUELLE DU CHAUFFEUR
// ======================================================

$sqlUpdate = "
    UPDATE course

    SET
        latitude_chauffeur_actuelle = ?,
        longitude_chauffeur_actuelle = ?

    WHERE
        id_course = ?
        AND id_chauffeur = ?
        AND statut_course = 'en_cours'
";


$stmtUpdate =
    $connexion->prepare(
        $sqlUpdate
    );


if (!$stmtUpdate) {

    reponseErreur(
        "Impossible de préparer la mise à jour GPS.",
        500
    );
}


$stmtUpdate->bind_param(
    "ddii",
    $latitude,
    $longitude,
    $id_course,
    $id_chauffeur
);


// ======================================================
// EXÉCUTER
// ======================================================

if (!$stmtUpdate->execute()) {

    $stmtUpdate->close();

    reponseErreur(
        "Impossible d'enregistrer la position du chauffeur.",
        500
    );
}


$stmtUpdate->close();


// ======================================================
// SUCCÈS
// ======================================================

reponseSucces([

    "message" =>
        "Position du chauffeur enregistrée.",

    "id_course" =>
        $id_course,

    "latitude" =>
        $latitude,

    "longitude" =>
        $longitude

]);

?>