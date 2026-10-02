<?php

// ======================================================
// GESTION DES SESSIONS
// ======================================================

if (
    session_status() === PHP_SESSION_NONE
) {

    session_start();

}

?>