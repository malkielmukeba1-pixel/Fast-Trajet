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


$id_alerte =
    isset($_POST["id_alerte"])
        ? (int) $_POST["id_alerte"]
        : 0;


$nouveau_statut =
    trim(
        $_POST["statut"] ?? ""
    );


$statutsAutorises = [
    "en_traitement",
    "resolue"
];


if (
    $id_alerte <= 0 ||
    !in_array(
        $nouveau_statut,
        $statutsAutorises,
        true
    )
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Données invalides."
    ]);

    exit;
}


$connexion->begin_transaction();


try {

    $verification =
        $connexion->prepare(
            "
            SELECT statut
            FROM alerte

            WHERE
                id_alerte = ?
                AND id_policier = ?

            FOR UPDATE
            "
        );


    $verification->bind_param(
        "ii",
        $id_alerte,
        $id_policier
    );


    $verification->execute();


    $resultat =
        $verification->get_result();


    if (
        $resultat->num_rows === 0
    ) {

        throw new Exception(
            "Cette alerte ne vous est pas attribuée."
        );
    }


    $alerte =
        $resultat->fetch_assoc();


    if (
        $nouveau_statut ===
        "en_traitement"
    ) {

        $requete =
            $connexion->prepare(
                "
                UPDATE alerte

                SET statut =
                    'en_traitement'

                WHERE id_alerte = ?
                AND id_policier = ?
                "
            );
    }
    else {

        $requete =
            $connexion->prepare(
                "
                UPDATE alerte

                SET
                    statut = 'resolue',
                    date_resolution = NOW()

                WHERE id_alerte = ?
                AND id_policier = ?
                "
            );
    }


    $requete->bind_param(
        "ii",
        $id_alerte,
        $id_policier
    );


    $requete->execute();


    $connexion->commit();


    echo json_encode([
        "success" => true,
        "message" =>
            $nouveau_statut ===
            "resolue"
                ? "Alerte résolue."
                : "Alerte prise en charge."
    ]);

}
catch (Throwable $erreur) {

    $connexion->rollback();

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" =>
            $erreur->getMessage()
    ]);
}