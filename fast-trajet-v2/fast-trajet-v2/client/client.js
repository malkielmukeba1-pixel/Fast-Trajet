// =====================================================
// FAST TRAJET V2
// ESPACE CLIENT
// =====================================================

"use strict";

console.log("FAST TRAJET V2 - client.js chargé");


// =====================================================
// VARIABLES
// =====================================================

let carte = null;

let marqueurClient = null;

let latitudeClient = null;

let longitudeClient = null;

let positionAutorisee = false;

let itineraireSuiviCourse = null;

let itineraireSuiviCharge = false;

let idCourseItineraireSuivi = null;

// =====================================================
// CARTE DE SUIVI DE LA COURSE
// =====================================================

let carteSuiviCourse = null;

let marqueurChauffeurSuivi = null;

let marqueurClientSuivi = null;

let marqueurDestinationSuivi = null;

let premiereVueSuiviCourse = true;

// =====================================================
// COURSE / NÉGOCIATION / NOTIFICATIONS
// =====================================================

let courseActiveClient = null;

let propositionsClient = [];

let surveillanceClient = null;

let derniereNotificationIdAnnoncee = 0;

let courseAccepteeDejaAffichee = false;



// =====================================================
// MENU CLIENT
// =====================================================

function initialiserMenu() {

    const menuButton =
        document.getElementById(
            "menuButton"
        );

    const mobileMenu =
        document.getElementById(
            "mobileMenu"
        );


    // OUVRIR / FERMER LE MENU
    if (
        menuButton &&
        mobileMenu
    ) {

        menuButton.addEventListener(
            "click",
            function () {

                mobileMenu.classList.toggle(
                    "open"
                );

            }
        );
    }


    // BOUTONS DES SECTIONS
    const boutons =
        document.querySelectorAll(
            "[data-section]"
        );


    boutons.forEach(
        function (bouton) {

            bouton.addEventListener(
                "click",
                function () {

                    const section =
                        bouton.dataset.section;


                    afficherSection(
                        section
                    );


                    // Fermer le menu sur mobile
                    if (
                        mobileMenu &&
                        window.innerWidth < 768
                    ) {

                        mobileMenu.classList.remove(
                            "open"
                        );
                    }

                }
            );

        }
    );
}


// =====================================================
// INITIALISATION
// =====================================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        console.log(
            "Interface client initialisée."
        );

        initialiserMenu();

initialiserCarte();

initialiserFormulaireCourse();

initialiserPosition();

initialiserDeconnexion();

initialiserNegociationClient();

chargerCourseActive();

chargerNombreNotificationsClient();

chargerNotificationsClient();

initialiserMessagerieClient();

demarrerSurveillanceClient();

    }
);


// =====================================================
// AFFICHER UNE SECTION
// =====================================================

function afficherSection(
    section
) {

    const sections =
        document.querySelectorAll(
            ".client-section"
        );


    sections.forEach(
        function (element) {

            element.classList.remove(
                "active"
            );

        }
    );


    if (
        section === "accueil"
    ) {

        document
            .getElementById(
                "sectionAccueil"
            )
            ?.classList.add(
                "active"
            );

    }


    if (
        section === "course"
    ) {

        document
            .getElementById(
                "sectionCourse"
            )
            ?.classList.add(
                "active"
            );

        chargerCourseActive();

    }


    if (
        section === "profil"
    ) {

        document
            .getElementById(
                "sectionProfil"
            )
            ?.classList.add(
                "active"
            );

    }


    if (
        section === "securite"
    ) {

        document
            .getElementById(
                "sectionSecurite"
            )
            ?.classList.add(
                "active"
            );

    }

    if (
    section === "notifications"
) {

    document
        .getElementById(
            "sectionNotifications"
        )
        ?.classList.add(
            "active"
        );


    ouvrirNotificationsClient();
}

}


// =====================================================
// OUVRIR LES NOTIFICATIONS CLIENT
// =====================================================

async function ouvrirNotificationsClient() {

    // Afficher d'abord les notifications
    await chargerNotificationsClient();


    // Les considérer comme lues
    await marquerToutesNotificationsLuesClient();


    // Recalculer immédiatement le compteur
    await chargerNombreNotificationsClient();


    // Réafficher pour enlever "Non lue"
    await chargerNotificationsClient();
}

// =====================================================
// CARTE
// =====================================================

function initialiserCarte() {

    const elementCarte =
        document.getElementById(
            "map"
        );


    if (!elementCarte) {

        return;

    }


    if (
        typeof L === "undefined"
    ) {

        console.error(
            "Leaflet n'est pas chargé."
        );

        return;

    }


    carte = L.map(
        "map"
    ).setView(
        [-4.325, 15.322],
        12
    );


    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,

            attribution:
                "&copy; OpenStreetMap"
        }
    ).addTo(
        carte
    );


    console.log(
        "Carte client créée avec succès."
    );

}


// =====================================================
// POSITION CLIENT
// =====================================================

function initialiserPosition() {

    const bouton =
        document.getElementById(
            "positionButton"
        );


    if (bouton) {

        bouton.addEventListener(
            "click",
            obtenirPosition
        );

    }

}


function obtenirPosition() {

    if (
        !navigator.geolocation
    ) {

        afficherNotification(
            "La géolocalisation n'est pas disponible sur cet appareil."
        );

        return;

    }


    afficherNotification(
        "📍 Recherche de votre position..."
    );


    navigator.geolocation.getCurrentPosition(

        function (position) {

            latitudeClient =
                position.coords.latitude;

            longitudeClient =
                position.coords.longitude;

            positionAutorisee = true;


            console.log(
                "Latitude :",
                latitudeClient
            );

            console.log(
                "Longitude :",
                longitudeClient
            );


            afficherPositionSurCarte();

            obtenirAdressePosition();

        },

        function (erreur) {

            console.error(
                "Erreur GPS :",
                erreur
            );

            afficherNotification(
                "Impossible d'obtenir votre position."
            );

        },

        {
            enableHighAccuracy: true,

            timeout: 10000,

            maximumAge: 0
        }

    );

}


// =====================================================
// AFFICHER POSITION SUR CARTE
// =====================================================

