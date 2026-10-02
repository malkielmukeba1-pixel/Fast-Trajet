<?php

// ======================================================
// FAST TRAJET V2
// TERMINER UNE COURSE
// ======================================================
require_once "../../config/notification.php";
require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";
require_once "../../config/historique.php";


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
    (int) exigerChauffeur();


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
// TRANSACTION
// ======================================================

try {

    $connexion->begin_transaction();


    // ==================================================
    // RÉCUPÉRER ET VERROUILLER LA COURSE
    // ==================================================

    $sqlCourse = "
        SELECT

            id_course,
            id_client,
            id_chauffeur,
            statut_course,
            statut_prix,
            prix_accepte,
            date_debut,
            date_fin

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
            "Impossible de vérifier la course."
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
            "Course introuvable."
        );
    }


    $course =
        $resultatCourse->fetch_assoc();


    $stmtCourse->close();


    // ==================================================
    // VÉRIFIER LE CHAUFFEUR
    // ==================================================

    if (
        $course["id_chauffeur"] === null ||
        (int) $course["id_chauffeur"] !==
        $id_chauffeur
    ) {

        throw new Exception(
            "Cette course ne vous est pas attribuée."
        );
    }


    // ==================================================
    // COURSE DÉJÀ TERMINÉE
    // ==================================================

    if (
        $course["statut_course"] ===
        "terminee"
    ) {

        throw new Exception(
            "Cette course est déjà terminée."
        );
    }


    // ==================================================
    // SEULE UNE COURSE EN COURS PEUT ÊTRE TERMINÉE
    // ==================================================

    if (
        $course["statut_course"] !==
        "en_cours"
    ) {

        throw new Exception(
            "Seule une course en cours peut être terminée."
        );
    }


    // ==================================================
    // TERMINER LA COURSE
    // ==================================================

    $sqlUpdate = "
        UPDATE course

        SET
            statut_course = 'terminee',
            date_fin = NOW()

        WHERE
            id_course = ?
            AND id_chauffeur = ?
            AND statut_course = 'en_cours'
    ";


    $stmtUpdate =
        $connexion->prepare(
            $sqlUpdate
        );


    if (!$stmtUpdate) {

        throw new Exception(
            "Impossible de préparer la fin de la course."
        );
    }


    $stmtUpdate->bind_param(
        "ii",
        $id_course,
        $id_chauffeur
    );


    $stmtUpdate->execute();


    if (
        $stmtUpdate->affected_rows !== 1
    ) {

        $stmtUpdate->close();

        throw new Exception(
            "Impossible de terminer la course."
        );
    }


    $stmtUpdate->close();


    // ==================================================
    // RENDRE LE CHAUFFEUR DISPONIBLE
    // ==================================================

    $sqlChauffeur = "
        UPDATE chauffeur

        SET
            disponibilite = 1

        WHERE
            id_chauffeur = ?
    ";


    $stmtChauffeur =
        $connexion->prepare(
            $sqlChauffeur
        );


    if (!$stmtChauffeur) {

        throw new Exception(
            "Impossible de mettre à jour la disponibilité du chauffeur."
        );
    }


    $stmtChauffeur->bind_param(
        "i",
        $id_chauffeur
    );


    $stmtChauffeur->execute();

    $stmtChauffeur->close();


    // ==================================================
    // HISTORIQUE
    // ==================================================

    enregistrerHistorique(
        $connexion,
        "course_terminee",
        "La course a été terminée par le chauffeur.",
        (int) $course["id_client"],
        $id_chauffeur,
        null,
        $id_course
    );


    // ==================================================
    // VALIDER
    // ==================================================

    $connexion->commit();

    // ==================================================
// NOTIFIER LE CLIENT DE LA FIN DE COURSE
// ==================================================

$notificationCreee =
    creerNotificationClient(
        $connexion,
        (int) $course["id_client"],
        "course_terminee",
        "🏁 Course terminée",
        
        $id_course
    );


if (!$notificationCreee) {

    error_log(
        "FAST TRAJET : impossible de créer la notification de fin pour la course " .
        $id_course
    );
}


    // ==================================================
    // SUCCÈS
    // ==================================================

    reponseSucces([

        "message" =>
            "Course terminée avec succès.",

        "id_course" =>
            $id_course,

        "statut_course" =>
            "terminee",

        "chauffeur_disponible" =>
            true

    ]);

}
catch (Throwable $erreur) {

    try {

        $connexion->rollback();

    }
    catch (Throwable $ignore) {
    }


    reponseErreur(
        $erreur->getMessage(),
        400
    );
}

?>