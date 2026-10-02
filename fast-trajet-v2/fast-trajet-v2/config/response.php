<?php

// ======================================================
// RÉPONSES JSON UNIFORMES
// ======================================================

function reponseSucces($donnees = [])
{

    header(
        "Content-Type: application/json; charset=UTF-8"
    );

    echo json_encode(
        array_merge(
            [
                "success" => true
            ],
            $donnees
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


function reponseErreur($message, $code = 400)
{

    http_response_code($code);

    header(
        "Content-Type: application/json; charset=UTF-8"
    );

    echo json_encode(
        [
            "success" => false,
            "message" => $message
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

?>