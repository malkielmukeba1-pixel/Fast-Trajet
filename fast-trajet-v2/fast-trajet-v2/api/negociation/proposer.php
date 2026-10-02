<?php

// ======================================================
// FAST TRAJET V2
// PROPOSER UN PRIX
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
// DONNÉES
// ======================================================

$id_course =
    (int) ($_POST["id_course"] ?? 0);

$prix =
    $_POST["prix"] ?? null;


if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide.",
        400
    );
}


if (
    $prix === null ||
    $prix === "" ||
    !is_numeric($prix)
) {

    reponseErreur(
        "Le prix est invalide.",
        400
    );
}


$prix = (float) $prix;


if ($prix <= 0) {

    reponseErreur(
        "Le prix doit être supérieur à zéro.",
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
            prix_initial,
            prix_actuel,
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
    // LA COURSE DOIT AVOIR UN CHAUFFEUR
    // ==================================================

    if ($course["id_chauffeur"] === null) {

        throw new Exception(
            "Aucun chauffeur n'est encore associé à cette course."
        );
    }


    // ==================================================
    // IDENTIFIER L'EXPÉDITEUR
    // ==================================================

    if ($estClient) {

        $id_client =
            (int) $_SESSION["id_client"];


        if (
            (int) $course["id_client"] !==
            $id_client
        ) {

            throw new Exception(
                "Vous n'avez pas accès à cette course."
            );
        }


        $id_chauffeur =
            (int) $course["id_chauffeur"];


        $expediteur =
            "client";

    } else {

        $id_chauffeur =
            (int) $_SESSION["id_chauffeur"];


        if (
            (int) $course["id_chauffeur"] !==
            $id_chauffeur
        ) {

            throw new Exception(
                "Cette course ne vous est pas attribuée."
            );
        }


        $id_client =
            (int) $course["id_client"];


        $expediteur =
            "chauffeur";
    }


    // ==================================================
    // STATUT AUTORISÉ
    // ==================================================

    if (
        !in_array(
            $course["statut_course"],
            [
                "acceptee",
                "negociation"
            ],
            true
        )
    ) {

        throw new Exception(
            "La négociation n'est plus disponible pour cette course."
        );
    }


    // ==================================================
    // REMPLACER L'ANCIENNE PROPOSITION ACTIVE
    // ==================================================

    $sqlAncienne = "
        UPDATE negociation

        SET statut = 'remplace'

        WHERE
            id_course = ?
            AND statut = 'propose'
    ";


    $stmtAncienne =
        $connexion->prepare($sqlAncienne);


    if (!$stmtAncienne) {

        throw new Exception(
            "Impossible de mettre à jour l'ancienne proposition."
        );
    }


    $stmtAncienne->bind_param(
        "i",
        $id_course
    );


    $stmtAncienne->execute();

    $stmtAncienne->close();


    // ==================================================
    // CRÉER LA NOUVELLE PROPOSITION
    // ==================================================

    $sqlInsert = "
        INSERT INTO negociation
        (
            id_course,
            id_client,
            id_chauffeur,
            expediteur,
            prix_propose,
            statut
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'propose'
        )
    ";


    $stmtInsert =
        $connexion->prepare($sqlInsert);


    if (!$stmtInsert) {

        throw new Exception(
            "Impossible de créer la proposition."
        );
    }


    $stmtInsert->bind_param(
        "iiisd",
        $id_course,
        $id_client,
        $id_chauffeur,
        $expediteur,
        $prix
    );


    $stmtInsert->execute();


    $id_negociation =
        (int) $connexion->insert_id;


    $stmtInsert->close();


    // ==================================================
    // METTRE À JOUR LA COURSE
    //
    // prix_initial n'est enregistré qu'une seule fois.
    // prix_actuel change à chaque contre-proposition.
    // ==================================================

    $sqlUpdate = "
        UPDATE course

        SET
            prix_initial =
                COALESCE(prix_initial, ?),

            prix_actuel = ?,

            statut_prix = 'negociation',

            statut_course = 'negociation'

        WHERE id_course = ?
    ";


    $stmtUpdate =
        $connexion->prepare($sqlUpdate);


    if (!$stmtUpdate) {

        throw new Exception(
            "Impossible de mettre à jour le prix de la course."
        );
    }


    $stmtUpdate->bind_param(
        "ddi",
        $prix,
        $prix,
        $id_course
    );


    $stmtUpdate->execute();

    $stmtUpdate->close();


    // ==================================================
    // HISTORIQUE
    // ==================================================

    if ($expediteur === "client") {

        enregistrerHistorique(
            $connexion,
            "proposition_client",
            "Le client a proposé " .
            number_format(
                $prix,
                2,
                ".",
                ""
            ) .
            " $.",
            $id_client,
            $id_chauffeur,
            null,
            $id_course
        );

    } else {

        enregistrerHistorique(
            $connexion,
            "proposition_chauffeur",
            "Le chauffeur a proposé " .
            number_format(
                $prix,
                2,
                ".",
                ""
            ) .
            " $.",
            $id_client,
            $id_chauffeur,
            null,
            $id_course
        );
    }


    // ==================================================
    // NOTIFIER L'AUTRE UTILISATEUR
    // ==================================================

    if ($expediteur === "chauffeur") {

        $notificationCreee =
            creerNotificationClient(
                $connexion,
                $id_client,
                "nouvelle_proposition",
                "💰 Nouvelle proposition de prix",
                "Le chauffeur propose " .
                number_format(
                    $prix,
                    2,
                    ".",
                    ""
                ) .
                " $.",
                $id_course
            );

    } else {

        $notificationCreee =
            creerNotificationChauffeur(
                $connexion,
                $id_chauffeur,
                "nouvelle_proposition",
                "💰 Nouvelle proposition de prix",
                "Le client propose " .
                number_format(
                    $prix,
                    2,
                    ".",
                    ""
                ) .
                " $.",
                $id_course
            );
    }


    // ==================================================
    // IMPORTANT :
    // ne pas ignorer une notification qui échoue
    // ==================================================

    if (!$notificationCreee) {

        throw new Exception(
            "La proposition a été préparée, mais la notification n'a pas pu être créée."
        );
    }


    // ==================================================
    // VALIDER L'ENSEMBLE
    // ==================================================

    $connexion->commit();


    // ==================================================
    // RÉPONSE
    // ==================================================

    reponseSucces([

        "message" =>
            "Proposition de prix envoyée.",

        "id_course" =>
            $id_course,

        "id_negociation" =>
            $id_negociation,

        "expediteur" =>
            $expediteur,

        "prix" =>
            $prix,

        "statut_prix" =>
            "negociation",

        "statut_course" =>
            "negociation"

    ]);

}
catch (Throwable $erreur) {

    try {

        $connexion->rollback();

    } catch (Throwable $ignore) {
    }


    reponseErreur(
        $erreur->getMessage(),
        400
    );
}

?>