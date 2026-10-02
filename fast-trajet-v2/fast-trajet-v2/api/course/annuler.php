<?php

// ======================================================
// FAST TRAJET V2
// ANNULER UNE COURSE
// ======================================================

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
// IDENTIFICATION
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
// COURSE
// ======================================================

$id_course =
    intval(
        $_POST["id_course"] ?? 0
    );


if ($id_course <= 0) {

    reponseErreur(
        "Identifiant de course invalide."
    );
}


$raison =
    trim(
        $_POST["raison"] ?? ""
    );


if ($raison === "") {

    reponseErreur(
        "Veuillez indiquer la raison de l'annulation."
    );
}


// ======================================================
// TRANSACTION
// ======================================================

$connexion->begin_transaction();

try {

    // ----------------------------------------------
    // Récupérer la course
    // ----------------------------------------------

    $sql = "
        SELECT
            id_course,
            id_client,
            id_chauffeur,
            statut_course
        FROM course
        WHERE id_course = ?
        FOR UPDATE
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Erreur de préparation."
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


    // ----------------------------------------------
    // Vérification propriétaire
    // ----------------------------------------------

    if ($estClient) {

        $id_client =
            intval(
                $_SESSION["id_client"]
            );

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
            intval(
                $_SESSION["id_chauffeur"]
            );

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


    // ----------------------------------------------
    // Vérifier le statut
    // ----------------------------------------------

    $statutsAnnulables = [
        "en_attente",
        "acceptee",
        "negociation"
    ];


    if (
        !in_array(
            $course["statut_course"],
            $statutsAnnulables,
            true
        )
    ) {

        throw new Exception(
            "Cette course ne peut plus être annulée."
        );
    }


    // ----------------------------------------------
    // Annuler
    // ----------------------------------------------

    $sqlUpdate = "
        UPDATE course
        SET
            statut_course = 'annulee',
            date_annulation = NOW(),
            raison_annulation = ?
        WHERE id_course = ?
    ";

    $stmtUpdate =
        $connexion->prepare($sqlUpdate);

    if (!$stmtUpdate) {

        throw new Exception(
            "Erreur lors de l'annulation."
        );
    }

    $stmtUpdate->bind_param(
        "si",
        $raison,
        $id_course
    );

    if (!$stmtUpdate->execute()) {

        $stmtUpdate->close();

        throw new Exception(
            "Impossible d'annuler la course."
        );
    }

    $stmtUpdate->close();


    // ----------------------------------------------
    // Chauffeur redevient disponible
    // ----------------------------------------------

    if (
        $course["id_chauffeur"] !== null
    ) {

        $id_chauffeur =
            intval(
                $course["id_chauffeur"]
            );


        $sqlChauffeur = "
            UPDATE chauffeur
            SET disponibilite = 1
            WHERE id_chauffeur = ?
        ";


        $stmtChauffeur =
            $connexion->prepare(
                $sqlChauffeur
            );


        if (!$stmtChauffeur) {

            throw new Exception(
                "Erreur lors de la remise en disponibilité."
            );
        }


        $stmtChauffeur->bind_param(
            "i",
            $id_chauffeur
        );


        if (!$stmtChauffeur->execute()) {

            $stmtChauffeur->close();

            throw new Exception(
                "Impossible de rendre le chauffeur disponible."
            );
        }


        $stmtChauffeur->close();
    }


    // ----------------------------------------------
    // COMMIT
    // ----------------------------------------------

    $connexion->commit();

    enregistrerHistorique(
    $connexion,
    "course_annulee",
    "Course annulée. Raison : " . $raison,
    $course["id_client"] !== null
        ? (int)$course["id_client"]
        : null,
    $course["id_chauffeur"] !== null
        ? (int)$course["id_chauffeur"]
        : null,
    null,
    $id_course
);


    reponseSucces([
        "message" =>
            "Course annulée avec succès.",

        "id_course" =>
            $id_course,

        "statut_course" =>
            "annulee"
    ]);

}

catch (Exception $erreur) {

    $connexion->rollback();

    reponseErreur(
        $erreur->getMessage()
    );
}

?>