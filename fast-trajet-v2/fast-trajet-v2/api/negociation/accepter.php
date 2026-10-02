<?php

// ======================================================
// FAST TRAJET V2
// ACCEPTER UN PRIX
// ======================================================
require_once "../../config/notification.php";
require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/auth.php";
require_once "../../config/response.php";
require_once "../../config/historique.php";


// ======================================================
// POST
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    reponseErreur(
        "Méthode non autorisée.",
        405
    );
}


// ======================================================
// IDENTIFIER L'UTILISATEUR
// ======================================================

$estClient =
    isset($_SESSION["client_connecte"]) &&
    $_SESSION["client_connecte"] === true;

$estChauffeur =
    isset($_SESSION["chauffeur_connecte"]) &&
    $_SESSION["chauffeur_connecte"] === true;


if (!$estClient && !$estChauffeur) {

    reponseErreur(
        "Utilisateur non connecté.",
        401
    );
}


// ======================================================
// ID COURSE
// ======================================================

$id_course =
    intval($_POST["id_course"] ?? 0);


if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide."
    );
}


// ======================================================
// TRANSACTION
// ======================================================

$connexion->begin_transaction();

try {

    // ==================================================
    // RÉCUPÉRER COURSE
    // ==================================================

    $sqlCourse = "
        SELECT
            id_course,
            id_client,
            id_chauffeur,
            prix_actuel,
            prix_accepte,
            statut_prix,
            statut_course

        FROM course

        WHERE id_course = ?

        FOR UPDATE
    ";

    $stmtCourse =
        $connexion->prepare($sqlCourse);

    if (!$stmtCourse) {

        throw new Exception(
            "Erreur de préparation de la course."
        );
    }

    $stmtCourse->bind_param(
        "i",
        $id_course
    );

    $stmtCourse->execute();

    $resultatCourse =
        $stmtCourse->get_result();

    if ($resultatCourse->num_rows === 0) {

        $stmtCourse->close();

        throw new Exception(
            "Course introuvable."
        );
    }

    $course =
        $resultatCourse->fetch_assoc();

    $stmtCourse->close();


    // ==================================================
    // VÉRIFIER L'UTILISATEUR
    // ==================================================

    if ($estClient) {

        $id_client =
            intval($_SESSION["id_client"]);

        if (
            intval($course["id_client"])
            !== $id_client
        ) {

            throw new Exception(
                "Vous n'avez pas accès à cette course."
            );
        }
    }

    else {

        $id_chauffeur =
            intval($_SESSION["id_chauffeur"]);

        if (
            $course["id_chauffeur"] === null ||
            intval($course["id_chauffeur"])
            !== $id_chauffeur
        ) {

            throw new Exception(
                "Vous n'avez pas accès à cette course."
            );
        }
    }


    // ==================================================
    // VÉRIFIER PRIX ACTUEL
    // ==================================================

    if (
        $course["prix_actuel"] === null ||
        (float)$course["prix_actuel"] <= 0
    ) {

        throw new Exception(
            "Aucun prix à accepter."
        );
    }


    // ==================================================
    // VÉRIFIER STATUT
    // ==================================================

    if (
        $course["statut_course"] !== "negociation" &&
        $course["statut_prix"] !== "propose"
    ) {

        throw new Exception(
            "Aucun prix n'est actuellement disponible."
        );
    }


    // ==================================================
    // RÉCUPÉRER LA PROPOSITION ACTIVE
    // ==================================================

    $sqlProposition = "
        SELECT
            id_negociation,
            prix_propose,
            expediteur

        FROM negociation

        WHERE id_course = ?
        AND statut = 'propose'

        ORDER BY date_proposition DESC

        LIMIT 1
    ";

    $stmtProposition =
        $connexion->prepare(
            $sqlProposition
        );

    if (!$stmtProposition) {

        throw new Exception(
            "Erreur lors de la récupération de la proposition."
        );
    }

    $stmtProposition->bind_param(
        "i",
        $id_course
    );

    $stmtProposition->execute();

    $resultatProposition =
        $stmtProposition->get_result();

    if (
        $resultatProposition->num_rows === 0
    ) {

        $stmtProposition->close();

        throw new Exception(
            "Aucune proposition active."
        );
    }

    $proposition =
        $resultatProposition->fetch_assoc();

    $stmtProposition->close();


    $prixFinal =
        (float)$proposition["prix_propose"];


    // ==================================================
    // ACCEPTER LA PROPOSITION
    // ==================================================

    $sqlNegociation = "
        UPDATE negociation
        SET statut = 'accepte'
        WHERE id_negociation = ?
    ";

    $stmtNegociation =
        $connexion->prepare(
            $sqlNegociation
        );

    if (!$stmtNegociation) {

        throw new Exception(
            "Erreur lors de l'acceptation."
        );
    }

    $id_negociation =
        (int)$proposition["id_negociation"];

    $stmtNegociation->bind_param(
        "i",
        $id_negociation
    );

    if (!$stmtNegociation->execute()) {

        $stmtNegociation->close();

        throw new Exception(
            "Impossible d'accepter la proposition."
        );
    }

    $stmtNegociation->close();


    // ==================================================
    // METTRE À JOUR LA COURSE
    // ==================================================

    $sqlUpdate = "
        UPDATE course
        SET
            prix_accepte = ?,
            prix_actuel = ?,
            statut_prix = 'accepte',
            statut_course = 'prix_accepte',
            date_prix_accepte = NOW()
            

        WHERE id_course = ?
    ";

    $stmtUpdate =
        $connexion->prepare(
            $sqlUpdate
        );

    if (!$stmtUpdate) {

        throw new Exception(
            "Erreur lors de la validation du prix."
        );
    }

    $stmtUpdate->bind_param(
        "ddi",
        $prixFinal,
        $prixFinal,
        $id_course
    );

    if (!$stmtUpdate->execute()) {

        $stmtUpdate->close();

        throw new Exception(
            "Impossible de valider le prix."
        );
    }

    $stmtUpdate->close();


    // ==================================================
    // COMMIT
    // ==================================================

    $connexion->commit();

    enregistrerHistorique(
    $connexion,
    "prix_accepte",
    "Prix final accepté : " .
    number_format($prixFinal, 2, ".", "") .
    " $.",
    (int)$course["id_client"],
    (int)$course["id_chauffeur"],
    null,
    $id_course
);

    // ==================================================
// NOTIFIER L'AUTRE PARTIE
// ==================================================

if ($estClient) {

    creerNotificationChauffeur(
        $connexion,
        (int)$course["id_chauffeur"],
        "prix_accepte",
        "✅ Prix accepté",
        "Le client a accepté le prix de " .
        number_format($prixFinal, 2, ".", "") .
        " $. La course peut maintenant démarrer.",
        $id_course
    );

}
else {

    creerNotificationClient(
        $connexion,
        (int)$course["id_client"],
        "prix_accepte",
        "✅ Prix accepté",
        "Le chauffeur a accepté le prix de " .
        number_format($prixFinal, 2, ".", "") .
        " $.",
        $id_course
    );

}


    // ==================================================
    // RÉPONSE
    // ==================================================

    reponseSucces([

        "message" =>
            "Prix accepté avec succès.",

        "id_course" =>
            $id_course,

        "id_negociation" =>
            $id_negociation,

        "prix_accepte" =>
            $prixFinal,

        "statut_prix" =>
            "accepte",

        "statut_course" =>
            "prix_accepte"

    ]);

}

catch (Exception $erreur) {

    $connexion->rollback();

    reponseErreur(
        $erreur->getMessage()
    );
}

?>