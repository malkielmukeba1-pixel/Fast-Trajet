<?php

// ======================================================
// FAST TRAJET V2
// CONNEXION À LA BASE DE DONNÉES
// ======================================================

$serveur = "localhost";
$utilisateur = "root";
$mot_de_passe = "";
$base_de_donnees = "fast_trajet_v2";

$connexion = new mysqli(
    $serveur,
    $utilisateur,
    $mot_de_passe,
    $base_de_donnees
);

// Vérifier la connexion

if ($connexion->connect_error) {

    die(
        "Erreur de connexion : " .
        $connexion->connect_error
    );
}

// Encodage UTF8

$connexion->set_charset("utf8mb4");

?>