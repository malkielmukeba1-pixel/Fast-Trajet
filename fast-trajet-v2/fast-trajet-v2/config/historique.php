<?php

// ======================================================
// FAST TRAJET V2
// JOURNAL D'ACTIVITÉ / HISTORIQUE
// ======================================================

function enregistrerHistorique(
    $connexion,
    $action,
    $description = null,
    $id_client = null,
    $id_chauffeur = null,
    $id_administrateur = null,
    $id_course = null
) {

    $adresse_ip =
        $_SERVER["REMOTE_ADDR"] ?? null;

    $user_agent =
        $_SERVER["HTTP_USER_AGENT"] ?? null;

    $sql = "
        INSERT INTO historique (
            id_client,
            id_chauffeur,
            id_administrateur,
            id_course,
            action,
            description,
            adresse_ip,
            user_agent,
            date_action
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";

    $stmt =
        $connexion->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "iiiissss",
        $id_client,
        $id_chauffeur,
        $id_administrateur,
        $id_course,
        $action,
        $description,
        $adresse_ip,
        $user_agent
    );

    $resultat =
        $stmt->execute();

    $stmt->close();

    return $resultat;
}
?>