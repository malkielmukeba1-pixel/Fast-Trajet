<?php
// ======================================================
// FAST TRAJET V2
// PAGE CENTRALE D'AUTHENTIFICATION
// ======================================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fast Trajet - Connexion</title>

    <link rel="stylesheet" href="auth.css">
</head>

<body>

    <main class="auth-page">

        <!-- LOGO -->
        <header class="auth-header">
            <div class="logo">
                <span class="logo-icon">🚕</span>
                <span>Fast <strong>Trajet</strong></span>
            </div>

            <p class="slogan">
                Votre trajet, notre priorité
            </p>
        </header>


        <!-- CONTENEUR -->
        <section class="auth-container">

            <!-- PARTIE PRÉSENTATION -->
            <div class="auth-presentation">

                <span class="presentation-icon">📍</span>

                <h1>
                    Déplacez-vous<br>
                    <strong>en toute sécurité</strong>
                </h1>

                <p>
                    Commandez votre trajet rapidement,
                    trouvez un chauffeur disponible et
                    voyagez en toute sérénité.
                </p>

                <div class="advantages">

                    <div class="advantage">
                        <span>📍</span>
                        <div>
                            <strong>Géolocalisation</strong>
                            <small>Suivez votre trajet en temps réel.</small>
                        </div>
                    </div>

                    <div class="advantage">
                        <span>🚕</span>
                        <div>
                            <strong>Chauffeurs disponibles</strong>
                            <small>Trouvez facilement un chauffeur.</small>
                        </div>
                    </div>

                    <div class="advantage">
                        <span>🛡️</span>
                        <div>
                            <strong>Sécurité</strong>
                            <small>Un système pensé pour votre sécurité.</small>
                        </div>
                    </div>

                </div>

            </div>


            <!-- AUTHENTIFICATION -->
            <div class="auth-card">

                <!-- CHOIX DU PROFIL -->
                <div class="role-selection">

                    <button
                        type="button"
                        class="role-button active"
                        data-role="client"
                    >
                        👤 Client
                    </button>

                    <button
                        type="button"
                        class="role-button"
                        data-role="chauffeur"
                    >
                        🚕 Chauffeur
                    </button>

                </div>


                <!-- TITRE -->
                <div class="auth-title">

                    <h2 id="authTitle">
                        Connexion Client
                    </h2>

                    <p id="authDescription">
                        Connectez-vous à votre compte Fast Trajet.
                    </p>

                </div>


                <!-- FORMULAIRE CONNEXION -->
                <form
                    id="connexionForm"
                    method="POST"
                    action="connexion.php"
                    novalidate
                >

                    <input
                        type="hidden"
                        name="role"
                        id="connexionRole"
                        value="client"
                    >

                    <div class="form-group">

                        <label for="identifiant">
                            E-mail ou téléphone
                        </label>

                        <input
                            type="text"
                            id="identifiant"
                            name="identifiant"
                            placeholder="Ex : client@email.com"
                            autocomplete="username"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="mot_de_passe">
                            Mot de passe
                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="mot_de_passe"
                                name="mot_de_passe"
                                placeholder="Votre mot de passe"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="toggle-password"
                                aria-label="Afficher le mot de passe"
                            >
                                👁️
                            </button>

                        </div>

                    </div>


                    <div
                        id="message"
                        class="auth-message"
                        aria-live="polite"
                    ></div>


                    <button
                        type="submit"
                        id="connexionButton"
                        class="main-button"
                    >
                        Se connecter
                    </button>

                </form>


                <!-- INSCRIPTION -->
                <div class="register-section">

                    <p>
                        Vous n'avez pas encore de compte ?
                    </p>

                    <button
                        type="button"
                        id="inscriptionButton"
                        class="secondary-button"
                    >
                        Créer un compte
                    </button>

                </div>


                <!-- RETOUR -->
                <a
                    href="../index.php"
                    class="back-link"
                >
                    ← Retour à l'accueil
                </a>

            </div>

        </section>

    </main>


    <script src="auth.js"></script>

</body>
</html>