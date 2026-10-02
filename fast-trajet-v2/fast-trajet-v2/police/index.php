<?php

session_start();

if (
    !isset($_SESSION["policier_connecte"]) ||
    $_SESSION["policier_connecte"] !== true
) {
    header("Location: connexion.php");
    exit;
}

$nom =
    $_SESSION["nom_policier"] ?? "";

$prenom =
    $_SESSION["prenom_policier"] ?? "";

$matricule =
    $_SESSION["matricule_policier"] ?? "";

$grade =
    $_SESSION["grade_policier"] ?? "";

$commissariat =
    $_SESSION["commissariat_policier"] ?? "";

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
        Fast Trajet — Espace Police
    </title>

    <link
        rel="stylesheet"
        href="police.css"
    >

</head>


<body>


<!-- =========================================
     BARRE SUPÉRIEURE
========================================= -->

<header class="topbar">

    <div class="topbar-left">

        <button
            type="button"
            id="menuButton"
            class="menu-button"
        >
            ☰
        </button>

        <div class="brand">

            <strong>
                Fast Trajet
            </strong>

            <span>
                Espace Police
            </span>

        </div>

    </div>


    <div class="topbar-right">

        <button
            type="button"
            class="notification-button"
            id="notificationButton"
        >

            🔔

            <span
                id="notificationCount"
                class="notification-count"
            >
                0
            </span>

        </button>


        <div class="police-user">

            <div class="police-avatar">
                👮
            </div>

            <div class="police-user-info">

                <strong>
                    <?= htmlspecialchars(
                        $prenom . " " . $nom
                    ) ?>
                </strong>

                <span>
                    <?= htmlspecialchars(
                        $matricule
                    ) ?>
                </span>

            </div>

        </div>

    </div>

</header>



<!-- =========================================
     MENU LATÉRAL
========================================= -->

<aside
    class="sidebar"
    id="sidebar"
>

    <div class="sidebar-logo">

        <div class="logo-circle">
            🚔
        </div>

        <div>

            <strong>
                FAST TRAJET
            </strong>

            <small>
                Police
            </small>

        </div>

    </div>


    <nav class="sidebar-menu">

        <button
            class="menu-item active"
            data-section="dashboard"
        >
            📊
            <span>
                Tableau de bord
            </span>
        </button>


        <button
            class="menu-item"
            data-section="alertes"
        >
            🚨
            <span>
                Mes alertes
            </span>
        </button>


        <button
            class="menu-item"
            data-section="notifications"
        >
            🔔
            <span>
                Notifications
            </span>
        </button>


        <button
            class="menu-item"
            data-section="profil"
        >
            👮
            <span>
                Mon profil
            </span>
        </button>

    </nav>


    <div class="sidebar-bottom">

        <a
            href="deconnexion.php"
            class="logout-button"
        >
            🚪
            <span>
                Déconnexion
            </span>
        </a>

    </div>

</aside>



<div
    id="overlay"
    class="overlay"
></div>



<!-- =========================================
     CONTENU PRINCIPAL
========================================= -->