function afficherPositionSurCarte() {

    if (
        !carte ||
        latitudeClient === null ||
        longitudeClient === null
    ) {

        return;

    }


    if (marqueurClient) {

        carte.removeLayer(
            marqueurClient
        );

    }


    marqueurClient =
        L.marker(
            [
                latitudeClient,
                longitudeClient
            ]
        )
        .addTo(
            carte
        )
        .bindPopup(
            "📍 Vous êtes ici"
        )
        .openPopup();


    carte.setView(
        [
            latitudeClient,
            longitudeClient
        ],
        16
    );

}


// =====================================================
// OBTENIR ADRESSE
// =====================================================

async function obtenirAdressePosition() {

    try {

        const url =
            "https://nominatim.openstreetmap.org/reverse" +
            "?format=json" +
            "&lat=" +
            encodeURIComponent(
                latitudeClient
            ) +
            "&lon=" +
            encodeURIComponent(
                longitudeClient
            ) +
            "&zoom=18" +
            "&addressdetails=1";

        const reponse =
            await fetch(
                url,
                {
                    headers: {
                        "Accept":
                            "application/json"
                    }
                }
            );


        if (!reponse.ok) {

            throw new Error(
                "Erreur adresse."
            );

        }


        const resultat =
            await reponse.json();


        const adresse =
            resultat.display_name || "";


        const champDepart =
            document.getElementById(
                "lieuDepart"
            );


        if (
            champDepart &&
            adresse !== ""
        ) {

            champDepart.value =
                adresse;

        }


        afficherNotification(
            "📍 Position récupérée."
        );

    }
    catch (erreur) {

        console.error(
            "Erreur récupération adresse :",
            erreur
        );

        afficherNotification(
            "Position GPS récupérée, mais l'adresse n'a pas pu être déterminée."
        );

    }

}


// =====================================================
// FORMULAIRE COURSE
// =====================================================

function initialiserFormulaireCourse() {

    const formulaire =
        document.getElementById(
            "courseForm"
        );


    if (!formulaire) {

        return;

    }


    formulaire.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();

            await commanderCourse();

        }
    );

}


// =====================================================
// FAST TRAJET V2
// COMMANDER UNE COURSE
// =====================================================

async function commanderCourse() {

    const lieuDepart =
        document
            .getElementById("lieuDepart")
            ?.value
            .trim();


    const lieuDestination =
        document
            .getElementById("lieuDestination")
            ?.value
            .trim();


    // =================================================
    // VÉRIFICATIONS
    // =================================================

    if (!lieuDepart) {

        afficherNotification(
            "Veuillez indiquer le lieu de départ."
        );

        return;
    }


    if (!lieuDestination) {

        afficherNotification(
            "Veuillez indiquer votre destination."
        );

        return;
    }


    if (
        latitudeClient === null ||
        longitudeClient === null
    ) {

        afficherNotification(
            "Votre position GPS n'est pas encore disponible."
        );

        return;
    }


    const bouton =
        document.getElementById(
            "commanderButton"
        );


    if (bouton) {

        bouton.disabled = true;

        bouton.textContent =
            "⏳ Recherche de la destination...";
    }


    try {

        // =================================================
        // RECHERCHER LA DESTINATION
        // =================================================

        const rechercheDestination =
            `${lieuDestination}, Kinshasa, République démocratique du Congo`;


        console.log(
            "Recherche destination :",
            rechercheDestination
        );


        const urlGeocodage =
            "https://nominatim.openstreetmap.org/search" +
            "?format=json" +
            "&limit=1" +
            "&countrycodes=cd" +
            "&q=" +
            encodeURIComponent(
                rechercheDestination
            );


        const reponseGeocodage =
            await fetch(
                urlGeocodage
            );


        if (!reponseGeocodage.ok) {

            throw new Error(
                "Impossible de rechercher la destination."
            );
        }


        const resultatsGeocodage =
            await reponseGeocodage.json();


        console.log(
            "Résultat géocodage :",
            resultatsGeocodage
        );


        if (
            !Array.isArray(
                resultatsGeocodage
            ) ||
            resultatsGeocodage.length === 0
        ) {

            throw new Error(
                "Destination introuvable. Veuillez préciser davantage le lieu."
            );
        }


        // =================================================
        // COORDONNÉES DESTINATION
        // =================================================

        const latitudeDestination =
            parseFloat(
                resultatsGeocodage[0].lat
            );


        const longitudeDestination =
            parseFloat(
                resultatsGeocodage[0].lon
            );


        if (
            !Number.isFinite(
                latitudeDestination
            ) ||
            !Number.isFinite(
                longitudeDestination
            )
        ) {

            throw new Error(
                "Les coordonnées de la destination sont invalides."
            );
        }


        console.log(
            "Latitude destination :",
            latitudeDestination
        );


        console.log(
            "Longitude destination :",
            longitudeDestination
        );


        // =================================================
        // ITINÉRAIRE
        // =================================================

        if (bouton) {

            bouton.textContent =
                "🗺️ Calcul de l'itinéraire...";
        }


        const urlItineraire =
            "https://router.project-osrm.org/route/v1/driving/" +

            longitudeClient +
            "," +
            latitudeClient +

            ";" +

            longitudeDestination +
            "," +
            latitudeDestination +

            "?overview=false";


        console.log(
            "URL itinéraire :",
            urlItineraire
        );


        const reponseItineraire =
            await fetch(
                urlItineraire
            );


        if (!reponseItineraire.ok) {

            throw new Error(
                "Impossible de calculer l'itinéraire."
            );
        }


        const resultatItineraire =
            await reponseItineraire.json();


        console.log(
            "Résultat itinéraire :",
            resultatItineraire
        );


        if (
            resultatItineraire.code !==
                "Ok" ||
            !resultatItineraire.routes ||
            resultatItineraire.routes.length ===
                0
        ) {

            throw new Error(
                "Aucun itinéraire routier trouvé vers cette destination."
            );
        }


        const route =
            resultatItineraire.routes[0];


        // =================================================
        // DISTANCE / DURÉE
        // =================================================

        const distanceKm =
            Number(
                (
                    route.distance /
                    1000
                ).toFixed(2)
            );


        const dureeMinutes =
            Math.max(
                1,
                Math.round(
                    route.duration /
                    60
                )
            );


        console.log(
            "Distance calculée :",
            distanceKm,
            "km"
        );


        console.log(
            "Durée estimée :",
            dureeMinutes,
            "minutes"
        );


        // =================================================
        // FORM DATA
        // =================================================

        const donnees =
            new FormData();


        donnees.append(
            "lieu_depart",
            lieuDepart
        );


        donnees.append(
            "lieu_destination",
            lieuDestination
        );


        donnees.append(
            "latitude_depart",
            latitudeClient
        );


        donnees.append(
            "longitude_depart",
            longitudeClient
        );


        donnees.append(
            "latitude_destination",
            latitudeDestination
        );


        donnees.append(
            "longitude_destination",
            longitudeDestination
        );


        donnees.append(
            "distance",
            distanceKm
        );


        donnees.append(
            "duree_estimee",
            dureeMinutes
        );


        console.log(
            "========== COURSE FAST TRAJET =========="
        );


        for (
            const [cle, valeur]
            of donnees.entries()
        ) {

            console.log(
                cle + " =",
                valeur
            );
        }


        console.log(
            "========================================"
        );


        // =================================================
        // CRÉER LA COURSE
        // =================================================

        if (bouton) {

            bouton.textContent =
                "⏳ Création de la course...";
        }


        const reponse =
            await fetch(
                "../api/course/creer.php",
                {
                    method:
                        "POST",

                    body:
                        donnees,

                    credentials:
                        "same-origin"
                }
            );


        const texte =
            await reponse.text();


        console.log(
            "Réponse création course :",
            texte
        );


        let resultat;


        try {

            resultat =
                JSON.parse(
                    texte
                );

        }
        catch (erreur) {

            console.error(
                "Réponse PHP non JSON :",
                texte
            );

            throw new Error(
                "Le serveur n'a pas renvoyé un JSON valide."
            );
        }


        if (
            !reponse.ok ||
            !resultat.success
        ) {

            throw new Error(
                resultat.message ||
                "Impossible de créer la course."
            );
        }


        // =================================================
        // SUCCÈS
        // =================================================

        console.log(
            "Course créée :",
            resultat
        );


        afficherNotification(
            "🚕 Course créée avec succès."
        );


        await chargerCourseActive();

    }
    catch (erreur) {

        console.error(
            "Erreur création course :",
            erreur
        );


        afficherNotification(
            erreur.message
        );

    }
    finally {

        if (bouton) {

            bouton.disabled =
                false;

            bouton.textContent =
                "🚕 Commander un taxi";
        }
    }
}




