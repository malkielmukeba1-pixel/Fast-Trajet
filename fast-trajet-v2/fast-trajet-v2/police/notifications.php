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

            id_notification,
            id_course,
            id_alerte,

            type_notification,
            titre,
            contenu,

            lu,
            date_creation,
            date_lecture

        FROM notification

        WHERE id_policier = ?

        ORDER BY
            id_notification DESC

        LIMIT 50
        "
    );


$requete->bind_param(
    "i",
    $id_policier
);

$requete->execute();


$resultat =
    $requete->get_result();


$notifications = [];


while (
    $ligne =
    $resultat->fetch_assoc()
) {

    $notifications[] =
        $ligne;
}


echo json_encode([
    "success" => true,
    "notifications" =>
        $notifications
]);