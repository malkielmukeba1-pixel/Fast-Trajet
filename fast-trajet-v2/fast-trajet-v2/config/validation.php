<?php

// ======================================================
// VALIDATION DES DONNÉES
// ======================================================

function nettoyerTexte($texte)
{

    return trim(
        htmlspecialchars(
            $texte,
            ENT_QUOTES,
            "UTF-8"
        )
    );

}


function estEmailValide($email)
{

    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    );

}


function estTelephoneValide($telephone)
{

    return preg_match(
        "/^[0-9+ ]{8,20}$/",
        $telephone
    );

}


function estPrixValide($prix)
{

    return
        is_numeric($prix)
        &&
        floatval($prix) > 0;

}


function estCoordonneeValide($latitude, $longitude)
{

    if (
        !is_numeric($latitude) ||
        !is_numeric($longitude)
    ) {

        return false;

    }

    $latitude = floatval($latitude);
    $longitude = floatval($longitude);

    return
        $latitude >= -90 &&
        $latitude <= 90 &&
        $longitude >= -180 &&
        $longitude <= 180;

}

?>