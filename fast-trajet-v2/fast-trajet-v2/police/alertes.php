<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";


if (
    !isset($_SESSION["policier_connecte"]) ||
    $_SESSION["policier_connecte"] !== true
) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Policier non connecté."
    ]);

    exit;
}


$id_policier =
    (int) $_SESSION["id_policier"];


$requete =
    $connexion->prepare(
        "
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

            c.lieu_depart,
            c.lieu_destination

        FROM alerte a

        LEFT JOIN course c
            ON c.id_course =
               a.id_course

        WHERE
            a.id_policier = ?

        ORDER BY
            CASE a.statut

                WHEN 'nouvelle'
                    THEN 1

                WHEN 'en_traitement'
                    THEN 2

                WHEN 'resolue'
                    THEN 3

                ELSE 4

            END,

            a.date_creation DESC
        "
    );


$requete->bind_param(
    "i",
    $id_policier
);

$requete->execute();

$resultat =
    $requete->get_result();


$alertes = [];


while (
    $ligne =
    $resultat->fetch_assoc()
) {

    $alertes[] = $ligne;
}


echo json_encode([
    "success" => true,
    "alertes" => $alertes
]);