<?php

// ======================================================
// FAST TRAJET V2
// HISTORIQUE ADMINISTRATION
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
// LIMITE
// ======================================================

$limite =
    intval(
        $_GET["limite"] ?? 100
    );

if ($limite < 1) {
    $limite = 100;
}

if ($limite > 500) {
    $limite = 500;
}


// ======================================================
// HISTORIQUE
// ======================================================

$sql = "
    SELECT

        h.id_historique,

        h.id_client,
        h.id_chauffeur,
        h.id_administrateur,
        h.id_course,

        h.action,
        h.description,

        h.adresse_ip,
        h.user_agent,

        h.date_action,

        cl.nom AS nom_client,
        cl.prenom AS prenom_client,

        ch.nom AS nom_chauffeur,
        ch.prenom AS prenom_chauffeur,

        a.nom AS nom_administrateur,
        a.prenom AS prenom_administrateur

    FROM historique h

    LEFT JOIN client cl
        ON h.id_client = cl.id_client

    LEFT JOIN chauffeur ch
        ON h.id_chauffeur = ch.id_chauffeur

    LEFT JOIN administrateur a
        ON h.id_administrateur =
           a.id_administrateur

    ORDER BY h.date_action DESC

    LIMIT ?
";

$stmt =
    $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Impossible de récupérer l'historique."
    );
}

$stmt->bind_param(
    "i",
    $limite
);

$stmt->execute();

$resultat =
    $stmt->get_result();

$historique = [];

while (
    $ligne =
    $resultat->fetch_assoc()
) {

    $historique[] =
        $ligne;
}

$stmt->close();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([

    "nombre_elements" =>
        count($historique),

    "historique" =>
        $historique

]);

?>