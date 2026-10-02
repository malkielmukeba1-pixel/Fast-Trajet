<?php

// ======================================================
// FAST TRAJET V2
// COURSES ADMINISTRATION
// ======================================================

require_once "../config/database.php";
require_once "../config/session.php";
require_once "../config/auth.php";
require_once "../config/response.php";
require_once "../config/historique.php";


// ======================================================
// ADMIN
// ======================================================

$id_administrateur =
    exigerAdministrateur();


// ======================================================
// COURSES
// ======================================================

$sql = "
    SELECT

        c.id_course,
        c.id_client,
        c.id_chauffeur,

        c.lieu_depart,
        c.lieu_destination,

        c.distance,
        c.duree_estimee,

        c.prix_initial,
        c.prix_actuel,
        c.prix_accepte,

        c.statut_prix,
        c.statut_course,

        c.date_creation,
        c.date_acceptation,
        c.date_prix_accepte,
        c.date_debut,
        c.date_fin,
        c.date_annulation,

        c.raison_annulation,

        cl.nom AS nom_client,
        cl.prenom AS prenom_client,

        ch.nom AS nom_chauffeur,
        ch.prenom AS prenom_chauffeur

    FROM course c

    LEFT JOIN client cl
        ON c.id_client = cl.id_client

    LEFT JOIN chauffeur ch
        ON c.id_chauffeur = ch.id_chauffeur

    ORDER BY c.date_creation DESC
";

$stmt =
    $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Impossible de récupérer les courses."
    );
}

$stmt->execute();

$resultat =
    $stmt->get_result();

$courses = [];

while (
    $course =
    $resultat->fetch_assoc()
) {

    $course["id_course"] =
        (int)$course["id_course"];

    $course["id_client"] =
        (int)$course["id_client"];

    $course["id_chauffeur"] =
        $course["id_chauffeur"] !== null
        ? (int)$course["id_chauffeur"]
        : null;

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

$stmt->close();


// ======================================================
// HISTORIQUE
// ======================================================

enregistrerHistorique(
    $connexion,
    "consultation_courses",
    "Consultation de la liste des courses.",
    null,
    null,
    $id_administrateur,
    null
);


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