async function chargerCourseActive() {

    const container =
        document.getElementById(
            "courseActiveContainer"
        );

    if (!container) {
        return;
    }

    try {

        const reponse =
            await fetch(
                "../api/course/active.php",
                {
                    method: "GET",
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

            throw new Error(
                "Réponse serveur invalide."
            );
        }

        if (
            !reponse.ok ||
            !resultat.success ||
            !resultat.course
            
        ) {

            courseActiveClient = null;

            masquerNegociationClient();

            afficherCourseVide(
                container
            );
            courseAccepteeDejaAffichee =
    false;

            return;
        }


        // ================================
        // MÉMORISER LA COURSE
        // ================================

        courseActiveClient =
            resultat.course;


            // =================================================
// SI UN CHAUFFEUR VIENT D'ACCEPTER,
// OUVRIR AUTOMATIQUEMENT "MA COURSE"
// =================================================

if (
    courseActiveClient.id_chauffeur !== null &&
    courseActiveClient.id_chauffeur !== undefined &&
    !courseAccepteeDejaAffichee
) {

    const sections =
        document.querySelectorAll(
            ".client-section"
        );


    sections.forEach(
        function (section) {

            section.classList.remove(
                "active"
            );
        }
    );


    document
        .getElementById(
            "sectionCourse"
        )
        ?.classList.add(
            "active"
        );


    courseAccepteeDejaAffichee =
        true;


    afficherNotification(
        "🚕 Votre chauffeur est arrivé dans la négociation. Vous pouvez maintenant discuter du prix."
    );
}


        // ================================
        // AFFICHER LA COURSE
        // ================================

        afficherCourse(
            container,
            courseActiveClient
        );


        // ================================
        // AFFICHER NÉGOCIATION
        // ================================

        mettreAJourZoneNegociationClient(
            courseActiveClient
        );


        // ================================
        // CHARGER LES PROPOSITIONS
        // ================================

        if (
            courseActiveClient.id_course
        ) {

            await chargerNegociationClient(
                courseActiveClient.id_course
            );
        }

        // =================================================
// SUIVI GPS SI LA COURSE EST EN COURS
// =================================================

await mettreAJourSuiviCourseClient(
    courseActiveClient
);

await mettreAJourMessagerieClient(
    courseActiveClient
);

    }

    catch (erreur) {

        console.error(
            "Erreur chargement course :",
            erreur
        );

        courseActiveClient = null;

        masquerNegociationClient();

        arreterSuiviCourseClient();

        afficherCourseVide(
            container
        );
    }
}

function afficherCourse(
    container,
    course
) {

    const statut =
        escapeHTML(
            (
                course.statut_course ||
                "inconnu"
            ).replaceAll(
                "_",
                " "
            )
        );


    const depart =
        escapeHTML(
            course.lieu_depart || "-"
        );


    const destination =
        escapeHTML(
            course.lieu_destination || "-"
        );


    const distance =
        course.distance !== null &&
        course.distance !== undefined
            ? parseFloat(
                course.distance
            ).toFixed(2) +
            " km"
            : "—";


    const duree =
        course.duree_estimee !== null &&
        course.duree_estimee !== undefined
            ? parseInt(
                course.duree_estimee,
                10
            ) +
            " min"
            : "—";


    const prix =
        course.prix_accepte ??
        course.prix_actuel ??
        course.prix_initial ??
        null;


    const prixAffiche =
        prix !== null
            ? parseFloat(
                prix
            ).toFixed(2) +
            " $"
            : "À négocier";


    const chauffeur =
        course.id_chauffeur !== null &&
        course.id_chauffeur !== undefined
            ? "✅ Chauffeur attribué"
            : "⏳ Recherche d'un chauffeur";


    container.innerHTML = `

        <div class="active-course">

            <h2>
                🚕 Course #${escapeHTML(
                    String(
                        course.id_course
                    )
                )}
            </h2>

            <div class="course-status">
                ${statut}
            </div>

            <p>
                ${chauffeur}
            </p>

            <div class="course-route">

                <p>
                    📍
                    <strong>Départ :</strong>
                    ${depart}
                </p>

                <p>
                    🎯
                    <strong>Destination :</strong>
                    ${destination}
                </p>

                <p>
                    📏
                    <strong>Distance :</strong>
                    ${escapeHTML(distance)}
                </p>

                <p>
                    ⏱️
                    <strong>Durée :</strong>
                    ${escapeHTML(duree)}
                </p>

            </div>

            <div class="course-price">

                💰 Prix :
                <strong>
                    ${escapeHTML(
                        prixAffiche
                    )}
                </strong>

            </div>

        </div>
    `;
}

// =====================================================
// COURSE VIDE
// =====================================================

function afficherCourseVide(
    container
) {

    container.innerHTML = `

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

    `;

}


// =====================================================
// NÉGOCIATION CÔTÉ CLIENT
// =====================================================

function initialiserNegociationClient() {

    const boutonProposer =
        document.getElementById(
            "proposerPrixClientButton"
        );

    const boutonAccepter =
        document.getElementById(
            "accepterPrixClientButton"
        );


    if (boutonProposer) {

        boutonProposer.addEventListener(
            "click",
            proposerPrixClient
        );
    }


    if (boutonAccepter) {

        boutonAccepter.addEventListener(
            "click",
            accepterPrixClient
        );
    }
}


// =====================================================
// AFFICHER ZONE NÉGOCIATION
// =====================================================

function mettreAJourZoneNegociationClient(
    course
) {

    const zone =
        document.getElementById(
            "negociationCard"
        );

    const prixActuel =
        document.getElementById(
            "prixActuelClient"
        );

    const champPrix =
        document.getElementById(
            "prixClient"
        );

    const proposerButton =
        document.getElementById(
            "proposerPrixClientButton"
        );

    const accepterButton =
        document.getElementById(
            "accepterPrixClientButton"
        );

    const message =
        document.getElementById(
            "messagePrixClient"
        );


    if (!zone) {
        return;
    }


    if (
        !course ||
        course.id_chauffeur === null ||
        course.id_chauffeur === undefined ||
        course.statut_course ===
            "en_attente" ||
        course.statut_course ===
            "annulee"
    ) {

        zone.hidden = true;

        return;
    }


    zone.hidden = false;


    const prix =
        course.prix_accepte ??
        course.prix_actuel ??
        course.prix_initial ??
        null;


    if (prixActuel) {

        prixActuel.textContent =
            prix !== null
                ? parseFloat(
                    prix
                ).toFixed(2) +
                " $"
                : "Aucun prix proposé";
    }


    const negociationOuverte =
        course.statut_course ===
            "acceptee" ||
        course.statut_course ===
            "negociation";


    if (champPrix) {

        champPrix.disabled =
            !negociationOuverte;
    }


    if (proposerButton) {

        proposerButton.disabled =
            !negociationOuverte;
    }


    if (accepterButton) {

        accepterButton.disabled =
            true;
    }


    if (message) {

        if (
            course.statut_course ===
                "prix_accepte"
        ) {

            message.textContent =
                "✅ Prix accepté. La négociation est terminée.";

        }
        else {

            message.textContent =
                "Vous pouvez proposer un prix ou accepter celui du chauffeur.";
        }
    }
}


// =====================================================
// MASQUER NÉGOCIATION
// =====================================================

function masquerNegociationClient() {

    const zone =
        document.getElementById(
            "negociationCard"
        );

    if (zone) {

        zone.hidden = true;
    }
}


// =====================================================
// CHARGER HISTORIQUE NÉGOCIATION
// =====================================================

async function chargerNegociationClient(
    idCourse
) {

    const historique =
        document.getElementById(
            "negociationHistoriqueClient"
        );


    if (
        !historique ||
        !idCourse
    ) {

        return;
    }


    try {

        const reponse =
            await fetch(
                "../api/negociation/historique.php?id_course=" +
                encodeURIComponent(
                    idCourse
                ),
                {
                    method: "GET",
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
                "Impossible de charger la négociation."
            );
        }


        propositionsClient =
            resultat.propositions || [];


        afficherHistoriqueNegociationClient(
            propositionsClient
        );

    }
    catch (erreur) {

        console.error(
            "Erreur négociation client :",
            erreur
        );

        historique.innerHTML =
            "<p>Impossible de charger les propositions.</p>";
    }
}


// =====================================================
// AFFICHER HISTORIQUE
// =====================================================

function afficherHistoriqueNegociationClient(
    propositions
) {

    const historique =
        document.getElementById(
            "negociationHistoriqueClient"
        );


    const accepterButton =
        document.getElementById(
            "accepterPrixClientButton"
        );


    if (!historique) {
        return;
    }


    historique.innerHTML = "";


    if (
        !Array.isArray(
            propositions
        ) ||
        propositions.length === 0
    ) {

        historique.innerHTML =
            "<p>Aucune proposition pour le moment.</p>";

        if (accepterButton) {

            accepterButton.disabled =
                true;
        }

        return;
    }


    propositions.forEach(
        function (proposition) {

            const bloc =
                document.createElement(
                    "div"
                );


            const auteur =
                proposition.expediteur ===
                    "client"
                    ? "👤 Vous"
                    : "🚕 Chauffeur";


            const prix =
                parseFloat(
                    proposition.prix_propose
                );


            bloc.className =
                "negociation-item";


            bloc.innerHTML = `

                <strong>
                    ${escapeHTML(auteur)}
                </strong>

                <div>
                    ${
                        Number.isFinite(
                            prix
                        )
                            ? prix.toFixed(
                                2
                            ) +
                            " $"
                            : "—"
                    }
                </div>

                <small>
                    ${escapeHTML(
                        proposition.statut ||
                        ""
                    )}

                    ·

                    ${escapeHTML(
                        proposition.date_proposition ||
                        ""
                    )}
                </small>
            `;


            historique.appendChild(
                bloc
            );
        }
    );


    // ===============================================
    // DERNIÈRE PROPOSITION ACTIVE
    // ===============================================

    const propositionActive =
        [...propositions]
            .reverse()
            .find(
                function (proposition) {

                    return (
                        proposition.statut ===
                        "propose"
                    );
                }
            );


    const peutAccepter =
        propositionActive &&
        propositionActive.expediteur ===
            "chauffeur" &&
        courseActiveClient &&
        (
            courseActiveClient.statut_course ===
                "acceptee" ||
            courseActiveClient.statut_course ===
                "negociation"
        );


    if (accepterButton) {

        accepterButton.disabled =
            !peutAccepter;
    }
}


// =====================================================
// CLIENT PROPOSE UN PRIX
// =====================================================

async function proposerPrixClient() {

    if (
        !courseActiveClient ||
        !courseActiveClient.id_course
    ) {

        afficherMessagePrixClient(
            "Aucune course active.",
            true
        );

        return;
    }


    const champ =
        document.getElementById(
            "prixClient"
        );


    const bouton =
        document.getElementById(
            "proposerPrixClientButton"
        );


    const prix =
        parseFloat(
            champ?.value
        );


    if (
        !Number.isFinite(
            prix
        ) ||
        prix <= 0
    ) {

        afficherMessagePrixClient(
            "Veuillez entrer un prix valide.",
            true
        );

        return;
    }


    const donnees =
        new FormData();


    donnees.append(
        "id_course",
        courseActiveClient.id_course
    );


    donnees.append(
        "prix",
        prix
    );


    if (bouton) {

        bouton.disabled = true;
    }


    try {

        const reponse =
            await fetch(
                "../api/negociation/proposer.php",
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
                "Impossible d'envoyer la proposition."
            );
        }


        if (champ) {

            champ.value = "";
        }


        afficherMessagePrixClient(
            "✅ Proposition envoyée.",
            false
        );


        afficherNotification(
            "💰 Votre proposition a été envoyée."
        );


        await chargerCourseActive();

    }
    catch (erreur) {

        console.error(
            "Erreur proposition client :",
            erreur
        );


        afficherMessagePrixClient(
            "❌ " +
            erreur.message,
            true
        );

    }
    finally {

        if (bouton) {

            bouton.disabled = false;
        }
    }
}


