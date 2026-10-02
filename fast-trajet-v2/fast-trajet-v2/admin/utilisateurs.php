<?php

// ======================================================
// FAST TRAJET V2
// UTILISATEURS ADMINISTRATION
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
// CLIENTS
// ======================================================

$sqlClients = "
    SELECT
        id_client,
        nom,
        prenom,
        telephone,
        email,
        sexe,
        statut,
        date_inscription
    FROM client
    ORDER BY date_inscription DESC
";

$stmtClients =
    $connexion->prepare($sqlClients);

if (!$stmtClients) {

    reponseErreur(
        "Impossible de récupérer les clients."
    );
}

$stmtClients->execute();

$resultatClients =
    $stmtClients->get_result();

$clients = [];

while (
    $client =
    $resultatClients->fetch_assoc()
) {

    $client["id_client"] =
        (int)$client["id_client"];

    $clients[] =
        $client;
}

$stmtClients->close();


// ======================================================
// CHAUFFEURS
// ======================================================

$sqlChauffeurs = "
    SELECT
        id_chauffeur,
        nom,
        prenom,
        telephone,
        email,
        sexe,
        disponibilite,
        statut,
        date_inscription
    FROM chauffeur
    ORDER BY date_inscription DESC
";

$stmtChauffeurs =
    $connexion->prepare($sqlChauffeurs);

if (!$stmtChauffeurs) {

    reponseErreur(
        "Impossible de récupérer les chauffeurs."
    );
}

$stmtChauffeurs->execute();

$resultatChauffeurs =
    $stmtChauffeurs->get_result();

$chauffeurs = [];

while (
    $chauffeur =
    $resultatChauffeurs->fetch_assoc()
) {

    $chauffeur["id_chauffeur"] =
        (int)$chauffeur["id_chauffeur"];

    $chauffeur["disponibilite"] =
        (int)$chauffeur["disponibilite"];

    $chauffeurs[] =
        $chauffeur;
}

$stmtChauffeurs->close();


// ======================================================
// HISTORIQUE
// ======================================================

enregistrerHistorique(
    $connexion,
    "consultation_utilisateurs",
    "Consultation de la liste des clients et des chauffeurs.",
    null,
    null,
    $id_administrateur,
    null
);


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "nombre_clients" =>
        count($clients),

    "nombre_chauffeurs" =>
        count($chauffeurs),

    "clients" =>
        $clients,

    "chauffeurs" =>
        $chauffeurs

]);

?>