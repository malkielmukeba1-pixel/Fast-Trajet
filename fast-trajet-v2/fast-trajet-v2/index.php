<?php
// ==========================================================
// FAST TRAJET V2
// PAGE D'ACCUEIL PRINCIPALE
// ==========================================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Fast Trajet - Application de transport urbain à Kinshasa">

    <title>Fast Trajet - Transport rapide et sécurisé</title>

    <style>
        /* ==================================================
           RESET
        ================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            font-family: inherit;
        }


        /* ==================================================
           NAVIGATION
        ================================================== */

        .navbar {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #eeeeee;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1200px;
            margin: auto;
            padding: 16px 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .logo {
            font-size: 27px;
            font-weight: 800;
            color: #111827;
            white-space: nowrap;
        }

        .logo span {
            color: #f5b400;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-links a {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            transition: 0.2s;
        }

        .nav-links a:hover {
            color: #f0a900;
        }

        .nav-actions {
            display: flex;
            gap: 10px;
        }

        .btn-nav-login,
        .btn-nav-register {
            padding: 10px 17px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s;
        }

        .btn-nav-login {
            border: 1px solid #d1d5db;
            background: #ffffff;
        }

        .btn-nav-login:hover {
            background: #f9fafb;
        }

        .btn-nav-register {
            background: #f5b400;
            color: #111111;
        }

        .btn-nav-register:hover {
            background: #e3a600;
        }


        /* ==================================================
           HERO
        ================================================== */

        .hero {
            min-height: 650px;
            background:
                linear-gradient(
                    120deg,
                    #fff8df 0%,
                    #ffffff 55%,
                    #f8fafc 100%
                );

            display: flex;
            align-items: center;
        }

        .hero-container {
            width: 100%;
            max-width: 1200px;
            margin: auto;
            padding: 80px 25px;

            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 70px;
        }

        .hero-badge {
            display: inline-block;
            padding: 8px 14px;
            margin-bottom: 20px;

            background: #fff1c2;
            color: #8a5b00;

            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
        }

        .hero h1 {
            font-size: clamp(42px, 5vw, 68px);
            line-height: 1.08;
            color: #111827;
            margin-bottom: 22px;
        }

        .hero h1 span {
            color: #e6a900;
        }

        .hero-text {
            max-width: 600px;
            font-size: 18px;
            color: #5b6472;
            margin-bottom: 32px;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 15px 24px;
            border-radius: 9px;

            background: #f5b400;
            color: #111111;

            font-size: 16px;
            font-weight: 700;

            transition: 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            background: #e4a700;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 14px 24px;
            border-radius: 9px;

            background: #ffffff;
            border: 1px solid #d5d9df;

            color: #1f2937;

            font-size: 16px;
            font-weight: 700;

            transition: 0.2s;
        }

        .btn-secondary:hover {
            background: #f8fafc;
        }


        /* ==================================================
           HERO VISUEL
        ================================================== */

        .hero-visual {
            min-height: 390px;
            position: relative;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .phone-card {
            width: 280px;
            min-height: 390px;

            background: #111827;
            border-radius: 35px;

            padding: 14px;

            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.18);

            transform: rotate(2deg);
        }

        .phone-screen {
            width: 100%;
            height: 100%;

            min-height: 362px;

            background: #ffffff;
            border-radius: 25px;

            padding: 25px 18px;
        }

        .phone-top {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 30px;
        }

        .phone-logo {
            font-weight: 800;
            font-size: 18px;
        }

        .phone-logo span {
            color: #e6a900;
        }

        .location-box {
            padding: 17px;
            border-radius: 14px;
            background: #f8f9fb;
            margin-bottom: 13px;
        }

        .location-label {
            font-size: 11px;
            color: #7b8490;
            margin-bottom: 4px;
        }

        .location-value {
            font-weight: 700;
            font-size: 14px;
        }

        .route-line {
            width: 2px;
            height: 25px;
            background: #d1d5db;
            margin-left: 24px;
        }

        .phone-button {
            margin-top: 25px;

            width: 100%;
            padding: 14px;

            border: none;
            border-radius: 10px;

            background: #f5b400;
            color: #111111;

            font-weight: 800;
        }

        .floating-card {
            position: absolute;
            padding: 15px 18px;

            background: #ffffff;
            border-radius: 13px;

            box-shadow: 0 15px 40px rgba(0,0,0,0.12);

            font-size: 14px;
            font-weight: 700;
        }

        .floating-card.one {
            top: 35px;
            left: 0;
        }

        .floating-card.two {
            bottom: 35px;
            right: 0;
        }


        /* ==================================================
           SECTION GENERALE
        ================================================== */

        .section {
            padding: 85px 25px;
        }

        .section-container {
            max-width: 1200px;
            margin: auto;
        }

        .section-heading {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 50px;
        }

        .section-heading h2 {
            font-size: 38px;
            line-height: 1.2;
            margin-bottom: 15px;
            color: #111827;
        }

        .section-heading p {
            color: #687282;
            font-size: 17px;
        }


        /* ==================================================
           SERVICES
        ================================================== */

        .services {
            background: #f8fafc;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .service-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid #edf0f3;

            transition: 0.25s;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.07);
        }

        .service-icon {
            width: 52px;
            height: 52px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: #fff4ce;

            font-size: 25px;
            margin-bottom: 20px;
        }

        .service-card h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }

        .service-card p {
            color: #687282;
            font-size: 15px;
        }


        /* ==================================================
           CLIENT / CHAUFFEUR
        ================================================== */

        .roles-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .role-card {
            padding: 38px;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .role-card h3 {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .role-card p {
            color: #687282;
            margin-bottom: 22px;
        }

        .role-list {
            list-style: none;
            margin-bottom: 25px;
        }

        .role-list li {
            margin-bottom: 11px;
            color: #374151;
        }

        .role-list li::before {
            content: "✓";
            font-weight: 800;
            margin-right: 9px;
            color: #d89e00;
        }


        /* ==================================================
           SECURITE
        ================================================== */

        .security {
            background: #111827;
            color: #ffffff;
        }

        .security-content {
            max-width: 850px;
            margin: auto;
            text-align: center;
        }

        .security-content h2 {
            font-size: 38px;
            margin-bottom: 18px;
        }

        .security-content p {
            color: #d1d5db;
            font-size: 17px;
            margin-bottom: 35px;
        }

        .security-items {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .security-item {
            padding: 25px;
            border: 1px solid #374151;
            border-radius: 14px;
        }

        .security-item strong {
            display: block;
            margin-bottom: 7px;
        }

        .security-item span {
            color: #b9c0ca;
            font-size: 14px;
        }


        /* ==================================================
           CTA
        ================================================== */

        .cta {
            padding: 80px 25px;
            text-align: center;
            background: #fff8df;
        }

        .cta h2 {
            font-size: 38px;
            margin-bottom: 15px;
        }

        .cta p {
            color: #687282;
            margin-bottom: 28px;
        }


        /* ==================================================
           FOOTER
        ================================================== */

        footer {
            background: #0b1120;
            color: #ffffff;
            padding: 45px 25px 25px;
        }

        .footer-container {
            max-width: 1200px;
            margin: auto;

            display: flex;
            justify-content: space-between;
            gap: 30px;

            padding-bottom: 30px;
        }

        .footer-brand {
            max-width: 400px;
        }

        .footer-brand h3 {
            font-size: 23px;
            margin-bottom: 10px;
        }

        .footer-brand h3 span {
            color: #f5b400;
        }

        .footer-brand p {
            color: #aeb5c0;
            font-size: 14px;
        }

        .footer-links {
            display: flex;
            gap: 35px;
        }

        .footer-links a {
            color: #c9ced6;
            font-size: 14px;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: auto;

            padding-top: 20px;

            border-top: 1px solid #263044;

            color: #8e97a5;
            font-size: 13px;

            text-align: center;
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 900px) {

            .nav-links {
                display: none;
            }

            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-visual {
                margin-top: 20px;
            }

            .service-grid {
                grid-template-columns: 1fr 1fr;
            }

            .security-items {
                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 650px) {

            .navbar-container {
                padding: 13px 16px;
            }

            .logo {
                font-size: 22px;
            }

            .btn-nav-login,
            .btn-nav-register {
                padding: 8px 10px;
                font-size: 12px;
            }

            .hero {
                min-height: auto;
            }

            .hero-container {
                padding: 60px 18px;
            }

            .hero h1 {
                font-size: 42px;
            }

            .hero-text {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
            }

            .hero-visual {
                min-height: 400px;
            }

            .phone-card {
                width: 245px;
            }

            .floating-card {
                font-size: 12px;
                padding: 11px;
            }

            .floating-card.one {
                left: -5px;
            }

            .floating-card.two {
                right: -5px;
            }

            .section {
                padding: 65px 18px;
            }

            .section-heading h2,
            .security-content h2,
            .cta h2 {
                font-size: 30px;
            }

            .service-grid,
            .roles-grid {
                grid-template-columns: 1fr;
            }

            .role-card {
                padding: 27px;
            }

            .footer-container {
                flex-direction: column;
            }

            .footer-links {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>

<body>

<!-- ======================================================
     NAVIGATION
====================================================== -->

<header class="navbar">

    <div class="navbar-container">

        <a href="index.php" class="logo">
            Fast <span>Trajet</span>
        </a>

        <nav class="nav-links">
            <a href="#accueil">Accueil</a>
            <a href="#services">Services</a>
            <a href="#fonctionnement">Fonctionnement</a>
            <a href="#securite">Sécurité</a>
        </nav>

        <div class="nav-actions">

            <a href="choix.php"
               class="btn-nav-login">
                Connexion
            </a>

            <a href="choix.php"
               class="btn-nav-register">
                Inscription
            </a>

        </div>

    </div>

</header>


<!-- ======================================================
     HERO
====================================================== -->

<main>

<section class="hero" id="accueil">

    <div class="hero-container">

        <div class="hero-content">

            <span class="hero-badge">
                🇨🇩 Transport urbain à Kinshasa
            </span>

            <h1>
                Vos trajets,
                <span>plus simples.</span>
            </h1>

            <p class="hero-text">
                Fast Trajet facilite la mise en relation entre
                clients et chauffeurs pour des déplacements rapides,
                pratiques et sécurisés à Kinshasa.
            </p>

            <div class="hero-buttons">

                <a href="client/inscription.html"
                   class="btn-primary">
                    🚕 Commander un trajet
                </a>

                <a href="chauffeur/inscription.html"
                   class="btn-secondary">
                    🚗 Devenir chauffeur
                </a>

            </div>

        </div>


        <div class="hero-visual">

            <div class="floating-card one">
                📍 Géolocalisation
            </div>

            <div class="phone-card">

                <div class="phone-screen">

                    <div class="phone-top">

                        <div class="phone-logo">
                            Fast <span>Trajet</span>
                        </div>

                        <span>🇨🇩</span>

                    </div>

                    <div class="location-box">

                        <div class="location-label">
                            DÉPART
                        </div>

                        <div class="location-value">
                            📍 Votre position
                        </div>

                    </div>

                    <div class="route-line"></div>

                    <div class="location-box">

                        <div class="location-label">
                            DESTINATION
                        </div>

                        <div class="location-value">
                            📍 Où allez-vous ?
                        </div>

                    </div>

                    <button class="phone-button"
                            type="button">
                        Rechercher un trajet
                    </button>

                </div>

            </div>

            <div class="floating-card two">
                🛡️ Trajet sécurisé
            </div>

        </div>

    </div>

</section>


<!-- ======================================================
     SERVICES
====================================================== -->

<section class="section services" id="services">

    <div class="section-container">

        <div class="section-heading">

            <h2>
                Tout ce qu'il faut pour vos déplacements
            </h2>

            <p>
                Une solution pensée pour rendre les déplacements
                urbains plus simples et plus pratiques.
            </p>

        </div>


        <div class="service-grid">

            <article class="service-card">

                <div class="service-icon">
                    📍
                </div>

                <h3>
                    Géolocalisation
                </h3>

                <p>
                    Utilisez votre position et retrouvez plus
                    facilement les informations liées à votre trajet.
                </p>

            </article>


            <article class="service-card">

                <div class="service-icon">
                    🚕
                </div>

                <h3>
                    Trouver un chauffeur
                </h3>

                <p>
                    Les clients peuvent rechercher un chauffeur
                    disponible pour leur déplacement.
                </p>

            </article>


            <article class="service-card">

                <div class="service-icon">
                    💬
                </div>

                <h3>
                    Négociation du prix
                </h3>

                <p>
                    Client et chauffeur peuvent échanger afin
                    de parvenir à un prix accepté pour la course.
                </p>

            </article>

        </div>

    </div>

</section>


<!-- ======================================================
     FONCTIONNEMENT
====================================================== -->

<section class="section" id="fonctionnement">

    <div class="section-container">

        <div class="section-heading">

            <h2>
                Fast Trajet pour chacun
            </h2>

            <p>
                Deux espaces adaptés aux besoins des utilisateurs.
            </p>

        </div>


        <div class="roles-grid">

            <!-- CLIENT -->

            <article class="role-card">

                <h3>
                    👤 Pour les clients
                </h3>

                <p>
                    Demandez facilement un trajet et suivez
                    son évolution.
                </p>

                <ul class="role-list">

                    <li>
                        Créer un compte
                    </li>

                    <li>
                        Rechercher un trajet
                    </li>

                    <li>
                        Indiquer le départ et la destination
                    </li>

                    <li>
                        Échanger avec le chauffeur
                    </li>

                    <li>
                        Négocier et accepter le prix
                    </li>

                    <li>
                        Suivre la course
                    </li>

                </ul>

                <a href="client/inscription.html"
                   class="btn-primary">
                    Créer un compte client
                </a>

            </article>


            <!-- CHAUFFEUR -->

            <article class="role-card">

                <h3>
                    🚗 Pour les chauffeurs
                </h3>

                <p>
                    Proposez vos services et gérez vos courses
                    depuis votre espace.
                </p>

                <ul class="role-list">

                    <li>
                        Créer un compte chauffeur
                    </li>

                    <li>
                        Enregistrer son véhicule
                    </li>

                    <li>
                        Gérer sa disponibilité
                    </li>

                    <li>
                        Consulter les demandes de course
                    </li>

                    <li>
                        Proposer et négocier un prix
                    </li>

                    <li>
                        Suivre la course
                    </li>

                </ul>

                <a href="chauffeur/inscription.html"
                   class="btn-secondary">
                    Devenir chauffeur
                </a>

            </article>

        </div>

    </div>

</section>


<!-- ======================================================
     SECURITE
====================================================== -->

<section class="section security" id="securite">

    <div class="section-container">

        <div class="security-content">

            <h2>
                La sécurité au cœur de Fast Trajet
            </h2>

            <p>
                Fast Trajet est conçu pour assurer un environnement
                fiable et sécurisé lors des interactions entre
                clients et chauffeurs.
            </p>


            <div class="security-items">

                <div class="security-item">

                    <strong>
                        🔐 Comptes protégés
                    </strong>

                    <span>
                        Les accès sont gérés de manière sécurisée.
                    </span>

                </div>


                <div class="security-item">

                    <strong>
                        📍 Suivi des positions
                    </strong>

                    <span>
                        Les informations de localisation sont
                        utilisées dans le cadre des trajets.
                    </span>

                </div>


                <div class="security-item">

                    <strong>
                        🚨 Alertes
                    </strong>

                    <span>
                        Le système est prévu pour gérer les
                        situations nécessitant une intervention.
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ======================================================
     CTA
====================================================== -->

<section class="cta">

    <div class="section-container">

        <h2>
            Prêt à utiliser Fast Trajet ?
        </h2>

        <p>
            Créez votre compte et commencez votre expérience.
        </p>

        <a href="choix.php"
           class="btn-primary">
            Commencer maintenant
        </a>

    </div>

</section>

</main>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer>

    <div class="footer-container">

        <div class="footer-brand">

            <h3>
                Fast <span>Trajet</span>
            </h3>

            <p>
                Une solution de transport urbain pensée pour
                faciliter les déplacements à Kinshasa.
            </p>

        </div>


        <div class="footer-links">

            <a href="#accueil">
                Accueil
            </a>

            <a href="#services">
                Services
            </a>

            <a href="#fonctionnement">
                Fonctionnement
            </a>

            <a href="#securite">
                Sécurité
            </a>

        </div>

    </div>


    <div class="footer-bottom">

        © <?php echo date("Y"); ?> Fast Trajet —
        Tous droits réservés.

    </div>

</footer>

</body>
</html>