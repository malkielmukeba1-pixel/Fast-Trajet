<?php

// ======================================================
// FAST TRAJET V2
// CRÉATION D'UNE COURSE PAR LE CLIENT
// ======================================================

session_start();

header("Content-Type: application/json; charset=UTF-8");


// ======================================================
// RÉPONSE JSON
// ======================================================

function reponseJson(
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
// MÉTHODE HTTP
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    reponseJson(
        false,
        "Méthode non autorisée."
    );
}


// ======================================================
// SESSION CLIENT
// ======================================================

if (
    !isset($_SESSION["client_connecte"]) ||
    $_SESSION["client_connecte"] !== true
) {

    reponseJson(
        false,
        "Vous devez être connecté."
    );
}


// ======================================================
// ID CLIENT
// ======================================================

$id_client =
    $_SESSION["id_client"] ?? null;


if (
    $id_client === null ||
    !is_numeric($id_client)
) {

    reponseJson(
        false,
        "Identifiant du client introuvable."
    );
}


$id_client =
    (int) $id_client;


// ======================================================
// CONNEXION BASE DE DONNÉES
// ======================================================

require_once "../../config/database.php";


if (!isset($connexion)) {

    reponseJson(
        false,
        "Connexion à la base de données indisponible."
    );
}


// ======================================================
// RÉCUPÉRER LES DONNÉES
// ======================================================

$lieu_depart =
    trim(
        $_POST["lieu_depart"] ?? ""
    );


$lieu_destination =
    trim(
        $_POST["lieu_destination"] ?? ""
    );


$latitude_depart =
    $_POST["latitude_depart"] ?? null;


$longitude_depart =
    $_POST["longitude_depart"] ?? null;


$latitude_destination =
    $_POST["latitude_destination"] ?? null;


$longitude_destination =
    $_POST["longitude_destination"] ?? null;


$distance =
    $_POST["distance"] ?? null;


$duree_estimee =
    $_POST["duree_estimee"] ?? null;


// ======================================================
// LIEU DE DÉPART
// ======================================================

if ($lieu_depart === "") {

    $lieu_depart =
        "Ma position actuelle";
}


// ======================================================
// DESTINATION
// ======================================================

if ($lieu_destination === "") {

    reponseJson(
        false,
        "Veuillez indiquer votre destination."
    );
}


// ======================================================
// POSITION DE DÉPART OBLIGATOIRE
// ======================================================

if (
    $latitude_depart === null ||
    $longitude_depart === null ||
    $latitude_depart === "" ||
    $longitude_depart === ""
) {

    reponseJson(
        false,
        "Votre position est obligatoire."
    );
}


if (
    !is_numeric($latitude_depart) ||
    !is_numeric($longitude_depart)
) {

    reponseJson(
        false,
        "Les coordonnées GPS de départ sont invalides."
    );
}


$latitude_depart =
    (float) $latitude_depart;


$longitude_depart =
    (float) $longitude_depart;


// ======================================================
// VÉRIFIER LES LIMITES GPS
// ======================================================

if (
    $latitude_depart < -90 ||
    $latitude_depart > 90
) {

    reponseJson(
        false,
        "Latitude de départ invalide."
    );
}


if (
    $longitude_depart < -180 ||
    $longitude_depart > 180
) {

    reponseJson(
        false,
        "Longitude de départ invalide."
    );
}


// ======================================================
// LATITUDE DESTINATION
// ======================================================

if (
    $latitude_destination === null ||
    $latitude_destination === ""
) {

    $latitude_destination =
        null;

} else {

    if (!is_numeric($latitude_destination)) {

        reponseJson(
            false,
            "Latitude de destination invalide."
        );
    }


    $latitude_destination =
        (float) $latitude_destination;
}


// ======================================================
// LONGITUDE DESTINATION
// ======================================================

if (
    $longitude_destination === null ||
    $longitude_destination === ""
) {

    $longitude_destination =
        null;

} else {

    if (!is_numeric($longitude_destination)) {

        reponseJson(
            false,
            "Longitude de destination invalide."
        );
    }


    $longitude_destination =
        (float) $longitude_destination;
}


// ======================================================
// DISTANCE
// ======================================================

