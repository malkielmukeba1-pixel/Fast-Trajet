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

            COUNT(*) AS total,

            COALESCE(
                SUM(
                    statut = 'en_traitement'
                ),
                0
            ) AS en_traitement,

            COALESCE(
                SUM(
                    statut = 'resolue'
                ),
                0
            ) AS resolues

        FROM alerte

        WHERE id_policier = ?
        "
    );


$requete->bind_param(
    "i",
    $id_policier
);

$requete->execute();

$stats =
    $requete
        ->get_result()
        ->fetch_assoc();


$requeteNotification =
    $connexion->prepare(
        "
        SELECT COUNT(*) AS total
        FROM notification
        WHERE id_policier = ?
        AND lu = 0
        "
    );


$requeteNotification->bind_param(
    "i",
    $id_policier
);

$requeteNotification->execute();

$notifications =
    $requeteNotification
        ->get_result()
        ->fetch_assoc();


echo json_encode([
    "success" => true,

    "alertes" =>
        (int) $stats["total"],

    "en_traitement" =>
        (int) $stats["en_traitement"],

    "resolues" =>
        (int) $stats["resolues"],

    "notifications_non_lues" =>
        (int) $notifications["total"]
]);