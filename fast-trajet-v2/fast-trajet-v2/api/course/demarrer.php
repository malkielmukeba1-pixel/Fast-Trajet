<?php

// ======================================================
// FAST TRAJET V2
// DÉMARRER UNE COURSE
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
    // RÉCUPÉRER LA COURSE
    // ==================================================

    $sql = "
        SELECT

            id_course,
            id_client,
            id_chauffeur,

            prix_accepte,
            statut_prix,
            statut_course,

            date_debut

        FROM course

        WHERE id_course = ?

        FOR UPDATE
    ";


    $stmt =
        $connexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            "Impossible de récupérer la course."
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

        throw new Exception(
            "Course introuvable."
        );
    }


    $course =
        $resultat->fetch_assoc();


    $stmt->close();


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
    // COURSE DÉJÀ EN COURS
    // ==================================================

    if (
        $course["statut_course"] ===
        "en_cours"
    ) {

        throw new Exception(
            "Cette course est déjà en cours."
        );
    }


    // ==================================================
    // PRIX OBLIGATOIREMENT ACCEPTÉ
    // ==================================================

    if (
        $course["statut_prix"] !==
        "accepte"
    ) {

        throw new Exception(
            "Le prix doit être accepté avant de démarrer la course."
        );
    }


    if (
        $course["prix_accepte"] === null ||
        (float) $course["prix_accepte"] <= 0
    ) {

        throw new Exception(
            "Aucun prix final valide n'a été enregistré."
        );
    }


    // ==================================================
    // STATUT AUTORISÉ
    // ==================================================

    if (
        $course["statut_course"] !==
        "prix_accepte"
    ) {

        throw new Exception(
            "La course ne peut pas encore être démarrée."
        );
    }


    // ==================================================
    // DÉMARRER
    // ==================================================

    $sqlUpdate = "
        UPDATE course

        SET
            statut_course = 'en_cours',
            date_debut = NOW()

        WHERE
            id_course = ?
            AND id_chauffeur = ?
            AND statut_prix = 'accepte'
            AND statut_course = 'prix_accepte'
    ";


    $stmtUpdate =
        $connexion->prepare(
            $sqlUpdate
        );


    if (!$stmtUpdate) {

        throw new Exception(
            "Impossible de préparer le démarrage de la course."
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
            "Impossible de démarrer cette course."
        );
    }


    $stmtUpdate->close();


    // ==================================================
    // HISTORIQUE
    // ==================================================

    enregistrerHistorique(
        $connexion,
        "course_demarre",
        "Le chauffeur a démarré la course.",
        (int) $course["id_client"],
        $id_chauffeur,
        null,
        $id_course
    );


    // ==================================================
    // VALIDER
    // ==================================================

    $connexion->commit();


    // ======================================================
// NOTIFIER LE CLIENT
// ======================================================

$notificationCreee =
    creerNotificationClient(
        $connexion,
        (int) $course["id_client"],
        "course_demarree",
        "🚕 Votre chauffeur est en route",
        "Votre chauffeur s'est mis en route. Vous pouvez suivre sa progression en temps réel sur la carte.",
        $id_course
    );


if (!$notificationCreee) {

    error_log(
        "FAST TRAJET : impossible de créer la notification de démarrage pour la course " .
        $id_course
    );
}


    reponseSucces([

        "message" =>
            "Course démarrée avec succès.",

        "id_course" =>
            $id_course,

        "statut_course" =>
            "en_cours"

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