if (
    $distance === null ||
    $distance === ""
) {

    $distance =
        null;

} else {

    if (!is_numeric($distance)) {

        reponseJson(
            false,
            "La distance est invalide."
        );
    }


    $distance =
        (float) $distance;
}


// ======================================================
// DURÉE ESTIMÉE
// ======================================================

if (
    $duree_estimee === null ||
    $duree_estimee === ""
) {

    $duree_estimee =
        null;

} else {

    if (!is_numeric($duree_estimee)) {

        reponseJson(
            false,
            "La durée estimée est invalide."
        );
    }


    $duree_estimee =
        (int) $duree_estimee;
}


// ======================================================
// STATUTS INITIAUX
// ======================================================

$statut_prix =
    "non_defini";


$statut_course =
    "en_attente";


// ======================================================
// POSITION ACTUELLE DU CLIENT
// ======================================================

$latitude_client_actuelle =
    $latitude_depart;


$longitude_client_actuelle =
    $longitude_depart;


// ======================================================
// REQUÊTE SQL
//
// TABLE course :
//
// id_course
// id_client
// id_chauffeur
// lieu_depart
// latitude_depart
// longitude_depart
// lieu_destination
// latitude_destination
// longitude_destination
// distance
// duree_estimee
// prix_initial
// prix_actuel
// prix_accepte
// statut_prix
// statut_course
// latitude_client_actuelle
// longitude_client_actuelle
// latitude_chauffeur_actuelle
// longitude_chauffeur_actuelle
// date_creation
// date_acceptation
// date_prix_accepte
// date_debut
// date_fin
// date_annulation
// raison_annulation
// ======================================================

$sql = "
    INSERT INTO course
    (
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

        statut_prix,
        statut_course,

        latitude_client_actuelle,
        longitude_client_actuelle,

        date_creation
    )
    VALUES
    (
        ?,
        NULL,

        ?,
        ?,
        ?,

        ?,
        ?,
        ?,

        ?,
        ?,

        ?,
        ?,

        ?,
        ?,

        NOW()
    )
";


// ======================================================
// EXÉCUTER
// ======================================================

try {

    $stmt =
        $connexion->prepare($sql);


    if (!$stmt) {

        reponseJson(
            false,
            "Impossible de préparer la création de la course.",
            [
                "error" =>
                    $connexion->error
            ]
        );
    }


    // ==================================================
    // IMPORTANT
    //
    // PHP 8.2 permet d'envoyer directement
    // les paramètres avec execute([...]).
    //
    // Nous supprimons donc bind_param().
    // ==================================================

    $stmt->execute([
        $id_client,

        $lieu_depart,
        $latitude_depart,
        $longitude_depart,

        $lieu_destination,
        $latitude_destination,
        $longitude_destination,

        $distance,
        $duree_estimee,

        $statut_prix,
        $statut_course,

        $latitude_client_actuelle,
        $longitude_client_actuelle
    ]);


    // ==================================================
    // ID COURSE
    // ==================================================

    $id_course =
        (int) $connexion->insert_id;


    $stmt->close();


    // ==================================================
    // SUCCÈS
    // ==================================================

    reponseJson(
        true,
        "Votre demande de taxi a été enregistrée.",
        [
            "id_course" =>
                $id_course,

            "id_client" =>
                $id_client,

            "lieu_depart" =>
                $lieu_depart,

            "lieu_destination" =>
                $lieu_destination,

            "latitude_depart" =>
                $latitude_depart,

            "longitude_depart" =>
                $longitude_depart,

            "latitude_destination" =>
                $latitude_destination,

            "longitude_destination" =>
                $longitude_destination,

            "distance" =>
                $distance,

            "duree_estimee" =>
                $duree_estimee,

            "statut_prix" =>
                $statut_prix,

            "statut_course" =>
                $statut_course
        ]
    );

}
catch (mysqli_sql_exception $erreur) {

    reponseJson(
        false,
        "Erreur lors de l'enregistrement de la course.",
        [
            "error" =>
                $erreur->getMessage()
        ]
    );
}
catch (Throwable $erreur) {

    reponseJson(
        false,
        "Une erreur interne est survenue.",
        [
            "error" =>
                $erreur->getMessage()
        ]
    );
}

?>