<?php
require_once 'config.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) redirect('ListeStagiairesValides.php');

$connection = db();
$stmt = $connection->prepare("
    SELECT s.*, st.titre AS stage_titre, st.description AS stage_desc, st.duree AS stage_duree,
           d.nom AS dept_nom, d.sigle AS dept_sigle
    FROM stagiaires s
    JOIN stages st ON s.stage_id = st.id
    LEFT JOIN departements d ON st.departement_id = d.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) redirect('Historique.php');

// Suppression depuis le profil
if (isset($_POST['delete_stagiaire'])) {
    try {
        $stmt = $connection->prepare("UPDATE stagiaires SET status = 'termine', date_fin = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Le stage a été marqué comme terminé.');
        redirect('ListeStagiairesValides.php');
    } catch (PDOException $e) {
        set_flash('error', 'Erreur lors de la suppression.');
    }
}

include 'header.php';
?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 10px;">
        <h1>Profil de <?= e($s['prenom']) ?> <?= e($s['nom']) ?></h1>
        <div style="display: flex; gap: 10px;">
            <?php if ($s['status'] === 'valide'): ?>
                <a href="ModifierStagiaire.php?id=<?= $s['id'] ?>" class="btn btn-primary">Modifier</a>
                <form method="post" onsubmit="return confirm('Marquer ce stage comme terminé ?');" style="margin: 0;">
                    <button type="submit" name="delete_stagiaire" class="btn btn-danger">Terminer le stage</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Informations Personnelles -->
        <div class="card" style="text-align: left;">
            <h3 style="color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 10px; margin-bottom: 1.5rem;">📍 Coordonnées & État Civil</h3>
            <div class="form-group">
                <label>Nom complet</label>
                <p><?= e($s['nom']) ?> <?= e($s['prenom']) ?></p>
            </div>
            <div class="form-group">
                <label>Email</label>
                <p><?= e($s['email']) ?></p>
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <p><?= e($s['telephone']) ?></p>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <p><?= date('d/m/Y', strtotime($s['date_naissance'])) ?></p>
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <p><?= e($s['adresse']) ?></p>
            </div>
        </div>

        <!-- Cursus et Documents -->
        <div class="card" style="text-align: left;">
            <h3 style="color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 10px; margin-bottom: 1.5rem;">🎓 Formation & Documents</h3>
            <div class="form-group">
                <label>Établissement / Formation</label>
                <p><?= e($s['formation']) ?></p>
            </div>
            <div class="form-group">
                <label>Spécialité</label>
                <p><?= e($s['specialite']) ?></p>
            </div>
            <div class="form-group" style="margin-top: 2rem;">
                <label>Pièces jointes</label>
                <div style="display: flex; gap: 10px; margin-top: 0.5rem;">
                    <a href="download.php?file=<?= basename($s['cv']) ?>" class="btn btn-primary" style="padding: 0.5rem 1rem;">Voir le CV</a>
                    <a href="download.php?file=<?= basename($s['lm']) ?>" class="btn btn-primary" style="padding: 0.5rem 1rem;">Voir la LM</a>
                    <?php if ($s['attestation_diplome']): ?>
                        <a href="download.php?file=<?= basename($s['attestation_diplome']) ?>" class="btn btn-success" style="padding: 0.5rem 1rem;">Attestation</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informations École (Si Académique) -->
        <?php if ($s['type_stage'] === 'academique'): ?>
        <div class="card" style="text-align: left; grid-column: span 2; border: 1px solid #bae6fd; background: #f0f9ff;">
            <h3 style="color: var(--primary); border-bottom: 2px solid #bae6fd; padding-bottom: 10px; margin-bottom: 1.5rem;">🏢 Établissement de Formation</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <div class="form-group">
                    <label>École</label>
                    <p><strong><?= e($s['ecole_nom']) ?></strong></p>
                </div>
                <div class="form-group">
                    <label>Directeur</label>
                    <p><?= e($s['ecole_directeur']) ?></p>
                </div>
                <div class="form-group">
                    <label>Email École</label>
                    <p><?= e($s['ecole_email']) ?></p>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Adresse École</label>
                    <p><?= e($s['ecole_adresse']) ?></p>
                </div>
                <div class="form-group">
                    <label>Téléphone École</label>
                    <p><?= e($s['ecole_telephone']) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Détails du Stage -->
        <div class="card" style="text-align: left; grid-column: span 2;">
            <h3 style="color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 10px; margin-bottom: 1.5rem;">💼 Détails de l'affectation</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <div class="form-group">
                    <label>Stage</label>
                    <p><strong><?= e($s['stage_titre']) ?></strong></p>
                </div>
                <div class="form-group">
                    <label>Département</label>
                    <p><?= e($s['dept_nom']) ?> (<?= e($s['dept_sigle']) ?>)</p>
                </div>
                <div class="form-group">
                    <label>Durée choisie</label>
                    <p><strong><?= e($s['duree_choisie'] ?? $s['stage_duree']) ?></strong></p>
                </div>
                <div class="form-group">
                    <label>Type de stage</label>
                    <p style="text-transform: capitalize;"><?= e($s['type_stage']) ?></p>
                </div>
                <div class="form-group">
                    <label>Motif / Objectif</label>
                    <p><?= e($s['motif']) ?></p>
                </div>
                <div class="form-group">
                    <label>Date de validation</label>
                    <p><?= date('d/m/Y H:i', strtotime($s['date_validation'])) ?></p>
                </div>
                <div class="form-group">
                    <label>Période de stage</label>
                    <p>
                        Du : <strong><?= $s['date_debut'] ? date('d/m/Y', strtotime($s['date_debut'])) : 'Non définie' ?></strong><br>
                        Au : <strong><?= $s['date_fin'] ? date('d/m/Y', strtotime($s['date_fin'])) : 'Non définie' ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Informations Binôme (Si applicable) -->
        <?php if ($s['modalite'] === 'binome'): ?>
        <div class="card" style="text-align: left; grid-column: span 2; border: 2px dashed var(--primary);">
            <h3 style="color: var(--primary); margin-bottom: 1.5rem;">👥 Informations du Binôme</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <div class="form-group">
                    <label>Nom du binôme</label>
                    <p><?= e($s['binome_nom']) ?> <?= e($s['binome_prenom']) ?></p>
                </div>
                <div class="form-group">
                    <label>Email du binôme</label>
                    <p><?= e($s['binome_email']) ?></p>
                </div>
                <div class="form-group">
                    <label>Téléphone du binôme</label>
                    <p><?= e($s['binome_telephone']) ?></p>
                </div>
                <div class="form-group">
                    <label>Date de naissance (Binôme)</label>
                    <p><?= $s['binome_date_naissance'] ? date('d/m/Y', strtotime($s['binome_date_naissance'])) : 'N/A' ?></p>
                </div>
                <div class="form-group">
                    <label>Adresse (Binôme)</label>
                    <p><?= e($s['binome_adresse']) ?></p>
                </div>
                <div class="form-group">
                    <label>Formation (Binôme)</label>
                    <p><?= e($s['binome_formation']) ?></p>
                </div>
                <div class="form-group">
                    <label>Spécialité (Binôme)</label>
                    <p><?= e($s['binome_specialite']) ?></p>
                </div>
                <div class="form-group">
                    <label>Documents binôme</label>
                    <div style="display: flex; gap: 10px;">
                        <a href="download.php?file=<?= basename($s['binome_cv']) ?>" style="color: var(--primary); font-weight: 600;">CV</a> | 
                        <a href="download.php?file=<?= basename($s['binome_lm']) ?>" style="color: var(--primary); font-weight: 600;">LM</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>