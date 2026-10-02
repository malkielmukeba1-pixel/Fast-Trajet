document.addEventListener(
    "DOMContentLoaded",
    function () {

        console.log(
            "FAST TRAJET V2 — CHAUFFEUR.JS"
        );


        // =================================================
        // VARIABLES
        // =================================================

        let map = null;

        let chauffeurPosition = null;

        let markerClient = null;

        let markerDestination = null;

        let markerChauffeur = null;

        let routeLayer = null;

        let routeCourseId = null;

        let courseActive = null;

        let surveillanceCourse = null;

        let surveillanceGPS = null;

        let derniereProposition = null;


        // =================================================
        // ÉLÉMENTS
        // =================================================

        const coursesContainer =
            document.getElementById(
                "coursesContainer"
            );

        const availabilityButton =
            document.getElementById(
                "availabilityButton"
            );

        const availabilityStatus =
            document.getElementById(
                "availabilityStatus"
            );

        const refreshCoursesButton =
            document.getElementById(
                "refreshCoursesButton"
            );

        const courseActiveSection =
            document.getElementById(
                "courseActiveSection"
            );

        const courseStatus =
            document.getElementById(
                "courseStatus"
            );

        const courseDetails =
            document.getElementById(
                "courseDetails"
            );

        const clientDetails =
            document.getElementById(
                "clientDetails"
            );

        const priceBox =
            document.getElementById(
                "priceBox"
            );

        const priceCurrent =
            document.getElementById(
                "priceCurrent"
            );

        const negotiationHistory =
            document.getElementById(
                "negotiationHistory"
            );

        const driverPrice =
            document.getElementById(
                "driverPrice"
            );

        const proposePriceButton =
            document.getElementById(
                "proposePriceButton"
            );

        const acceptPriceButton =
            document.getElementById(
                "acceptPriceButton"
            );

        const priceMessage =
            document.getElementById(
                "priceMessage"
            );

        const startCourseButton =
            document.getElementById(
                "startCourseButton"
            );

        const finishCourseButton =
            document.getElementById(
                "finishCourseButton"
            );

        const trackingMessage =
            document.getElementById(
                "trackingMessage"
            );

        const notificationsSection =
            document.getElementById(
                "notificationsSection"
            );

        const notificationsContainer =
            document.getElementById(
                "notificationsContainer"
            );

        const notificationButton =
            document.getElementById(
                "notificationButton"
            );

        const notificationCount =
            document.getElementById(
                "notificationCount"
            );

        const menuButton =
            document.getElementById(
                "menuButton"
            );

        const mobileMenu =
            document.getElementById(
                "mobileMenu"
            );

          setInterval(
    async function () {

        await chargerNombreNotifications();

        if (
            notificationsSection &&
            !notificationsSection.classList.contains(
                "hidden"
            )
        ) {

            await chargerNotifications();
        }

    },
    5000
);

   async function marquerToutesNotificationsLuesChauffeur() {

    try {

        const donnees =
            new FormData();


        donnees.append(
            "tout",
            "1"
        );


        const resultat =
            await getJSON(
                "../api/notifications/lire.php",
                {
                    method: "POST",
                    body: donnees
                }
            );


        console.log(
            "Notifications chauffeur lues :",
            resultat.nombre_modifie
        );


        await chargerNombreNotifications();

    }
    catch (erreur) {

        console.error(
            "Erreur lecture notifications chauffeur :",
            erreur
        );
    }
}   


        // =================================================
        // CARTE
        // =================================================

        if (
            typeof L ===
            "undefined"
        ) {

            console.error(
                "Leaflet n'est pas chargé."
            );

            return;
        }


        map =
            L.map(
                "map"
            ).setView(
                [-4.325, 15.322],
                12
            );


        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom:
                    19,

                attribution:
                    "&copy; OpenStreetMap contributors"
            }
        ).addTo(map);


        // =================================================
        // MENU
        // =================================================

        if (menuButton) {

            menuButton.addEventListener(
                "click",
                function () {

                    mobileMenu.classList.toggle(
                        "open"
                    );

                }
            );

        }


        // =================================================
        // OUTIL FETCH JSON
        // =================================================

        async function getJSON(
            url,
            options = {}
        ) {

            const reponse =
                await fetch(
                    url,
                    {
                        credentials:
                            "same-origin",

                        ...options
                    }
                );


            const texte =
                await reponse.text();


            let resultat;


            try {

                resultat =
                    JSON.parse(
                        texte
                    );

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


            if (
                !reponse.ok
            ) {

                throw new Error(
                    resultat.message ||
                    "Erreur serveur."
                );

            }


            return resultat;

        }


        // =================================================
        // DISPONIBILITÉ
        // =================================================

        async function chargerDisponibilite() {

            try {

                const resultat =
                    await getJSON(
                        "../api/chauffeur/disponibilite.php"
                    );


                if (
                    !resultat.success
                ) {

                    throw new Error(
                        resultat.message
                    );

                }


                afficherDisponibilite(
                    resultat.disponibilite
                );

            }

            catch (erreur) {

                console.error(
                    "Erreur disponibilité :",
                    erreur
                );

            }

        }


        function afficherDisponibilite(
            disponibilite
        ) {

            const disponible =
                parseInt(
                    disponibilite
                ) === 1;


            availabilityStatus.textContent =
                disponible
                    ? "🟢 Disponible"
                    : "🔴 Indisponible";


            availabilityButton.textContent =
                disponible
                    ? "🔴 Devenir indisponible"
                    : "🟢 Devenir disponible";


            if (
                disponible
            ) {

                chargerCourses();

            }
            else {

                coursesContainer.innerHTML =
                    "<p>Vous êtes actuellement indisponible.</p>";

            }

        }


        if (
            availabilityButton
        ) {

            availabilityButton.addEventListener(
                "click",
                async function () {

                    availabilityButton.disabled =
                        true;


                    try {

                        const resultat =
                            await getJSON(
                                "../api/chauffeur/disponibilite.php",
                                {
                                    method:
                                        "POST"
                                }
                            );


                        if (
                            !resultat.success
                        ) {

                            throw new Error(
                                resultat.message
                            );

                        }


                        afficherDisponibilite(
                            resultat.disponibilite
                        );

                    }

                    catch (erreur) {

                        console.error(
                            erreur
                        );

                        alert(
                            "❌ " +
                            erreur.message
                        );

                    }


                    availabilityButton.disabled =
                        false;

                }
            );

        }


        // =================================================
        // COURSES DISPONIBLES
        // =================================================

        async function chargerCourses() {

            try {

                coursesContainer.innerHTML =
                    "<p class='loading'>🔎 Recherche des courses...</p>";


                const resultat =
                    await getJSON(
                        "../api/chauffeur/courses_disponibles.php"
                    );


                if (
                    !resultat.success
                ) {

                    throw new Error(
                        resultat.message
                    );

                }


                if (
                    parseInt(
                        resultat.disponibilite
                    ) !== 1
                ) {

                    coursesContainer.innerHTML =
                        "<p>Vous êtes indisponible.</p>";

                    return;
                }


                if (
                    !resultat.courses ||
                    resultat.courses.length === 0
                ) {

                    coursesContainer.innerHTML =
                        "<p>Aucune course disponible pour le moment.</p>";

                    return;
                }


                coursesContainer.innerHTML =
                    "";


                resultat.courses.forEach(
                    function (course) {

                        afficherCourseDisponible(
                            course
                        );

                    }
                );

            }

            catch (erreur) {

                console.error(
                    "Erreur courses :",
                    erreur
                );


                coursesContainer.innerHTML =
                    "<p>❌ Impossible de récupérer les courses.</p>";

            }

        }


        function afficherCourseDisponible(
            course
        ) {

            const bloc =
                document.createElement(
                    "div"
                );


            bloc.className =
                "course-card";


            bloc.innerHTML =

                "<h3>🚕 Nouvelle demande</h3>" +

                "<p><strong>📍 Départ :</strong><br>" +
                escapeHTML(
                    course.lieu_depart ||
                    "Non renseigné"
                ) +
                "</p>" +

                "<p><strong>🏁 Destination :</strong><br>" +
                escapeHTML(
                    course.lieu_destination ||
                    "Non renseignée"
                ) +
                "</p>" +

                "<p><strong>📏 Distance :</strong> " +
                (
                    course.distance !== null &&
                    course.distance !== undefined
                        ? parseFloat(
                            course.distance
                        ).toFixed(1) +
                        " km"
                        : "—"
                ) +
                "</p>" +

                "<p><strong>⏱️ Durée :</strong> " +
                (
                    course.duree_estimee !== null &&
                    course.duree_estimee !== undefined
                        ? course.duree_estimee +
                        " min"
                        : "—"
                ) +
                "</p>" +

                "<button " +
                "type='button' " +
                "class='accept-course-button'>" +
                "🚕 Accepter la course" +
                "</button>";


            coursesContainer.appendChild(
                bloc
            );


            const bouton =
                bloc.querySelector(
                    ".accept-course-button"
                );


            bouton.addEventListener(
                "click",
                function () {

                    accepterCourse(
                        course.id_course,
                        bouton
                    );

                }
            );

        }


        // =================================================
        // ACCEPTER UNE COURSE
        // =================================================

        async function accepterCourse(
            idCourse,
            bouton
        ) {

            bouton.disabled =
                true;

            bouton.textContent =
                "⏳ Acceptation...";


            const donnees =
                new FormData();


            donnees.append(
                "id_course",
                idCourse
            );


            try {

                const resultat =
                    await getJSON(
                        "../api/chauffeur/accepter_course.php",
                        {
                            method:
                                "POST",

                            body:
                                donnees
                        }
                    );


                if (
                    !resultat.success
                ) {

                    throw new Error(
                        resultat.message
                    );

                }


                alert(
                    "✅ " +
                    resultat.message
                );


                courseActive = {

                    id_course:
                        resultat.id_course,

                    statut_course:
                        resultat.statut_course

                };


                courseActiveSection.classList.remove(
                    "hidden"
                );


                await chargerDetailsCourse(
                    resultat.id_course
                );


                arreterSurveillanceCourses();

                demarrerSurveillanceCourse();


            }

            catch (erreur) {

                console.error(
                    "Erreur acceptation :",
                    erreur
                );


                alert(
                    "❌ " +
                    erreur.message
                );


                bouton.disabled =
                    false;

                bouton.textContent =
                    "🚕 Accepter la course";

            }

        }


        // =================================================
        // COURSE ACTIVE
        // =================================================

        async function chargerDetailsCourse(
            idCourse
        ) {

            try {

                const resultat =
                    await getJSON(
                        "../api/course/details.php?id_course=" +
                        encodeURIComponent(
                            idCourse
                        )
                    );


                if (
                    !resultat.success ||
                    !resultat.course
                ) {

                    return;

                }


                courseActive =
                    resultat.course;


                afficherCourseActive(
                    courseActive
                );

                courseActive =
    resultat.course;


afficherCourseActive(
    courseActive
);


// =============================================
// AFFICHER LA ROUTE BLEUE
// =============================================

await afficherItineraireCourseChauffeur(
    courseActive
);


chargerNegociation(
    idCourse
);


chargerPositionCourse(
    idCourse
);


                chargerNegociation(
                    idCourse
                );


                chargerPositionCourse(
                    idCourse
                );

                // =================================================
// MESSAGERIE CHAUFFEUR <-> CLIENT
// =================================================

await mettreAJourMessagerieChauffeur(
    courseActive
);

            }

            catch (erreur) {

                console.error(
                    "Erreur détails course :",
                    erreur
                );

            }

        }


        function afficherCourseActive(
            course
        ) {

            courseActiveSection.classList.remove(
                "hidden"
            );


            courseStatus.textContent =
                course.statut_course ||
                "—";


            courseDetails.innerHTML =

                "<p><strong>📍 Départ :</strong><br>" +
                escapeHTML(
                    course.lieu_depart ||
                    ""
                ) +
                "</p>" +

                "<p><strong>🏁 Destination :</strong><br>" +
                escapeHTML(
                    course.lieu_destination ||
                    ""
                ) +
                "</p>" +

                "<p><strong>📏 Distance :</strong> " +
                (
                    course.distance !== null
                        ? parseFloat(
                            course.distance
                        ).toFixed(1) +
                        " km"
                        : "—"
                ) +
                "</p>" +

                "<p><strong>⏱️ Durée :</strong> " +
                (
                    course.duree_estimee !== null
                        ? course.duree_estimee +
                        " min"
                        : "—"
                ) +
                "</p>";


            // =============================================
            // CLIENT
            // =============================================

            clientDetails.innerHTML =
                "Client associé à la course #" +
                escapeHTML(
                    course.id_course
                );


           // =============================================
// PRIX / NÉGOCIATION
// =============================================

const prixDisponible =
    course.prix_actuel !== null &&
    course.prix_actuel !== undefined &&
    course.prix_actuel !== "";


const negociationOuverte =
    course.statut_course === "acceptee";


if (
    negociationOuverte ||
    prixDisponible
) {

    priceBox.classList.remove(
        "hidden"
    );


    if (prixDisponible) {

        priceCurrent.textContent =
            parseFloat(
                course.prix_actuel
            ).toFixed(2) +
            " $";

    }
    else {

        priceCurrent.textContent =
            "Aucun prix proposé";
    }


    // On ne peut accepter que s'il existe déjà un prix
    if (acceptPriceButton) {

        acceptPriceButton.disabled =
            !prixDisponible;
    }

}
else {

    priceBox.classList.add(
        "hidden"
    );
}           


            // =============================================
            // DÉMARRAGE
            // =============================================

            startCourseButton.classList.add(
                "hidden"
            );

            finishCourseButton.classList.add(
                "hidden"
            );


            if (
                course.statut_course ===
                "prix_accepte"
            ) {

                startCourseButton.classList.remove(
                    "hidden"
                );

                trackingMessage.textContent =
                    "✅ Prix accepté. Vous pouvez maintenant démarrer la course.";

            }


            if (
                course.statut_course ===
                "en_cours"
            ) {

                finishCourseButton.classList.remove(
                    "hidden"
                );

                trackingMessage.textContent =
                    "📍 Course en cours. Votre position GPS est transmise.";

                demarrerGPS(
                    course.id_course
                );

            }


            if (
                course.statut_course ===
                "terminee"
            ) {

                trackingMessage.textContent =
                    "🏁 Course terminée.";

                arreterGPS();

            }

        }


        // =================================================
        // SURVEILLANCE COURSE
        // =================================================

        function demarrerSurveillanceCourse() {

            if (
                surveillanceCourse
            ) {

                clearInterval(
                    surveillanceCourse
                );

            }


            chargerDetailsCourse(
                courseActive.id_course
            );


            surveillanceCourse =
                setInterval(
                    function () {

                        if (
                            courseActive &&
                            courseActive.id_course
                        ) {

                            chargerDetailsCourse(
                                courseActive.id_course
                            );

                        }

                    },
                    5000
                );

        }


        function arreterSurveillanceCourses() {

            // La liste des courses cessera d'être
            // actualisée dès qu'une course est active.

        }


        // =================================================
        // NÉGOCIATION
        // =================================================

        async function chargerNegociation(
            idCourse
        ) {

            try {

                const resultat =
                    await getJSON(
                        "../api/negociation/historique.php?id_course=" +
                        encodeURIComponent(
                            idCourse
                        )
                    );


                if (
                    !resultat.success
                ) {

                    return;

                }


                afficherHistoriqueNegociation(
                    resultat.propositions
                );


            }

            catch (erreur) {

                console.error(
                    "Erreur négociation :",
                    erreur
                );

            }

        }


        function afficherHistoriqueNegociation(
            propositions
        ) {

            negotiationHistory.innerHTML =
                "";


            if (
                !propositions ||
                propositions.length === 0
            ) {

                negotiationHistory.innerHTML =
                    "<p>Aucune proposition pour le moment.</p>";

                return;

            }


            propositions.forEach(
                function (proposition) {

                    const bloc =
                        document.createElement(
                            "div"
                        );


                    bloc.className =
                        "negotiation-item";


                    const auteur =
                        proposition.expediteur ===
                        "chauffeur"
                            ? "🚕 Vous"
                            : "👤 Client";


                    bloc.innerHTML =

                        "<strong>" +
                        auteur +
                        "</strong>" +

                        "<div>" +
                        parseFloat(
                            proposition.prix_propose
                        ).toFixed(2) +
                        " $" +
                        "</div>" +

                        "<small>" +
                        escapeHTML(
                            proposition.statut
                        ) +
                        " · " +
                        escapeHTML(
                            proposition.date_proposition ||
                            ""
                        ) +
                        "</small>";


                    negotiationHistory.appendChild(
                        bloc
                    );

                }
            );

        }


        // =================================================
        // PROPOSER UN PRIX
        // =================================================

        if (
            proposePriceButton
        ) {

            proposePriceButton.addEventListener(
                "click",
                async function () {

                    if (
                        !courseActive
                    ) {

                        return;

                    }


                    const prix =
                        parseFloat(
                            driverPrice.value
                        );


                    if (
                        isNaN(prix) ||
                        prix <= 0
                    ) {

                        afficherMessagePrix(
                            "Veuillez entrer un prix valide.",
                            true
                        );

                        return;

                    }


                    const donnees =
                        new FormData();


                    donnees.append(
                        "id_course",
                        courseActive.id_course
                    );

                    donnees.append(
                        "prix",
                        prix
                    );


                    proposePriceButton.disabled =
                        true;


                    try {

                        const resultat =
                            await getJSON(
                                "../api/negociation/proposer.php",
                                {
                                    method:
                                        "POST",

                                    body:
                                        donnees
                                }
                            );


                        if (
                            !resultat.success
                        ) {

                            throw new Error(
                                resultat.message
                            );

                        }


                        driverPrice.value =
                            "";


                        afficherMessagePrix(
                            "✅ Proposition envoyée.",
                            false
                        );


                        await chargerDetailsCourse(
                            courseActive.id_course
                        );

                    }

                    catch (erreur) {

                        console.error(
                            "Erreur proposition prix :",
                            erreur
                        );


                        afficherMessagePrix(
                            "❌ " +
                            erreur.message,
                            true
                        );

                    }


                    proposePriceButton.disabled =
                        false;

                }
            );

        }


        // =================================================
        // ACCEPTER PRIX
        // =================================================

        if (
            acceptPriceButton
        ) {

            acceptPriceButton.addEventListener(
                "click",
                async function () {

                    if (
                        !courseActive
                    ) {

                        return;

                    }


                    const confirmation =
                        confirm(
                            "Accepter le prix actuel ?"
                        );


                    if (
                        !confirmation
                    ) {

                        return;

                    }


                    const donnees =
                        new FormData();


                    donnees.append(
                        "id_course",
                        courseActive.id_course
                    );


                    acceptPriceButton.disabled =
                        true;


                    try {

                        const resultat =
                            await getJSON(
                                "../api/negociation/accepter.php",
                                {
                                    method:
                                        "POST",

                                    body:
                                        donnees
                                }
                            );


                        if (
                            !resultat.success
                        ) {

                            throw new Error(
                                resultat.message
                            );

                        }


                        afficherMessagePrix(
                            "✅ Prix accepté.",
                            false
                        );


                        await chargerDetailsCourse(
                            courseActive.id_course
                        );

                    }

                    catch (erreur) {

                        console.error(
                            "Erreur acceptation prix :",
                            erreur
                        );


                        afficherMessagePrix(
                            "❌ " +
                            erreur.message,
                            true
                        );

                    }


                    acceptPriceButton.disabled =
                        false;

                }
            );

        }


        function afficherMessagePrix(
            message,
            erreur
        ) {

            priceMessage.textContent =
                message;


            priceMessage.style.color =
                erreur
                    ? "#dc2626"
                    : "#16a34a";

        }


        // =================================================
        // DÉMARRER COURSE
        // =================================================

        if (
            startCourseButton
        ) {

            startCourseButton.addEventListener(
                "click",
                async function () {

                    if (
                        !courseActive
                    ) {

                        return;

                    }


                    try {

                        const donnees =
                            new FormData();


                        donnees.append(
                            "id_course",
                            courseActive.id_course
                        );


                        const resultat =
                            await getJSON(
                                "../api/course/demarrer.php",
                                {
                                    method:
                                        "POST",

                                    body:
                                        donnees
                                }
                            );


                        if (
                            !resultat.success
                        ) {

                            throw new Error(
                                resultat.message
                            );

                        }


                        alert(
                            "▶️ " +
                            resultat.message
                        );


                        await chargerDetailsCourse(
                            courseActive.id_course
                        );

                    }

                    catch (erreur) {

                        console.error(
                            "Erreur démarrage :",
                            erreur
                        );


                        alert(
                            "❌ " +
                            erreur.message
                        );

                    }

                }
            );

        }


        // =================================================
        // TERMINER COURSE
        // =================================================

        if (
            finishCourseButton
        ) {

            finishCourseButton.addEventListener(
                "click",
                async function () {

                    if (
                        !courseActive
                    ) {

                        return;

                    }


                    const confirmation =
                        confirm(
                            "Voulez-vous vraiment terminer cette course ?"
                        );


                    if (
                        !confirmation
                    ) {

                        return;

                    }


                    const donnees =
                        new FormData();


                    donnees.append(
                        "id_course",
                        courseActive.id_course
                    );


                    try {

                        const resultat =
                            await getJSON(
                                "../api/course/terminer.php",
                                {
                                    method:
                                        "POST",

                                    body:
                                        donnees
                                }
                            );


                        if (
                            !resultat.success
                        ) {

                            throw new Error(
                                resultat.message
                            );

                        }


                        alert(
                            "🏁 " +
                            resultat.message
                        );


                        arreterGPS();


                        await chargerDetailsCourse(
                            courseActive.id_course
                        );

                    }

                    catch (erreur) {

                        console.error(
                            "Erreur fin course :",
                            erreur
                        );


                        alert(
                            "❌ " +
                            erreur.message
                        );

                    }

                }
            );

        }
        

        // =====================================================
// FAST TRAJET V2
// MESSAGERIE CHAUFFEUR <-> CLIENT
// =====================================================

async function mettreAJourMessagerieChauffeur(
    course
) {

    const carte =
        document.getElementById(
            "messagesCourseCardChauffeur"
        );


    if (!carte) {

        console.warn(
            "Zone messagerie chauffeur introuvable."
        );

        return;
    }


    // =============================================
    // DISCUSSION UNIQUEMENT APRÈS PRIX ACCEPTÉ
    // =============================================

    if (
        !course ||
        course.statut_prix !== "accepte" ||
        (
            course.statut_course !== "prix_accepte" &&
            course.statut_course !== "en_cours"
        )
    ) {

        carte.hidden = true;

        return;
    }


    carte.hidden = false;


    await chargerMessagesCourseChauffeur(
        course.id_course
    );
}


// =====================================================
// CHARGER LES MESSAGES
// =====================================================

async function chargerMessagesCourseChauffeur(
    idCourse
) {

    const zone =
        document.getElementById(
            "listeMessagesCourseChauffeur"
        );


    if (
        !zone ||
        !idCourse
    ) {
        return;
    }


    try {

        const resultat =
            await getJSON(
                "../api/message/liste.php?id_course=" +
                encodeURIComponent(
                    idCourse
                )
            );


        if (!resultat.success) {

            console.error(
                "Erreur messages chauffeur :",
                resultat.message
            );

            return;
        }


        afficherMessagesChauffeur(
            resultat.messages || []
        );

    }
    catch (erreur) {

        console.error(
            "Erreur chargement discussion chauffeur :",
            erreur
        );
    }
}


// =====================================================
// AFFICHER LES MESSAGES
// =====================================================

function afficherMessagesChauffeur(
    messages
) {

    const zone =
        document.getElementById(
            "listeMessagesCourseChauffeur"
        );


    if (!zone) {
        return;
    }


    zone.innerHTML = "";


    if (
        !Array.isArray(messages) ||
        messages.length === 0
    ) {

        const vide =
            document.createElement(
                "p"
            );


        vide.className =
            "chat-vide";


        vide.textContent =
            "💬 Aucun message pour le moment.";


        zone.appendChild(
            vide
        );

        return;
    }


    messages.forEach(
        function (message) {

            const ligne =
                document.createElement(
                    "div"
                );


            ligne.className =
                "chat-message " +
                (
                    message.expediteur ===
                    "chauffeur"
                        ? "me"
                        : "other"
                );


            const bulle =
                document.createElement(
                    "div"
                );


            bulle.className =
                "chat-bubble";


            const texte =
                document.createElement(
                    "p"
                );


            texte.textContent =
                message.contenu || "";


            const heure =
                document.createElement(
                    "small"
                );


            heure.textContent =
                message.date_heure || "";


            bulle.appendChild(
                texte
            );


            bulle.appendChild(
                heure
            );


            ligne.appendChild(
                bulle
            );


            zone.appendChild(
                ligne
            );
        }
    );


    zone.scrollTop =
        zone.scrollHeight;
}


// =====================================================
// ENVOYER UN MESSAGE
// =====================================================

async function envoyerMessageCourseChauffeur() {

    console.count(
    "APPEL envoyerMessageCourseChauffeur"
);

    if (
        !courseActive ||
        !courseActive.id_course
    ) {

        alert(
            "Aucune course active."
        );

        return;
    }


    const champ =
        document.getElementById(
            "messageCourseChauffeur"
        );


    const bouton =
        document.getElementById(
            "envoyerMessageCourseChauffeur"
        );


    if (
        !champ ||
        !bouton
    ) {
        return;
    }


    const message =
        champ.value.trim();


    if (message === "") {

        champ.focus();

        return;
    }


    const donnees =
        new FormData();


    donnees.append(
        "id_course",
        courseActive.id_course
    );


    donnees.append(
        "message",
        message
    );


    bouton.disabled = true;


    try {

        const resultat =
            await getJSON(
                "../api/message/envoyer.php",
                {
                    method: "POST",
                    body: donnees
                }
            );


        if (!resultat.success) {

            throw new Error(
                resultat.message ||
                "Impossible d'envoyer le message."
            );
        }


        champ.value = "";


        await chargerMessagesCourseChauffeur(
            courseActive.id_course
        );

    }
    catch (erreur) {

        console.error(
            "Erreur envoi message chauffeur :",
            erreur
        );


        alert(
            "❌ " +
            erreur.message
        );
    }


    bouton.disabled = false;
}


// =====================================================
// BOUTON ENVOYER MESSAGE
// =====================================================

const boutonMessageChauffeur =
    document.getElementById(
        "envoyerMessageCourseChauffeur"
    );


if (
    boutonMessageChauffeur &&
    boutonMessageChauffeur.dataset.initialise !== "oui"
) {

    boutonMessageChauffeur.dataset.initialise =
        "oui";


    boutonMessageChauffeur.addEventListener(
        "click",
        envoyerMessageCourseChauffeur
    );
}



        // =================================================
        // GPS CHAUFFEUR
        // =================================================

        function demarrerGPS(
            idCourse
        ) {

            if (
                surveillanceGPS
            ) {

                return;

            }


            if (
                !navigator.geolocation
            ) {

                trackingMessage.textContent =
                    "La géolocalisation n'est pas disponible.";

                return;

            }


            surveillanceGPS =
                navigator.geolocation.watchPosition(

                    function (position) {

                        chauffeurPosition = {

                            latitude:
                                position.coords.latitude,

                            longitude:
                                position.coords.longitude

                        };


                        afficherPositionChauffeur(
                            chauffeurPosition.latitude,
                            chauffeurPosition.longitude
                        );


                        const donnees =
                            new FormData();


                        donnees.append(
                            "id_course",
                            idCourse
                        );


                        donnees.append(
                            "latitude",
                            chauffeurPosition.latitude
                        );


                        donnees.append(
                            "longitude",
                            chauffeurPosition.longitude
                        );


                        donnees.append(
                            "precision_gps",
                            position.coords.accuracy
                        );


                        fetch(
                            "../api/gps/position_chauffeur.php",
                            {
                                method:
                                    "POST",

                                body:
                                    donnees,

                                credentials:
                                    "same-origin"
                            }
                        )
                        .then(
                            function (reponse) {

                                return reponse.text();

                            }
                        )
                        .then(
                            function (texte) {

                                try {

                                    const resultat =
                                        JSON.parse(
                                            texte
                                        );


                                    if (
                                        !resultat.success
                                    ) {

                                        console.error(
                                            "Erreur GPS chauffeur :",
                                            resultat.message
                                        );

                                    }

                                }

                                catch (erreur) {

                                    console.error(
                                        "Réponse GPS invalide :",
                                        texte
                                    );

                                }

                            }
                        )
                        .catch(
                            function (erreur) {

                                console.error(
                                    "Erreur envoi GPS :",
                                    erreur
                                );

                            }
                        );

                    },

                    function (erreur) {

                        console.error(
                            "Erreur géolocalisation chauffeur :",
                            erreur
                        );

                    },

                    {

                        enableHighAccuracy:
                            true,

                        maximumAge:
                            5000,

                        timeout:
                            10000

                    }

                );


            trackingMessage.textContent =
                "📍 GPS chauffeur actif.";

        }


        function arreterGPS() {

            if (
                surveillanceGPS
            ) {

                navigator.geolocation.clearWatch(
                    surveillanceGPS
                );

                surveillanceGPS =
                    null;

            }

        }


        function afficherPositionChauffeur(
            latitude,
            longitude
        ) {

            if (
                markerChauffeur
            ) {

                markerChauffeur.setLatLng(
                    [
                        latitude,
                        longitude
                    ]
                );

                return;

            }


            markerChauffeur =
                L.marker(
                    [
                        latitude,
                        longitude
                    ]
                )
                .addTo(map)
                .bindPopup(
                    "🚕 Vous êtes ici"
                );

        }

         // =================================================
// ITINÉRAIRE BLEU CHAUFFEUR
// CLIENT → DESTINATION
// =================================================

async function afficherItineraireCourseChauffeur(
    course
) {

    if (
        !course ||
        !course.id_course ||
        !map
    ) {

        return;
    }


    // Ne pas recalculer la même route toutes les 5 secondes
    if (
        routeCourseId ===
        course.id_course &&
        routeLayer
    ) {

        return;
    }


    const latitudeDepart =
        parseFloat(
            course.latitude_depart
        );


    const longitudeDepart =
        parseFloat(
            course.longitude_depart
        );


    const latitudeDestination =
        parseFloat(
            course.latitude_destination
        );


    const longitudeDestination =
        parseFloat(
            course.longitude_destination
        );


    if (
        !Number.isFinite(latitudeDepart) ||
        !Number.isFinite(longitudeDepart) ||
        !Number.isFinite(latitudeDestination) ||
        !Number.isFinite(longitudeDestination)
    ) {

        console.warn(
            "Coordonnées insuffisantes pour tracer l'itinéraire."
        );

        return;
    }


    try {

        const url =
            "https://router.project-osrm.org/route/v1/driving/" +

            longitudeDepart +
            "," +
            latitudeDepart +

            ";" +

            longitudeDestination +
            "," +
            latitudeDestination +

            "?overview=full" +
            "&geometries=geojson";


        const reponse =
            await fetch(url);


        if (!reponse.ok) {

            throw new Error(
                "Impossible de récupérer l'itinéraire."
            );
        }


        const resultat =
            await reponse.json();


        if (
            resultat.code !== "Ok" ||
            !resultat.routes ||
            resultat.routes.length === 0
        ) {

            throw new Error(
                "Aucun itinéraire routier disponible."
            );
        }


        // Supprimer une ancienne route
        if (routeLayer) {

            map.removeLayer(
                routeLayer
            );
        }


        // =============================================
        // LIGNE BLEUE
        // =============================================

        routeLayer =
            L.geoJSON(
                resultat.routes[0].geometry,
                {
                    style: {

                        color:
                            "#2563eb",

                        weight:
                            6,

                        opacity:
                            0.85
                    }
                }
            )
            .addTo(map);


        // Afficher tout le trajet
        map.fitBounds(
            routeLayer.getBounds(),
            {
                padding:
                    [40, 40]
            }
        );


        routeCourseId =
            course.id_course;


        console.log(
            "✅ Itinéraire bleu chauffeur affiché."
        );

    }
    catch (erreur) {

        console.error(
            "Erreur itinéraire chauffeur :",
            erreur
        );
    }
}


        // =================================================
        // POSITIONS COURSE
        // =================================================

        async function chargerPositionCourse(
            idCourse
        ) {

            try {

                const resultat =
                    await getJSON(
                        "../api/gps/positions_course.php?id_course=" +
                        encodeURIComponent(
                            idCourse
                        )
                    );


                if (
                    !resultat.success
                ) {

                    return;

                }


                // =========================================
                // POSITION CLIENT
                // =========================================

                if (
                    resultat.latitude_client !== null &&
                    resultat.longitude_client !== null
                ) {

                    const latitude =
                        parseFloat(
                            resultat.latitude_client
                        );

                    const longitude =
                        parseFloat(
                            resultat.longitude_client
                        );


                    if (
                        !isNaN(latitude) &&
                        !isNaN(longitude)
                    ) {

                        if (
                            markerClient
                        ) {

                            markerClient.setLatLng(
                                [
                                    latitude,
                                    longitude
                                ]
                            );

                        }

                        else {

                            markerClient =
                                L.marker(
                                    [
                                        latitude,
                                        longitude
                                    ]
                                )
                                .addTo(map)
                                .bindPopup(
                                    "👤 Position du client"
                                );

                        }

                    }

                }


                // =========================================
                // DESTINATION
                // =========================================

                if (
                    resultat.latitude_destination !== null &&
                    resultat.longitude_destination !== null
                ) {

                    const latitude =
                        parseFloat(
                            resultat.latitude_destination
                        );

                    const longitude =
                        parseFloat(
                            resultat.longitude_destination
                        );


                    if (
                        !isNaN(latitude) &&
                        !isNaN(longitude)
                    ) {

                        if (
                            markerDestination
                        ) {

                            markerDestination.setLatLng(
                                [
                                    latitude,
                                    longitude
                                ]
                            );

                        }

                        else {

                            markerDestination =
                                L.marker(
                                    [
                                        latitude,
                                        longitude
                                    ]
                                )
                                .addTo(map)
                                .bindPopup(
                                    "🏁 Destination"
                                );

                        }

                    }

                }

            }

            catch (erreur) {

                console.error(
                    "Erreur position course :",
                    erreur
                );

            }

        }


        // =================================================
        // ACTUALISATION COURSES
        // =================================================

        if (
            refreshCoursesButton
        ) {

            refreshCoursesButton.addEventListener(
                "click",
                chargerCourses
            );

        }


        setInterval(
            async function () {

                if (
                    !courseActive
                ) {

                    try {

                        const resultat =
                            await getJSON(
                                "../api/chauffeur/disponibilite.php"
                            );


                        if (
                            resultat.success &&
                            parseInt(
                                resultat.disponibilite
                            ) === 1
                        ) {

                            chargerCourses();

                        }

                    }

                    catch (erreur) {

                        console.error(
                            erreur
                        );

                    }

                }

            },
            5000
        );


        // =================================================
        // NOTIFICATIONS
        // =================================================

        async function chargerNombreNotifications() {

            try {

                const resultat =
                    await getJSON(
                        "../api/notifications/non_lues.php"
                    );


                if (
                    resultat.success
                ) {

                    notificationCount.textContent =
                        resultat.non_lues;

                }

            }

            catch (erreur) {

                console.error(
                    "Erreur compteur notifications :",
                    erreur
                );

            }

        }


        async function chargerNotifications() {

            try {

                const resultat =
                    await getJSON(
                        "../api/notifications/liste.php"
                    );


                if (
                    !resultat.success
                ) {

                    return;

                }


                notificationsContainer.innerHTML =
                    "";


                if (
                    !resultat.notifications ||
                    resultat.notifications.length === 0
                ) {

                    notificationsContainer.innerHTML =
                        "<p>Aucune notification.</p>";

                    return;

                }


                resultat.notifications.forEach(
                    function (notification) {

                        const bloc =
                            document.createElement(
                                "div"
                            );


                        bloc.className =
                            "notification-item " +
                            (
                                parseInt(
                                    notification.lu
                                ) === 0
                                    ? "unread"
                                    : ""
                            );


                        bloc.innerHTML =

                            "<strong>" +
                            escapeHTML(
                                notification.titre
                            ) +
                            "</strong>" +

                            "<div>" +
                            escapeHTML(
                                notification.contenu
                            ) +
                            "</div>" +

                            "<small>" +
                            escapeHTML(
                                notification.date_creation
                            ) +
                            "</small>";


                        notificationsContainer.appendChild(
                            bloc
                        );

                    }
                );


                chargerNombreNotifications();

            }

            catch (erreur) {

                console.error(
                    "Erreur notifications :",
                    erreur
                );

            }

        }


        if (
            notificationButton
        ) {

            notificationButton.addEventListener(
    "click",
    async function () {

        notificationsSection.classList.toggle(
            "hidden"
        );


        if (
            !notificationsSection.classList.contains(
                "hidden"
            )
        ) {

            await chargerNotifications();

            await marquerToutesNotificationsLuesChauffeur();

            await chargerNotifications();
        }
    }
);

        }


        // =================================================
        // ACTIONS DU MENU
        // =================================================

        document
            .querySelectorAll(
                "[data-action]"
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        "click",
                        async function () {

                            const action =
                                button.dataset.action;


                            if (
                                action ===
                                "notifications"
                            ) {

                                notificationsSection.classList.remove(
                                    "hidden"
                                );

                                chargerNotifications();

                            }


                            else if (
                                action ===
                                "deconnexion"
                            ) {

                                const confirmation =
                                    confirm(
                                        "Voulez-vous vraiment vous déconnecter ?"
                                    );


                                if (
                                    !confirmation
                                ) {

                                    return;

                                }


                                try {

                                    await getJSON(
                                        "../api/auth/deconnexion.php",
                                        {
                                            method:
                                                "POST"
                                        }
                                    );

                                }

                                catch (erreur) {

                                    console.error(
                                        erreur
                                    );

                                }


                                window.location.href =
                                    "../api/auth/deconnexion.php";

                            }


                            mobileMenu.classList.remove(
                                "open"
                            );

                        }
                    );

                }
            );


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


            return String(
                valeur
            )
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
         
        // =====================================================
