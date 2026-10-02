<?php

// ======================================================
// FAST TRAJET V2
// CRÉATION D'UNE NOTIFICATION
// ======================================================

function creerNotificationClient(
    $connexion,
    $id_client,
    $type,
    $titre,
    $contenu,
    $id_course = null
) {

    $sql = "
        INSERT INTO notification (
            id_client,
            id_course,
            type_notification,
            titre,
            contenu,
            lu
        )
        VALUES (?, ?, ?, ?, ?, 0)
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "iisss",
        $id_client,
        $id_course,
        $type,
        $titre,
        $contenu
    );

    $resultat =
        $stmt->execute();

    $stmt->close();

    return $resultat;
}


// ======================================================
// NOTIFICATION CHAUFFEUR
// ======================================================

function creerNotificationChauffeur(
    $connexion,
    $id_chauffeur,
    $type,
    $titre,
    $contenu,
    $id_course = null
) {

    $sql = "
        INSERT INTO notification (
            id_chauffeur,
            id_course,
            type_notification,
            titre,
            contenu,
            lu
        )
        VALUES (?, ?, ?, ?, ?, 0)
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "iisss",
        $id_chauffeur,
        $id_course,
        $type,
        $titre,
        $contenu
    );

    $resultat =
        $stmt->execute();

    $stmt->close();

    return $resultat;
}


// ======================================================
// NOTIFICATION ADMINISTRATEUR
// ======================================================

function creerNotificationAdmin(
    $connexion,
    $id_administrateur,
    $type,
    $titre,
    $contenu,
    $id_course = null
) {

    $sql = "
        INSERT INTO notification (
            id_administrateur,
            id_course,
            type_notification,
            titre,
            contenu,
            lu
        )
        VALUES (?, ?, ?, ?, ?, 0)
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "iisss",
        $id_administrateur,
        $id_course,
        $type,
        $titre,
        $contenu
    );

    $resultat =
        $stmt->execute();

    $stmt->close();

    return $resultat;
}

?>