// =====================================================
// CLIENT ACCEPTE PRIX CHAUFFEUR
// =====================================================

async function accepterPrixClient() {

    if (
        !courseActiveClient ||
        !courseActiveClient.id_course
    ) {

        return;
    }


    const active =
        [...propositionsClient]
            .reverse()
            .find(
                function (proposition) {

                    return (
                        proposition.statut ===
                        "propose"
                    );
                }
            );


    if (
        !active ||
        active.expediteur !==
            "chauffeur"
    ) {

        afficherMessagePrixClient(
            "Aucune proposition du chauffeur à accepter.",
            true
        );

        return;
    }


    const confirmation =
        confirm(
            "Accepter le prix proposé par le chauffeur ?"
        );


    if (!confirmation) {

        return;
    }


    const donnees =
        new FormData();


    donnees.append(
        "id_course",
        courseActiveClient.id_course
    );


    try {

        const reponse =
            await fetch(
                "../api/negociation/accepter.php",
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
                "Impossible d'accepter le prix."
            );
        }


        afficherMessagePrixClient(
            "✅ Prix accepté.",
            false
        );


        afficherNotification(
            "✅ Prix accepté."
        );


        await chargerCourseActive();

    }
    catch (erreur) {

        console.error(
            "Erreur acceptation prix :",
            erreur
        );


        afficherMessagePrixClient(
            "❌ " +
            erreur.message,
            true
        );
    }
}


