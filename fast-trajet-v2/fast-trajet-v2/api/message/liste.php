<?php

// ======================================================
// FAST TRAJET V2
// LISTE DES MESSAGES CLIENT <-> CHAUFFEUR
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";

header(
    "Content-Type: application/json; charset=UTF-8"
);

if (
    session_status() !== PHP_SESSION_ACTIVE
) {
    session_start();
}


function repondreListe(
    bool $success,
    array $donnees = [],
    int $code = 200
): void {

    http_response_code($code);

    echo json_encode(
        array_merge(
            [
                "success" => $success
            ],
            $donnees
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


// ======================================================
// UTILISATEUR
// ======================================================

$typeUtilisateur = null;
$idUtilisateur = null;


if (
    !empty($_SESSION["client_connecte"]) &&
    isset($_SESSION["id_client"])
) {

    $typeUtilisateur = "client";

    $idUtilisateur =
        (int) $_SESSION["id_client"];

}
elseif (
    !empty($_SESSION["chauffeur_connecte"]) &&
    isset($_SESSION["id_chauffeur"])
) {

    $typeUtilisateur = "chauffeur";

    $idUtilisateur =
        (int) $_SESSION["id_chauffeur"];

}
else {

    repondreListe(
        false,
        [
            "message" =>
                "Vous devez être connecté."
        ],
        401
    );
}


// ======================================================
// COURSE
// ======================================================

$id_course =
    (int) (
        $_GET["id_course"] ?? 0
    );


if ($id_course <= 0) {

    repondreListe(
        false,
        [
            "message" =>
                "Course invalide."
        ],
        400
    );
}


// ======================================================
// VÉRIFIER APPARTENANCE
// ======================================================

$stmtCourse =
    $connexion->prepare(
        "
        SELECT

            id_client,
            id_chauffeur,
            statut_prix,
            statut_course

        FROM course

        WHERE id_course = ?

        LIMIT 1
        "
    );


$stmtCourse->bind_param(
    "i",
    $id_course
);


$stmtCourse->execute();


$resultat =
    $stmtCourse->get_result();


if ($resultat->num_rows === 0) {

    repondreListe(
        false,
        [
            "message" =>
                "Course introuvable."
        ],
        404
    );
}


$course =
    $resultat->fetch_assoc();


$stmtCourse->close();


if (
    $typeUtilisateur === "client" &&
    (int) $course["id_client"] !==
        $idUtilisateur
) {

    repondreListe(
        false,
        [
            "message" =>
                "Accès interdit."
        ],
        403
    );
}


if (
    $typeUtilisateur === "chauffeur" &&
    (
        $course["id_chauffeur"] === null ||
        (int) $course["id_chauffeur"] !==
            $idUtilisateur
    )
) {

    repondreListe(
        false,
        [
            "message" =>
                "Accès interdit."
        ],
        403
    );
}


// ======================================================
// MARQUER LES MESSAGES REÇUS COMME LUS
// ======================================================

$expediteurAutre =
    $typeUtilisateur === "client"
        ? "chauffeur"
        : "client";


$stmtLu =
    $connexion->prepare(
        "
        UPDATE message

        SET lu = 1

        WHERE
            id_course = ?
            AND expediteur = ?
            AND lu = 0
        "
    );


$stmtLu->bind_param(
    "is",
    $id_course,
    $expediteurAutre
);


$stmtLu->execute();

$stmtLu->close();


// ======================================================
// CHARGER LES MESSAGES
// ======================================================

$stmtMessages =
    $connexion->prepare(
        "
        SELECT

            id_message,
            expediteur,
            contenu,
            lu,
            date_heure

        FROM message

        WHERE id_course = ?

        ORDER BY
            id_message ASC
        "
    );


$stmtMessages->bind_param(
    "i",
    $id_course
);


$stmtMessages->execute();


$resultatMessages =
    $stmtMessages->get_result();


$messages = [];


while (
    $ligne =
    $resultatMessages->fetch_assoc()
) {

    $messages[] = $ligne;
}


$stmtMessages->close();


// ======================================================
// ÉCRITURE AUTORISÉE ?
// ======================================================

$ecritureAutorisee =
    $course["statut_prix"] === "accepte" &&
    in_array(
        $course["statut_course"],
        [
            "prix_accepte",
            "en_cours"
        ],
        true
    );


// ======================================================
// RÉPONSE
// ======================================================

repondreListe(
    true,
    [
        "messages" =>
            $messages,

        "utilisateur" =>
            $typeUtilisateur,

        "ecriture_autorisee" =>
            $ecritureAutorisee
    ]
);