<?php

// ======================================================
// FAST TRAJET V2
// ESPACE CLIENT
// ======================================================

require_once "../config/session.php";

// ======================================================
// VÉRIFIER LA CONNEXION CLIENT
// ======================================================

if (
    !isset($_SESSION["client_connecte"]) ||
    $_SESSION["client_connecte"] !== true
) {
    header("Location: ../index.php");
    exit;
}

$id_client = (int) ($_SESSION["id_client"] ?? 0);
$nom_client = htmlspecialchars(
    $_SESSION["nom_client"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

$prenom_client = htmlspecialchars(
    $_SESSION["prenom_client"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Fast Trajet - Espace Client</title>

    <link
        rel="stylesheet"
        href="client.css"
    >

    <!-- Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

</head>

<body>

<!-- ==================================================
     HEADER
================================================== -->

<header class="client-header">

    <div class="logo">
        🚕 <span>Fast Trajet</span>
    </div>

    <button
        type="button"
        id="menuButton"
        class="menu-button"
        aria-label="Ouvrir le menu"
    >
        ☰
    </button>

</header>


<!-- ==================================================
     MENU MOBILE
================================================== -->

<nav
    id="mobileMenu"
    class="mobile-menu"
>

    <div class="menu-user">

        <div class="avatar">
            👤
        </div>

        <div>

            <strong>
                <?= $prenom_client . " " . $nom_client ?>
            </strong>

            <small>
                Client
            </small>

        </div>

    </div>

    <button
        type="button"
        data-section="accueil"
    >
        🏠 Accueil
    </button>

    <button
        type="button"
        data-section="course"
    >
        🚕 Ma course
    </button>

    <button
        type="button"
        data-section="profil"
    >
        👤 Mon profil
    </button>

    <button
        type="button"
        data-section="securite"
    >
        🛡️ Sécurité
    </button>

    <button
    type="button"
    data-section="notifications"
>
    🔔 Notifications
    (<span id="notificationCount">0</span>)
</button>

    <button
        type="button"
        id="deconnexionButton"
        class="logout-button"
    >
        🚪 Déconnexion
    </button>

</nav>


<!-- ==================================================
     CONTENU PRINCIPAL
================================================== -->

<main class="client-container">


    <!-- ==================================================
         ACCUEIL
    ================================================== -->

    <section
        id="sectionAccueil"
        class="client-section active"
    >

        <div class="welcome-card">

            <div>

                <span class="welcome-label">
                    Bienvenue 👋
                </span>

                <h1>
                    <?= $prenom_client ?>
                </h1>

                <p>
                    Où souhaitez-vous aller aujourd'hui ?
                </p>

            </div>

            <div class="welcome-icon">
                🚕
            </div>

        </div>


        <!-- ==============================================
             FORMULAIRE COURSE
        =============================================== -->

        <div class="course-card">

            <div class="card-title">

                <span>🚕</span>

                <div>

                    <h2>
                        Commander un taxi
                    </h2>

                    <p>
                        Indiquez votre trajet
                    </p>

                </div>

            </div>


            <form id="courseForm">


                <!-- DÉPART -->

                <div class="field-group">

                    <label for="lieuDepart">
                        📍 Lieu de départ
                    </label>

                    <div class="input-location">

                        <input
                            type="text"
                            id="lieuDepart"
                            name="lieu_depart"
                            placeholder="Votre lieu de départ"
                            autocomplete="off"
                            required
                        >

                        <button
                            type="button"
                            id="positionButton"
                            title="Utiliser ma position"
                        >
                            📍
                        </button>

                    </div>

                </div>


                <!-- DESTINATION -->

                <div class="field-group">

                    <label for="lieuDestination">
                        🎯 Destination
                    </label>

                    <input
                        type="text"
                        id="lieuDestination"
                        name="lieu_destination"
                        placeholder="Où allez-vous ?"
                        autocomplete="off"
                        required
                    >

                </div>


                <!-- DISTANCE -->

                <div
                    id="trajetInformations"
                    class="trajet-informations hidden"
                >

                    <div>

                        <span>
                            📏
                        </span>

                        <strong id="distance">
                            --
                        </strong>

                    </div>

                    <div>

                        <span>
                            ⏱️
                        </span>

                        <strong id="duree">
                            --
                        </strong>

                    </div>

                </div>


                <!-- BOUTON -->

                <button
                    type="submit"
                    id="commanderButton"
                    class="primary-button"
                >

                    🚕 Commander un taxi

                </button>

            </form>

        </div>


        <!-- ==============================================
             CARTE
        =============================================== -->

        <div class="map-card">

            <div class="card-title">

                <span>🗺️</span>

                <div>

                    <h2>
                        Votre position
                    </h2>

                    <p>
                        Localisation en temps réel
                    </p>

                </div>

            </div>

            <div id="map"></div>

        </div>

    </section>

<!-- ==============================================
     DISCUSSION CLIENT <-> CHAUFFEUR
=============================================== -->

<div
    id="messagesCourseCardClient"
    class="chat-course-card"
    hidden
>

    <div class="chat-course-header">

        <div>
            💬
        </div>

        <div>
            <h2>
                Discussion avec votre chauffeur
            </h2>

            <p>
                Échangez des informations utiles concernant votre course.
            </p>
        </div>

    </div>


    <div
        id="listeMessagesCourseClient"
        class="chat-messages"
    >

        <p class="chat-vide">
            Aucun message pour le moment.
        </p>

    </div>


    <div
        id="zoneEnvoiMessageClient"
        class="chat-input-zone"
    >

        <textarea
            id="messageCourseClient"
            maxlength="1000"
            rows="2"
            placeholder="Écrivez votre message..."
        ></textarea>


        <button
            type="button"
            id="envoyerMessageCourseClient"
        >
            ➤
        </button>

    </div>

</div>

    <!-- ==================================================
         COURSE
    ================================================== -->

    <section
        id="sectionCourse"
        class="client-section"
    >

        <div class="section-header">

            <span>🚕</span>

            <div>

                <h1>
                    Ma course
                </h1>

                <p>
                    Suivez l'état de votre trajet
                </p>

            </div>

        </div>


        <div
            id="courseActiveContainer"
            class="course-active-container"
        >

            <div class="empty-state">

                <div>
                    🚕
                </div>

                <h2>
                    Aucune course active
                </h2>

                <p>
                    Votre course apparaîtra ici.
                </p>

            </div>

        </div>

        <!-- ==============================================
     NÉGOCIATION DU PRIX
=============================================== -->

<div
    id="negociationCard"
    class="course-card"
    hidden
>

    <div class="card-title">

        <span>💰</span>

        <div>

            <h2>
                Négociation du prix
            </h2>

            <p>
                Échangez uniquement des propositions de prix.
            </p>

        </div>

    </div>


    <p>
        <strong>
            Prix actuel :
        </strong>

        <span id="prixActuelClient">
            Aucun prix proposé
        </span>
    </p>


    <div
        id="negociationHistoriqueClient"
    >

        <p>
            Aucune proposition pour le moment.
        </p>

    </div>


    <div class="field-group">

        <label for="prixClient">
            Votre proposition ($)
        </label>

        <input
            type="number"
            id="prixClient"
            min="0.01"
            step="0.01"
            inputmode="decimal"
            placeholder="Exemple : 10"
        >

    </div>


    <button
        type="button"
        id="proposerPrixClientButton"
        class="primary-button"
    >
        💰 Proposer ce prix
    </button>


    <button
        type="button"
        id="accepterPrixClientButton"
        class="primary-button"
        disabled
    >
        ✅ Accepter le prix du chauffeur
    </button>


    <p id="messagePrixClient"></p>

</div>

<!-- ==============================================
     SUIVI DU CHAUFFEUR
=============================================== -->

<div
    id="suiviCourseCard"
    class="course-card"
    hidden
>

    <div class="card-title">

        <span>🗺️</span>

        <div>

            <h2>
                Suivi de votre chauffeur
            </h2>

            <p id="messageSuiviCourse">
                🚕 Votre chauffeur s'est mis en route.
            </p>

        </div>

    </div>


    <div
        id="courseTrackingMap"
        style="
            width: 100%;
            height: 420px;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 15px;
        "
    ></div>


    <p
        id="etatPositionChauffeur"
        style="margin-top: 12px;"
    >
        📡 Recherche de la position du chauffeur...
    </p>

</div>

    </section>


    <!-- ==================================================
         PROFIL
    ================================================== -->

    <section
        id="sectionProfil"
        class="client-section"
    >

        <div class="section-header">

            <span>👤</span>

            <div>

                <h1>
                    Mon profil
                </h1>

                <p>
                    Vos informations personnelles
                </p>

            </div>

        </div>


        <div class="profile-card">

            <div class="profile-avatar">
                👤
            </div>

            <h2>
                <?= $prenom_client . " " . $nom_client ?>
            </h2>

            <p>
                Client Fast Trajet
            </p>

        </div>

    </section>


    <!-- ==================================================
         SÉCURITÉ
    ================================================== -->

    <section
        id="sectionSecurite"
        class="client-section"
    >

        <div class="section-header">

            <span>🛡️</span>

            <div>

                <h1>
                    Sécurité
                </h1>

                <p>
                    Votre sécurité est notre priorité
                </p>

            </div>

        </div>


        <div class="security-card">

            <div class="security-item">

                <span>📍</span>

                <div>

                    <strong>
                        Géolocalisation
                    </strong>

                    <p>
                        Votre position peut être utilisée
                        pendant votre course.
                    </p>

                </div>

            </div>


            <div class="security-item signalement-box">

    <span>
        🚨
    </span>

    <div>

        <strong>
            Signaler un incident
        </strong>

        <p>
            Utilisez ce formulaire en cas de problème
            pendant votre course.
        </p>


        <select
            id="typeAlerteClient"
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
            id="niveauAlerteClient"
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
            id="descriptionAlerteClient"
            rows="4"
            maxlength="1000"
            placeholder="Décrivez ce qui se passe..."
        ></textarea>


        <button
            type="button"
            id="envoyerAlerteClient"
        >
            🚨 Envoyer le signalement
        </button>

    </div>

</div>

        </div>

    </section>

    <!-- ==================================================
     NOTIFICATIONS
================================================== -->

<section
    id="sectionNotifications"
    class="client-section"
>

    <div class="section-header">

        <span>
            🔔
        </span>

        <div>

            <h1>
                Notifications
            </h1>

            <p>
                Les nouvelles concernant vos courses
            </p>

        </div>

    </div>


    <div
        id="notificationsClientContainer"
    >

        <div class="empty-state">

            <div>
                🔔
            </div>

            <h2>
                Aucune notification
            </h2>

            <p>
                Vos notifications apparaîtront ici.
            </p>

        </div>

    </div>

</section>

</main>





<!-- ==================================================
     NOTIFICATION
================================================== -->

<div
    id="notification"
    class="notification"
></div>


<!-- ==================================================
     DONNÉES PHP → JAVASCRIPT
================================================== -->

<script>

    window.FAST_TRAJET = {

        idClient: <?= $id_client ?>,

        prenomClient:
            <?= json_encode(
                $_SESSION["prenom_client"] ?? "",
                JSON_UNESCAPED_UNICODE
            ) ?>,

        nomClient:
            <?= json_encode(
                $_SESSION["nom_client"] ?? "",
                JSON_UNESCAPED_UNICODE
            ) ?>

    };

</script>


<!-- Leaflet -->

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<script
    src="client.js"
></script>

</body>
</html>