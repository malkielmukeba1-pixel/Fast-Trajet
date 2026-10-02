<?php

// ======================================================
// FAST TRAJET V2
// ESPACE CHAUFFEUR
// ======================================================

session_start();


// ======================================================
// PROTECTION
// ======================================================

if (
    !isset($_SESSION["chauffeur_connecte"]) ||
    $_SESSION["chauffeur_connecte"] !== true
) {

    header(
        "Location: ../index.html"
    );

    exit;
}


// ======================================================
// INFORMATIONS SESSION
// ======================================================

$prenomChauffeur =
    $_SESSION["prenom_chauffeur"] ?? "Chauffeur";

$nomChauffeur =
    $_SESSION["nom_chauffeur"] ?? "";

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
        Fast Trajet — Espace chauffeur
    </title>


    <!-- =================================================
         LEAFLET
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >


    <!-- =================================================
         CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="chauffeur.css"
    >

</head>


<body>


<header class="topbar">

    <div class="logo-zone">

        <div class="logo">
            🚕
        </div>

        <div>

            <strong>
                FAST TRAJET
            </strong>

            <small>
                Espace chauffeur
            </small>

        </div>

    </div>


    <div class="topbar-actions">

        <button
            type="button"
            id="notificationButton"
            class="icon-button"
            aria-label="Notifications"
        >

            🔔

            <span
                id="notificationCount"
                class="notification-count"
            >
                0
            </span>

        </button>


        <button
            type="button"
            id="menuButton"
            class="icon-button"
            aria-label="Menu"
        >
            ☰
        </button>

    </div>

</header>


<!-- =====================================================
     MENU MOBILE
====================================================== -->

<div
    id="mobileMenu"
    class="mobile-menu"
>

    <div class="menu-profile">

        <strong>

            <?= htmlspecialchars(
                $prenomChauffeur . " " . $nomChauffeur,
                ENT_QUOTES,
                "UTF-8"
            ) ?>

        </strong>

        <small>
            Chauffeur
        </small>

    </div>


    <button
        type="button"
        data-action="notifications"
    >
        🔔 Notifications
    </button>


    <button
        type="button"
        data-action="deconnexion"
    >
        🚪 Déconnexion
    </button>

</div>


