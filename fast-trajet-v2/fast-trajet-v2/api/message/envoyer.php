<?php

// ======================================================
// FAST TRAJET V2
// ENVOYER UN MESSAGE CLIENT <-> CHAUFFEUR
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


// ======================================================
// RÉPONSE JSON
// ======================================================

function repondreMessage(
    bool $success,
    string $message,
    array $donnees = [],
    int $code = 200
): void {

    http_response_code($code);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $donnees
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


// ======================================================
// POST UNIQUEMENT
// ======================================================

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    repondreMessage(
        false,
        "Méthode non autorisée.",
        [],
        405
    );
}


// ======================================================
// IDENTIFIER L'UTILISATEUR
// ======================================================

$typeUtilisateur = null;
$idUtilisateur = null;


if (
    !empty($_SESSION["client_connecte"]) &&
    isset($_SESSION["id_client"]) &&
    is_numeric($_SESSION["id_client"])
) {

    $typeUtilisateur = "client";

    $idUtilisateur =
        (int) $_SESSION["id_client"];

}
elseif (
    !empty($_SESSION["chauffeur_connecte"]) &&
    isset($_SESSION["id_chauffeur"]) &&
    is_numeric($_SESSION["id_chauffeur"])
) {

    $typeUtilisateur = "chauffeur";

    $idUtilisateur =
        (int) $_SESSION["id_chauffeur"];

}
else {

    repondreMessage(
        false,
        "Vous devez être connecté.",
        [],
        401
    );
}


// ======================================================
// DONNÉES
// ======================================================

$id_course =
    (int) (
        $_POST["id_course"] ?? 0
    );


$contenu =
    trim(
        $_POST["message"] ?? ""
    );


if ($id_course <= 0) {

    repondreMessage(
        false,
        "Course invalide.",
        [],
        400
    );
}


if ($contenu === "") {

    repondreMessage(
        false,
        "Veuillez écrire un message.",
        [],
        400
    );
}


if (
    mb_strlen($contenu) > 1000
) {

    repondreMessage(
        false,
        "Le message est trop long.",
        [],
        400
    );
}


// ======================================================
// VÉRIFIER LA COURSE
// ======================================================

$sqlCourse = "
    SELECT

        id_course,
        id_client,
        id_chauffeur,
        statut_prix,
        statut_course

    FROM course

    WHERE id_course = ?

    LIMIT 1
";


$stmtCourse =
    $connexion->prepare(
        $sqlCourse
    );


$stmtCourse->bind_param(
    "i",
    $id_course
);


$stmtCourse->execute();


$resultatCourse =
    $stmtCourse->get_result();


if (
    $resultatCourse->num_rows === 0
) {

    repondreMessage(
        false,
        "Course introuvable.",
        [],
        404
    );
}


$course =
    $resultatCourse->fetch_assoc();


$stmtCourse->close();


// ======================================================
// AUTORISATION
// ======================================================

if (
    $typeUtilisateur === "client" &&
    (int) $course["id_client"] !==
        $idUtilisateur
) {

    repondreMessage(
        false,
        "Accès interdit à cette course.",
        [],
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

    repondreMessage(
        false,
        "Accès interdit à cette course.",
        [],
        403
    );
}


// ======================================================
// LE CHAUFFEUR DOIT ÊTRE ATTRIBUÉ
// ======================================================

if (
    $course["id_chauffeur"] === null
) {

    repondreMessage(
        false,
        "Aucun chauffeur n'est encore attribué.",
        [],
        400
    );
}


// ======================================================
// LE PRIX DOIT ÊTRE ACCEPTÉ
// ======================================================

if (
    $course["statut_prix"] !==
    "accepte"
) {

    repondreMessage(
        false,
        "La discussion sera disponible après l'acceptation du prix.",
        [],
        400
    );
}


// ======================================================
// COURSE AUTORISÉE
// ======================================================

$statutsAutorises = [
    "prix_accepte",
    "en_cours"
];


if (
    !in_array(
        $course["statut_course"],
        $statutsAutorises,
        true
    )
) {

    repondreMessage(
        false,
        "La discussion n'est plus disponible pour cette course.",
        [],
        400
    );
}


// ======================================================
// ENREGISTRER LE MESSAGE
// ======================================================

$sqlMessage = "
    INSERT INTO message
    (
        id_course,
        id_client,
        id_chauffeur,
        expediteur,
        contenu,
        lu,
        date_heure
    )

    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        0,
        NOW()
    )
";


$stmt =
    $connexion->prepare(
        $sqlMessage
    );


$id_client =
    (int) $course["id_client"];


$id_chauffeur =
    (int) $course["id_chauffeur"];


$stmt->bind_param(
    "iiiss",
    $id_course,
    $id_client,
    $id_chauffeur,
    $typeUtilisateur,
    $contenu
);


$stmt->execute();


$id_message =
    $connexion->insert_id;


$stmt->close();


// ======================================================
// SUCCÈS
// ======================================================

repondreMessage(
    true,
    "Message envoyé.",
    [
        "id_message" =>
            $id_message,

        "expediteur" =>
            $typeUtilisateur
    ]
);