<main class="main-content">



    <!-- =====================================
         TABLEAU DE BORD
    ====================================== -->

    <section
        id="dashboard"
        class="police-section active"
    >

        <div class="welcome-card">

            <div>

                <span class="welcome-label">
                    ESPACE SÉCURISÉ
                </span>

                <h1>
                    Bonjour,
                    <?= htmlspecialchars(
                        $prenom
                    ) ?> 👋
                </h1>

                <p>
                    Consultez les alertes qui vous sont
                    attribuées par Fast Trajet.
                </p>

            </div>


            <div class="welcome-icon">
                👮
            </div>

        </div>



        <div class="stats-grid">

            <div class="stat-card danger">

                <div class="stat-icon">
                    🚨
                </div>

                <div>

                    <span>
                        Alertes attribuées
                    </span>

                    <strong id="statAlertes">
                        0
                    </strong>

                </div>

            </div>



            <div class="stat-card warning">

                <div class="stat-icon">
                    ⚠️
                </div>

                <div>

                    <span>
                        En traitement
                    </span>

                    <strong id="statTraitement">
                        0
                    </strong>

                </div>

            </div>



            <div class="stat-card success">

                <div class="stat-icon">
                    ✅
                </div>

                <div>

                    <span>
                        Résolues
                    </span>

                    <strong id="statResolues">
                        0
                    </strong>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">
                    🔔
                </div>

                <div>

                    <span>
                        Non lues
                    </span>

                    <strong id="statNotifications">
                        0
                    </strong>

                </div>

            </div>

        </div>



        <div class="dashboard-grid">


            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            🚨 Alertes récentes
                        </h2>

                        <p>
                            Derniers signalements reçus
                        </p>

                    </div>


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
                    class="empty-state"
                >

                    <div class="empty-icon">
                        🚔
                    </div>

                    <strong>
                        Aucune alerte pour le moment
                    </strong>

                    <p>
                        Les alertes qui vous seront
                        attribuées apparaîtront ici.
                    </p>

                </div>

            </div>



            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            🔔 Notifications
                        </h2>

                        <p>
                            Vos dernières notifications
                        </p>

                    </div>

                    <button
                        type="button"
                        class="text-button"
                        data-section-link="notifications"
                    >
                        Voir tout
                    </button>

                </div>


                <div
                    id="dashboardNotifications"
                    class="empty-state"
                >

                    <div class="empty-icon">
                        🔔
                    </div>

                    <strong>
                        Aucune notification
                    </strong>

                    <p>
                        Une notification apparaîtra lorsqu'une
                        alerte vous sera transmise.
                    </p>

                </div>

            </div>

        </div>

    </section>



    <!-- =====================================
         ALERTES
    ====================================== -->

    <section
        id="alertes"
        class="police-section"
    >

        <div class="page-header">

            <div>

                <h1>
                    🚨 Mes alertes
                </h1>

                <p>
                    Signalements qui vous ont été attribués
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
                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody id="alertesTable">

                        <tr>

                            <td
                                colspan="8"
                                class="table-empty"
                            >
                                Aucune alerte attribuée.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </section>



    <!-- =====================================
         NOTIFICATIONS
    ====================================== -->

    <section
        id="notifications"
        class="police-section"
    >

        <div class="page-header">

            <div>

                <h1>
                    🔔 Notifications
                </h1>

                <p>
                    Informations concernant vos alertes
                </p>

            </div>

        </div>


        <div
            class="panel"
            id="notificationsContainer"
        >

            <div class="empty-state">

                <div class="empty-icon">
                    🔔
                </div>

                <strong>
                    Aucune notification
                </strong>

                <p>
                    Vous serez averti lorsqu'une nouvelle
                    alerte vous sera attribuée.
                </p>

            </div>

        </div>

    </section>



    <!-- =====================================
         PROFIL
    ====================================== -->

    <section
        id="profil"
        class="police-section"
    >

        <div class="page-header">

            <div>

                <h1>
                    👮 Mon profil
                </h1>

                <p>
                    Informations de l'agent connecté
                </p>

            </div>

        </div>


        <div class="profile-card">


            <div class="profile-avatar-large">
                👮
            </div>


            <div class="profile-title">

                <h2>
                    <?= htmlspecialchars(
                        $prenom . " " . $nom
                    ) ?>
                </h2>

                <span>
                    Agent Fast Trajet — Police
                </span>

            </div>


            <div class="profile-grid">


                <div class="profile-item">

                    <span>
                        Matricule
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $matricule
                        ) ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>
                        Grade
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $grade
                        ) ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>
                        Commissariat
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $commissariat
                        ) ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>
                        Statut
                    </span>

                    <strong class="status-active">
                        ● Connecté
                    </strong>

                </div>

            </div>

        </div>

    </section>


</main>



<script>

const menuItems =
    document.querySelectorAll(
        ".menu-item"
    );

const sections =
    document.querySelectorAll(
        ".police-section"
    );

const sidebar =
    document.getElementById(
        "sidebar"
    );

const menuButton =
    document.getElementById(
        "menuButton"
    );

const overlay =
    document.getElementById(
        "overlay"
    );


function afficherSection(
    nomSection
) {

    sections.forEach(
        function(section) {

            section.classList.toggle(
                "active",
                section.id === nomSection
            );

        }
    );


    menuItems.forEach(
        function(item) {

            item.classList.toggle(
                "active",
                item.dataset.section ===
                nomSection
            );

        }
    );


    sidebar.classList.remove(
        "open"
    );

    overlay.classList.remove(
        "active"
    );
}


menuItems.forEach(
    function(item) {

        item.addEventListener(
            "click",
            function() {

                afficherSection(
                    item.dataset.section
                );

            }
        );

    }
);


document
    .querySelectorAll(
        "[data-section-link]"
    )
    .forEach(
        function(button) {

            button.addEventListener(
                "click",
                function() {

                    afficherSection(
                        button.dataset.sectionLink
                    );

                }
            );

        }
    );


menuButton.addEventListener(
    "click",
    function() {

        sidebar.classList.toggle(
            "open"
        );

        overlay.classList.toggle(
            "active"
        );

    }
);


overlay.addEventListener(
    "click",
    function() {

        sidebar.classList.remove(
            "open"
        );

        overlay.classList.remove(
            "active"
        );

    }
);

</script>

<script src="police.js"></script>

</body>

</html>