// =====================================================
// MESSAGE PRIX
// =====================================================

function afficherMessagePrixClient(
    message,
    erreur
) {

    const element =
        document.getElementById(
            "messagePrixClient"
        );


    if (!element) {
        return;
    }


    element.textContent =
        message;


    element.style.color =
        erreur
            ? "#dc2626"
            : "#16a34a";
}


// =====================================================
// COMPTER NOTIFICATIONS
// =====================================================

async function chargerNombreNotificationsClient() {

    const compteur =
        document.getElementById(
            "notificationCount"
        );


    if (!compteur) {
        return;
    }


    try {

        const reponse =
            await fetch(
                "../api/notifications/non_lues.php",
                {
                    credentials:
                        "same-origin"
                }
            );


        const resultat =
            await reponse.json();


        if (
            reponse.ok &&
            resultat.success
        ) {

            compteur.textContent =
                String(
                    resultat.non_lues ??
                    0
                );
        }

    }
    catch (erreur) {

        console.error(
            "Erreur compteur notifications :",
            erreur
        );
    }
}


// =====================================================
// LISTE NOTIFICATIONS
// =====================================================

async function chargerNotificationsClient() {

    const container =
        document.getElementById(
            "notificationsClientContainer"
        );


    try {

        const reponse =
            await fetch(
                "../api/notifications/liste.php",
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
                "Impossible de récupérer les notifications."
            );
        }


        const notifications =
            resultat.notifications || [];


        if (container) {

            container.innerHTML = "";


            if (
                notifications.length === 0
            ) {

                container.innerHTML =
                    "<p>Aucune notification.</p>";
            }


            notifications.forEach(
                function (notification) {

                    const bloc =
                        document.createElement(
                            "div"
                        );


                    bloc.className =
                        "course-card";


                    bloc.innerHTML = `

                        <strong>
                            ${escapeHTML(
                                notification.titre ||
                                ""
                            )}
                        </strong>

                        <p>
                            ${escapeHTML(
                                notification.contenu ||
                                ""
                            )}
                        </p>

                        <small>

                            ${escapeHTML(
                                notification.date_creation ||
                                ""
                            )}

                            ${
                                parseInt(
                                    notification.lu,
                                    10
                                ) === 0
                                    ? " · Non lue"
                                    : ""
                            }

                        </small>
                    `;


                    container.appendChild(
                        bloc
                    );
                }
            );
        }


        annoncerNouvelleNotificationClient(
            notifications
        );

    }
    catch (erreur) {

        console.error(
            "Erreur notifications client :",
            erreur
        );
    }
}


// =====================================================
// TOAST NOUVELLE NOTIFICATION
// =====================================================

function annoncerNouvelleNotificationClient(
    notifications
) {

    if (
        !Array.isArray(
            notifications
        ) ||
        notifications.length === 0
    ) {

        return;
    }


    const ids =
        notifications
            .map(
                n =>
                    parseInt(
                        n.id_notification,
                        10
                    )
            )
            .filter(
                Number.isFinite
            );


    if (ids.length === 0) {
        return;
    }


    const maxId =
        Math.max(
            ...ids
        );


    if (
        derniereNotificationIdAnnoncee ===
        0
    ) {

        derniereNotificationIdAnnoncee =
            maxId;

        return;
    }


    const nouvelle =
        notifications.find(
            function (notification) {

                return (
                    parseInt(
                        notification.id_notification,
                        10
                    ) >
                    derniereNotificationIdAnnoncee
                );
            }
        );


    if (nouvelle) {

        afficherNotification(

            (nouvelle.titre ||
                "Notification") +

            " — " +

            (nouvelle.contenu || "")
        );
    }


    derniereNotificationIdAnnoncee =
        Math.max(
            derniereNotificationIdAnnoncee,
            maxId
        );
}

