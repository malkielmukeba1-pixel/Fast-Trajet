<?php

// ======================================================
// FAST TRAJET V2
// CONNEXION CLIENT / CHAUFFEUR
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/response.php";


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
// DONNÉES
// ======================================================

$identifiant = trim(
    $_POST["identifiant"] ?? ""
);

$mot_de_passe =
    $_POST["mot_de_passe"] ?? "";


if ($identifiant === "") {

    reponseErreur(
        "Veuillez entrer votre e-mail ou téléphone."
    );
}

if ($mot_de_passe === "") {

    reponseErreur(
        "Veuillez entrer votre mot de passe."
    );
}


// ======================================================
// CHERCHER CLIENT
// ======================================================

$sqlClient = "
    SELECT
        id_client,
        nom,
        prenom,
        email,
        telephone,
        mot_de_passe,
        statut
    FROM client
    WHERE email = ?
       OR telephone = ?
    LIMIT 1
";

$stmt = $connexion->prepare($sqlClient);

if (!$stmt) {

    reponseErreur(
        "Erreur de connexion."
    );
}

$stmt->bind_param(
    "ss",
    $identifiant,
    $identifiant
);

$stmt->execute();

$resultat =
    $stmt->get_result();

$client =
    $resultat->fetch_assoc();

$stmt->close();


// ======================================================
// CLIENT TROUVÉ
// ======================================================

if ($client) {

    if (
        $client["statut"] !== "actif"
    ) {

        reponseErreur(
            "Votre compte client n'est pas actif.",
            403
        );
    }


    if (
        !password_verify(
            $mot_de_passe,
            $client["mot_de_passe"]
        )
    ) {

        reponseErreur(
            "Identifiant ou mot de passe incorrect.",
            401
        );
    }


    // Régénérer l'identifiant de session

    session_regenerate_id(true);


    $_SESSION["client_connecte"] = true;

    $_SESSION["id_client"] =
        (int)$client["id_client"];

    $_SESSION["nom_client"] =
        $client["nom"];

    $_SESSION["prenom_client"] =
        $client["prenom"];

    $_SESSION["email_client"] =
        $client["email"];


    reponseSucces([
        "message" => "Connexion client réussie.",
        "role" => "client",
        "id_client" =>
            (int)$client["id_client"]
    ]);
}


// ======================================================
// CHERCHER CHAUFFEUR
// ======================================================

$sqlChauffeur = "
    SELECT
        id_chauffeur,
        nom,
        prenom,
        email,
        telephone,
        mot_de_passe,
        disponibilite,
        statut
    FROM chauffeur
    WHERE email = ?
       OR telephone = ?
    LIMIT 1
";

$stmt = $connexion->prepare(
    $sqlChauffeur
);

if (!$stmt) {

    reponseErreur(
        "Erreur de connexion."
    );
}

$stmt->bind_param(
    "ss",
    $identifiant,
    $identifiant
);

$stmt->execute();

$resultat =
    $stmt->get_result();

$chauffeur =
    $resultat->fetch_assoc();

$stmt->close();


// ======================================================
// CHAUFFEUR TROUVÉ
// ======================================================

if ($chauffeur) {

    if (
        $chauffeur["statut"] !== "actif"
    ) {

        reponseErreur(
            "Votre compte chauffeur n'est pas actif.",
            403
        );
    }


    if (
        !password_verify(
            $mot_de_passe,
            $chauffeur["mot_de_passe"]
        )
    ) {

        reponseErreur(
            "Identifiant ou mot de passe incorrect.",
            401
        );
    }


    session_regenerate_id(true);


    $_SESSION["chauffeur_connecte"] = true;

    $_SESSION["id_chauffeur"] =
        (int)$chauffeur["id_chauffeur"];

    $_SESSION["nom_chauffeur"] =
        $chauffeur["nom"];

    $_SESSION["prenom_chauffeur"] =
        $chauffeur["prenom"];

    $_SESSION["email_chauffeur"] =
        $chauffeur["email"];


    reponseSucces([
        "message" =>
            "Connexion chauffeur réussie.",
        "role" =>
            "chauffeur",
        "id_chauffeur" =>
            (int)$chauffeur["id_chauffeur"]
    ]);
}


// ======================================================
// AUCUN COMPTE
// ======================================================

reponseErreur(
    "Identifiant ou mot de passe incorrect.",
    401
);

?>