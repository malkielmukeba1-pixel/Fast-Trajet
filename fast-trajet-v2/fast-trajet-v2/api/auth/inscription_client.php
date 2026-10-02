<?php

// ======================================================
// FAST TRAJET V2
// INSCRIPTION CLIENT
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/response.php";
require_once "../../config/validation.php";


// ======================================================
// AUTORISER UNIQUEMENT POST
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    reponseErreur(
        "Méthode non autorisée.",
        405
    );
}


// ======================================================
// RÉCUPÉRER LES DONNÉES
// ======================================================

$nom = trim($_POST["nom"] ?? "");
$prenom = trim($_POST["prenom"] ?? "");
$telephone = trim($_POST["telephone"] ?? "");
$email = trim($_POST["email"] ?? "");
$mot_de_passe = $_POST["mot_de_passe"] ?? "";
$date_naissance = trim($_POST["date_naissance"] ?? "");
$sexe = trim($_POST["sexe"] ?? "");


// ======================================================
// VALIDATIONS
// ======================================================

if ($nom === "") {
    reponseErreur("Le nom est obligatoire.");
}

if ($prenom === "") {
    reponseErreur("Le prénom est obligatoire.");
}

if ($telephone === "") {
    reponseErreur("Le numéro de téléphone est obligatoire.");
}

if (!estTelephoneValide($telephone)) {
    reponseErreur("Le numéro de téléphone est invalide.");
}

if ($email === "") {
    reponseErreur("L'adresse e-mail est obligatoire.");
}

if (!estEmailValide($email)) {
    reponseErreur("L'adresse e-mail est invalide.");
}

if (strlen($mot_de_passe) < 8) {
    reponseErreur(
        "Le mot de passe doit contenir au moins 8 caractères."
    );
}


// ======================================================
// VALIDATION DU SEXE
// ======================================================

$sexesAutorises = [
    "homme",
    "femme",
    "autre"
];

if (
    $sexe !== "" &&
    !in_array($sexe, $sexesAutorises, true)
) {

    reponseErreur(
        "La valeur du sexe est invalide."
    );
}


// ======================================================
// VÉRIFIER EMAIL ET TÉLÉPHONE
// ======================================================

$sqlVerification = "
    SELECT id_client
    FROM client
    WHERE email = ?
       OR telephone = ?
    LIMIT 1
";

$stmt = $connexion->prepare(
    $sqlVerification
);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la préparation de la vérification."
    );
}

$stmt->bind_param(
    "ss",
    $email,
    $telephone
);

$stmt->execute();

$resultat = $stmt->get_result();

if ($resultat->num_rows > 0) {

    $stmt->close();

    reponseErreur(
        "Cette adresse e-mail ou ce numéro de téléphone est déjà utilisé."
    );
}

$stmt->close();


// ======================================================
// HASH DU MOT DE PASSE
// ======================================================

$mot_de_passe_hash = password_hash(
    $mot_de_passe,
    PASSWORD_DEFAULT
);


// ======================================================
// PRÉPARER LA DATE DE NAISSANCE
// ======================================================

$dateNaissanceSQL = null;

if ($date_naissance !== "") {

    $date = DateTime::createFromFormat(
        "Y-m-d",
        $date_naissance
    );

    if (
        !$date ||
        $date->format("Y-m-d") !== $date_naissance
    ) {

        reponseErreur(
            "La date de naissance est invalide."
        );
    }

    $dateNaissanceSQL = $date_naissance;
}


// ======================================================
// INSCRIPTION
// ======================================================

$sql = "
    INSERT INTO client (
        nom,
        prenom,
        telephone,
        email,
        mot_de_passe,
        date_naissance,
        sexe,
        statut
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'actif')
";

$stmt = $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la préparation de l'inscription."
    );
}

$stmt->bind_param(
    "sssssss",
    $nom,
    $prenom,
    $telephone,
    $email,
    $mot_de_passe_hash,
    $dateNaissanceSQL,
    $sexe
);


// ======================================================
// EXÉCUTION
// ======================================================

if (!$stmt->execute()) {

    $stmt->close();

    reponseErreur(
        "Impossible de créer le compte."
    );
}

$idClient = $connexion->insert_id;

$stmt->close();


// ======================================================
// SUCCÈS
// ======================================================

reponseSucces([
    "message" => "Compte client créé avec succès.",
    "id_client" => $idClient
]);

?>