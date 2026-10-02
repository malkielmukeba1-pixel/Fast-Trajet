<?php

// ======================================================
// FAST TRAJET V2
// DÉTAILS D'UNE COURSE
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";


// ======================================================
// UTILISATEUR CONNECTÉ
// ======================================================

if (!utilisateurConnecte()) {

    reponseErreur(
        "Utilisateur non connecté.",
        401
    );
}


// ======================================================
// ID COURSE
// ======================================================

$id_course =
    intval(
        $_GET["id_course"]
        ?? $_POST["id_course"]
        ?? 0
    );


if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide."
    );
}


// ======================================================
// RÉCUPÉRER LA COURSE
// ======================================================

$sql = "
    SELECT

        c.id_course,
        c.id_client,
        c.id_chauffeur,

        c.lieu_depart,
        c.latitude_depart,
        c.longitude_depart,

        c.lieu_destination,
        c.latitude_destination,
        c.longitude_destination,

        c.distance,
        c.duree_estimee,

        c.prix_initial,
        c.prix_actuel,
        c.prix_accepte,

        c.statut_prix,
        c.statut_course,

        c.date_creation,
        c.date_acceptation,
        c.date_prix_accepte,
        c.date_debut,
        c.date_fin,
        c.date_annulation,

        c.raison_annulation,

        ch.nom AS chauffeur_nom,
        ch.prenom AS chauffeur_prenom,
        ch.telephone AS chauffeur_telephone,

        v.id_vehicule,
        v.marque,
        v.modele,
        v.immatriculation,
        v.couleur,
        v.nombre_places,
        v.photo_vehicule

    FROM course c

    LEFT JOIN chauffeur ch
        ON c.id_chauffeur = ch.id_chauffeur

    LEFT JOIN vehicule v
        ON v.id_chauffeur = ch.id_chauffeur

    WHERE c.id_course = ?

    LIMIT 1
";


$stmt =
    $connexion->prepare($sql);


if (!$stmt) {

    reponseErreur(
        "Erreur lors de la préparation de la recherche."
    );
}


$stmt->bind_param(
    "i",
    $id_course
);

$stmt->execute();

$resultat =
    $stmt->get_result();


if ($resultat->num_rows === 0) {

    $stmt->close();

    reponseErreur(
        "Course introuvable.",
        404
    );
}


$course =
    $resultat->fetch_assoc();


$stmt->close();


// ======================================================
// AUTORISATION CLIENT
// ======================================================

if (
    isset($_SESSION["client_connecte"]) &&
    $_SESSION["client_connecte"] === true
) {

    $id_client =
        intval(
            $_SESSION["id_client"]
        );


    if (
        intval($course["id_client"])
        !== $id_client
    ) {

        reponseErreur(
            "Vous n'avez pas accès à cette course.",
            403
        );
    }
}


// ======================================================
// AUTORISATION CHAUFFEUR
// ======================================================

elseif (
    isset($_SESSION["chauffeur_connecte"]) &&
    $_SESSION["chauffeur_connecte"] === true
) {

    $id_chauffeur =
        intval(
            $_SESSION["id_chauffeur"]
        );


    if (
        $course["id_chauffeur"] === null ||
        intval($course["id_chauffeur"])
        !== $id_chauffeur
    ) {

        reponseErreur(
            "Vous n'avez pas accès à cette course.",
            403
        );
    }
}


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([
    "course" => $course
]);

?>