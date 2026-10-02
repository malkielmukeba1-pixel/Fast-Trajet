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
        "success" => false
    ]);

    exit;
}


$id_policier =
    (int) $_SESSION["id_policier"];


$requete =
    $connexion->prepare(
        "
        UPDATE notification

        SET
            lu = 1,
            date_lecture = NOW()

        WHERE
            id_policier = ?
            AND lu = 0
        "
    );


$requete->bind_param(
    "i",
    $id_policier
);


$requete->execute();


echo json_encode([
    "success" => true
]);