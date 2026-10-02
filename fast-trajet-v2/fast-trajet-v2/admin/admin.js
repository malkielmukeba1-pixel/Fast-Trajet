document.addEventListener(
    "DOMContentLoaded",
    function () {

        console.log(
            "FAST TRAJET V2 — ADMIN.JS"
        );


        // =================================================
        // ÉLÉMENTS
        // =================================================

        const menuButton =
            document.getElementById(
                "menuButton"
            );

        const sidebar =
            document.getElementById(
                "sidebar"
            );

        const overlay =
            document.getElementById(
                "overlay"
            );

        const logoutButton =
            document.getElementById(
                "logoutButton"
            );

        const refreshButton =
            document.getElementById(
                "refreshButton"
            );


        const menuItems =
            document.querySelectorAll(
                ".menu-item"
            );

        const sections =
            document.querySelectorAll(
                ".admin-section"
            );


        // =================================================
        // MENU MOBILE
        // =================================================

        function fermerMenu() {

            if (sidebar) {
                sidebar.classList.remove(
                    "active"
                );
            }

            if (overlay) {
                overlay.classList.remove(
                    "active"
                );
            }
        }


        if (
            menuButton &&
            sidebar
        ) {

            menuButton.addEventListener(
                "click",
                function (event) {

                    event.stopPropagation();

                    sidebar.classList.toggle(
                        "active"
                    );

                    if (overlay) {

                        overlay.classList.toggle(
                            "active"
                        );

                    }

                }
            );

        }


        if (overlay) {

            overlay.addEventListener(
                "click",
                fermerMenu
            );

        }


        // =================================================
        // NAVIGATION
        // =================================================

        function afficherSection(
            nomSection
        ) {

            menuItems.forEach(
                function (item) {

                    item.classList.toggle(
                        "active",
                        item.dataset.section ===
                        nomSection
                    );

                }
            );


            sections.forEach(
                function (section) {

                    section.classList.toggle(
                        "active",
                        section.id ===
                        nomSection
                    );

                }
            );


            fermerMenu();


            if (
                nomSection ===
                "utilisateurs"
            ) {

                chargerUtilisateurs();

            }

            else if (
                nomSection ===
                "courses"
            ) {

                chargerCourses();

            }

            else if (
                nomSection ===
                "alertes"
            ) {

                chargerAlertes();

            }

            else if (
                nomSection ===
                "historique"
            ) {

                chargerHistorique();

            }

        }


        menuItems.forEach(
            function (item) {

                item.addEventListener(
                    "click",
                    function () {

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
                function (button) {

                    button.addEventListener(
                        "click",
                        function () {

                            afficherSection(
                                button.dataset.sectionLink
                            );

                        }
                    );

                }
            );


        // =================================================
        // OUTIL FETCH JSON
        // =================================================

        async function getJSON(
            url
        ) {

            const reponse =
                await fetch(
                    url,
                    {
                        credentials: "same-origin"
                    }
                );


            const texte =
                await reponse.text();


            let resultat;


            try {

                resultat =
                    JSON.parse(texte);

            }
            catch (erreur) {

                console.error(
                    "Réponse serveur invalide :",
                    texte
                );

                throw new Error(
                    "Le serveur n'a pas renvoyé un JSON valide."
                );

            }


            if (!reponse.ok) {

                throw new Error(
                    resultat.message ||
                    "Erreur serveur."
                );

            }


            return resultat;

        }


        // =================================================
        // STATISTIQUES
        // =================================================

        async function chargerStatistiques() {

            try {

                const resultat =
                    await getJSON(
                        "statistiques.php"
                    );


                if (!resultat.success) {
                    throw new Error(
                        resultat.message
                    );
                }


                document.getElementById(
                    "statClients"
                ).textContent =
                    resultat.clients;


                document.getElementById(
                    "statChauffeurs"
                ).textContent =
                    resultat.chauffeurs;


                document.getElementById(
                    "statCourses"
                ).textContent =
                    resultat.courses;


                document.getElementById(
                    "statAlertes"
                ).textContent =
                    resultat.alertes_actives;


                document.getElementById(
                    "statEnAttente"
                ).textContent =
                    resultat.courses_en_attente;


                document.getElementById(
                    "statEnCours"
                ).textContent =
                    resultat.courses_en_cours;


                document.getElementById(
                    "statTerminees"
                ).textContent =
                    resultat.courses_terminees;

            }

            catch (erreur) {

                console.error(
                    "Erreur statistiques :",
                    erreur
                );

            }

        }


        // =================================================
        // UTILISATEURS
        // =================================================

        async function chargerUtilisateurs() {

            const clientsTable =
                document.getElementById(
                    "clientsTable"
                );

            const chauffeursTable =
                document.getElementById(
                    "chauffeursTable"
                );


            clientsTable.innerHTML =
                "<tr><td colspan='5'>Chargement...</td></tr>";

            chauffeursTable.innerHTML =
                "<tr><td colspan='5'>Chargement...</td></tr>";


            try {

                const resultat =
                    await getJSON(
                        "utilisateurs.php"
                    );


                clientsTable.innerHTML = "";


                resultat.clients.forEach(
                    function (client) {

                        clientsTable.innerHTML +=

                            "<tr>" +

                            "<td>" +
                            escapeHTML(
                                client.id_client
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                client.nom +
                                " " +
                                client.prenom
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                client.telephone
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                client.email
                            ) +
                            "</td>" +

                            "<td>" +
                            creerBadge(
                                client.statut
                            ) +
                            "</td>" +

                            "</tr>";

                    }
                );


                chauffeursTable.innerHTML = "";


                resultat.chauffeurs.forEach(
                    function (chauffeur) {

                        const disponibilite =
                            chauffeur.disponibilite === 1
                                ? "Disponible"
                                : "Indisponible";


                        chauffeursTable.innerHTML +=

                            "<tr>" +

                            "<td>" +
                            escapeHTML(
                                chauffeur.id_chauffeur
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                chauffeur.nom +
                                " " +
                                chauffeur.prenom
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                chauffeur.telephone
                            ) +
                            "</td>" +

                            "<td>" +
                            creerBadge(
                                disponibilite
                            ) +
                            "</td>" +

                            "<td>" +
                            creerBadge(
                                chauffeur.statut
                            ) +
                            "</td>" +

                            "</tr>";

                    }
                );

            }

            catch (erreur) {

                console.error(
                    "Erreur utilisateurs :",
                    erreur
                );

                clientsTable.innerHTML =
                    "<tr><td colspan='5'>Erreur de chargement.</td></tr>";

                chauffeursTable.innerHTML =
                    "<tr><td colspan='5'>Erreur de chargement.</td></tr>";

            }

        }


        // =================================================
        // COURSES
        // =================================================

        async function chargerCourses() {

            const tableau =
                document.getElementById(
                    "coursesTable"
                );


            const dashboard =
                document.getElementById(
                    "dashboardCourses"
                );


            tableau.innerHTML =
                "<tr><td colspan='7'>Chargement...</td></tr>";


            dashboard.innerHTML =
                "<p class='loading'>Chargement...</p>";


            try {

                const resultat =
                    await getJSON(
                        "courses.php"
                    );


                tableau.innerHTML = "";


                resultat.courses.forEach(
                    function (course) {

                        tableau.innerHTML +=

                            "<tr>" +

                            "<td>" +
                            escapeHTML(
                                course.id_course
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                (
                                    course.nom_client ||
                                    ""
                                ) +
                                " " +
                                (
                                    course.prenom_client ||
                                    ""
                                )
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                (
                                    course.nom_chauffeur ||
                                    "Non attribué"
                                ) +
                                " " +
                                (
                                    course.prenom_chauffeur ||
                                    ""
                                )
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                course.lieu_depart
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                course.lieu_destination
                            ) +
                            "</td>" +

                            "<td>" +
                            (
                                course.prix_accepte !== null
                                    ? escapeHTML(
                                        course.prix_accepte
                                    ) + " $"
                                    : "-"
                            ) +
                            "</td>" +

                            "<td>" +
                            creerBadge(
                                course.statut_course
                            ) +
                            "</td>" +

                            "</tr>";

                    }
                );


                const dernieres =
                    resultat.courses.slice(
                        0,
                        5
                    );


                dashboard.innerHTML =
                    "";


                if (
                    dernieres.length === 0
                ) {

                    dashboard.innerHTML =
                        "<p>Aucune course.</p>";

                }
                else {

                    dernieres.forEach(
                        function (course) {

                            dashboard.innerHTML +=

                                "<div class='card-item'>" +

                                "<strong>" +
                                "🚕 Course #" +
                                escapeHTML(
                                    course.id_course
                                ) +
                                "</strong>" +

                                "<div>" +
                                escapeHTML(
                                    course.lieu_depart
                                ) +
                                " → " +
                                escapeHTML(
                                    course.lieu_destination
                                ) +
                                "</div>" +

                                "<small>" +
                                escapeHTML(
                                    course.statut_course
                                ) +
                                "</small>" +

                                "</div>";

                        }
                    );

                }

            }

            catch (erreur) {

                console.error(
                    "Erreur courses :",
                    erreur
                );

                tableau.innerHTML =
                    "<tr><td colspan='7'>Erreur de chargement.</td></tr>";

            }

        }

         
        let policiersAdmin = [];


async function chargerPoliciersAdmin() {

    const resultat =
        await getJSON(
            "policiers.php"
        );


    if (!resultat.success) {

        throw new Error(
            resultat.message ||
            "Impossible de charger les policiers."
        );
    }


    policiersAdmin =
        Array.isArray(
            resultat.policiers
        )
            ? resultat.policiers
            : [];
}



function creerOptionsPoliciers(
    idPolicierActuel
) {

    let html =
        "<option value=''>Choisir un policier</option>";


    policiersAdmin.forEach(
        function (policier) {

            const selected =
                String(
                    policier.id_policier
                ) ===
                String(
                    idPolicierActuel || ""
                )
                    ? " selected"
                    : "";


            const libelle =
                (
                    policier.grade
                        ? policier.grade + " "
                        : ""
                ) +
                policier.prenom +
                " " +
                policier.nom +
                " (" +
                policier.matricule +
                ")";


            html +=
                "<option value='" +
                escapeHTML(
                    policier.id_policier
                ) +
                "'" +
                selected +
                ">" +
                escapeHTML(
                    libelle
                ) +
                "</option>";
        }
    );


    return html;
}



async function attribuerAlertePolice(
    idAlerte,
    idPolicier
) {

    const donnees =
        new FormData();


    donnees.append(
        "id_alerte",
        idAlerte
    );

    donnees.append(
        "id_policier",
        idPolicier
    );


    const reponse =
        await fetch(
            "attribuer_alerte.php",
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
            resultat.message ||
            "Attribution impossible."
        );
    }


    alert(
        "✅ " +
        resultat.message
    );


    await chargerAlertes();
    await chargerStatistiques();
}

        // =================================================
        // ALERTES
        // =================================================

        async function chargerAlertes() {

            const tableau =
                document.getElementById(
                    "alertesTable"
                );

            const dashboard =
                document.getElementById(
                    "dashboardAlertes"
                );


            tableau.innerHTML =
                "<tr><td colspan='8'>Chargement...</td></tr>";


            dashboard.innerHTML =
                "<p class='loading'>Chargement...</p>";


            try {
                  await chargerPoliciersAdmin();
                const resultat =
                    await getJSON(
                        "alertes.php"
                    );


                tableau.innerHTML = "";


                resultat.alertes.forEach(
                    function (alerte) {

                        tableau.innerHTML +=

                            "<tr>" +

                            "<td>" +
                            escapeHTML(
                                alerte.id_alerte
                            ) +
                            "</td>" +

                            "<td>" +
                            creerBadge(
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
                            creerBadge(
                                alerte.statut
                            ) +
                            "</td>" +

                            "<td>" +
                            "<td>" +
escapeHTML(
    alerte.date_creation
) +
"</td>" +

"<td>" +

    "<div class='police-assignment'>" +

        "<select " +
            "class='policier-select' " +
            "data-id-alerte='" +
            escapeHTML(
                alerte.id_alerte
            ) +
            "'>" +

            creerOptionsPoliciers(
                alerte.id_policier
            ) +

        "</select>" +

        "<button " +
            "type='button' " +
            "class='attribuer-police-button' " +
            "data-id-alerte='" +
            escapeHTML(
                alerte.id_alerte
            ) +
            "'>" +

            "👮 Transmettre" +

        "</button>" +

    "</div>" +

"</td>" +

"</tr>";

                            "</tr>";

                    }
                );


                const recentes =
                    resultat.alertes.slice(
                        0,
                        5
                    );


                dashboard.innerHTML = "";


                if (
                    recentes.length === 0
                ) {

                    dashboard.innerHTML =
                        "<p>Aucune alerte.</p>";

                }
                else {

                    recentes.forEach(
                        function (alerte) {

                            dashboard.innerHTML +=

                                "<div class='card-item'>" +

                                "<strong>" +
                                "🚨 " +
                                escapeHTML(
                                    alerte.type_alerte
                                ) +
                                "</strong>" +

                                "<div>" +
                                escapeHTML(
                                    alerte.description
                                ) +
                                "</div>" +

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

            catch (erreur) {

                console.error(
                    "Erreur alertes :",
                    erreur
                );

                tableau.innerHTML =
                    "<tr><td colspan='8'>Erreur de chargement.</td></tr>";

            }

        }


        // =================================================
        // HISTORIQUE
        // =================================================

        async function chargerHistorique() {

            const tableau =
                document.getElementById(
                    "historiqueTable"
                );


            tableau.innerHTML =
                "<tr><td colspan='5'>Chargement...</td></tr>";


            try {

                const resultat =
                    await getJSON(
                        "historique.php?limite=100"
                    );


                tableau.innerHTML = "";


                resultat.historique.forEach(
                    function (ligne) {

                        let utilisateur =
                            "Système";


                        if (
                            ligne.nom_client
                        ) {

                            utilisateur =
                                "Client : " +
                                ligne.nom_client +
                                " " +
                                ligne.prenom_client;

                        }
                        else if (
                            ligne.nom_chauffeur
                        ) {

                            utilisateur =
                                "Chauffeur : " +
                                ligne.nom_chauffeur +
                                " " +
                                ligne.prenom_chauffeur;

                        }
                        else if (
                            ligne.nom_administrateur
                        ) {

                            utilisateur =
                                "Admin : " +
                                ligne.nom_administrateur +
                                " " +
                                ligne.prenom_administrateur;

                        }


                        tableau.innerHTML +=

                            "<tr>" +

                            "<td>" +
                            escapeHTML(
                                ligne.date_action
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                ligne.action
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                ligne.description ||
                                ""
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                utilisateur
                            ) +
                            "</td>" +

                            "<td>" +
                            escapeHTML(
                                ligne.id_course ||
                                "-"
                            ) +
                            "</td>" +

                            "</tr>";

                    }
                );

            }

            catch (erreur) {

                console.error(
                    "Erreur historique :",
                    erreur
                );

                tableau.innerHTML =
                    "<tr><td colspan='5'>Erreur de chargement.</td></tr>";

            }

        }


        // =================================================
        // BADGE
        // =================================================

        function creerBadge(
            valeur
        ) {

            return (
                "<span class='status'>" +
                escapeHTML(
                    valeur
                ) +
                "</span>"
            );

        }


        // =================================================
        // PROTECTION HTML
        // =================================================

        function escapeHTML(
            valeur
        ) {

            if (
                valeur === null ||
                valeur === undefined
            ) {

                return "";

            }


            return String(valeur)
                .replace(
                    /&/g,
                    "&amp;"
                )
                .replace(
                    /</g,
                    "&lt;"
                )
                .replace(
                    />/g,
                    "&gt;"
                )
                .replace(
                    /"/g,
                    "&quot;"
                )
                .replace(
                    /'/g,
                    "&#039;"
                );

        }


        // =================================================
        // DÉCONNEXION
        // =================================================

        if (logoutButton) {

            logoutButton.addEventListener(
                "click",
                async function () {

                    const confirmation =
                        confirm(
                            "Voulez-vous vraiment vous déconnecter ?"
                        );


                    if (!confirmation) {
                        return;
                    }


                    try {

                        const donnees =
                            new FormData();


                        const reponse =
                            await fetch(
                                "../api/auth/deconnexion.php",
                                {
                                    method: "POST",
                                    body: donnees
                                }
                            );


                        const resultat =
                            await reponse.json();


                        if (
                            resultat.success
                        ) {

                            window.location.href =
                                "../index.php";

                        }
                        else {

                            alert(
                                resultat.message ||
                                "Déconnexion impossible."
                            );

                        }

                    }

                    catch (erreur) {

                        console.error(
                            "Erreur déconnexion :",
                            erreur
                        );

                        alert(
                            "Impossible de se déconnecter."
                        );

                    }

                }
            );

        }


        // =================================================
        // ACTUALISATION
        // =================================================

        if (refreshButton) {

            refreshButton.addEventListener(
                "click",
                async function () {

                    refreshButton.disabled =
                        true;

                    refreshButton.textContent =
                        "⏳ Actualisation...";


                    await chargerStatistiques();

                    await chargerCourses();

                    await chargerAlertes();


                    refreshButton.disabled =
                        false;

                    refreshButton.textContent =
                        "🔄 Actualiser";

                }
            );

        }


        // =================================================
        // DÉMARRAGE
        // =================================================

        chargerStatistiques();

        chargerCourses();

        chargerAlertes();


        document.addEventListener(
    "click",
    async function (event) {

        const bouton =
            event.target.closest(
                ".attribuer-police-button"
            );


        if (!bouton) {
            return;
        }


        const idAlerte =
            bouton.dataset.idAlerte;


        const selecteur =
            document.querySelector(
                ".policier-select[data-id-alerte='" +
                idAlerte +
                "']"
            );


        if (!selecteur) {
            return;
        }


        const idPolicier =
            selecteur.value;


        if (!idPolicier) {

            alert(
                "Choisissez d'abord un policier."
            );

            return;
        }


        const confirmation =
            confirm(
                "Transmettre cette alerte au policier sélectionné ?"
            );


        if (!confirmation) {
            return;
        }


        try {

            bouton.disabled = true;

            bouton.textContent =
                "Transmission...";


            await attribuerAlertePolice(
                idAlerte,
                idPolicier
            );

        }
        catch (erreur) {

            console.error(
                "Erreur attribution police :",
                erreur
            );


            alert(
                "❌ " +
                erreur.message
            );

        }
        finally {

            bouton.disabled = false;

            bouton.textContent =
                "👮 Transmettre";
        }

    }
);
    }

    
);
