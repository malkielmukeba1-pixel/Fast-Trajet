<?php

// ======================================================
// FAST TRAJET V2
// CONNEXION ADMINISTRATEUR
// ======================================================

require_once "../config/database.php";
require_once "../config/session.php";
require_once "../config/response.php";


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

$email = trim(
    $_POST["email"] ?? ""
);

$mot_de_passe =
    $_POST["mot_de_passe"] ?? "";


if ($email === "") {

    reponseErreur(
        "L'adresse e-mail est obligatoire."
    );
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    reponseErreur(
        "L'adresse e-mail est invalide."
    );
}

if ($mot_de_passe === "") {

    reponseErreur(
        "Le mot de passe est obligatoire."
    );
}


// ======================================================
// RECHERCHER ADMINISTRATEUR
// ======================================================

$sql = "
    SELECT
        id_administrateur,
        nom,
        prenom,
        email,
        mot_de_passe,
        role,
        statut
    FROM administrateur
    WHERE email = ?
    LIMIT 1
";

$stmt = $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Erreur de connexion administrateur."
    );
}

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$resultat =
    $stmt->get_result();

$admin =
    $resultat->fetch_assoc();

$stmt->close();


// ======================================================
// VÉRIFIER LE COMPTE
// ======================================================

if (!$admin) {

    reponseErreur(
        "Identifiant ou mot de passe incorrect.",
        401
    );
}


// ======================================================
// STATUT
// ======================================================

if (
    $admin["statut"] !== "actif"
) {

    reponseErreur(
        "Ce compte administrateur n'est pas actif.",
        403
    );
}


// ======================================================
// MOT DE PASSE
// ======================================================

if (
    !password_verify(
        $mot_de_passe,
        $admin["mot_de_passe"]
    )
) {

    reponseErreur(
        "Identifiant ou mot de passe incorrect.",
        401
    );
}


// ======================================================
// SESSION
// ======================================================

session_regenerate_id(true);

$_SESSION["administrateur_connecte"] = true;

$_SESSION["id_administrateur"] =
    (int)$admin["id_administrateur"];

$_SESSION["nom_administrateur"] =
    $admin["nom"];

$_SESSION["prenom_administrateur"] =
    $admin["prenom"];

$_SESSION["email_administrateur"] =
    $admin["email"];

$_SESSION["role_administrateur"] =
    $admin["role"];


// ======================================================
// SUCCÈS
// ======================================================

reponseSucces([
    "message" =>
        "Connexion administrateur réussie.",
    "role" =>
        "administrateur",
    "id_administrateur" =>
        (int)$admin["id_administrateur"]
]);

?>