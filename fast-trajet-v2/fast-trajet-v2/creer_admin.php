<?php

require_once "config/database.php";

$nom = "Mukeba";
$prenom = "Malkiel";
$email = "malk@gmail.com";
$mot_de_passe = "12345678";


// Chiffrer le mot de passe
$mot_de_passe_hash =
    password_hash(
        $mot_de_passe,
        PASSWORD_DEFAULT
    );


// Vérifier si l'email existe déjà
$verification = $connexion->prepare(
    "SELECT id_administrateur
     FROM administrateur
     WHERE email = ?"
);

$verification->bind_param(
    "s",
    $email
);

$verification->execute();

$resultat =
    $verification->get_result();


if ($resultat->num_rows > 0) {

    die(
        "❌ Un administrateur avec cet email existe déjà."
    );
}

$verification->close();


// Ajouter l'administrateur
$requete = $connexion->prepare(
    "INSERT INTO administrateur
    (
        nom,
        prenom,
        email,
        mot_de_passe
    )
    VALUES (?, ?, ?, ?)"
);

$requete->bind_param(
    "ssss",
    $nom,
    $prenom,
    $email,
    $mot_de_passe_hash
);


if ($requete->execute()) {

    echo "
        <h2>✅ Administrateur créé avec succès</h2>

        <p>
            Email :
            <strong>malk@gmail.com</strong>
        </p>

        <p>
            Mot de passe :
            <strong>12345678</strong>
        </p>
    ";

}
else {

    echo "❌ Erreur : "
        . $connexion->error;
}


$requete->close();

?>