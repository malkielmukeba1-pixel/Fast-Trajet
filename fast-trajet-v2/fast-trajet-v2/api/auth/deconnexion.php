<?php

// ======================================================
// FAST TRAJET V2
// DÉCONNEXION
// ======================================================

require_once "../../config/session.php";
require_once "../../config/response.php";


// ======================================================
// VIDER LA SESSION
// ======================================================

$_SESSION = [];


// ======================================================
// DÉTRUIRE LE COOKIE DE SESSION
// ======================================================

if (ini_get("session.use_cookies")) {

    $parametres =
        session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametres["path"],
        $parametres["domain"],
        $parametres["secure"],
        $parametres["httponly"]
    );
}


// ======================================================
// DÉTRUIRE LA SESSION
// ======================================================

session_destroy();


// ======================================================
// RÉPONSE
// ======================================================

reponseSucces([
    "message" =>
        "Déconnexion réussie."
]);

?>