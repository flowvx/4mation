<?php
 

require_once __DIR__ . '/includes/functions.php';

// Si l'admin est déjà connecté, on le redirige directement vers ses candidatures
startSecureSession();
if (!empty($_SESSION['admin_id'])) {
    header('Location: candidatures.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - 4Mation</title>
    <link rel="stylesheet" href="css/index.css"> 
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="auth-page">

    <main class="auth-container">
        <div class="auth-card">
            <img src="img/66pp-removebg-preview.png" alt="4Mation" class="logo-auth" style="max-width: 150px; margin-bottom: 20px;">
            
            <h2 id="auth-title">Administration</h2>
            <p id="auth-subtitle">Veuillez vous connecter pour gérer les offres.</p>

            <div class="auth-tabs">
                <button class="auth-tab active" data-tab="login">Se connecter</button>
                <button class="auth-tab" data-tab="register">Créer un compte</button>
            </div>

            <?php renderFlash(); ?>

            <form id="form-login" class="auth-form" action="process_login.php" method="POST">
                <div class="input-group">
                    <label for="username">Identifiant</label>
                    <input type="text" id="username" name="username" placeholder="ex: dahirou_admin" required>
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="hero-cta" style="width: 100%; border: none; cursor: pointer;">Se connecter</button>
                
                <p class="auth-footer-link">
                    Pas encore de compte ? <a href="#" data-tab-link="register">Créer un compte</a>
                </p>
            </form>

            <form id="form-register" class="auth-form" action="process_register.php" method="POST" hidden>
                <div class="input-row">
                    <div class="input-group">
                        <label for="reg-prenom">Prénom</label>
                        <input type="text" id="reg-prenom" name="prenom" placeholder="Marie" required>
                    </div>
                    <div class="input-group">
                        <label for="reg-nom">Nom</label>
                        <input type="text" id="reg-nom" name="nom" placeholder="Dupont" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="reg-email">Adresse email</label>
                    <input type="email" id="reg-email" name="email" placeholder="marie@4mation.fr" required>
                </div> 
                <div class="input-group">
                    <label for="reg-password">Mot de passe</label>
                    <input type="password" id="reg-password" name="password" placeholder="••••••••" required>
                    <div class="password-strength">
                        <div class="strength-bar" id="bar-1"></div>
                        <div class="strength-bar" id="bar-2"></div>
                        <div class="strength-bar" id="bar-3"></div>
                    </div>
                    <span class="strength-label" id="strength-label"></span>
                </div>
                <div class="input-group">
                    <label for="reg-confirm">Confirmer le mot de passe</label>
                    <input type="password" id="reg-confirm" name="password_confirm" placeholder="••••••••" required>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="reg-terms" name="terms" required>
                    <label for="reg-terms">
                        J'accepte les <a href="MentionLegal.html">mentions légales</a>.
                    </label>
                </div>
                <button type="submit" class="hero-cta" style="width: 100%; border: none; cursor: pointer;">Créer le compte</button>
                <p class="auth-footer-link">
                    Déjà un compte ? <a href="#" data-tab-link="login">Se connecter</a>
                </p>
            </form>
        </div>
    </main>

    <script>
        const tabs = document.querySelectorAll('.auth-tab');
        const formLogin = document.getElementById('form-login');
        const formRegister = document.getElementById('form-register');
        const authTitle = document.getElementById('auth-title');
        const authSubtitle = document.getElementById('auth-subtitle');

        function switchTab(target) {
            tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === target));
            formLogin.hidden = target !== 'login';
            formRegister.hidden = target !== 'register';
            authTitle.textContent = target === 'login' ? 'Administration' : 'Créer un compte';
            authSubtitle.textContent = target === 'login' ? 'Veuillez vous connecter pour gérer les offres.' : "Rejoignez l'équipe de modération.";
        }

        tabs.forEach(tab => tab.addEventListener('click', () => switchTab(tab.dataset.tab)));
        document.querySelectorAll('[data-tab-link]').forEach(link => {
            link.addEventListener('click', e => { e.preventDefault(); switchTab(link.dataset.tabLink); });
        });
    </script>
</body>
</html>