// =====================================================
// MARQUER TOUTES LES NOTIFICATIONS CLIENT COMME LUES
// =====================================================

async function marquerToutesNotificationsLuesClient() {

    try {

        const donnees =
            new FormData();


        donnees.append(
            "tout",
            "1"
        );


        const reponse =
            await fetch(
                "../api/notifications/lire.php",
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
                "Impossible de marquer les notifications."
            );
        }


        console.log(
            "Notifications client lues :",
            resultat.nombre_modifie
        );

    }
    catch (erreur) {

        console.error(
            "Erreur lecture notifications client :",
            erreur
        );
    }
}


// =====================================================
// ACTUALISATION AUTOMATIQUE
// =====================================================

function demarrerSurveillanceClient() {

    if (surveillanceClient) {

        clearInterval(
            surveillanceClient
        );
    }


    surveillanceClient =
        setInterval(
            async function () {

                await chargerCourseActive();

                await chargerNombreNotificationsClient();

                await chargerNotificationsClient();

            },
            5000
        );
}

// =====================================================
// FAST TRAJET V2
// SUIVI DU CHAUFFEUR CÔTÉ CLIENT
// =====================================================

function initialiserCarteSuiviCourse() {

    if (carteSuiviCourse) {

        return;
    }


    const element =
        document.getElementById(
            "courseTrackingMap"
        );


    if (
        !element ||
        typeof L === "undefined"
    ) {

        return;
    }


    carteSuiviCourse =
        L.map(
            "courseTrackingMap"
        ).setView(
            [
                latitudeClient || -4.325,
                longitudeClient || 15.322
            ],
            16
        );


    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,

            attribution:
                "&copy; OpenStreetMap contributors"
        }
    ).addTo(
        carteSuiviCourse
    );


    console.log(
        "✅ Carte de suivi client initialisée."
    );
}


// =====================================================
// AFFICHER / MASQUER LE SUIVI
// =====================================================

async function mettreAJourSuiviCourseClient(
    course
) {

    const carteBloc =
        document.getElementById(
            "suiviCourseCard"
        );


    if (!carteBloc) {

        return;
    }


    // =================================================
    // LA CARTE N'APPARAÎT QUE PENDANT LA COURSE
    // =================================================

    if (
        !course ||
        course.statut_course !==
            "en_cours"
    ) {

        carteBloc.hidden = true;

        return;
    }


    carteBloc.hidden = false;


    const message =
        document.getElementById(
            "messageSuiviCourse"
        );


    if (message) {

        message.textContent =
            "🚕 Votre chauffeur s'est mis en route. Suivez sa progression en temps réel.";
    }


    // =================================================
    // INITIALISER LA CARTE
    // =================================================

    initialiserCarteSuiviCourse();

await afficherItineraireSuiviClient(
    course
);


    // Leaflet doit recalculer la taille puisque
    // la carte était auparavant cachée.
    if (carteSuiviCourse) {

        setTimeout(
            function () {

                carteSuiviCourse.invalidateSize();

            },
            150
        );
    }


    // =================================================
    // CHARGER LES POSITIONS
    // =================================================

    await chargerPositionsSuiviClient(
        course
    );
}


// =====================================================
// RÉCUPÉRER LES POSITIONS DE LA COURSE
// =====================================================

async function chargerPositionsSuiviClient(
    course
) {

    if (
        !course ||
        !course.id_course ||
        !carteSuiviCourse
    ) {

        return;
    }


    const etat =
        document.getElementById(
            "etatPositionChauffeur"
        );


    try {

        const reponse =
            await fetch(
                "../api/gps/positions_course.php?id_course=" +
                encodeURIComponent(
                    course.id_course
                ),
                {
                    method: "GET",

                    credentials:
                        "same-origin"
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
                "Réponse positions GPS invalide :",
                texte
            );

            return;
        }


        if (
            !reponse.ok ||
            !resultat.success
        ) {

            console.error(
                "Erreur positions course :",
                resultat.message ||
                "Erreur inconnue"
            );

            return;
        }


        const points =
            [];


        // =================================================
        // POSITION DU CLIENT
        // =================================================

        if (
            resultat.latitude_client !== null &&
            resultat.latitude_client !== undefined &&
            resultat.longitude_client !== null &&
            resultat.longitude_client !== undefined
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
                Number.isFinite(
                    latitude
                ) &&
                Number.isFinite(
                    longitude
                )
            ) {

                if (
                    marqueurClientSuivi
                ) {

                    marqueurClientSuivi.setLatLng(
                        [
                            latitude,
                            longitude
                        ]
                    );

                }
                else {

                    marqueurClientSuivi =
                        L.marker(
                            [
                                latitude,
                                longitude
                            ]
                        )
                        .addTo(
                            carteSuiviCourse
                        )
                        .bindPopup(
                            "👤 Votre position"
                        );
                }


                points.push(
                    [
                        latitude,
                        longitude
                    ]
                );
            }
        }


        // =================================================
        // POSITION DU CHAUFFEUR
        // =================================================

        if (
            resultat.latitude_chauffeur !== null &&
            resultat.latitude_chauffeur !== undefined &&
            resultat.longitude_chauffeur !== null &&
            resultat.longitude_chauffeur !== undefined
        ) {

            const latitude =
                parseFloat(
                    resultat.latitude_chauffeur
                );


            const longitude =
                parseFloat(
                    resultat.longitude_chauffeur
                );


            if (
                Number.isFinite(
                    latitude
                ) &&
                Number.isFinite(
                    longitude
                )
            ) {

                if (
                    marqueurChauffeurSuivi
                ) {

                    // =====================================
                    // LE MARQUEUR SE DÉPLACE
                    // =====================================

                    marqueurChauffeurSuivi.setLatLng(
                        [
                            latitude,
                            longitude
                        ]
                    );

                }
                else {

                    marqueurChauffeurSuivi =
                        L.marker(
                            [
                                latitude,
                                longitude
                            ]
                        )
                        .addTo(
                            carteSuiviCourse
                        )
                        .bindPopup(
                            "🚕 Votre chauffeur"
                        );
                }


                points.push(
                    [
                        latitude,
                        longitude
                    ]
                );


                if (etat) {

                    etat.textContent =
                        "🟢 Position du chauffeur actualisée en temps réel.";
                }
            }

        }
        else {

            if (etat) {

                etat.textContent =
                    "📡 En attente de la première position GPS du chauffeur...";
            }
        }


        // =================================================
        // DESTINATION
        // =================================================

        const latitudeDestination =
            parseFloat(
                resultat.latitude_destination ??
                course.latitude_destination
            );


        const longitudeDestination =
            parseFloat(
                resultat.longitude_destination ??
                course.longitude_destination
            );


        if (
            Number.isFinite(
                latitudeDestination
            ) &&
            Number.isFinite(
                longitudeDestination
            )
        ) {

            if (
                marqueurDestinationSuivi
            ) {

                marqueurDestinationSuivi.setLatLng(
                    [
                        latitudeDestination,
                        longitudeDestination
                    ]
                );

            }
            else {

                marqueurDestinationSuivi =
                    L.marker(
                        [
                            latitudeDestination,
                            longitudeDestination
                        ]
                    )
                    .addTo(
                        carteSuiviCourse
                    )
                    .bindPopup(
                        "🏁 Destination"
                    );
            }


            points.push(
                [
                    latitudeDestination,
                    longitudeDestination
                ]
            );
        }


        // =================================================
        // CADRER LA CARTE UNE SEULE FOIS
        // =================================================

       if (
    premiereVueSuiviCourse &&
    marqueurChauffeurSuivi
) {

    const positionChauffeur =
        marqueurChauffeurSuivi.getLatLng();


    carteSuiviCourse.setView(
        [
            positionChauffeur.lat,
            positionChauffeur.lng
        ],
        16
    );


    premiereVueSuiviCourse =
        false;
}

    }
    catch (erreur) {

        console.error(
            "Erreur suivi chauffeur côté client :",
            erreur
        );


        if (etat) {

            etat.textContent =
                "⚠️ Impossible d'actualiser momentanément la position du chauffeur.";
        }
    }
}


