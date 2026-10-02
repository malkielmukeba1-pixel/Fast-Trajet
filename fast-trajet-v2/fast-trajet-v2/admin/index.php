<?php

// ======================================================
// FAST TRAJET V2
// PROTECTION DU TABLEAU DE BORD ADMINISTRATEUR
// ======================================================

session_start();


// ======================================================
// EMPÊCHER LE CACHE
// ======================================================

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


// ======================================================
// VÉRIFIER LA SESSION ADMINISTRATEUR
// ======================================================

if (
    !isset($_SESSION["administrateur_connecte"]) ||
    $_SESSION["administrateur_connecte"] !== true
) {

    header(
        "Location: connexion.html"
    );

    exit;
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

    <title>Fast Trajet — Administration</title>

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>

<body>

<header class="topbar">

    <button
        type="button"
        id="menuButton"
        class="menu-button"
        aria-label="Ouvrir le menu"
    >
        ☰
    </button>

    <div class="topbar-title">
        <strong>Fast Trajet</strong>
        <span>Administration</span>
    </div>

    <div class="admin-user">

        <span id="adminName">
            Administrateur
        </span>

        <button
            type="button"
            id="logoutButton"
        >
            Déconnexion
        </button>

    </div>

</header>


<div
    id="overlay"
    class="overlay"
></div>


<aside
    id="sidebar"
    class="sidebar"
>

    <div class="sidebar-logo">

        <div class="logo-circle">
            🚕
        </div>

        <div>
            <strong>FAST TRAJET</strong>
            <small>Administration</small>
        </div>

    </div>


    <nav>

        <button
            class="menu-item active"
            data-section="dashboard"
        >
            📊 Tableau de bord
        </button>

        <button
            class="menu-item"
            data-section="utilisateurs"
        >
            👥 Utilisateurs
        </button>

        <button
            class="menu-item"
            data-section="courses"
        >
            🚕 Courses
        </button>

        <button
            class="menu-item"
            data-section="alertes"
        >
            🚨 Alertes
        </button>

        <button
            class="menu-item"
            data-section="historique"
        >
            🛡️ Historique
        </button>

    </nav>

</aside>


<main class="main-content">


    <!-- ================================================= -->
    <!-- TABLEAU DE BORD -->
    <!-- ================================================= -->

    <section
        id="dashboard"
        class="admin-section active"
    >

        <div class="page-header">

            <div>

                <h1>Tableau de bord</h1>

                <p>
                    Vue générale de Fast Trajet
                </p>

            </div>

            <button
                type="button"
                id="refreshButton"
                class="primary-button"
            >
                🔄 Actualiser
            </button>

        </div>


        <div class="stats-grid">

            <article class="stat-card">

                <span class="stat-icon">
                    👥
                </span>

                <div>

                    <span class="stat-label">
                        Clients
                    </span>

                    <strong id="statClients">
                        0
                    </strong>

                </div>

            </article>


            <article class="stat-card">

                <span class="stat-icon">
                    🚕
                </span>

                <div>

                    <span class="stat-label">
                        Chauffeurs
                    </span>

                    <strong id="statChauffeurs">
                        0
                    </strong>

                </div>

            </article>


            <article class="stat-card">

                <span class="stat-icon">
                    🛣️
                </span>

                <div>

                    <span class="stat-label">
                        Courses
                    </span>

                    <strong id="statCourses">
                        0
                    </strong>

                </div>

            </article>


            <article class="stat-card danger">

                <span class="stat-icon">
                    🚨
                </span>

                <div>

                    <span class="stat-label">
                        Alertes actives
                    </span>

                    <strong id="statAlertes">
                        0
                    </strong>

                </div>

            </article>

        </div>


        <div class="secondary-stats">

            <div>
                En attente :
                <strong id="statEnAttente">
                    0
                </strong>
            </div>

            <div>
                En cours :
                <strong id="statEnCours">
                    0
                </strong>
            </div>

            <div>
                Terminées :
                <strong id="statTerminees">
                    0
                </strong>
            </div>

        </div>


        <div class="dashboard-grid">

            <div class="panel">

                <div class="panel-header">

                    <h2>Dernières courses</h2>

                    <button
                        type="button"
                        class="text-button"
                        data-section-link="courses"
                    >
                        Voir tout
                    </button>

                </div>

                <div
                    id="dashboardCourses"
                    class="list-container"
                >
                    <p class="loading">
                        Chargement...
                    </p>
                </div>

            </div>


            <div class="panel">

                <div class="panel-header">

                    <h2>Alertes récentes</h2>

                    <button
                        type="button"
                        class="text-button"
                        data-section-link="alertes"
                    >
                        Voir tout
                    </button>

                </div>

                <div
                    id="dashboardAlertes"
                    class="list-container"
                >
                    <p class="loading">
                        Chargement...
                    </p>
                </div>

            </div>

        </div>

    </section>


    <!-- ================================================= -->
    <!-- UTILISATEURS -->
    <!-- ================================================= -->

    <section
        id="utilisateurs"
        class="admin-section"
    >

        <div class="page-header">

            <div>

                <h1>Utilisateurs</h1>

                <p>
                    Clients et chauffeurs
                </p>

            </div>

        </div>


        <div class="panel">

            <h2>Clients</h2>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Statut</th>
                        </tr>

                    </thead>

                    <tbody
                        id="clientsTable"
                    ></tbody>

                </table>

            </div>

        </div>


        <div class="panel">

            <h2>Chauffeurs</h2>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Disponibilité</th>
                            <th>Statut</th>
                        </tr>

                    </thead>

                    <tbody
                        id="chauffeursTable"
                    ></tbody>

                </table>

            </div>

        </div>

    </section>


    <!-- ================================================= -->
    <!-- COURSES -->
    <!-- ================================================= -->

    <section
        id="courses"
        class="admin-section"
    >

        <div class="page-header">

            <div>

                <h1>Courses</h1>

                <p>
                    Toutes les courses Fast Trajet
                </p>

            </div>

        </div>


        <div class="panel">

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Chauffeur</th>
                            <th>Départ</th>
                            <th>Destination</th>
                            <th>Prix</th>
                            <th>Statut</th>
                        </tr>

                    </thead>

                    <tbody
                        id="coursesTable"
                    ></tbody>

                </table>

            </div>

        </div>

    </section>


    <!-- ================================================= -->
    <!-- ALERTES -->
    <!-- ================================================= -->

    <section
        id="alertes"
        class="admin-section"
    >

        <div class="page-header">

            <div>

                <h1>Alertes & incidents</h1>

                <p>
                    Surveillance des signalements
                </p>

            </div>

        </div>


        <div class="panel">

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Niveau</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Course</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Police</th>
                        </tr>

                    </thead>

                    <tbody
                        id="alertesTable"
                    ></tbody>

                </table>

            </div>

        </div>

    </section>


    <!-- ================================================= -->
    <!-- HISTORIQUE -->
    <!-- ================================================= -->

    <section
        id="historique"
        class="admin-section"
    >

        <div class="page-header">

            <div>

                <h1>Journal d'activité</h1>

                <p>
                    Traçabilité des opérations
                </p>

            </div>

        </div>


        <div class="panel">

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Utilisateur</th>
                            <th>Course</th>
                        </tr>

                    </thead>

                    <tbody
                        id="historiqueTable"
                    ></tbody>

                </table>

            </div>

        </div>

    </section>

</main>


<script src="admin.js"></script>

</body>

</html>