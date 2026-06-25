<?php
require_once 'config.php';

function upload_pdf_file(array $file, string $prefix, string $uploadDir): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'Fichier invalide.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Le téléversement du fichier a échoué.'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return [false, 'Le fichier est trop volumineux (maximum 5 Mo).'];
    }

    if (empty($file['name']) || !is_uploaded_file($file['tmp_name'])) {
        return [false, 'Aucun fichier valide reçu.'];
    }

    $originalName = (string) $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($extension !== 'pdf') {
        return [false, 'Seuls les fichiers PDF sont autorisés.'];
    }

    $detectedMime = null;

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $detectedMime = mime_content_type($file['tmp_name']);
    }

    if ($detectedMime !== null) {
        $allowedMimes = [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'applications/vnd.pdf',
            'text/pdf',
            'text/x-pdf',
        ];

        if (!in_array($detectedMime, $allowedMimes, true)) {
            return [false, 'Le fichier doit être un PDF valide.'];
        }
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        return [false, 'Impossible de créer le dossier de stockage.'];
    }

    try {
        $uniquePart = bin2hex(random_bytes(8));
    } catch (Exception $e) {
        $uniquePart = uniqid('', true);
    }

    $safeName = $prefix . '_' . date('YmdHis') . '_' . str_replace('.', '', (string) $uniquePart) . '.pdf';
    $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [false, 'Impossible d\'enregistrer le fichier envoyé.'];
    }

    return [true, $safeName];
}

$stageId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stage = null;
$errorMessage = null;

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

if ($stageId) {
    try {
        $stmt = db()->prepare("SELECT id, titre, description, duree FROM stages WHERE id = ?");
        $stmt->execute([$stageId]);
        $stage = $stmt->fetch();
    } catch (PDOException $e) {
        $errorMessage = 'Impossible de charger les informations du stage.';
    }
}

