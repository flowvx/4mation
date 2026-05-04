<?php
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_log("Tentative de chargement de la page d'accueil...");
error_reporting(E_ALL);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pdo = getPDO(); 

$stmt = $pdo->query('
    SELECT
        o.id,
        o.titre,
        o.lieu,
        o.type_contrat,
        o.date_publication,
        o.teletravail_possible,
        o.niveau_etude,
        e.nom        AS entreprise_nom,
        e.logo_url   AS entreprise_logo,
        m.nom_metier AS metier
    FROM offres o
    INNER JOIN entreprises e ON o.entreprise_id = e.id
    LEFT  JOIN metiers     m ON o.metier_id     = m.id
    WHERE o.is_active = 1
    ORDER BY o.date_publication DESC
    LIMIT 12
');

$offres = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4Mation - Trouvez votre stage ou alternance</title>
    <link rel="stylesheet" href="css/index.css"> 
</head>
<body>

 
<header>
    <div class="header-container">
        <img src="img/66pp-removebg-preview.png" alt="Logo 4Mation" class="logo">
        <nav>
            <ul class="nav-menu">
                <li><a href="#">Nos Formations</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
        <a href="login.php" class="cta-button">Espace Recruteur</a>
    </div>
</header>
 
<?php renderFlash(); ?>

 
<section class="hero">
    <h1>Propulsez votre carrière avec un stage qui vous ressemble</h1>
    <p>4Mation vous accompagne dans votre recherche de stage ou d'alternance.</p>
    <a href="offres.php" class="cta-button">Parcourir les offres</a>
    <img src="img/9898uyu.png" alt="Étudiants en situation professionnelle" class="hero-image">
</section>

 
<section class="quick-search">
    <form action="offres.php" method="GET">
        <input type="text" name="q" placeholder="Mots-clés (ex: Développeur, Marketing)"
               value="<?= h(getStr('q')) ?>">
        <input type="text" name="lieu" placeholder="Localisation"
               value="<?= h(getStr('lieu')) ?>">
        <select name="type_contrat">
            <option value="">Type de contrat</option>
            <option value="Stage">Stage</option>
            <option value="Alternance">Alternance</option>
        </select>
        <button type="submit">Rechercher</button>
    </form>
</section>

 
<section class="latest-offers">
    <h2>Dernières Offres</h2>

    <?php if (empty($offres)): ?>
        <p style="color:var(--text-muted); text-align:center; padding:40px 0;">
            Aucune offre disponible pour le moment. Revenez bientôt !
        </p>
    <?php else: ?>
    <div class="offers-grid">
        <?php foreach ($offres as $offre): ?>
        <div class="offer-card"> 
            <h3><?= h($offre['titre']) ?></h3>
            <p><?= h($offre['entreprise_nom']) ?></p>
            <p><?= h($offre['lieu']) ?></p>

            <span class="tag"><?= h($offre['type_contrat']) ?></span>
            <?php if ($offre['teletravail_possible']): ?>
                <span class="teletravail-badge">Télétravail</span>
            <?php endif; ?>

            <?php if ($offre['metier']): ?>
                <p><small style="color:var(--text-dim)"><?= h($offre['metier']) ?></small></p>
            <?php endif; ?>

            <?php if ($offre['niveau_etude']): ?>
                <p>Niveau : <em><?= h($offre['niveau_etude']) ?></em></p>
            <?php endif; ?>

            <p><small style="color:var(--text-dim)">
                Publiée le <?= h(date('d/m/Y', strtotime($offre['date_publication']))) ?>
            </small></p>

            <a href="offre.php?id=<?= (int)$offre['id'] ?>" class="offer-link">Voir l'offre</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top:40px;">
        <a href="offres.php" class="cta-button" style="display:inline-block;">
            Voir toutes les offres →
        </a>
    </div>
</section>
 
<section class="why-choose">
    <h2>Pourquoi choisir 4Mation ?</h2>
    <div class="value-props">
        <div class="value-prop">
            <h3>Accompagnement</h3>
            <p>Coaching personnalisé pour les entretiens.</p>
        </div>
        <div class="value-prop">
            <h3>Réseau</h3>
            <p>Plus de 500 entreprises partenaires.</p>
        </div>
        <div class="value-prop">
            <h3>Rapidité</h3>
            <p>Processus de candidature simplifié.</p>
        </div>
    </div>
</section>
 
<section class="social-proof">
    <h2>Ils nous font confiance</h2>
    <div class="testimonials">
        <blockquote>
            <p>"Grâce à 4Mation, j'ai trouvé une alternance en moins d'un mois !"</p>
            <cite>- Étudiant satisfait</cite>
        </blockquote>
    </div>
</section>

 
<footer>
    <div class="footer-links">
        <a href="MentionLegal.html">Mentions Légales</a>
    </div>
    <div class="social-icons">
        <a href="#"><img src="img/linkedin-icon.png" alt="LinkedIn"></a>
        <a href="#"><img src="img/twitter-icon.png" alt="Twitter"></a>
    </div>
</footer>

</body>
</html>
