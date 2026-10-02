<?php

// ======================================================
// FAST TRAJET V2
// ACCEPTER UNE COURSE PAR LE CHAUFFEUR
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";
require_once "../../config/historique.php";
require_once "../../config/notification.php";


// ======================================================
// POST UNIQUEMENT
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    reponseErreur(
        "Méthode non autorisée.",
        405
    );
}


// ======================================================
// CHAUFFEUR CONNECTÉ
// ======================================================

$id_chauffeur =
    exigerChauffeur();


$id_chauffeur =
    (int) $id_chauffeur;


// ======================================================
// ID COURSE
// ======================================================

$id_course =
    (int) (
        $_POST["id_course"] ?? 0
    );


if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide.",
        400
    );
}


// ======================================================
// DÉBUT DE LA TRANSACTION
// ======================================================

try {

    $connexion->begin_transaction();


    // ==================================================
    // 1. VÉRIFIER LE CHAUFFEUR
    // ==================================================

    $sqlChauffeur = "
        SELECT
            id_chauffeur,
            disponibilite,
            statut

        FROM chauffeur

        WHERE id_chauffeur = ?

        FOR UPDATE
    ";


    $stmtChauffeur =
        $connexion->prepare(
            $sqlChauffeur
        );


    if (!$stmtChauffeur) {

        throw new Exception(
            "Impossible de vérifier le chauffeur."
        );
    }


    $stmtChauffeur->bind_param(
        "i",
        $id_chauffeur
    );


    $stmtChauffeur->execute();


    $resultatChauffeur =
        $stmtChauffeur->get_result();


    if (
        $resultatChauffeur->num_rows === 0
    ) {

        $stmtChauffeur->close();

        throw new Exception(
            "Chauffeur introuvable."
        );
    }


    $chauffeur =
        $resultatChauffeur->fetch_assoc();


    $stmtChauffeur->close();


    // ==================================================
    // 2. VÉRIFIER LE STATUT DU CHAUFFEUR
    // ==================================================

    if (
        isset($chauffeur["statut"]) &&
        $chauffeur["statut"] !== "actif"
    ) {

        throw new Exception(
            "Votre compte chauffeur n'est pas actif."
        );
    }


    // ==================================================
    // 3. VÉRIFIER LA DISPONIBILITÉ
    // ==================================================

    if (
        (int) $chauffeur["disponibilite"] !== 1
    ) {

        throw new Exception(
            "Vous êtes actuellement indisponible."
        );
    }


    // ==================================================
    // 4. RÉCUPÉRER ET VERROUILLER LA COURSE
    // ==================================================

    $sqlCourse = "
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
            statut_course

        FROM course

        WHERE id_course = ?

        FOR UPDATE
    ";


    $stmtCourse =
        $connexion->prepare(
            $sqlCourse
        );


    if (!$stmtCourse) {

        throw new Exception(
            "Impossible de récupérer la course."
        );
    }


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

        $stmtCourse->close();

        throw new Exception(
            "Cette course n'existe pas."
        );
    }


    $course =
        $resultatCourse->fetch_assoc();


    $stmtCourse->close();


    // ==================================================
    // 5. VÉRIFIER QUE LA COURSE EST ENCORE DISPONIBLE
    // ==================================================

    if (
        $course["statut_course"] !== "en_attente"
    ) {

        throw new Exception(
            "Cette course n'est plus disponible."
        );
    }


    if (
        $course["id_chauffeur"] !== null
    ) {

        throw new Exception(
            "Cette course a déjà été acceptée par un autre chauffeur."
        );
    }


    // ==================================================
    // 6. ATTRIBUER LA COURSE AU CHAUFFEUR
    // ==================================================

    $sqlUpdateCourse = "
        UPDATE course

        SET
            id_chauffeur = ?,
            statut_course = 'acceptee',
            date_acceptation = NOW()

        WHERE
            id_course = ?
            AND id_chauffeur IS NULL
            AND statut_course = 'en_attente'
    ";


    $stmtUpdateCourse =
        $connexion->prepare(
            $sqlUpdateCourse
        );


    if (!$stmtUpdateCourse) {

        throw new Exception(
            "Impossible de préparer l'acceptation de la course."
        );
    }


    $stmtUpdateCourse->bind_param(
        "ii",
        $id_chauffeur,
        $id_course
    );


    $stmtUpdateCourse->execute();


    if (
        $stmtUpdateCourse->affected_rows !== 1
    ) {

        $stmtUpdateCourse->close();

        throw new Exception(
            "La course vient d'être acceptée par un autre chauffeur."
        );
    }


    $stmtUpdateCourse->close();


    // ==================================================
    // 7. RENDRE LE CHAUFFEUR INDISPONIBLE
    // ==================================================

    $sqlDisponibilite = "
        UPDATE chauffeur

        SET
            disponibilite = 0

        WHERE
            id_chauffeur = ?
    ";


    $stmtDisponibilite =
        $connexion->prepare(
            $sqlDisponibilite
        );


    if (!$stmtDisponibilite) {

        throw new Exception(
            "Impossible de modifier la disponibilité du chauffeur."
        );
    }


    $stmtDisponibilite->bind_param(
        "i",
        $id_chauffeur
    );


    $stmtDisponibilite->execute();


    $stmtDisponibilite->close();


    // ==================================================
    // 8. HISTORIQUE
    // ==================================================

    enregistrerHistorique(
        $connexion,
        "course_acceptee",
        "Le chauffeur a accepté la course.",
        null,
        $id_chauffeur,
        null,
        $id_course
    );


    // ==================================================
    // 9. NOTIFIER LE CLIENT
    // ==================================================

    creerNotificationClient(
        $connexion,
        (int) $course["id_client"],
        "course_acceptee",
        "🚕 Course acceptée",
        "Un chauffeur a accepté votre course.",
        $id_course
    );


    // ==================================================
    // 10. VALIDER LA TRANSACTION
    // ==================================================

    $connexion->commit();


    // ==================================================
    // 11. RÉPONSE
    // ==================================================

    reponseSucces([

        "message" =>
            "Course acceptée avec succès.",

        "id_course" =>
            $id_course,

        "id_chauffeur" =>
            $id_chauffeur,

        "id_client" =>
            (int) $course["id_client"],

        "statut_course" =>
            "acceptee",

        "disponibilite" =>
            0

    ]);

}
catch (Throwable $erreur) {

    try {

        $connexion->rollback();

    }
    catch (Throwable $ignore) {
        // Rien à faire
    }


    reponseErreur(
        $erreur->getMessage(),
        400
    );
}

?>