<?php
 

require_once __DIR__ . '/includes/functions.php';

// Seul un admin connecté peut publier
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: publier.php');
    exit;
}

$pdo = getPDO();

 
$titre          = postStr('titre');
$lieu           = postStr('lieu');
$type_contrat   = postStr('type_contrat');
$teletravail    = isset($_POST['teletravail_possible']) ? (int)$_POST['teletravail_possible'] : 0;
$tele_frequence = postStr('teletravail_frequence');
$missions       = postStr('missions');
$competences    = postStr('competences_cles');
$profil         = postStr('profil_recherche');
$niveau_etude   = postStr('niveau_etude');
$experience     = postStr('experience');
$langues        = postStr('langues');
$temps_travail  = postStr('temps_travail', 'Temps plein');

$metier_id_raw     = postStr('metier_id');
$entreprise_id_raw = postStr('entreprise_id');

// Nouveaux champs (si l'admin crée une nouvelle entité)
$new_metier_nom       = postStr('new_metier_nom');
$new_entreprise_nom   = postStr('new_entreprise_nom');
$new_logo_url         = postStr('new_logo_url');
$new_site_web         = postStr('new_site_web');
$new_entreprise_desc  = postStr('new_entreprise_desc');

$errors = [];
if (empty($titre))        $errors[] = 'L\'intitulé du poste est obligatoire.';
if (empty($lieu))         $errors[] = 'Le lieu est obligatoire.';
if (!in_array($type_contrat, ['Stage', 'Alternance'])) $errors[] = 'Type de contrat invalide.';

if (!empty($errors)) {
    redirectWithMessage('publier.php', 'error', implode(' | ', $errors));
}

 
try {
    $pdo->beginTransaction();

    // --- 1. GESTION DU MÉTIER ---
    if ($metier_id_raw === 'new') {
        // Création d'un nouveau métier
        if (empty($new_metier_nom)) {
            throw new \RuntimeException('Le nom du nouveau métier est obligatoire.');
        }
        $stmtM = $pdo->prepare('INSERT INTO metiers (nom_metier) VALUES (:nom)');
        $stmtM->execute([':nom' => $new_metier_nom]);
        $metier_id = (int)$pdo->lastInsertId();
    } elseif ((int)$metier_id_raw > 0) {
        $metier_id = (int)$metier_id_raw;
    } else {
        $metier_id = null;  // Métier facultatif
    }

    // --- 2. GESTION DE L'ENTREPRISE ---
    if ($entreprise_id_raw === 'new') {
        // Création d'une nouvelle entreprise
        if (empty($new_entreprise_nom)) {
            throw new \RuntimeException('Le nom de la nouvelle entreprise est obligatoire.');
        }
        $stmtE = $pdo->prepare('
            INSERT INTO entreprises (nom, logo_url, site_web, description)
            VALUES (:nom, :logo, :site, :desc)
        ');
        $stmtE->execute([
            ':nom'  => $new_entreprise_nom,
            ':logo' => $new_logo_url   ?: null,
            ':site' => $new_site_web   ?: null,
            ':desc' => $new_entreprise_desc ?: null,
        ]);
        $entreprise_id = (int)$pdo->lastInsertId();
    } else {
        $entreprise_id = (int)$entreprise_id_raw;
        if ($entreprise_id <= 0) {
            throw new \RuntimeException('Veuillez sélectionner ou créer une entreprise.');
        }
    }

 
    $stmtO = $pdo->prepare('
        INSERT INTO offres (
            admin_id, entreprise_id, metier_id, titre, lieu,
            date_publication, type_contrat, temps_travail,
            teletravail_possible, teletravail_frequence, langues,
            experience, niveau_etude, missions, profil_recherche,
            competences_cles, is_active
        ) VALUES (
            :admin_id, :entreprise_id, :metier_id, :titre, :lieu,
            CURDATE(), :type_contrat, :temps_travail,
            :teletravail, :tele_frequence, :langues,
            :experience, :niveau_etude, :missions, :profil,
            :competences, 1
        )
    ');

    $stmtO->execute([
        ':admin_id'      => $_SESSION['admin_id'],
        ':entreprise_id' => $entreprise_id,
        ':metier_id'     => $metier_id,
        ':titre'         => $titre,
        ':lieu'          => $lieu,
        ':type_contrat'  => $type_contrat,
        ':temps_travail' => $temps_travail,
        ':teletravail'   => $teletravail,
        ':tele_frequence'=> $tele_frequence ?: null,
        ':langues'       => $langues ?: null,
        ':experience'    => $experience ?: null,
        ':niveau_etude'  => $niveau_etude ?: null,
        ':missions'      => $missions ?: null,
        ':profil'        => $profil ?: null,
        ':competences'   => $competences ?: null,
    ]);

    $offreId = (int)$pdo->lastInsertId();

    $pdo->commit();

    redirectWithMessage(
        'offre.php?id=' . $offreId,
        'success',
        'L\'offre "' . $titre . '" a été publiée avec succès !'
    );

} catch (\Exception $e) {
    $pdo->rollBack();
    error_log('[4Mation] save_offer error: ' . $e->getMessage());
    redirectWithMessage('publier.php', 'error', 'Erreur : ' . $e->getMessage());
}