if (!$stage && $errorMessage === null) {
    $errorMessage = 'Le stage demandé est introuvable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $stage) {
    $nom = trim((string) ($_POST['nom'] ?? ''));
    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $date_naissance = $_POST['date_naissance'] ?? '';
    $adresse = trim((string) ($_POST['adresse'] ?? ''));
    $formation = trim((string) ($_POST['formation'] ?? ''));
    $specialite = trim((string) ($_POST['specialite'] ?? ''));
    $telephone = trim((string) ($_POST['telephone'] ?? ''));
    $type_stage = $_POST['type_stage'] ?? '';
    $modalite = $_POST['modalite'] ?? null;
    
    $ecole_nom = trim((string) ($_POST['ecole_nom'] ?? ''));
    $ecole_directeur = trim((string) ($_POST['ecole_directeur'] ?? ''));
    $ecole_adresse = trim((string) ($_POST['ecole_adresse'] ?? ''));
    $ecole_telephone = trim((string) ($_POST['ecole_telephone'] ?? ''));
    $ecole_email = strtolower(trim((string) ($_POST['ecole_email'] ?? '')));

    $duree_choisie = $_POST['duree_choisie'] ?? '';
    $motif = $_POST['motif'] ?? '';
    
    // Infos binôme
    $binome_nom = trim((string) ($_POST['binome_nom'] ?? ''));
    $binome_prenom = trim((string) ($_POST['binome_prenom'] ?? ''));
    $binome_email = strtolower(trim((string) ($_POST['binome_email'] ?? '')));
    $binome_date_naissance = $_POST['binome_date_naissance'] ?? '';
    $binome_telephone = trim((string) ($_POST['binome_telephone'] ?? ''));
    $binome_adresse = trim((string) ($_POST['binome_adresse'] ?? ''));
    $binome_formation = trim((string) ($_POST['binome_formation'] ?? ''));
    $binome_specialite = trim((string) ($_POST['binome_specialite'] ?? ''));
    $binome_cvResult = $binome_lmResult = $attResult = null;

    if ($nom === '' || $prenom === '' || $email === '' || $type_stage === '' || $motif === '' || $duree_choisie === '' ||
        $date_naissance === '' || $adresse === '' || $formation === '' || $specialite === '' || $telephone === '') {
        $errorMessage = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Veuillez saisir une adresse email valide.';
    } elseif (strtotime($date_naissance) > time()) {
        $errorMessage = 'La date de naissance ne peut pas être dans le futur.';
    } elseif (!isset($_FILES['cv'], $_FILES['lm'])) {
        $errorMessage = 'Veuillez joindre votre CV et votre lettre de motivation.';
    } elseif ($type_stage === 'professionnel' && (!isset($_FILES['attestation_diplome']) || $_FILES['attestation_diplome']['error'] === UPLOAD_ERR_NO_FILE)) {
        $errorMessage = 'Veuillez joindre votre attestation de diplôme.';
    } elseif ($type_stage === 'academique' && ($ecole_nom === '' || $ecole_directeur === '' || $ecole_adresse === '' || $ecole_telephone === '' || $ecole_email === '')) {
        $errorMessage = 'Veuillez remplir toutes les informations relatives à votre école.';
    } elseif ($type_stage === 'academique' && empty($modalite)) {
        $errorMessage = 'Veuillez choisir une modalité (Solo ou Binôme) pour un stage académique.';
    } elseif ($modalite === 'binome' && ($binome_nom === '' || $binome_prenom === '' || $binome_email === '' || 
              $binome_date_naissance === '' || $binome_telephone === '' || $binome_adresse === '' || $binome_formation === '' || $binome_specialite === '')) {
        $errorMessage = 'Veuillez remplir toutes les informations personnelles de votre binôme.';
    } elseif ($modalite === 'binome' && (!isset($_FILES['binome_cv'], $_FILES['binome_lm']))) {
        $errorMessage = 'Veuillez joindre le CV et la lettre de motivation de votre binôme.';
    } else {
        try {
            $check = db()->prepare("SELECT id FROM stagiaires WHERE email = ? AND stage_id = ? AND status IN ('en_attente', 'valide') LIMIT 1");
            $check->execute([$email, (int) $stage['id']]);
            $existing = $check->fetch();

            if ($existing) {
                $errorMessage = 'Une candidature active existe déjà pour cet email sur ce stage.';
            } else {
                $cvResult = null;
                $lmResult = null;

                // Préparation d'une version propre du nom pour le fichier (minuscules, pas d'espaces ni caractères spéciaux)
                $nameClean = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', strtolower($nom . '_' . $prenom)));

                // Upload main applicant's CV
                [$cvOk, $cvResult] = upload_pdf_file($_FILES['cv'], 'cv_' . $nameClean, $uploadDir);
                if (!$cvOk) {
                    $errorMessage = 'CV : ' . $cvResult;
                } else {
                    // Upload main applicant's LM
                    [$lmOk, $lmResult] = upload_pdf_file($_FILES['lm'], 'lm_' . $nameClean, $uploadDir);
                    if (!$lmOk) {
                        $errorMessage = 'Lettre de motivation : ' . $lmResult;
                        @unlink($uploadDir . DIRECTORY_SEPARATOR . $cvResult); // Clean up CV if LM fails
                    } else {
                        // Si stage pro, on télécharge l'attestation
                        if ($type_stage === 'professionnel') {
                            [$attOk, $attResult] = upload_pdf_file($_FILES['attestation_diplome'], 'attestation_' . $nameClean, $uploadDir);
                            if (!$attOk) {
                                $errorMessage = 'Attestation : ' . $attResult;
                                @unlink($uploadDir . DIRECTORY_SEPARATOR . $cvResult);
                                @unlink($uploadDir . DIRECTORY_SEPARATOR . $lmResult);
                            }
                        }

                        // If binome, upload binome's files
                        if ($errorMessage === null && $modalite === 'binome') {
                            $binomeClean = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', strtolower($binome_nom . '_' . $binome_prenom)));

                            [$bCvOk, $binome_cvResult] = upload_pdf_file($_FILES['binome_cv'], 'binome_cv_' . $binomeClean, $uploadDir);
                            if (!$bCvOk) {
                                $errorMessage = 'CV Binôme : ' . $binome_cvResult;
                                @unlink($uploadDir . DIRECTORY_SEPARATOR . $cvResult);
                                @unlink($uploadDir . DIRECTORY_SEPARATOR . $lmResult);
                                if ($attResult) @unlink($uploadDir . DIRECTORY_SEPARATOR . $attResult);
                            } else {
                                [$bLmOk, $binome_lmResult] = upload_pdf_file($_FILES['binome_lm'], 'binome_lm_' . $binomeClean, $uploadDir);
                                if (!$bLmOk) {
                                    $errorMessage = 'LM Binôme : ' . $binome_lmResult;
                                    @unlink($uploadDir . DIRECTORY_SEPARATOR . $cvResult);
                                    @unlink($uploadDir . DIRECTORY_SEPARATOR . $lmResult);
                                    if ($attResult) @unlink($uploadDir . DIRECTORY_SEPARATOR . $attResult);
                                    @unlink($uploadDir . DIRECTORY_SEPARATOR . $binome_cvResult);
                                }
                            }
                        }

                        // If no errors after all uploads, proceed to DB insertion
                        if ($errorMessage === null) {
                            try {
                                $insert = db()->prepare("INSERT INTO stagiaires (nom, prenom, email, date_naissance, adresse, formation, specialite, telephone, cv, lm, attestation_diplome, stage_id, duree_choisie, type_stage, modalite, ecole_nom, ecole_directeur, ecole_adresse, ecole_telephone, ecole_email, motif, binome_nom, binome_prenom, binome_email, binome_date_naissance, binome_telephone, binome_adresse, binome_formation, binome_specialite, binome_cv, binome_lm, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
                                $insert->execute([
                                    $nom, $prenom, $email, $date_naissance, $adresse, $formation, $specialite, $telephone,
                                    $cvResult, $lmResult, $attResult, (int) $stage['id'],
                                    $duree_choisie, $type_stage, $modalite,
                                    $ecole_nom, $ecole_directeur, $ecole_adresse, $ecole_telephone, $ecole_email,
                                    $motif,
                                    $binome_nom, $binome_prenom, $binome_email, $binome_date_naissance, $binome_telephone, $binome_adresse, $binome_formation, $binome_specialite,
                                    $binome_cvResult, $binome_lmResult
                                ]);

                                $lastId = db()->lastInsertId();
                                $downloadBtn = '<br><br><a href="generer_recepisse.php?id='.$lastId.'" target="_blank" class="btn btn-primary" style="background:#fff; color:var(--primary);">📥 Télécharger mon récépissé (PDF)</a>';
                                set_flash('success', 'Votre candidature a été enregistrée avec succès.' . $downloadBtn);
                                redirect('postuler.php?id=' . (int) $stage['id']);
                            } catch (PDOException $e) {
                                // Clean up all uploaded files if DB insertion fails
                                $errorMessage = 'Une erreur est survenue lors de l\'enregistrement de votre candidature.';
                                foreach ([$cvResult, $lmResult, $attResult, $binome_cvResult, $binome_lmResult] as $fileToDelete) {
                                    if ($fileToDelete && is_file($uploadDir . DIRECTORY_SEPARATOR . $fileToDelete)) {
                                        @unlink($uploadDir . DIRECTORY_SEPARATOR . $fileToDelete);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $errorMessage = 'Impossible de traiter votre demande pour le moment.';
        }
    }
}
?>
<?php include 'header.php'; ?>
    <div style="text-align: center; margin-bottom: 2rem;">
        <img src="logo.png" alt="Logo" style="width: 180px; height: auto; margin-bottom: 1rem; border-radius: 20px; box-shadow: var(--shadow);">
        <h1>Candidature à un stage</h1>
    </div>
    
    <div class="card">
        <?php if ($stage): ?>
            <div class="stage-box" style="margin-bottom: 2rem;">
                <h2><?= e($stage['titre']) ?></h2>
                <p><?= nl2br(e($stage['description'])) ?></p>
                <div style="color: var(--text-muted); font-weight: 600; margin-top: 1rem;">Durée : <?= e($stage['duree']) ?></div>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= e($errorMessage) ?></div>
            <?php endif; ?>

            <form action="postuler.php?id=<?= (int) $stage['id'] ?>" method="post" enctype="multipart/form-data">
                <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" maxlength="100" required value="<?= e($_POST['nom'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" maxlength="100" required value="<?= e($_POST['prenom'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" maxlength="150" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" required value="<?= e($_POST['date_naissance'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" maxlength="20" required value="<?= e($_POST['telephone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" maxlength="255" required value="<?= e($_POST['adresse'] ?? '') ?>">
                </div>

                <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="formation">Formation</label>
                        <input type="text" id="formation" name="formation" maxlength="150" required value="<?= e($_POST['formation'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <input type="text" id="specialite" name="specialite" maxlength="150" required value="<?= e($_POST['specialite'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="type_stage">Type de stage</label>
                        <select id="type_stage" name="type_stage" required onchange="toggleModalite()">
                            <option value="">Sélectionnez...</option>
                            <option value="professionnel" <?= ($_POST['type_stage'] ?? '') === 'professionnel' ? 'selected' : '' ?>>Stage Professionnel</option>
                            <option value="academique" <?= ($_POST['type_stage'] ?? '') === 'academique' ? 'selected' : '' ?>>Stage Académique</option>
                        </select>
                    </div>

                    <div class="form-group" id="modalite_container" style="display: <?= ($_POST['type_stage'] ?? '') === 'academique' ? 'block' : 'none' ?>;">
                        <label for="modalite">Modalité</label>
                        <select id="modalite" name="modalite" onchange="toggleBinome()">
                            <option value="">Sélectionnez...</option>
                            <option value="solo" <?= ($_POST['modalite'] ?? '') === 'solo' ? 'selected' : '' ?>>Solo</option>
                            <option value="binome" <?= ($_POST['modalite'] ?? '') === 'binome' ? 'selected' : '' ?>>Binôme</option>
                        </select>
                    </div>
                </div>

                <!-- Champs spécifiques à l'école pour stage académique -->
                <div id="school_fields" style="display: <?= ($_POST['type_stage'] ?? '') === 'academique' ? 'block' : 'none' ?>; background: #f0f9ff; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #bae6fd;">
                    <h3 style="margin-top: 0; font-size: 1.1rem; color: var(--primary);">Informations de l'établissement</h3>
                    <div class="form-group">
                        <label for="ecole_nom">Nom de l'école de formation</label>
                        <input type="text" id="ecole_nom" name="ecole_nom" value="<?= e($_POST['ecole_nom'] ?? '') ?>">
                    </div>
                    <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="ecole_directeur">Directeur d'école</label>
                            <input type="text" id="ecole_directeur" name="ecole_directeur" value="<?= e($_POST['ecole_directeur'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ecole_email">Email de l'école</label>
                            <input type="email" id="ecole_email" name="ecole_email" value="<?= e($_POST['ecole_email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="ecole_adresse">Adresse de l'école</label>
                            <input type="text" id="ecole_adresse" name="ecole_adresse" value="<?= e($_POST['ecole_adresse'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ecole_telephone">Téléphone de l'école</label>
                            <input type="tel" id="ecole_telephone" name="ecole_telephone" value="<?= e($_POST['ecole_telephone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Champs spécifiques au Binôme -->
                <div id="binome_fields" style="display: <?= ($_POST['modalite'] ?? '') === 'binome' ? 'block' : 'none' ?>; background: #f9fafb; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px dashed #d1d5db;">
                    <h3 style="margin-top: 0; font-size: 1.1rem; color: var(--primary);">Informations du Binôme</h3>
                    <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="binome_nom">Nom du binôme</label>
                            <input type="text" id="binome_nom" name="binome_nom" value="<?= e($_POST['binome_nom'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="binome_prenom">Prénom du binôme</label>
                            <input type="text" id="binome_prenom" name="binome_prenom" value="<?= e($_POST['binome_prenom'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="binome_email">Email du binôme</label>
                        <input type="email" id="binome_email" name="binome_email" value="<?= e($_POST['binome_email'] ?? '') ?>">
                    </div>
                    <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="binome_date_naissance">Date de naissance (Binôme)</label>
                            <input type="date" id="binome_date_naissance" name="binome_date_naissance" value="<?= e($_POST['binome_date_naissance'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="binome_telephone">Téléphone (Binôme)</label>
                            <input type="tel" id="binome_telephone" name="binome_telephone" value="<?= e($_POST['binome_telephone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="binome_adresse">Adresse (Binôme)</label>
                        <input type="text" id="binome_adresse" name="binome_adresse" value="<?= e($_POST['binome_adresse'] ?? '') ?>">
                    </div>
                    <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="binome_formation">Formation (Binôme)</label>
                            <input type="text" id="binome_formation" name="binome_formation" value="<?= e($_POST['binome_formation'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="binome_specialite">Spécialité (Binôme)</label>
                            <input type="text" id="binome_specialite" name="binome_specialite" value="<?= e($_POST['binome_specialite'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <label for="binome_cv">CV du binôme</label>
                            <input type="file" id="binome_cv" name="binome_cv" accept="application/pdf,.pdf">
                        </div>
                        <div>
                            <label for="binome_lm">LM du binôme</label>
                            <input type="file" id="binome_lm" name="binome_lm" accept="application/pdf,.pdf">
                        </div>
                    </div>
                </div>

                <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="duree_choisie">Durée du stage</label>
                        <select id="duree_choisie" name="duree_choisie" required>
                            <option value="">Sélectionnez...</option>
                            <option value="2 mois" <?= ($_POST['duree_choisie'] ?? '') === '2 mois' ? 'selected' : '' ?>>2 mois</option>
                            <option value="3 mois" <?= ($_POST['duree_choisie'] ?? '') === '3 mois' ? 'selected' : '' ?>>3 mois</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="motif">Motif de stage</label>
                        <select id="motif" name="motif" required>
                            <option value="">Sélectionnez...</option>
                            <option value="renforcement" <?= ($_POST['motif'] ?? '') === 'renforcement' ? 'selected' : '' ?>>Renforcement de capacité</option>
                            <option value="memoire" <?= ($_POST['motif'] ?? '') === 'memoire' ? 'selected' : '' ?>>Rédaction de mémoire</option>
                            <option value="rapport" <?= ($_POST['motif'] ?? '') === 'rapport' ? 'selected' : '' ?>>Rédaction de rapport de stage</option>
                        </select>
                    </div>
                </div>

                <div class="form-group grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="cv">CV (PDF uniquement)</label>
                        <input type="file" id="cv" name="cv" accept="application/pdf,.pdf" required>
                        <small style="color: var(--text-muted);">Formats acceptés : PDF uniquement.</small>
                    </div>

                    <div>
                        <label for="lm">Lettre de motivation (PDF uniquement)</label>
                        <input type="file" id="lm" name="lm" accept="application/pdf,.pdf" required>
                        <small style="color: var(--text-muted);">Formats acceptés : PDF uniquement.</small>
                    </div>
                </div>

                <div id="attestation_container" style="display: <?= ($_POST['type_stage'] ?? '') === 'professionnel' ? 'block' : 'none' ?>; margin-bottom: 1.5rem;">
                    <label for="attestation_diplome">Attestation de diplôme (PDF uniquement)</label>
                    <input type="file" id="attestation_diplome" name="attestation_diplome" accept="application/pdf,.pdf">
                    <small style="color: var(--text-muted);">Obligatoire pour un stage professionnel.</small>
                </div>

                <div style="margin-top: 1.5rem;">
                    <a href="ListStage.php" class="btn btn-primary" style="margin-right: 1rem;">Retour</a>
                    <button type="submit" class="btn btn-success">Envoyer ma candidature</button>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <h2>Stage indisponible</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem;"><?= e($errorMessage ?? 'Le stage demandé est introuvable.') ?></p>
                <a href="ListStage.php" class="btn btn-primary">Voir les stages disponibles</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function toggleModalite() {
        const typeSelect = document.getElementById('type_stage');
        const modaliteContainer = document.getElementById('modalite_container');
        const modaliteSelect = document.getElementById('modalite');
        const attestationContainer = document.getElementById('attestation_container');
        const attestationInput = document.getElementById('attestation_diplome');
        const schoolFields = document.getElementById('school_fields');
        const schoolInputs = schoolFields.querySelectorAll('input');
        
        if (typeSelect.value === 'academique') {
            modaliteContainer.style.display = 'block';
            modaliteSelect.required = true;
            schoolFields.style.display = 'block';
            schoolInputs.forEach(input => input.required = true);
            attestationContainer.style.display = 'none';
            attestationInput.required = false;
        } else {
            modaliteContainer.style.display = 'none';
            modaliteSelect.required = false;
            modaliteSelect.value = '';
            schoolFields.style.display = 'none';
            schoolInputs.forEach(input => input.required = false);
            if (typeSelect.value === 'professionnel') {
                attestationContainer.style.display = 'block';
                attestationInput.required = true;
            } else {
                attestationContainer.style.display = 'none';
                attestationInput.required = false;
            }
            toggleBinome(); // Masquer aussi les champs binôme
        }
    }

    function toggleBinome() {
        const modaliteSelect = document.getElementById('modalite');
        const binomeFields = document.getElementById('binome_fields');
        const binomeInputs = binomeFields.querySelectorAll('input');
        
        if (modaliteSelect.value === 'binome') {
            binomeFields.style.display = 'block';
            binomeInputs.forEach(input => {
                if (input.name.includes('binome')) input.required = true;
            });
        } else {
            binomeFields.style.display = 'none';
            binomeInputs.forEach(input => {
                input.required = false;
                if (input.type !== 'file') input.value = '';
            });
        }
    }
    </script>
</body>
</html>
