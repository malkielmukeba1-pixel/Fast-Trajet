<?php

// ======================================================
// FAST TRAJET V2
// STATISTIQUES ADMINISTRATEUR
// ======================================================

require_once "../config/database.php";

require_once "../config/session.php";

require_once "../config/auth.php";

require_once "../config/response.php";


// ======================================================
// ADMIN
// ======================================================

exigerAdministrateur();


// ======================================================
// CLIENTS
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM client
        WHERE statut = 'actif'
        "
    );

$totalClients =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// CHAUFFEURS
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM chauffeur
        WHERE statut = 'actif'
        "
    );

$totalChauffeurs =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// COURSES
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM course
        "
    );

$totalCourses =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// COURSES EN ATTENTE
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM course
        WHERE statut_course = 'en_attente'
        "
    );

$coursesEnAttente =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// COURSES EN COURS
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM course
        WHERE statut_course = 'en_cours'
        "
    );

$coursesEnCours =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// COURSES TERMINÉES
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM course
        WHERE statut_course = 'terminee'
        "
    );

$coursesTerminees =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// ALERTES
// ======================================================

$resultat =
    $connexion->query(
        "
        SELECT COUNT(*) AS total
        FROM alerte
        WHERE statut IN (
            'nouvelle',
            'en_traitement'
        )
        "
    );

$alertesActives =
    (int)$resultat->fetch_assoc()["total"];


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "clients" =>
        $totalClients,

    "chauffeurs" =>
        $totalChauffeurs,

    "courses" =>
        $totalCourses,

    "courses_en_attente" =>
        $coursesEnAttente,

    "courses_en_cours" =>
        $coursesEnCours,

    "courses_terminees" =>
        $coursesTerminees,

    "alertes_actives" =>
        $alertesActives

]);

?>