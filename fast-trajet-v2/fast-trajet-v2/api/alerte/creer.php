<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once "../../config/database.php";


function repondre(
    bool $success,
    string $message,
    array $donnees = []
): void {

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


if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    repondre(
        false,
        "Méthode non autorisée."
    );
}


/* =====================================================
   IDENTIFIER CLIENT OU CHAUFFEUR
   ===================================================== */

$estClient =
    isset($_SESSION["client_connecte"]) &&
    $_SESSION["client_connecte"] === true;

$estChauffeur =
    isset($_SESSION["chauffeur_connecte"]) &&
    $_SESSION["chauffeur_connecte"] === true;


if (
    !$estClient &&
    !$estChauffeur
) {

    repondre(
        false,
        "Vous devez être connecté."
    );
}


$idClient = null;
$idChauffeur = null;
$acteur = null;


if ($estClient) {

    $idClient =
        (int) (
            $_SESSION["id_client"] ?? 0
        );

    $acteur = "client";


    if ($idClient <= 0) {

        repondre(
            false,
            "Client introuvable."
        );
    }

}
else {

    $idChauffeur =
        (int) (
            $_SESSION["id_chauffeur"] ?? 0
        );

    $acteur = "chauffeur";


    if ($idChauffeur <= 0) {

        repondre(
            false,
            "Chauffeur introuvable."
        );
    }
}


/* =====================================================
   DONNÉES
   ===================================================== */

$idCourse =
    (int) (
        $_POST["id_course"] ?? 0
    );

$typeAlerte =
    trim(
        $_POST["type_alerte"] ?? ""
    );

$niveau =
    trim(
        $_POST["niveau"] ?? ""
    );

$description =
    trim(
        $_POST["description"] ?? ""
    );


if ($idCourse <= 0) {

    repondre(
        false,
        "Aucune course active."
    );
}


$typesAutorises = [
    "urgence",
    "securite",
    "accident",
    "incident"
];


if (
    !in_array(
        $typeAlerte,
        $typesAutorises,
        true
    )
) {

    repondre(
        false,
        "Type d'alerte invalide."
    );
}


$niveauxAutorises = [
    "faible",
    "moyen",
    "eleve",
    "critique"
];


if (
    !in_array(
        $niveau,
        $niveauxAutorises,
        true
    )
) {

    repondre(
        false,
        "Niveau d'alerte invalide."
    );
}


if ($description === "") {

    repondre(
        false,
        "Veuillez décrire l'incident."
    );
}


/* =====================================================
   VÉRIFIER LA COURSE
   ===================================================== */

$sql = "
    SELECT
        id_client,
        id_chauffeur,
        latitude_client_actuelle,
        longitude_client_actuelle,
        latitude_chauffeur_actuelle,
        longitude_chauffeur_actuelle
    FROM course
    WHERE id_course = ?
    LIMIT 1
";


$stmt =
    $connexion->prepare($sql);

$stmt->bind_param(
    "i",
    $idCourse
);

$stmt->execute();

$resultat =
    $stmt->get_result();

$course =
    $resultat->fetch_assoc();

$stmt->close();


if (!$course) {

    repondre(
        false,
        "Course introuvable."
    );
}


/* =====================================================
   VÉRIFIER QUE LA COURSE APPARTIENT À L'ACTEUR
   ===================================================== */

if ($acteur === "client") {

    if (
        (int) $course["id_client"] !==
        $idClient
    ) {

        repondre(
            false,
            "Cette course ne vous appartient pas."
        );
    }


    $latitude =
        $course[
            "latitude_client_actuelle"
        ];

    $longitude =
        $course[
            "longitude_client_actuelle"
        ];
}
else {

    if (
        (int) $course["id_chauffeur"] !==
        $idChauffeur
    ) {

        repondre(
            false,
            "Cette course ne vous appartient pas."
        );
    }


    $latitude =
        $course[
            "latitude_chauffeur_actuelle"
        ];

    $longitude =
        $course[
            "longitude_chauffeur_actuelle"
        ];
}


/* =====================================================
   ENREGISTRER L'ALERTE
   ===================================================== */

if ($acteur === "client") {

    $sql = "
        INSERT INTO alerte
        (
            id_course,
            id_client,
            id_chauffeur,
            id_policier,
            type_alerte,
            niveau,
            description,
            latitude,
            longitude,
            statut,
            date_creation
        )
        VALUES
        (
            ?,
            ?,
            NULL,
            NULL,
            ?,
            ?,
            ?,
            ?,
            ?,
            'nouvelle',
            NOW()
        )
    ";


    $stmt =
        $connexion->prepare($sql);


    $stmt->bind_param(
        "iisssdd",
        $idCourse,
        $idClient,
        $typeAlerte,
        $niveau,
        $description,
        $latitude,
        $longitude
    );

}
else {

    $sql = "
        INSERT INTO alerte
        (
            id_course,
            id_client,
            id_chauffeur,
            id_policier,
            type_alerte,
            niveau,
            description,
            latitude,
            longitude,
            statut,
            date_creation
        )
        VALUES
        (
            ?,
            NULL,
            ?,
            NULL,
            ?,
            ?,
            ?,
            ?,
            ?,
            'nouvelle',
            NOW()
        )
    ";


    $stmt =
        $connexion->prepare($sql);


    $stmt->bind_param(
        "iisssdd",
        $idCourse,
        $idChauffeur,
        $typeAlerte,
        $niveau,
        $description,
        $latitude,
        $longitude
    );
}


$stmt->execute();

$idAlerte =
    $connexion->insert_id;

$stmt->close();


repondre(
    true,
    "🚨 Signalement envoyé avec succès.",
    [
        "id_alerte" => $idAlerte
    ]
);