<main class="chauffeur-container">


    <!-- =================================================
         BIENVENUE
    ================================================== -->

    <section class="welcome-card">

        <div>

            <span>
                Bonjour
            </span>

            <h1>

                <?= htmlspecialchars(
                    $prenomChauffeur,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

                👋

            </h1>

            <p>
                Gérez vos courses depuis Fast Trajet.
            </p>

        </div>

    </section>


    <!-- =================================================
         DISPONIBILITÉ
    ================================================== -->

    <section class="card availability-card">

        <div>

            <span class="section-label">
                État de disponibilité
            </span>

            <strong
                id="availabilityStatus"
                class="availability-status"
            >
                Chargement...
            </strong>

        </div>


        <button
            type="button"
            id="availabilityButton"
            class="availability-button"
        >
            Chargement...
        </button>

    </section>


    <!-- =================================================
         COURSES DISPONIBLES
    ================================================== -->

    <section
        id="coursesSection"
        class="card"
    >

        <div class="section-header">

            <div>

                <h2>
                    🚕 Courses disponibles
                </h2>

                <small>
                    Demandes actuellement disponibles
                </small>

            </div>


            <button
                type="button"
                id="refreshCoursesButton"
                class="small-action"
            >
                🔄
            </button>

        </div>


        <div
            id="coursesContainer"
            class="courses-container"
        >

            <p class="loading">
                Recherche des courses...
            </p>

        </div>

    </section>


    <!-- =================================================
         COURSE ACTIVE
    ================================================== -->

    <section
        id="courseActiveSection"
        class="card hidden"
    >

        <div class="section-header">

            <div>

                <h2>
                    🚕 Course active
                </h2>

                <span
                    id="courseStatus"
                    class="status-badge"
                >
                    —
                </span>

            </div>

        </div>


        <div
            id="courseDetails"
            class="course-details"
        ></div>


        <!-- CLIENT -->

        <div
            id="clientInfo"
            class="info-box"
        >

            <h3>
                👤 Client
            </h3>

            <div id="clientDetails">
                Chargement...
            </div>

        </div>


        <!-- PRIX -->

        <div
            id="priceBox"
            class="info-box hidden"
        >

            <h3>
                💰 Négociation du prix
            </h3>


            <div
                id="priceCurrent"
                class="price-current"
            >
                —
            </div>


            <div
                id="negotiationHistory"
                class="negotiation-history"
            >
                Aucun historique.
            </div>


            <div class="price-form">

                <input
                    type="number"
                    id="driverPrice"
                    min="0.01"
                    step="0.01"
                    placeholder="Votre prix en $"
                >


                <button
                    type="button"
                    id="proposePriceButton"
                >
                    💰 Proposer
                </button>

            </div>


            <button
                type="button"
                id="acceptPriceButton"
                class="accept-price-button"
            >
                ✅ Accepter le prix actuel
            </button>


            <p
                id="priceMessage"
                class="form-message"
            ></p>

        </div>


        <!-- DÉMARRAGE -->

        <div
            id="courseActions"
            class="course-actions"
        >

            <button
                type="button"
                id="startCourseButton"
                class="primary-button hidden"
            >
                ▶️ Démarrer la course
            </button>


            <button
                type="button"
                id="finishCourseButton"
                class="finish-button hidden"
            >
                🏁 Terminer la course
            </button>

        </div>


        <!-- SUIVI GPS -->

        <div
            id="trackingBox"
            class="info-box"
        >

            <h3>
                📍 Suivi GPS
            </h3>

            <p id="trackingMessage">
                Le suivi GPS sera activé lorsque la course commencera.
            </p>

        </div>


        <!-- ==============================================
     SIGNALEMENT CHAUFFEUR
=============================================== -->

<div
    id="alerteChauffeurBox"
    class="info-box"
>

    <h3>
        🚨 Signaler un incident
    </h3>

    <p>
        Signalez un problème concernant votre course.
    </p>


    <select
        id="typeAlerteChauffeur"
    >
        <option value="">
            Type de signalement
        </option>

        <option value="urgence">
            🚨 Urgence
        </option>

        <option value="securite">
            🛡️ Problème de sécurité
        </option>

        <option value="accident">
            🚗 Accident
        </option>

        <option value="incident">
            ⚠️ Incident
        </option>
    </select>


    <select
        id="niveauAlerteChauffeur"
    >
        <option value="moyen">
            Niveau moyen
        </option>

        <option value="faible">
            Faible
        </option>

        <option value="eleve">
            Élevé
        </option>

        <option value="critique">
            Critique
        </option>
    </select>


    <textarea
        id="descriptionAlerteChauffeur"
        rows="4"
        maxlength="1000"
        placeholder="Décrivez ce qui se passe..."
    ></textarea>


    <button
        type="button"
        id="envoyerAlerteChauffeur"
    >
        🚨 Envoyer le signalement
    </button>

</div>

        <!-- ==============================================
     DISCUSSION CHAUFFEUR <-> CLIENT
=============================================== -->

<div
    id="messagesCourseCardChauffeur"
    class="chat-course-card"
    hidden
>

    <div class="chat-course-header">

        <div>
            💬
        </div>

        <div>
            <h2>
                Discussion avec le client
            </h2>

            <p>
                Échangez des informations utiles concernant la course.
            </p>
        </div>

    </div>


    <div
        id="listeMessagesCourseChauffeur"
        class="chat-messages"
    >

        <p class="chat-vide">
            Aucun message pour le moment.
        </p>

    </div>


    <div
        id="zoneEnvoiMessageChauffeur"
        class="chat-input-zone"
    >

        <textarea
            id="messageCourseChauffeur"
            maxlength="1000"
            rows="2"
            placeholder="Écrivez votre message..."
        ></textarea>


        <button
            type="button"
            id="envoyerMessageCourseChauffeur"
        >
            ➤
        </button>

    </div>

</div>

    </section>


    <!-- =================================================
         CARTE
    ================================================== -->

    <section class="card">

        <div class="section-header">

            <div>

                <h2>
                    🗺️ Carte
                </h2>

                <small>
                    Position du client et destination
                </small>

            </div>

        </div>


        <div id="map"></div>

    </section>


    <!-- =================================================
         NOTIFICATIONS
    ================================================== -->

    <section
        id="notificationsSection"
        class="card hidden"
    >

        <div class="section-header">

            <h2>
                🔔 Notifications
            </h2>

        </div>


        <div
            id="notificationsContainer"
        >
            Chargement...
        </div>

    </section>

</main>


<!-- =====================================================
     LEAFLET
====================================================== -->

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script
    src="chauffeur.js"
></script>

</body>

</html>