<?php

// ======================================================
// FAST TRAJET V2
// RÉCUPÉRER LA COURSE ACTIVE DU CLIENT
// ======================================================

session_start();

header("Content-Type: application/json; charset=UTF-8");


// ======================================================
// FONCTION DE RÉPONSE JSON
// ======================================================

function envoyerReponse(
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


// ======================================================
// VÉRIFIER LA MÉTHODE
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    envoyerReponse(
        false,
        "Méthode non autorisée."
    );
}


// ======================================================
// VÉRIFIER LA SESSION CLIENT
// ======================================================

if (
    !isset($_SESSION["client_connecte"]) ||
    $_SESSION["client_connecte"] !== true
) {

    envoyerReponse(
        false,
        "Vous devez être connecté."
    );
}


// ======================================================
// RÉCUPÉRER L'ID DU CLIENT
// ======================================================

$id_client =
    $_SESSION["id_client"] ?? null;


if (
    $id_client === null ||
    !is_numeric($id_client)
) {

    envoyerReponse(
        false,
        "Identifiant du client introuvable."
    );
}


$id_client = (int) $id_client;


// ======================================================
// CONNEXION BASE DE DONNÉES
// ======================================================

require_once "../../config/database.php";


// ======================================================
// RECHERCHER LA COURSE ACTIVE
//
// On considère active une course qui n'est pas :
// - terminée
// - annulée
// ======================================================

$sql = "
    SELECT

        id_course,
        id_client,
        id_chauffeur,

        lieu_depart,
        latitude_depart,
        longitude_depart,

        lieu_destination,
        latitude_destination,
        longitude_destination,

        distance,
        duree_estimee,

        prix_initial,
        prix_actuel,
        prix_accepte,

        statut_prix,
        statut_course,

        latitude_client_actuelle,
        longitude_client_actuelle,

        latitude_chauffeur_actuelle,
        longitude_chauffeur_actuelle,

        date_creation,
        date_acceptation,
        date_prix_accepte,
        date_debut,
        date_fin,
        date_annulation,
        raison_annulation

    FROM course

    WHERE
        id_client = ?

        AND statut_course NOT IN (
            'terminee',
            'annulee'
        )

    ORDER BY
        id_course DESC

    LIMIT 1
";


// ======================================================
// EXÉCUTION
// ======================================================

try {

    $stmt =
        $connexion->prepare($sql);


    if (!$stmt) {

        envoyerReponse(
            false,
            "Erreur lors de la préparation de la requête.",
            [
                "error" =>
                    $connexion->error
            ]
        );
    }


    $stmt->bind_param(
        "i",
        $id_client
    );


    $stmt->execute();


    $resultat =
        $stmt->get_result();


    // ==================================================
    // AUCUNE COURSE ACTIVE
    // ==================================================

    if ($resultat->num_rows === 0) {

        $stmt->close();

        envoyerReponse(
            true,
            "Aucune course active.",
            [
                "course" => null
            ]
        );
    }


    // ==================================================
    // COURSE TROUVÉE
    // ==================================================

    $course =
        $resultat->fetch_assoc();


    $stmt->close();


    // ==================================================
    // CONVERSIONS UTILES
    // ==================================================

    if ($course["id_course"] !== null) {

        $course["id_course"] =
            (int) $course["id_course"];
    }


    if ($course["id_client"] !== null) {

        $course["id_client"] =
            (int) $course["id_client"];
    }


    if ($course["id_chauffeur"] !== null) {

        $course["id_chauffeur"] =
            (int) $course["id_chauffeur"];
    }


    if ($course["distance"] !== null) {

        $course["distance"] =
            (float) $course["distance"];
    }


    if ($course["duree_estimee"] !== null) {

        $course["duree_estimee"] =
            (int) $course["duree_estimee"];
    }


    if ($course["prix_initial"] !== null) {

        $course["prix_initial"] =
            (float) $course["prix_initial"];
    }


    if ($course["prix_actuel"] !== null) {

        $course["prix_actuel"] =
            (float) $course["prix_actuel"];
    }


    if ($course["prix_accepte"] !== null) {

        $course["prix_accepte"] =
            (float) $course["prix_accepte"];
    }


    // ==================================================
    // RÉPONSE
    // ==================================================

    envoyerReponse(
        true,
        "Course active récupérée.",
        [
            "course" =>
                $course
        ]
    );

}
catch (mysqli_sql_exception $erreur) {

    envoyerReponse(
        false,
        "Erreur lors du chargement de la course.",
        [
            "error" =>
                $erreur->getMessage()
        ]
    );
}
catch (Throwable $erreur) {

    envoyerReponse(
        false,
        "Une erreur interne est survenue.",
        [
            "error" =>
                $erreur->getMessage()
        ]
    );
}

?>