// ACTUALISATION AUTOMATIQUE DES NOTIFICATIONS CHAUFFEUR
// =====================================================

setInterval(
    async function () {

        await chargerNombreNotifications();


        if (
            notificationsSection &&
            !notificationsSection.classList.contains(
                "hidden"
            )
        ) {

            await chargerNotifications();
        }

    },
    5000
);

        // =================================================
        // DÉMARRAGE
        // =================================================

        chargerDisponibilite();

        chargerNombreNotifications();


        console.log(
            "✅ Espace chauffeur V2 prêt."
        );



        // =====================================================
// SIGNALEMENT CHAUFFEUR
// =====================================================

const envoyerAlerteChauffeur =
    document.getElementById(
        "envoyerAlerteChauffeur"
    );


if (envoyerAlerteChauffeur) {

    envoyerAlerteChauffeur.addEventListener(
        "click",
        async function () {

            if (
                !courseActive ||
                !courseActive.id_course
            ) {

                alert(
                    "Aucune course active."
                );

                return;
            }


            const type =
                document.getElementById(
                    "typeAlerteChauffeur"
                )?.value;


            const niveau =
                document.getElementById(
                    "niveauAlerteChauffeur"
                )?.value;


            const champDescription =
                document.getElementById(
                    "descriptionAlerteChauffeur"
                );


            const description =
                champDescription
                    ?.value
                    .trim();


            if (!type) {

                alert(
                    "Veuillez choisir le type de signalement."
                );

                return;
            }


            if (!description) {

                alert(
                    "Veuillez décrire l'incident."
                );

                return;
            }


            const confirmation =
                confirm(
                    "Voulez-vous envoyer ce signalement à Fast Trajet ?"
                );


            if (!confirmation) {
                return;
            }


            const donnees =
                new FormData();


            donnees.append(
                "id_course",
                courseActive.id_course
            );


            donnees.append(
                "type_alerte",
                type
            );


            donnees.append(
                "niveau",
                niveau
            );


            donnees.append(
                "description",
                description
            );


            envoyerAlerteChauffeur.disabled =
                true;


            try {

                const resultat =
                    await getJSON(
                        "../api/alerte/creer.php",
                        {
                            method:
                                "POST",

                            body:
                                donnees
                        }
                    );


                if (!resultat.success) {

                    throw new Error(
                        resultat.message
                    );
                }


                champDescription.value =
                    "";


                alert(
                    resultat.message
                );

            }
            catch (erreur) {

                console.error(
                    "Erreur signalement chauffeur :",
                    erreur
                );


                alert(
                    "❌ " +
                    erreur.message
                );
            }
            finally {

                envoyerAlerteChauffeur.disabled =
                    false;
            }

        }
    );
}

    }


    
    
);