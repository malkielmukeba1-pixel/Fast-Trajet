<?php

// ======================================================
// FAST TRAJET V2
// HISTORIQUE DE NÉGOCIATION
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
// ID COURSE
// ======================================================

$id_course =
    intval(
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
// VÉRIFIER L'ACCÈS
// ======================================================

$sqlCourse = "
    SELECT
        id_course,
        id_client,
        id_chauffeur
    FROM course
    WHERE id_course = ?
    LIMIT 1
";

$stmtCourse =
    $connexion->prepare($sqlCourse);

if (!$stmtCourse) {

    reponseErreur(
        "Erreur de préparation."
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


if ($estClient) {

    $id_client =
        intval($_SESSION["id_client"]);

    if (
        intval($course["id_client"])
        !== $id_client
    ) {

        reponseErreur(
            "Vous n'avez pas accès à cette course.",
            403
        );
    }
}

else {

    $id_chauffeur =
        intval($_SESSION["id_chauffeur"]);

    if (
        $course["id_chauffeur"] === null ||
        intval($course["id_chauffeur"])
        !== $id_chauffeur
    ) {

        reponseErreur(
            "Vous n'avez pas accès à cette course.",
            403
        );
    }
}


// ======================================================
// HISTORIQUE
// ======================================================

$sql = "
    SELECT

        id_negociation,
        id_course,
        id_client,
        id_chauffeur,
        expediteur,
        prix_propose,
        statut,
        date_proposition

    FROM negociation

    WHERE id_course = ?

    ORDER BY date_proposition ASC
";

$stmt =
    $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la récupération de la négociation."
    );
}

$stmt->bind_param(
    "i",
    $id_course
);

$stmt->execute();

$resultat =
    $stmt->get_result();


$propositions = [];

while (
    $ligne =
    $resultat->fetch_assoc()
) {

    $ligne["id_negociation"] =
        (int)$ligne["id_negociation"];

    $ligne["prix_propose"] =
        (float)$ligne["prix_propose"];

    $propositions[] =
        $ligne;
}

$stmt->close();


reponseSucces([

    "id_course" =>
        $id_course,

    "nombre_propositions" =>
        count($propositions),

    "propositions" =>
        $propositions

]);

?>