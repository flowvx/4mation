<?php
 

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: offres.php');
    exit;
}
 
$offreId  = (int)postStr('offre_id', '0');
$sexe     = postStr('sexe');
$nom      = postStr('nom');
$prenom   = postStr('prenom');
$email    = postStr('email');
$tel      = postStr('tel');
$lettreOp = postStr('lettre-option', 'upload'); // 'upload' | 'redigee'
$lettreRedigee = postStr('lettre-redigee', '');

$errors = [];

// Validation de l'offre
if ($offreId <= 0) {
    $errors[] = 'Offre introuvable.';
}

// Sexe
if (!in_array($sexe, ['homme', 'femme'])) {
    $errors[] = 'Veuillez sélectionner votre civilité.';
}

// Champs texte
if (empty($nom))    $errors[] = 'Le nom est obligatoire.';
if (empty($prenom)) $errors[] = 'Le prénom est obligatoire.';

// Validation email (côté serveur, ne pas se fier au HTML5)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'L\'adresse email n\'est pas valide.';
}

// Validation téléphone (format français : 0X XX XX XX XX ou +33...)
if (!preg_match('/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]*\d{2}){4}$/', $tel)) {
    $errors[] = 'Le numéro de téléphone n\'est pas valide.';
}

// Validation lettre
if ($lettreOp === 'redigee' && mb_strlen(trim($lettreRedigee)) < 50) {
    $errors[] = 'La lettre de motivation rédigée doit contenir au moins 50 caractères.';
}

// En cas d'erreurs de validation → retour avec message
if (!empty($errors)) {
    redirectWithMessage(
        'formulaire.php?offre_id=' . $offreId,
        'error',
        implode(' | ', $errors)
    );
}

 

 
$cvPath     = '';
$lettrePath = null;

try {
    if (empty($_FILES['cv']['name'])) {
        throw new \RuntimeException('Le CV est obligatoire.');
    }
    $cvPath = uploadPdf($_FILES['cv'], UPLOAD_CV_DIR);

   
    if ($lettreOp === 'upload') {
        if (!empty($_FILES['lettre']['name']) && $_FILES['lettre']['error'] !== UPLOAD_ERR_NO_FILE) {
            $lettrePath = uploadPdf($_FILES['lettre'], UPLOAD_LM_DIR);
        }
        
    }

} catch (\RuntimeException $e) {
    redirectWithMessage(
        'formulaire.php?offre_id=' . $offreId,
        'error',
        'Erreur de fichier : ' . $e->getMessage()
    );
}
 
//  INSERTION EN BASE DE DONNÉES ET CALCUL ATS
$pdo = getPDO();

// On vérifie que l'offre existe avant d'insérer
$checkOffre = $pdo->prepare('SELECT id FROM offres WHERE id = :id AND is_active = 1');
$checkOffre->execute([':id' => $offreId]);
if (!$checkOffre->fetch()) {
    redirectWithMessage('offres.php', 'error', 'Cette offre n\'est plus disponible.');
}

try {
    $pdo->beginTransaction();

    $insertStmt = $pdo->prepare('
        INSERT INTO candidatures (
            offre_id, sexe, nom, prenom, email, telephone,
            cv_path, lettre_option, lettre_path, lettre_redigee,
            score_ats, analyse_ia, statut
        ) VALUES (
            :offre_id, :sexe, :nom, :prenom, :email, :telephone,
            :cv_path, :lettre_option, :lettre_path, :lettre_redigee,
            0.00, NULL, \'nouveau\'
        )
    ');

    $insertStmt->execute([
        ':offre_id'      => $offreId,
        ':sexe'          => $sexe,
        ':nom'           => $nom,
        ':prenom'        => $prenom,
        ':email'         => $email,
        ':telephone'     => $tel,
        ':cv_path'       => $cvPath,
        ':lettre_option' => $lettreOp,
        ':lettre_path'   => $lettrePath,
        ':lettre_redigee'=> $lettreOp === 'redigee' ? $lettreRedigee : null,
    ]);

    $candidatureId = (int)$pdo->lastInsertId();
 
    //  MOTEUR ATS : Calcul du score de pertinence en fonction des compétences clés de l'offre
    $atsResult = calculerScoreAts(
        $offreId,
        $lettreOp === 'redigee' ? $lettreRedigee : '',
        basename($cvPath)
    );

    // Mise à jour du score et de l'analyse IA
    $updateAts = $pdo->prepare('
        UPDATE candidatures
        SET score_ats = :score, analyse_ia = :analyse
        WHERE id = :id
    ');
    $updateAts->execute([
        ':score'   => $atsResult['score'],
        ':analyse' => $atsResult['analyse'],
        ':id'      => $candidatureId,
    ]);

    $pdo->commit();

} catch (\Exception $e) {
    $pdo->rollBack();
    error_log('[4Mation] process_candidature error: ' . $e->getMessage());

    // Nettoyage des fichiers uploadés si l'insertion échoue
    if ($cvPath && file_exists(UPLOAD_CV_DIR . basename($cvPath))) {
        unlink(UPLOAD_CV_DIR . basename($cvPath));
    }
    if ($lettrePath && file_exists(UPLOAD_LM_DIR . basename($lettrePath))) {
        unlink(UPLOAD_LM_DIR . basename($lettrePath));
    }

    redirectWithMessage(
        'formulaire.php?offre_id=' . $offreId,
        'error',
        'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.'
    );
} 
//  CONFIRMATION : Redirection vers la page d'accueil avec message 
redirectWithMessage(
    'index.php',
    'success',
    'Votre candidature a bien été envoyée ! '
    . 'Score ATS : ' . number_format($atsResult['score'], 1) . '%'
);
