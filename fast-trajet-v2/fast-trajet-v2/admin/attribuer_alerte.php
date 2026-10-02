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


$id_alerte =
    isset($_POST["id_alerte"])
        ? (int) $_POST["id_alerte"]
        : 0;

$id_policier =
    isset($_POST["id_policier"])
        ? (int) $_POST["id_policier"]
        : 0;


if (
    $id_alerte <= 0 ||
    $id_policier <= 0
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" =>
            "Alerte ou policier invalide."
    ]);

    exit;
}


$connexion->begin_transaction();


try {

    // =========================================
    // VÉRIFIER L'ALERTE
    // =========================================

    $requeteAlerte =
        $connexion->prepare(
            "
            SELECT
                id_alerte,
                id_course,
                id_policier,
                type_alerte,
                niveau,
                description,
                statut
            FROM alerte
            WHERE id_alerte = ?
            FOR UPDATE
            "
        );


    $requeteAlerte->bind_param(
        "i",
        $id_alerte
    );

    $requeteAlerte->execute();

    $resultatAlerte =
        $requeteAlerte->get_result();


    if (
        $resultatAlerte->num_rows === 0
    ) {

        throw new Exception(
            "Alerte introuvable."
        );
    }


    $alerte =
        $resultatAlerte->fetch_assoc();


    // =========================================
    // VÉRIFIER LE POLICIER
    // =========================================

    $requetePolicier =
        $connexion->prepare(
            "
            SELECT
                id_policier,
                nom,
                prenom,
                matricule
            FROM policier
            WHERE id_policier = ?
            LIMIT 1
            "
        );


    $requetePolicier->bind_param(
        "i",
        $id_policier
    );

    $requetePolicier->execute();

    $resultatPolicier =
        $requetePolicier->get_result();


    if (
        $resultatPolicier->num_rows === 0
    ) {

        throw new Exception(
            "Policier introuvable."
        );
    }


    $policier =
        $resultatPolicier->fetch_assoc();


    // =========================================
    // ÉVITER UNE DOUBLE ATTRIBUTION IDENTIQUE
    // =========================================

    if (
        $alerte["id_policier"] !== null &&
        (int) $alerte["id_policier"] ===
        $id_policier
    ) {

        throw new Exception(
            "Cette alerte est déjà attribuée à ce policier."
        );
    }


    // =========================================
    // ATTRIBUER
    // =========================================

    $requeteAttribution =
        $connexion->prepare(
            "
            UPDATE alerte
            SET id_policier = ?
            WHERE id_alerte = ?
            "
        );


    $requeteAttribution->bind_param(
        "ii",
        $id_policier,
        $id_alerte
    );


    $requeteAttribution->execute();


    // =========================================
    // TYPE DE NOTIFICATION
    // =========================================

    $type_notification =
        "alerte_police";


    /*
        Si type_notification est un ENUM,
        on vérifie que "alerte_police"
        est accepté avant l'INSERT.
    */

    $resultatColonne =
        $connexion->query(
            "
            SHOW COLUMNS
            FROM notification
            LIKE 'type_notification'
            "
        );


    $colonne =
        $resultatColonne->fetch_assoc();


    $definition =
        strtolower(
            $colonne["Type"] ?? ""
        );


    if (
        str_starts_with(
            $definition,
            "enum("
        )
    ) {

        preg_match_all(
            "/'([^']*)'/",
            $definition,
            $correspondances
        );


        $valeursAutorisees =
            $correspondances[1] ?? [];


        if (
            !in_array(
                "alerte_police",
                $valeursAutorisees,
                true
            )
        ) {

            throw new Exception(
                "Le champ type_notification est un ENUM "
                . "qui n'accepte pas encore la valeur "
                . "'alerte_police'."
            );
        }
    }


    // =========================================
    // CONTENU NOTIFICATION
    // =========================================

    $titre =
        "Nouvelle alerte attribuée";


    $contenu =
        "Une alerte "
        . $alerte["niveau"]
        . " de type "
        . $alerte["type_alerte"]
        . " vous a été attribuée.";


    $id_course =
        $alerte["id_course"] !== null
            ? (int) $alerte["id_course"]
            : null;


    // =========================================
    // CRÉER NOTIFICATION
    // =========================================

    $requeteNotification =
        $connexion->prepare(
            "
            INSERT INTO notification
            (
                id_policier,
                id_course,
                id_alerte,
                type_notification,
                titre,
                contenu,
                lu,
                date_creation
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                0,
                NOW()
            )
            "
        );


    $requeteNotification->bind_param(
        "iiisss",
        $id_policier,
        $id_course,
        $id_alerte,
        $type_notification,
        $titre,
        $contenu
    );


    $requeteNotification->execute();


    $connexion->commit();


    echo json_encode([
        "success" => true,

        "message" =>
            "Alerte transmise à "
            . $policier["prenom"]
            . " "
            . $policier["nom"]
            . ".",

        "id_alerte" =>
            $id_alerte,

        "id_policier" =>
            $id_policier
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