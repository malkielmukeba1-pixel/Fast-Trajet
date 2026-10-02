<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once "../config/database.php";


if (
    !isset($_SESSION["administrateur_connecte"]) ||
    $_SESSION["administrateur_connecte"] !== true
) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Administrateur non connecté."
    ]);

    exit;
}


try {

    $sql = "
        SELECT
            id_policier,
            nom,
            prenom,
            matricule,
            grade,
            commissariat,
            statut
        FROM policier
        ORDER BY nom ASC, prenom ASC
    ";

    $resultat =
        $connexion->query($sql);


    $policiers = [];


    while (
        $ligne =
        $resultat->fetch_assoc()
    ) {

        $policiers[] = $ligne;
    }


    echo json_encode([
        "success" => true,
        "policiers" => $policiers
    ]);

}
catch (Throwable $erreur) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" =>
            "Impossible de récupérer les policiers.",
        "error" =>
            $erreur->getMessage()
    ]);
}