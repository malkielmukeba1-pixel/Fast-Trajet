// ======================================================
// FAST TRAJET V2
// AUTHENTIFICATION CLIENT / CHAUFFEUR
// ======================================================

document.addEventListener("DOMContentLoaded", () => {

    // ==================================================
    // ÉLÉMENTS
    // ==================================================

    const roleButtons = document.querySelectorAll(".role-button");

    const connexionForm =
        document.getElementById("connexionForm");

    const connexionRole =
        document.getElementById("connexionRole");

    const authTitle =
        document.getElementById("authTitle");

    const authDescription =
        document.getElementById("authDescription");

    const identifiant =
        document.getElementById("identifiant");

    const motDePasse =
        document.getElementById("mot_de_passe");

    const togglePassword =
        document.getElementById("togglePassword");

    const connexionButton =
        document.getElementById("connexionButton");

    const inscriptionButton =
        document.getElementById("inscriptionButton");

    const message =
        document.getElementById("message");


    // ==================================================
    // RÔLE ACTUEL
    // ==================================================

    let roleActuel = "client";


    // ==================================================
    // AFFICHER UN MESSAGE
    // ==================================================

    function afficherMessage(texte, type = "error") {

        if (!message) {
            return;
        }

        message.textContent = texte;

        message.className =
            "auth-message show " + type;
    }


    // ==================================================
    // CACHER LE MESSAGE
    // ==================================================

    function cacherMessage() {

        if (!message) {
            return;
        }

        message.textContent = "";

        message.className =
            "auth-message";
    }


    // ==================================================
    // CHANGER LE PROFIL
    // ==================================================

    function changerRole(role) {

        roleActuel = role;

        // ----------------------------------------------
        // Boutons
        // ----------------------------------------------

        roleButtons.forEach(button => {

            button.classList.remove("active");

            if (button.dataset.role === role) {
                button.classList.add("active");
            }

        });


        // ----------------------------------------------
        // Champ caché
        // ----------------------------------------------

        if (connexionRole) {
            connexionRole.value = role;
        }


        // ----------------------------------------------
        // Interface
        // ----------------------------------------------

        if (role === "client") {

            if (authTitle) {
                authTitle.textContent =
                    "Connexion Client";
            }

            if (authDescription) {
                authDescription.textContent =
                    "Connectez-vous à votre compte Fast Trajet.";
            }

            if (identifiant) {
                identifiant.placeholder =
                    "Ex : client@email.com";
            }

        } else {

            if (authTitle) {
                authTitle.textContent =
                    "Connexion Chauffeur";
            }

            if (authDescription) {
                authDescription.textContent =
                    "Connectez-vous à votre espace chauffeur.";
            }

            if (identifiant) {
                identifiant.placeholder =
                    "Ex : chauffeur@email.com";
            }
        }


        cacherMessage();

        if (identifiant) {
            identifiant.focus();
        }
    }


    // ==================================================
    // ÉCOUTER LES BOUTONS CLIENT / CHAUFFEUR
    // ==================================================

    roleButtons.forEach(button => {

        button.addEventListener("click", () => {

            const role =
                button.dataset.role;

            if (
                role === "client" ||
                role === "chauffeur"
            ) {
                changerRole(role);
            }

        });

    });


    // ==================================================
    // AFFICHER / CACHER MOT DE PASSE
    // ==================================================

    if (togglePassword && motDePasse) {

        togglePassword.addEventListener(
            "click",
            () => {

                if (
                    motDePasse.type ===
                    "password"
                ) {

                    motDePasse.type =
                        "text";

                    togglePassword.textContent =
                        "🙈";

                    togglePassword.setAttribute(
                        "aria-label",
                        "Masquer le mot de passe"
                    );

                } else {

                    motDePasse.type =
                        "password";

                    togglePassword.textContent =
                        "👁️";

                    togglePassword.setAttribute(
                        "aria-label",
                        "Afficher le mot de passe"
                    );
                }

            }
        );
    }


    // ==================================================
    // CONNEXION
    // ==================================================

    if (connexionForm) {

        connexionForm.addEventListener(
            "submit",
            async (event) => {

                event.preventDefault();

                cacherMessage();


                // --------------------------------------
                // Vérifications côté navigateur
                // --------------------------------------

                const identifiantValue =
                    identifiant.value.trim();

                const motDePasseValue =
                    motDePasse.value;


                if (identifiantValue === "") {

                    afficherMessage(
                        "Veuillez entrer votre e-mail ou téléphone."
                    );

                    identifiant.focus();

                    return;
                }


                if (motDePasseValue === "") {

                    afficherMessage(
                        "Veuillez entrer votre mot de passe."
                    );

                    motDePasse.focus();

                    return;
                }


                // --------------------------------------
                // Désactiver le bouton
                // --------------------------------------

                if (connexionButton) {

                    connexionButton.disabled =
                        true;

                    connexionButton.textContent =
                        "Connexion en cours...";
                }


                try {

                    // ----------------------------------
                    // FormData
                    // ----------------------------------

                    const donnees =
                        new FormData(connexionForm);


                    // ----------------------------------
                    // ENVOI PHP
                    // ----------------------------------

                    const reponse =
                        await fetch(
                            "connexion.php",
                            {
                                method: "POST",

                                body: donnees,

                                credentials:
                                    "same-origin"
                            }
                        );


                    // ----------------------------------
                    // RÉCUPÉRER LA RÉPONSE
                    // ----------------------------------

                    const texte =
                        await reponse.text();


                    console.log(
                        "Réponse brute :",
                        texte
                    );


                    // ----------------------------------
                    // Vérifier JSON
                    // ----------------------------------

                    let resultat;

                    try {

                        resultat =
                            JSON.parse(texte);

                    } catch (erreurJSON) {

                        throw new Error(
                            "Le serveur n'a pas renvoyé un JSON valide."
                        );
                    }


                    // ----------------------------------
                    // ERREUR SERVEUR
                    // ----------------------------------

                    if (
                        !resultat ||
                        resultat.success !== true
                    ) {

                        afficherMessage(
                            resultat?.message ||
                            "Identifiant ou mot de passe incorrect."
                        );

                        return;
                    }


                    // ----------------------------------
                    // SUCCÈS
                    // ----------------------------------

                    afficherMessage(
                        resultat.message ||
                        "Connexion réussie.",
                        "success"
                    );


                    // ----------------------------------
                    // REDIRECTION
                    // ----------------------------------

                    if (
                        resultat.role ===
                        "client"
                    ) {

                        window.location.href =
                            "../client/index.php";

                    } else if (
                        resultat.role ===
                        "chauffeur"
                    ) {

                        window.location.href =
                            "../chauffeur/index.php";

                    } else {

                        afficherMessage(
                            "Connexion réussie, mais le rôle est inconnu."
                        );
                    }

                } catch (erreur) {

                    console.error(
                        "Erreur connexion :",
                        erreur
                    );

                    afficherMessage(
                        erreur.message ||
                        "Une erreur est survenue lors de la connexion."
                    );

                } finally {

                    // ----------------------------------
                    // Réactiver bouton
                    // ----------------------------------

                    if (connexionButton) {

                        connexionButton.disabled =
                            false;

                        connexionButton.textContent =
                            "Se connecter";
                    }
                }

            }
        );
    }


    // ==================================================
    // INSCRIPTION
    // ==================================================

    if (inscriptionButton) {

        inscriptionButton.addEventListener(
            "click",
            () => {

                if (roleActuel === "client") {

                    window.location.href =
                        "inscription_client.php";

                } else {

                    window.location.href =
                        "inscription_chauffeur.php";
                }

            }
        );
    }


    // ==================================================
    // EFFACER MESSAGE LORSQUE L'UTILISATEUR ÉCRIT
    // ==================================================

    if (identifiant) {

        identifiant.addEventListener(
            "input",
            () => {
                cacherMessage();
            }
        );
    }


    if (motDePasse) {

        motDePasse.addEventListener(
            "input",
            () => {
                cacherMessage();
            }
        );
    }


    // ==================================================
    // INITIALISATION
    // ==================================================

    changerRole("client");

});