<?php

// ======================================================
// FAST TRAJET V2
// ALERTES ADMINISTRATION
// ======================================================

require_once "../config/database.php";
require_once "../config/session.php";
require_once "../config/auth.php";
require_once "../config/response.php";


// ======================================================
// ADMIN CONNECTÉ
// ======================================================

$id_administrateur =
    exigerAdministrateur();


// ======================================================
// RÉCUPÉRER LES ALERTES
// ======================================================

$sql = "
    SELECT

        a.id_alerte,
        a.id_course,

        a.id_client,
        a.id_chauffeur,

        a.type_alerte,
        a.niveau,

        a.description,

        a.latitude,
        a.longitude,

        a.statut,

        a.date_creation,
        a.date_resolution,

        c.nom AS nom_client,
        c.prenom AS prenom_client,

        ch.nom AS nom_chauffeur,
        ch.prenom AS prenom_chauffeur

    FROM alerte a

    LEFT JOIN client c
        ON a.id_client = c.id_client

    LEFT JOIN chauffeur ch
        ON a.id_chauffeur = ch.id_chauffeur

    ORDER BY
        CASE a.niveau
            WHEN 'critique' THEN 1
            WHEN 'eleve' THEN 2
            WHEN 'moyen' THEN 3
            WHEN 'faible' THEN 4
        END,

        a.date_creation DESC
";

$stmt =
    $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la récupération des alertes."
    );
}

$stmt->execute();

$resultat =
    $stmt->get_result();


$alertes = [];

while (
    $alerte =
    $resultat->fetch_assoc()
) {

    $alertes[] =
        $alerte;
}

$stmt->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "nombre_alertes" =>
        count($alertes),

    "alertes" =>
        $alertes

]);

?>