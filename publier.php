<?php
 

require_once __DIR__ . '/includes/functions.php';

requireAdmin();

$pdo = getPDO();

 
$entreprises = $pdo->query('SELECT id, nom FROM entreprises ORDER BY nom ASC')->fetchAll();
$metiers     = $pdo->query('SELECT id, nom_metier FROM metiers ORDER BY nom_metier ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une offre - 4Mation</title>
    <link rel="stylesheet" href="/css/formulaire.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/admin.css"> 
    <link rel="stylesheet" href="/css/publier.css"> 
</head>
<body>

<div class="header-container" style="background:var(--surface);border-bottom:1px solid #1e1e1e;">
    <h2 style="font-size:16px;font-weight:800;text-transform:uppercase;">Nouvelle Offre</h2>
    <nav class="nav-menu">
        <li><a href="candidatures.php">← Dashboard</a></li>
        <li><a href="index.php">Voir le site</a></li>
    </nav>
</div>

<main>
    <?php renderFlash(); ?>

    <form action="save_offer.php" method="POST">

 
        <section>
            <h2>Détails du Poste</h2>

            <label for="titre">Intitulé de l'offre *</label>
            <input type="text" id="titre" name="titre"
                   placeholder="ex: Stagiaire Cybersécurité" required>

            <label for="metier-select">Secteur / Métier</label>
            <select id="metier-select" name="metier_id"
                    onchange="toggleNewField('metier-select', 'new-metier-block')">
                <option value="">-- Choisir un métier (optionnel) --</option>
                <?php foreach ($metiers as $m): ?>
                    <option value="<?= (int)$m['id'] ?>"><?= h($m['nom_metier']) ?></option>
                <?php endforeach; ?>
                <option value="new">+ Ajouter un nouveau métier...</option>
            </select>

            <div id="new-metier-block" class="conditional-field">
                <label>Nom du nouveau métier</label>
                <input type="text" name="new_metier_nom"
                       placeholder="ex: Intelligence Artificielle">
            </div>
        </section>

 
        <section>
            <h2>Entreprise Partenaire</h2>

            <label for="entreprise-select">Sélectionner l'entreprise *</label>
            <select id="entreprise-select" name="entreprise_id" required
                    onchange="toggleNewField('entreprise-select', 'new-entreprise-block')">
                <option value="">-- Choisir une entreprise --</option>
                <?php foreach ($entreprises as $e): ?>
                    <option value="<?= (int)$e['id'] ?>"><?= h($e['nom']) ?></option>
                <?php endforeach; ?>
                <option value="new">+ Enregistrer une nouvelle entreprise...</option>
            </select>

            <div id="new-entreprise-block" class="conditional-field">
                <label>Nom de l'entreprise *</label>
                <input type="text" name="new_entreprise_nom"
                       placeholder="ex: POST Luxembourg">

                <div class="row-flex" style="margin-top:12px;">
                    <div style="flex:1">
                        <label>URL du Logo</label>
                        <input type="url" name="new_logo_url"
                               placeholder="https://logo.clearbit.com/example.com">
                    </div>
                    <div style="flex:1">
                        <label>Site Web</label>
                        <input type="url" name="new_site_web"
                               placeholder="https://www.exemple.com">
                    </div>
                </div>

                <label style="margin-top:12px;">Description de l'entreprise</label>
                <textarea name="new_entreprise_desc"
                          placeholder="Présentation de l'entreprise..."
                          style="min-height:80px;"></textarea>
            </div>
        </section>

 
        <section>
            <h2>Conditions de Travail</h2>

            <div class="row-flex">
                <div style="flex:1">
                    <label for="type_contrat">Type de contrat *</label>
                    <select id="type_contrat" name="type_contrat" required>
                        <option value="Stage">Stage</option>
                        <option value="Alternance">Alternance</option>
                    </select>
                </div>
                <div style="flex:1">
                    <label for="lieu">Lieu *</label>
                    <input type="text" id="lieu" name="lieu"
                           placeholder="ex: Nancy, France" required>
                </div>
            </div>

            <div class="row-flex" style="margin-top:4px;">
                <div style="flex:1">
                    <label>Temps de travail</label>
                    <select name="temps_travail">
                        <option value="Temps plein">Temps plein</option>
                        <option value="Temps partiel">Temps partiel</option>
                    </select>
                </div>
                <div style="flex:1">
                    <label>Niveau d'étude</label>
                    <input type="text" name="niveau_etude"
                           placeholder="ex: Bac+2, Bac+3, Master...">
                </div>
            </div>

            <div class="row-flex" style="margin-top:4px;">
                <div style="flex:1">
                    <label>Expérience requise</label>
                    <input type="text" name="experience"
                           placeholder="ex: Débutant, 1 an...">
                </div>
                <div style="flex:1">
                    <label>Langues</label>
                    <input type="text" name="langues"
                           placeholder="ex: Français, Anglais (B2)">
                </div>
            </div>

            <label>Télétravail possible ?</label>
            <div class="row-flex">
                <label>
                    <input type="radio" name="teletravail_possible" value="1"
                           onclick="document.getElementById('freq-block').style.display='block'">
                    Oui
                </label>
                <label>
                    <input type="radio" name="teletravail_possible" value="0" checked
                           onclick="document.getElementById('freq-block').style.display='none'">
                    Non
                </label>
            </div>

            <div id="freq-block" style="display:none; margin-top:8px;">
                <label>Fréquence du télétravail</label>
                <input type="text" name="teletravail_frequence"
                       placeholder="ex: 2 jours/semaine ou 100% remote">
            </div>
        </section>

 
        <section>
            <h2>Contenu de l'Offre</h2>

            <label>
                Compétences clés pour l'ATS
                <small style="color:var(--text-dim)">(mots-clés séparés par virgules)</small>
            </label>
            <textarea name="competences_cles"
                      placeholder="Python, Cisco, CCNA, Intelligence Artificielle, Anglais B2, LLM"
                      style="min-height: 80px;"></textarea>

            <label>Description des Missions</label>
            <textarea name="missions"
                      placeholder="Décrivez les tâches et responsabilités du candidat..."
                      style="min-height: 150px;"></textarea>

            <label>Profil Recherché</label>
            <textarea name="profil_recherche"
                      placeholder="Décrivez le profil idéal (formation, qualités, etc.)..."
                      style="min-height: 120px;"></textarea>
        </section>

        <section>
            <button type="submit"> Publier l'offre maintenant</button>
        </section>
    </form>
</main>

<script>
function toggleNewField(selectId, blockId) {
    const select = document.getElementById(selectId);
    const block  = document.getElementById(blockId);
    block.style.display = select.value === 'new' ? 'block' : 'none';
}
</script>

</body>
</html>
