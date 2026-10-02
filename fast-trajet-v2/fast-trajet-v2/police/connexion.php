<?php

session_start();

require_once "../config/database.php";


// Si le policier est déjà connecté
if (
    isset($_SESSION["policier_connecte"]) &&
    $_SESSION["policier_connecte"] === true
) {

    header("Location: index.php");
    exit;
}


$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"] ?? "");

    $mot_de_passe =
        $_POST["mot_de_passe"] ?? "";


    if (
        $email === "" ||
        $mot_de_passe === ""
    ) {

        $message =
            "Veuillez remplir tous les champs.";

    }
    else {

        $requete =
            $connexion->prepare(
                "
                SELECT
                    id_policier,
                    nom,
                    prenom,
                    matricule,
                    email,
                    mot_de_passe,
                    grade,
                    commissariat,
                    statut
                FROM policier
                WHERE email = ?
                LIMIT 1
                "
            );


        $requete->bind_param(
            "s",
            $email
        );


        $requete->execute();


        $resultat =
            $requete->get_result();


        if ($resultat->num_rows === 1) {

            $policier =
                $resultat->fetch_assoc();


            if (
                password_verify(
                    $mot_de_passe,
                    $policier["mot_de_passe"]
                )
            ) {

                session_regenerate_id(true);


                $_SESSION["policier_connecte"] =
                    true;

                $_SESSION["id_policier"] =
                    (int) $policier["id_policier"];

                $_SESSION["nom_policier"] =
                    $policier["nom"];

                $_SESSION["prenom_policier"] =
                    $policier["prenom"];

                $_SESSION["matricule_policier"] =
                    $policier["matricule"];

                $_SESSION["grade_policier"] =
                    $policier["grade"];

                $_SESSION["commissariat_policier"] =
                    $policier["commissariat"];


                header(
                    "Location: index.php"
                );

                exit;

            }
            else {

                $message =
                    "Email ou mot de passe incorrect.";

            }

        }
        else {

            $message =
                "Email ou mot de passe incorrect.";

        }


        $requete->close();

    }

}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Police — Fast Trajet
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;

            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .connexion-card {

            width: 100%;
            max-width: 430px;

            background: white;

            padding: 35px;

            border-radius: 15px;

            box-shadow:
                0 8px 30px
                rgba(0, 0, 0, 0.12);
        }

        .logo {
            text-align: center;
            font-size: 40px;
            margin-bottom: 10px;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {

            width: 100%;

            padding: 13px;

            border:
                1px solid #ccc;

            border-radius: 8px;

            font-size: 15px;
        }

        button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            cursor: pointer;

            font-size: 16px;

            font-weight: bold;

            background: #111827;
            color: white;
        }

        .message {

            margin-bottom: 20px;

            padding: 12px;

            border-radius: 8px;

            background: #fee2e2;

            color: #991b1b;
        }

    </style>

</head>


<body>


<div class="connexion-card">

    <div class="logo">
        👮
    </div>

    <h1>
        Fast Trajet — Police
    </h1>

    <p class="description">
        Connexion sécurisée des agents de police
    </p>


    <?php if ($message !== ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <form
        method="POST"
        action=""
    >

        <div class="form-group">

            <label for="email">
                Adresse email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                required
                autocomplete="email"
            >

        </div>


        <div class="form-group">

            <label for="mot_de_passe">
                Mot de passe
            </label>

            <input
                type="password"
                id="mot_de_passe"
                name="mot_de_passe"
                required
                autocomplete="current-password"
            >

        </div>


        <button type="submit">
            🔐 Se connecter
        </button>

    </form>

</div>


</body>

</html>