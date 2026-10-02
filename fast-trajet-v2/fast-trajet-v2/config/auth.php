<?php

require_once "session.php";
require_once "response.php";


// ======================================================
// CLIENT
// ======================================================

function exigerClient()
{

    if (
        !isset($_SESSION["client_connecte"]) ||
        $_SESSION["client_connecte"] !== true
    ) {

        reponseErreur(
            "Client non connecté.",
            401
        );

    }

    return intval(
        $_SESSION["id_client"]
    );
}


// ======================================================
// CHAUFFEUR
// ======================================================

function exigerChauffeur()
{

    if (
        !isset($_SESSION["chauffeur_connecte"]) ||
        $_SESSION["chauffeur_connecte"] !== true
    ) {

        reponseErreur(
            "Chauffeur non connecté.",
            401
        );

    }

    return intval(
        $_SESSION["id_chauffeur"]
    );
}


// ======================================================
// ADMINISTRATEUR
// ======================================================

function exigerAdministrateur()
{

    if (
        !isset($_SESSION["administrateur_connecte"]) ||
        $_SESSION["administrateur_connecte"] !== true
    ) {

        reponseErreur(
            "Administrateur non connecté.",
            401
        );

    }

    return intval(
        $_SESSION["id_administrateur"]
    );
}


// ======================================================
// UTILISATEUR CONNECTÉ
// ======================================================

function utilisateurConnecte()
{

    return
        (
            isset($_SESSION["client_connecte"]) &&
            $_SESSION["client_connecte"] === true
        )
        ||
        (
            isset($_SESSION["chauffeur_connecte"]) &&
            $_SESSION["chauffeur_connecte"] === true
        );

}

?>