// =====================================================
// TRAJET ROUTIER BLEU
// CLIENT → DESTINATION
// =====================================================

async function afficherItineraireSuiviClient(
    course
) {

    if (
        !course ||
        !carteSuiviCourse
    ) {

        return;
    }


   // =================================================
// VÉRIFIER SI CET ITINÉRAIRE APPARTIENT
// DÉJÀ À LA COURSE ACTUELLE
// =================================================

const idCourseActuelle =
    parseInt(
        course.id_course
    );


if (
    itineraireSuiviCourse &&
    idCourseItineraireSuivi ===
        idCourseActuelle
) {

    return;
}


// =================================================
// SI C'EST UNE NOUVELLE COURSE,
// SUPPRIMER L'ANCIEN ITINÉRAIRE
// =================================================

if (
    itineraireSuiviCourse &&
    idCourseItineraireSuivi !==
        idCourseActuelle
) {

    carteSuiviCourse.removeLayer(
        itineraireSuiviCourse
    );

    itineraireSuiviCourse = null;


    // Supprimer également les anciens marqueurs
    if (marqueurChauffeurSuivi) {

        carteSuiviCourse.removeLayer(
            marqueurChauffeurSuivi
        );

        marqueurChauffeurSuivi = null;
    }


    if (marqueurClientSuivi) {

        carteSuiviCourse.removeLayer(
            marqueurClientSuivi
        );

        marqueurClientSuivi = null;
    }


    if (marqueurDestinationSuivi) {

        carteSuiviCourse.removeLayer(
            marqueurDestinationSuivi
        );

        marqueurDestinationSuivi = null;
    }


    premiereVueSuiviCourse = true;

    itineraireSuiviCharge = false;
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
        !Number.isFinite(
            latitudeDepart
        ) ||
        !Number.isFinite(
            longitudeDepart
        ) ||
        !Number.isFinite(
            latitudeDestination
        ) ||
        !Number.isFinite(
            longitudeDestination
        )
    ) {

        console.warn(
            "Impossible de tracer l'itinéraire : coordonnées manquantes."
        );

        return;
    }


    try {

        // =============================================
        // OSRM :
        // longitude,latitude
        // =============================================

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
            await fetch(
                url
            );


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


        const route =
            resultat.routes[0];


        // =============================================
        // SUPPRIMER ANCIENNE ROUTE
        // =============================================

        if (
            itineraireSuiviCourse
        ) {

            carteSuiviCourse.removeLayer(
                itineraireSuiviCourse
            );
        }


        // =============================================
        // CRÉER LA LIGNE BLEUE
        // =============================================

        itineraireSuiviCourse =
            L.geoJSON(
                route.geometry,
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
            .addTo(
                carteSuiviCourse
            );


        // =============================================
        // AJUSTER LA CARTE AU TRAJET
        // =============================================

        carteSuiviCourse.fitBounds(

            itineraireSuiviCourse
                .getBounds(),

            {
                padding:
                    [40, 40]
            }
        );


        itineraireSuiviCharge =
            true;

            idCourseItineraireSuivi =
    idCourseActuelle;


        console.log(
            "✅ Itinéraire bleu affiché."
        );

    }
    catch (erreur) {

        console.error(
            "Erreur itinéraire suivi :",
            erreur
        );
    }
}


// =====================================================
// ARRÊTER LE SUIVI DE LA COURSE
// =====================================================

function arreterSuiviCourseClient() {

    const blocSuivi =
        document.getElementById(
            "suiviCourseCard"
        );


    // Cacher la carte de suivi
    if (blocSuivi) {

        blocSuivi.hidden = true;
    }


    if (!carteSuiviCourse) {

        return;
    }


    // Retirer le chauffeur
    if (marqueurChauffeurSuivi) {

        carteSuiviCourse.removeLayer(
            marqueurChauffeurSuivi
        );

        marqueurChauffeurSuivi = null;
    }


    // Retirer le client
    if (marqueurClientSuivi) {

        carteSuiviCourse.removeLayer(
            marqueurClientSuivi
        );

        marqueurClientSuivi = null;
    }


    // Retirer la destination
    if (marqueurDestinationSuivi) {

        carteSuiviCourse.removeLayer(
            marqueurDestinationSuivi
        );

        marqueurDestinationSuivi = null;
    }


    // Retirer la route bleue
    if (itineraireSuiviCourse) {

        carteSuiviCourse.removeLayer(
            itineraireSuiviCourse
        );

        itineraireSuiviCourse = null;
    }


    // Préparer la carte pour une prochaine course
    premiereVueSuiviCourse = true;

    itineraireSuiviCharge = false;

    idCourseItineraireSuivi = null;


    console.log(
        "🛑 Suivi GPS client arrêté."
    );
}

// =====================================================
// FAST TRAJET V2
// MESSAGERIE CLIENT <-> CHAUFFEUR
// =====================================================

function initialiserMessagerieClient() {

    const bouton =
        document.getElementById(
            "envoyerMessageCourseClient"
        );


    if (!bouton) {
        return;
    }


    bouton.addEventListener(
        "click",
        envoyerMessageCourseClient
    );
}


// =====================================================
// AFFICHER / MASQUER LA DISCUSSION
// =====================================================

async function mettreAJourMessagerieClient(
    course
) {

    const carte =
        document.getElementById(
            "messagesCourseCardClient"
        );


    if (!carte) {
        return;
    }


    if (
        !course ||
        course.statut_prix !== "accepte" ||
        (
            course.statut_course !==
                "prix_accepte" &&
            course.statut_course !==
                "en_cours"
        )
    ) {

        carte.hidden = true;

        return;
    }


    carte.hidden = false;


    await chargerMessagesCourseClient(
        course.id_course
    );
}


// =====================================================
// CHARGER LES MESSAGES
// =====================================================

async function chargerMessagesCourseClient(
    idCourse
) {

    const zone =
        document.getElementById(
            "listeMessagesCourseClient"
        );


    if (!zone || !idCourse) {
        return;
    }


    try {

        const reponse =
            await fetch(
                "../api/message/liste.php?id_course=" +
                encodeURIComponent(
                    idCourse
                ),
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

            console.error(
                "Erreur messages :",
                resultat.message
            );

            return;
        }


        afficherMessagesClient(
            resultat.messages || []
        );

    }
    catch (erreur) {

        console.error(
            "Erreur chargement discussion client :",
            erreur
        );
    }
}


// =====================================================
// AFFICHER LES MESSAGES
// =====================================================

function afficherMessagesClient(
    messages
) {

    const zone =
        document.getElementById(
            "listeMessagesCourseClient"
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

        zone.appendChild(vide);

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
                    "client"
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

async function envoyerMessageCourseClient() {

    if (
        !courseActiveClient ||
        !courseActiveClient.id_course
    ) {

        afficherNotification(
            "Aucune course active."
        );

        return;
    }


    const champ =
        document.getElementById(
            "messageCourseClient"
        );


    const bouton =
        document.getElementById(
            "envoyerMessageCourseClient"
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
        courseActiveClient.id_course
    );


    donnees.append(
        "message",
        message
    );


    bouton.disabled = true;


    try {

        const reponse =
            await fetch(
                "../api/message/envoyer.php",
                {
                    method:
                        "POST",

                    body:
                        donnees,

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
                "Impossible d'envoyer le message."
            );
        }


        champ.value = "";


        await chargerMessagesCourseClient(
            courseActiveClient.id_course
        );

    }
    catch (erreur) {

        afficherNotification(
            "❌ " +
            erreur.message
        );
    }


    bouton.disabled = false;
}


// =====================================================
// DÉCONNEXION
// =====================================================

function initialiserDeconnexion() {

    const bouton =
        document.getElementById(
            "deconnexionButton"
        );


    if (!bouton) {

        return;

    }


    bouton.addEventListener(
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

                const reponse =
                    await fetch(
                        "../api/auth/deconnexion.php",
                        {
                            method: "POST",

                            credentials:
                                "same-origin"
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

                    afficherNotification(
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

                afficherNotification(
                    "Erreur lors de la déconnexion."
                );

            }

        }
    );

}


// =====================================================
// NOTIFICATION
// =====================================================

let timerNotification = null;


function afficherNotification(
    message
) {

    const notification =
        document.getElementById(
            "notification"
        );


    if (!notification) {

        return;

    }


    notification.textContent =
        message;


    notification.classList.add(
        "show"
    );


    clearTimeout(
        timerNotification
    );


    timerNotification =
        setTimeout(
            function () {

                notification.classList.remove(
                    "show"
                );

            },
            3500
        );

}


// =====================================================
// PROTECTION HTML
// =====================================================

function escapeHTML(
    valeur
) {

    const div =
        document.createElement(
            "div"
        );

    div.textContent =
        valeur ?? "";

    return div.innerHTML;

}


// =====================================================
// SIGNALEMENT CLIENT
// =====================================================

const envoyerAlerteClient =
    document.getElementById(
        "envoyerAlerteClient"
    );


if (envoyerAlerteClient) {

    envoyerAlerteClient.addEventListener(
        "click",
        async function () {

            if (
                !courseActiveClient ||
                !courseActiveClient.id_course
            ) {

                afficherNotification(
                    "Vous devez avoir une course active pour envoyer un signalement."
                );

                return;
            }


            const type =
                document.getElementById(
                    "typeAlerteClient"
                )?.value;


            const niveau =
                document.getElementById(
                    "niveauAlerteClient"
                )?.value;


            const champDescription =
                document.getElementById(
                    "descriptionAlerteClient"
                );


            const description =
                champDescription
                    ?.value
                    .trim();


            if (!type) {

                afficherNotification(
                    "Veuillez choisir le type de signalement."
                );

                return;
            }


            if (!description) {

                afficherNotification(
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
                courseActiveClient.id_course
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


            envoyerAlerteClient.disabled =
                true;


            try {

                const reponse =
                    await fetch(
                        "../api/alerte/creer.php",
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
                        "Signalement impossible."
                    );
                }


                champDescription.value =
                    "";


                afficherNotification(
                    resultat.message
                );

            }
            catch (erreur) {

                console.error(
                    "Erreur signalement client :",
                    erreur
                );


                afficherNotification(
                    "❌ " +
                    erreur.message
                );
            }
            finally {

                envoyerAlerteClient.disabled =
                    false;
            }

        }
    );
}

// =====================================================
// LOG
// =====================================================

console.log(
    "Fast Trajet V2 - interface client prête."
);