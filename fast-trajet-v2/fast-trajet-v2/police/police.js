document.addEventListener(
    "DOMContentLoaded",
    function () {

        let dernierIdNotification = 0;


        function escapeHTML(valeur) {

            const div =
                document.createElement(
                    "div"
                );

            div.textContent =
                valeur ?? "";

            return div.innerHTML;
        }


        async function getJSON(url) {

            const reponse =
                await fetch(
                    url,
                    {
                        credentials:
                            "same-origin"
                    }
                );


            const resultat =
                await reponse.json();


            if (
                !reponse.ok ||
                !resultat.success
            ) {

                throw new Error(
                    resultat.message ||
                    "Erreur serveur."
                );
            }


            return resultat;
        }


        function toast(message) {

            let element =
                document.getElementById(
                    "toastPolice"
                );


            if (!element) {

                element =
                    document.createElement(
                        "div"
                    );

                element.id =
                    "toastPolice";

                element.className =
                    "toast-police";

                document.body.appendChild(
                    element
                );
            }


            element.textContent =
                message;

            element.classList.add(
                "show"
            );


            setTimeout(
                function () {

                    element.classList.remove(
                        "show"
                    );

                },
                4000
            );
        }


        function ouvrirSection(
            section
        ) {

            const bouton =
                document.querySelector(
                    ".menu-item[data-section='" +
                    section +
                    "']"
                );


            if (bouton) {
                bouton.click();
            }
        }


        async function chargerStatistiques() {

            try {

                const resultat =
                    await getJSON(
                        "statistiques.php"
                    );


                document.getElementById(
                    "statAlertes"
                ).textContent =
                    resultat.alertes;


                document.getElementById(
                    "statTraitement"
                ).textContent =
                    resultat.en_traitement;


                document.getElementById(
                    "statResolues"
                ).textContent =
                    resultat.resolues;


                document.getElementById(
                    "statNotifications"
                ).textContent =
                    resultat.notifications_non_lues;


                document.getElementById(
                    "notificationCount"
                ).textContent =
                    resultat.notifications_non_lues;

            }
            catch (erreur) {

                console.error(
                    "Erreur statistiques police :",
                    erreur
                );
            }
        }

   
        async function chargerAlertes() {

    const tableau =
        document.getElementById(
            "alertesTable"
        );

    const dashboard =
        document.getElementById(
            "dashboardAlertes"
        );


    
    


    if (!tableau) {

        console.error(
            "Le tbody #alertesTable est introuvable."
        );

        return;
    }


    try {

        const resultat =
            await getJSON(
                "alertes.php"
            );


        const alertes =
            Array.isArray(
                resultat.alertes
            )
                ? resultat.alertes
                : [];


        tableau.innerHTML = "";


        // =====================================
        // AUCUNE ALERTE
        // =====================================

        if (
            alertes.length === 0
        ) {

            tableau.innerHTML =
                "<tr>" +
                    "<td colspan='8' class='table-empty'>" +
                        "Aucune alerte attribuée." +
                    "</td>" +
                "</tr>";

        }

        // =====================================
        // AFFICHER LES ALERTES
        // =====================================

        else {

            alertes.forEach(
                function (alerte) {

                    let actions = "";


                    // =========================
                    // POSITION GPS
                    // =========================

                    if (
                        alerte.latitude !== null &&
                        alerte.longitude !== null &&
                        alerte.latitude !== "" &&
                        alerte.longitude !== ""
                    ) {

                        actions +=
                            "<button " +
                                "type='button' " +
                                "class='police-action position-button' " +
                                "data-latitude='" +
                                escapeHTML(
                                    alerte.latitude
                                ) +
                                "' " +
                                "data-longitude='" +
                                escapeHTML(
                                    alerte.longitude
                                ) +
                            "'>" +
                                "📍 Position" +
                            "</button>";
                    }


                    // =========================
                    // NOUVELLE
                    // =========================

                    if (
                        alerte.statut ===
                        "nouvelle"
                    ) {

                        actions +=
                            "<button " +
                                "type='button' " +
                                "class='police-action statut-button' " +
                                "data-id-alerte='" +
                                escapeHTML(
                                    alerte.id_alerte
                                ) +
                                "' " +
                                "data-statut='en_traitement'" +
                            ">" +
                                "🚔 Prendre en charge" +
                            "</button>";
                    }


                    // =========================
                    // EN TRAITEMENT
                    // =========================

                    if (
                        alerte.statut ===
                        "en_traitement"
                    ) {

                        actions +=
                            "<button " +
                                "type='button' " +
                                "class='police-action statut-button resolve-button' " +
                                "data-id-alerte='" +
                                escapeHTML(
                                    alerte.id_alerte
                                ) +
                                "' " +
                                "data-statut='resolue'" +
                            ">" +
                                "✅ Résoudre" +
                            "</button>";
                    }


                    // =========================
                    // RÉSOLUE
                    // =========================

                    if (
                        alerte.statut ===
                        "resolue"
                    ) {

                        actions +=
                            "<span>" +
                                "✅ Résolue" +
                            "</span>";
                    }


                    // =========================
                    // CRÉER LA LIGNE
                    // =========================

                    tableau.innerHTML +=

                        "<tr>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.id_alerte
                                ) +
                            "</td>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.niveau
                                ) +
                            "</td>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.type_alerte
                                ) +
                            "</td>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.description
                                ) +
                            "</td>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.id_course ||
                                    "-"
                                ) +
                            "</td>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.statut
                                ) +
                            "</td>" +

                            "<td>" +
                                escapeHTML(
                                    alerte.date_creation
                                ) +
                            "</td>" +

                            "<td class='actions-cell'>" +
                                actions +
                            "</td>" +

                        "</tr>";

                }
            );

        }


        // =====================================
        // ALERTES RÉCENTES DU DASHBOARD
        // =====================================

        if (dashboard) {

            dashboard.innerHTML = "";

            const recentes =
                alertes.slice(
                    0,
                    5
                );


            if (
                recentes.length === 0
            ) {

                dashboard.innerHTML =
                    "<div class='empty-state'>" +
                        "<div class='empty-icon'>🚔</div>" +
                        "<strong>Aucune alerte</strong>" +
                        "<p>" +
                            "Les alertes attribuées apparaîtront ici." +
                        "</p>" +
                    "</div>";

            }
            else {

                recentes.forEach(
                    function (alerte) {

                        dashboard.innerHTML +=

                            "<div class='police-list-item'>" +

                                "<strong>" +
                                    "🚨 " +
                                    escapeHTML(
                                        alerte.type_alerte
                                    ) +
                                "</strong>" +

                                "<p>" +
                                    escapeHTML(
                                        alerte.description
                                    ) +
                                "</p>" +

                                "<small>" +
                                    escapeHTML(
                                        alerte.niveau
                                    ) +
                                    " · " +
                                    escapeHTML(
                                        alerte.statut
                                    ) +
                                "</small>" +

                            "</div>";

                    }
                );

            }

        }

    }
    catch (erreur) {

        console.error(
            "Erreur alertes police :",
            erreur
        );


        tableau.innerHTML =
            "<tr>" +
                "<td colspan='8' class='table-empty'>" +
                    "Erreur lors du chargement des alertes." +
                "</td>" +
            "</tr>";

    }

}

        async function chargerNotifications(
            annoncer
        ) {

            try {

                const resultat =
                    await getJSON(
                        "notifications.php"
                    );


                const notifications =
                    resultat.notifications || [];


                const container =
                    document.getElementById(
                        "notificationsContainer"
                    );


                const dashboard =
                    document.getElementById(
                        "dashboardNotifications"
                    );


                container.innerHTML = "";
                dashboard.innerHTML = "";


                if (
                    notifications.length === 0
                ) {

                    container.innerHTML =
                        "<div class='empty-state'>" +
                        "<div class='empty-icon'>🔔</div>" +
                        "<strong>Aucune notification</strong>" +
                        "</div>";

                    dashboard.innerHTML =
                        "<div class='empty-state'>" +
                        "<strong>Aucune notification</strong>" +
                        "</div>";

                    return;
                }


                const maxId =
                    Math.max(
                        ...notifications.map(
                            function (n) {
                                return parseInt(
                                    n.id_notification,
                                    10
                                );
                            }
                        )
                    );


                if (
                    dernierIdNotification === 0
                ) {

                    dernierIdNotification =
                        maxId;

                }
                else if (
                    annoncer &&
                    maxId >
                    dernierIdNotification
                ) {

                    const nouvelle =
                        notifications.find(
                            function (n) {

                                return (
                                    parseInt(
                                        n.id_notification,
                                        10
                                    ) >
                                    dernierIdNotification
                                );
                            }
                        );


                    if (nouvelle) {

                        toast(
                            "🚨 " +
                            nouvelle.titre +
                            " — " +
                            nouvelle.contenu
                        );
                    }


                    dernierIdNotification =
                        maxId;
                }


                notifications.forEach(
                    function (notification) {

                        container.innerHTML +=

                            "<div class='notification-police-item " +
                            (
                                parseInt(
                                    notification.lu,
                                    10
                                ) === 0
                                    ? "non-lue"
                                    : ""
                            ) +
                            "'>" +

                            "<strong>" +
                            escapeHTML(
                                notification.titre
                            ) +
                            "</strong>" +

                            "<p>" +
                            escapeHTML(
                                notification.contenu
                            ) +
                            "</p>" +

                            "<small>" +
                            escapeHTML(
                                notification.date_creation
                            ) +
                            "</small>" +

                            "</div>";
                    }
                );


                notifications
                    .slice(
                        0,
                        5
                    )
                    .forEach(
                        function (notification) {

                            dashboard.innerHTML +=

                                "<div class='police-list-item'>" +

                                "<strong>" +
                                "🔔 " +
                                escapeHTML(
                                    notification.titre
                                ) +
                                "</strong>" +

                                "<p>" +
                                escapeHTML(
                                    notification.contenu
                                ) +
                                "</p>" +

                                "</div>";
                        }
                    );

            }
            catch (erreur) {

                console.error(
                    "Erreur notifications police :",
                    erreur
                );
            }
        }


        async function lireNotifications() {

            try {

                await fetch(
                    "lire_notifications.php",
                    {
                        method: "POST",
                        credentials:
                            "same-origin"
                    }
                );


                await chargerStatistiques();

                await chargerNotifications(
                    false
                );

            }
            catch (erreur) {

                console.error(
                    "Erreur lecture notifications :",
                    erreur
                );
            }
        }


        document.addEventListener(
            "click",
            async function (event) {

                const position =
                    event.target.closest(
                        ".position-button"
                    );


                if (position) {

                    const latitude =
                        position.dataset.latitude;

                    const longitude =
                        position.dataset.longitude;


                    window.open(
                        "https://www.google.com/maps?q=" +
                        encodeURIComponent(
                            latitude +
                            "," +
                            longitude
                        ),
                        "_blank"
                    );

                    return;
                }


                const statutButton =
                    event.target.closest(
                        ".statut-button"
                    );


                if (!statutButton) {
                    return;
                }


                const idAlerte =
                    statutButton.dataset.idAlerte;

                const statut =
                    statutButton.dataset.statut;


                const donnees =
                    new FormData();


                donnees.append(
                    "id_alerte",
                    idAlerte
                );

                donnees.append(
                    "statut",
                    statut
                );


                try {

                    statutButton.disabled =
                        true;


                    const reponse =
                        await fetch(
                            "changer_statut.php",
                            {
                                method: "POST",
                                body: donnees,
                                credentials:
                                    "same-origin"
                            }
                        );


                    const resultat =
                        await reponse.json();


                    if (
                        !reponse.ok ||
                        !resultat.success
                    ) {

                        throw new Error(
                            resultat.message
                        );
                    }


                    toast(
                        "✅ " +
                        resultat.message
                    );


                    await chargerAlertes();

                    await chargerStatistiques();

                }
                catch (erreur) {

                    alert(
                        "❌ " +
                        erreur.message
                    );

                }
                finally {

                    statutButton.disabled =
                        false;
                }

            }
        );


        const notificationButton =
            document.getElementById(
                "notificationButton"
            );


        notificationButton.addEventListener(
            "click",
            async function () {

                ouvrirSection(
                    "notifications"
                );

                await lireNotifications();
            }
        );


        const menuNotifications =
            document.querySelector(
                ".menu-item[data-section='notifications']"
            );


        if (menuNotifications) {

            menuNotifications.addEventListener(
                "click",
                lireNotifications
            );
        }


        chargerStatistiques();

        chargerAlertes();

        chargerNotifications(
            false
        );


        setInterval(
            function () {

                chargerStatistiques();

                chargerAlertes();

                chargerNotifications(
                    true
                );

            },
            5000
        );

    }
);