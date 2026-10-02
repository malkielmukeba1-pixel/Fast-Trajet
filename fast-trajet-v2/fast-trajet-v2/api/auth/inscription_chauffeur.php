<?php

// ======================================================
// FAST TRAJET V2
// INSCRIPTION CHAUFFEUR
// ======================================================

require_once "../../config/database.php";
require_once "../../config/session.php";
require_once "../../config/response.php";
require_once "../../config/validation.php";


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
// DONNÉES CHAUFFEUR
// ======================================================

$nom = trim($_POST["nom"] ?? "");
$prenom = trim($_POST["prenom"] ?? "");
$telephone = trim($_POST["telephone"] ?? "");
$email = trim($_POST["email"] ?? "");
$mot_de_passe = $_POST["mot_de_passe"] ?? "";
$date_naissance = trim($_POST["date_naissance"] ?? "");
$sexe = trim($_POST["sexe"] ?? "");


// ======================================================
// DONNÉES VÉHICULE
// ======================================================

$marque = trim($_POST["marque"] ?? "");
$modele = trim($_POST["modele"] ?? "");
$immatriculation = trim(
    $_POST["immatriculation"] ?? ""
);
$couleur = trim($_POST["couleur"] ?? "");
$nombre_places = intval(
    $_POST["nombre_places"] ?? 0
);


// ======================================================
// VALIDATIONS CHAUFFEUR
// ======================================================

if ($nom === "") {
    reponseErreur("Le nom est obligatoire.");
}

if ($prenom === "") {
    reponseErreur("Le prénom est obligatoire.");
}

if ($telephone === "") {
    reponseErreur("Le téléphone est obligatoire.");
}

if (!estTelephoneValide($telephone)) {
    reponseErreur("Le numéro de téléphone est invalide.");
}

if ($email === "") {
    reponseErreur("L'e-mail est obligatoire.");
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
// SEXE
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
// VÉHICULE
// ======================================================

if ($marque === "") {
    reponseErreur("La marque du véhicule est obligatoire.");
}

if ($modele === "") {
    reponseErreur("Le modèle du véhicule est obligatoire.");
}

if ($immatriculation === "") {
    reponseErreur(
        "L'immatriculation est obligatoire."
    );
}

if ($couleur === "") {
    reponseErreur(
        "La couleur du véhicule est obligatoire."
    );
}

if ($nombre_places < 1) {
    reponseErreur(
        "Le nombre de places est invalide."
    );
}


// ======================================================
// VÉRIFIER EMAIL / TÉLÉPHONE
// ======================================================

$sql = "
    SELECT id_chauffeur
    FROM chauffeur
    WHERE email = ?
       OR telephone = ?
    LIMIT 1
";

$stmt = $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la vérification du chauffeur."
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
        "Cet e-mail ou ce numéro de téléphone est déjà utilisé."
    );
}

$stmt->close();


// ======================================================
// VÉRIFIER IMMATRICULATION
// ======================================================

$sql = "
    SELECT id_vehicule
    FROM vehicule
    WHERE immatriculation = ?
    LIMIT 1
";

$stmt = $connexion->prepare($sql);

if (!$stmt) {

    reponseErreur(
        "Erreur lors de la vérification du véhicule."
    );
}

$stmt->bind_param(
    "s",
    $immatriculation
);

$stmt->execute();

$resultat = $stmt->get_result();

if ($resultat->num_rows > 0) {

    $stmt->close();

    reponseErreur(
        "Cette immatriculation est déjà enregistrée."
    );
}

$stmt->close();


// ======================================================
// DATE DE NAISSANCE
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
// HASH
// ======================================================

$mot_de_passe_hash = password_hash(
    $mot_de_passe,
    PASSWORD_DEFAULT
);


// ======================================================
// TRANSACTION
// ======================================================

$connexion->begin_transaction();

try {

    // ----------------------------------------------
    // CHAUFFEUR
    // ----------------------------------------------

    $sqlChauffeur = "
        INSERT INTO chauffeur (
            nom,
            prenom,
            telephone,
            email,
            mot_de_passe,
            date_naissance,
            sexe,
            disponibilite,
            statut
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'actif')
    ";

    $stmtChauffeur =
        $connexion->prepare($sqlChauffeur);

    if (!$stmtChauffeur) {
        throw new Exception(
            "Erreur préparation chauffeur."
        );
    }

    $stmtChauffeur->bind_param(
        "sssssss",
        $nom,
        $prenom,
        $telephone,
        $email,
        $mot_de_passe_hash,
        $dateNaissanceSQL,
        $sexe
    );

    if (!$stmtChauffeur->execute()) {
        throw new Exception(
            "Impossible de créer le chauffeur."
        );
    }

    $idChauffeur =
        $connexion->insert_id;

    $stmtChauffeur->close();


    // ----------------------------------------------
    // VÉHICULE
    // ----------------------------------------------

    $sqlVehicule = "
        INSERT INTO vehicule (
            id_chauffeur,
            marque,
            modele,
            immatriculation,
            couleur,
            nombre_places,
            statut
        )
        VALUES (?, ?, ?, ?, ?, ?, 'actif')
    ";

    $stmtVehicule =
        $connexion->prepare($sqlVehicule);

    if (!$stmtVehicule) {
        throw new Exception(
            "Erreur préparation véhicule."
        );
    }

    $stmtVehicule->bind_param(
        "issssi",
        $idChauffeur,
        $marque,
        $modele,
        $immatriculation,
        $couleur,
        $nombre_places
    );

    if (!$stmtVehicule->execute()) {
        throw new Exception(
            "Impossible d'enregistrer le véhicule."
        );
    }

    $idVehicule =
        $connexion->insert_id;

    $stmtVehicule->close();


    // ----------------------------------------------
    // VALIDER
    // ----------------------------------------------

    $connexion->commit();


    reponseSucces([
        "message" =>
            "Compte chauffeur créé avec succès.",
        "id_chauffeur" =>
            $idChauffeur,
        "id_vehicule" =>
            $idVehicule
    ]);

}
catch (Exception $erreur) {

    $connexion->rollback();

    reponseErreur(
        $erreur->getMessage()
    );
}

?>