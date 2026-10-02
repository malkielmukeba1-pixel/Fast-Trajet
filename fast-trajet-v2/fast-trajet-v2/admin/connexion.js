document.addEventListener(
    "DOMContentLoaded",
    function () {

        const formulaire =
            document.getElementById(
                "connexionForm"
            );

        const bouton =
            document.getElementById(
                "connexionButton"
            );

        const message =
            document.getElementById(
                "message"
            );


        formulaire.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();


                message.textContent =
                    "";


                bouton.disabled =
                    true;

                bouton.textContent =
                    "⏳ Connexion...";


                const donnees =
                    new FormData(
                        formulaire
                    );


                try {

                   const reponse =
    await fetch(
        "connexion.php",
        {
            method: "POST",
            body: donnees,
            credentials: "same-origin"
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
                            JSON.parse(texte);

                    }
                    catch (erreur) {

                        throw new Error(
                            "Le serveur n'a pas renvoyé un JSON valide."
                        );

                    }


                    console.log(
                        "Réponse connexion admin :",
                        resultat
                    );


                    if (
                        resultat.success
                    ) {

                        message.style.color =
                            "#16a34a";

                        message.textContent =
                            "✅ Connexion réussie.";


                        setTimeout(
                            function () {

                                window.location.href =
                                    "index.php";

                            },
                            500
                        );

                    }
                    else {

                        message.style.color =
                            "#dc2626";

                        message.textContent =
                            resultat.message ||
                            "Connexion impossible.";


                        bouton.disabled =
                            false;

                        bouton.textContent =
                            "Se connecter";
                    }

                }

                catch (erreur) {

                    console.error(
                        "Erreur connexion admin :",
                        erreur
                    );


                    message.style.color =
                        "#dc2626";

                    message.textContent =
                        "❌ Impossible de contacter le serveur.";


                    bouton.disabled =
                        false;

                    bouton.textContent =
                        "Se connecter";
                }

            }
        );

    }
);