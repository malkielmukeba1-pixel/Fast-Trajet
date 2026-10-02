<?php
// Page intermédiaire de choix du profil Fast Trajet
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Choisir un profil - Fast Trajet</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #f4f8ff, #eaf2ff);
            color: #172033;
            display: flex;
            flex-direction: column;
        }

        /* =========================
           HEADER
        ========================== */

        header {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #e5eaf2;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            text-decoration: none;
            font-size: 26px;
            font-weight: 800;
            color: #1769ff;
        }

        .logo span {
            color: #172033;
        }

        .retour {
            text-decoration: none;
            color: #526071;
            font-size: 15px;
            font-weight: 600;
            transition: 0.3s;
        }

        .retour:hover {
            color: #1769ff;
        }

        /* =========================
           CONTENU
        ========================== */

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
        }

        .container {
            width: 100%;
            max-width: 1050px;
            text-align: center;
        }

        .petit-titre {
            color: #1769ff;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        h1 {
            font-size: 42px;
            line-height: 1.15;
            margin-bottom: 15px;
            color: #172033;
        }

        .description {
            max-width: 650px;
            margin: 0 auto 45px;
            color: #687386;
            font-size: 17px;
            line-height: 1.7;
        }

        /* =========================
           CARTES
        ========================== */

        .choix {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 900px;
            margin: auto;
        }

        .carte {
            background: #ffffff;
            border-radius: 22px;
            padding: 40px 35px;
            border: 1px solid #e3e9f2;
            box-shadow: 0 15px 40px rgba(23, 45, 80, 0.08);
            transition: transform 0.3s ease,
                        box-shadow 0.3s ease,
                        border-color 0.3s ease;
        }

        .carte:hover {
            transform: translateY(-8px);
            border-color: #1769ff;
            box-shadow: 0 22px 50px rgba(23, 45, 80, 0.14);
        }

        .icone {
            width: 75px;
            height: 75px;
            margin: 0 auto 22px;
            border-radius: 20px;
            background: #edf4ff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 38px;
        }

        .carte h2 {
            font-size: 25px;
            margin-bottom: 12px;
            color: #172033;
        }

        .carte p {
            color: #6b7585;
            line-height: 1.6;
            font-size: 15px;
            min-height: 50px;
            margin-bottom: 28px;
        }

        .boutons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-principal {
            background: #1769ff;
            color: #ffffff;
        }

        .btn-principal:hover {
            background: #0d57dc;
            transform: translateY(-2px);
        }

        .btn-secondaire {
            background: #f2f5f9;
            color: #26344a;
        }

        .btn-secondaire:hover {
            background: #e5ebf3;
            transform: translateY(-2px);
        }

        /* =========================
           FOOTER
        ========================== */

        footer {
            text-align: center;
            padding: 20px;
            color: #7a8493;
            font-size: 13px;
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 750px) {

            header {
                padding: 16px 5%;
            }

            .logo {
                font-size: 23px;
            }

            h1 {
                font-size: 32px;
            }

            .description {
                font-size: 15px;
                margin-bottom: 30px;
            }

            .choix {
                grid-template-columns: 1fr;
                max-width: 500px;
                gap: 20px;
            }

            .carte {
                padding: 30px 25px;
            }

            main {
                padding: 40px 15px;
            }
        }

        @media (max-width: 420px) {

            h1 {
                font-size: 28px;
            }

            .petit-titre {
                font-size: 12px;
            }

            .description {
                font-size: 14px;
            }

            .carte h2 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

    <!-- =========================
         HEADER
    ========================== -->

    <header>

        <a href="index.php" class="logo">
            Fast <span>Trajet</span>
        </a>

        <a href="index.php" class="retour">
            ← Retour à l'accueil
        </a>

    </header>


    <!-- =========================
         CONTENU PRINCIPAL
    ========================== -->

    <main>

        <div class="container">

            <div class="petit-titre">
                Bienvenue sur Fast Trajet
            </div>

            <h1>
                Que souhaitez-vous faire ?
            </h1>

            <p class="description">
                Choisissez votre profil pour accéder aux services
                Fast Trajet adaptés à vos besoins.
            </p>


            <div class="choix">

                <!-- =====================
                     CLIENT
                ====================== -->

                <div class="carte">

                    <div class="icone">
                        🚗
                    </div>

                    <h2>
                        Je suis client
                    </h2>

                    <p>
                        Recherchez un chauffeur, demandez un trajet
                        et déplacez-vous facilement dans Kinshasa.
                    </p>

                    <div class="boutons">

                        <a
                            href="client/connexion.html"
                            class="btn btn-principal"
                        >
                            Se connecter
                        </a>

                        <a
                            href="client/inscription.html"
                            class="btn btn-secondaire"
                        >
                            Créer un compte
                        </a>

                    </div>

                </div>


                <!-- =====================
                     CHAUFFEUR
                ====================== -->

                <div class="carte">

                    <div class="icone">
                        👨‍✈️
                    </div>

                    <h2>
                        Je suis chauffeur
                    </h2>

                    <p>
                        Proposez vos services, recevez des demandes
                        de trajet et développez votre activité.
                    </p>

                    <div class="boutons">

                        <a
                            href="chauffeur/connexion.html"
                            class="btn btn-principal"
                        >
                            Se connecter
                        </a>

                        <a
                            href="chauffeur/inscription.html"
                            class="btn btn-secondaire"
                        >
                            Devenir chauffeur
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </main>


    <!-- =========================
         FOOTER
    ========================== -->

    <footer>
        © 2026 Fast Trajet — Kinshasa, RDC
    </footer>

</body>
</html>