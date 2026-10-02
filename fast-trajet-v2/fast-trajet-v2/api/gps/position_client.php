<?php

// ======================================================
// FAST TRAJET V2
// ENREGISTRER LA POSITION DU CLIENT
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
// CLIENT CONNECTÉ
// ======================================================

$id_client = exigerClient();

// ======================================================
// DONNÉES
// ======================================================

$id_course = intval(
    $_POST["id_course"] ?? 0
);

$latitude = $_POST["latitude"] ?? "";
$longitude = $_POST["longitude"] ?? "";
$precision = $_POST["precision_gps"] ?? null;

// ======================================================
// VALIDATION
// ======================================================

if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide."
    );
}

if (!estCoordonneeValide($latitude, $longitude)) {

    reponseErreur(
        "Coordonnées GPS invalides."
    );
}

$latitude = (float)$latitude;
$longitude = (float)$longitude;

$precisionSQL = null;

if (
    $precision !== null &&
    $precision !== "" &&
    is_numeric($precision)
) {

    $precisionSQL = (float)$precision;
}

// ======================================================
// RÉCUPÉRER LA COURSE
// ======================================================

$sqlCourse = "
    SELECT
        id_course,
        id_client,
        id_chauffeur,
        statut_course
    FROM course
    WHERE id_course = ?
    LIMIT 1
";

$stmt = $connexion->prepare($sqlCourse);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la récupération de la course."
    );
}

$stmt->bind_param(
    "i",
    $id_course
);

$stmt->execute();

$resultat = $stmt->get_result();

if ($resultat->num_rows === 0) {

    $stmt->close();

    reponseErreur(
        "Course introuvable.",
        404
    );
}

$course = $resultat->fetch_assoc();

$stmt->close();

// ======================================================
// VÉRIFIER PROPRIÉTÉ
// ======================================================

if (
    (int)$course["id_client"] !==
    $id_client
) {

    reponseErreur(
        "Cette course ne vous appartient pas.",
        403
    );
}

// ======================================================
// GPS AUTORISÉ UNIQUEMENT EN_COURS
// ======================================================

if (
    $course["statut_course"] !== "en_cours"
) {

    reponseErreur(
        "La transmission GPS n'est autorisée que pendant une course en cours.",
        403
    );
}

// ======================================================
// ENREGISTRER L'HISTORIQUE GPS
// ======================================================

$sqlInsert = "
    INSERT INTO position_client (
        id_course,
        id_client,
        latitude,
        longitude,
        precision_gps,
        date_heure
    )
    VALUES (?, ?, ?, ?, ?, NOW())
";

$stmtInsert =
    $connexion->prepare($sqlInsert);

if (!$stmtInsert) {

    reponseErreur(
        "Erreur lors de l'enregistrement de la position."
    );
}

$stmtInsert->bind_param(
    "iiddi",
    $id_course,
    $id_client,
    $latitude,
    $longitude,
    $precisionSQL
);

if (!$stmtInsert->execute()) {

    $stmtInsert->close();

    reponseErreur(
        "Impossible d'enregistrer la position du client."
    );
}

$id_position =
    $connexion->insert_id;

$stmtInsert->close();

// ======================================================
// METTRE À JOUR LA DERNIÈRE POSITION
// ======================================================

$sqlUpdate = "
    UPDATE course
    SET
        latitude_client_actuelle = ?,
        longitude_client_actuelle = ?,
        date_modification = NOW()
    WHERE id_course = ?
";

$stmtUpdate =
    $connexion->prepare($sqlUpdate);

if (!$stmtUpdate) {

    reponseErreur(
        "La position a été enregistrée mais la dernière position n'a pas pu être mise à jour."
    );
}

$stmtUpdate->bind_param(
    "ddi",
    $latitude,
    $longitude,
    $id_course
);

$stmtUpdate->execute();

$stmtUpdate->close();

// ======================================================
// SUCCÈS
// ======================================================

reponseSucces([

    "message" =>
        "Position du client enregistrée.",

    "id_position" =>
        $id_position,

    "latitude" =>
        $latitude,

    "longitude" =>
        $longitude

]);

?>