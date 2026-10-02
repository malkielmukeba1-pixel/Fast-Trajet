"use strict";

document.addEventListener(
    "DOMContentLoaded",
    function () {

        console.log(
            "Fast Trajet - authentification client"
        );


        const connexionForm =
            document.getElementById(
                "connexionForm"
            );

        const inscriptionForm =
            document.getElementById(
                "inscriptionForm"
            );

        const message =
            document.getElementById(
                "message"
            );

        const bouton =
            document.getElementById(
                "submitButton"
            );


        async function envoyer(
            formulaire,
            url,
            redirection
        ) {

            bouton.disabled = true;

            bouton.textContent =
                "⏳ Traitement...";

            message.textContent = "";


            try {

                const donnees =
                    new FormData(
                        formulaire
                    );


                const reponse =
                    await fetch(
                        url,
                        {
                            method: "POST",
                            body: donnees,
                            credentials:
                                "same-origin"
                        }
                    );


                const texte =
                    await reponse.text();


                console.log(
                    "Réponse brute :",
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
                        "Réponse non JSON :",
                        texte
                    );

                    throw new Error(
                        "Le serveur n'a pas renvoyé une réponse JSON valide."
                    );

                }


                if (
                    !reponse.ok ||
                    !resultat.success
                ) {

                    throw new Error(
                        resultat.message ||
                        "Opération impossible."
                    );

                }


                message.className =
                    "message success";

                message.textContent =
                    "✅ " +
                    resultat.message;


                setTimeout(
                    function () {

                        window.location.href =
                            redirection;

                    },
                    700
                );

            }
            catch (erreur) {

                console.error(
                    "Erreur :",
                    erreur
                );


                message.className =
                    "message error";

                message.textContent =
                    "❌ " +
                    erreur.message;


                bouton.disabled =
                    false;


                bouton.textContent =
                    formulaire === connexionForm
                        ? "Se connecter"
                        : "Créer mon compte";

            }

        }


        // =================================================
        // CONNEXION
        // =================================================

        if (connexionForm) {

            connexionForm.addEventListener(
                "submit",
                function (event) {

                    event.preventDefault();


                    envoyer(
                        connexionForm,
                        "../api/auth/connexion.php",
                        "index.php"
                    );

                }
            );

        }


        // =================================================
        // INSCRIPTION
        // =================================================

        if (inscriptionForm) {

            inscriptionForm.addEventListener(
                "submit",
                function (event) {

                    event.preventDefault();


                    envoyer(
                        inscriptionForm,
                        "../api/auth/inscription_client.php",
                        "connexion.html"
                    );

                }
            );

        }

    }
);