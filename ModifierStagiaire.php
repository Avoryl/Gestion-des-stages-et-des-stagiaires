<?php
require_once 'config.php';
require_role('gestionnaire');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) redirect('ListeStagiairesValides.php');

$connection = db();

// Récupération des données actuelles
$stmt = $connection->prepare("
    SELECT s.*, st.titre as stage_titre, d.nom as dept_nom 
    FROM stagiaires s 
    JOIN stages st ON s.stage_id = st.id 
    LEFT JOIN departements d ON st.departement_id = d.id 
    WHERE s.id = ? AND s.status IN ('valide', 'en_attente')
");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) redirect('ListeStagiairesValides.php');

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $isNewValidation = ($s['status'] === 'en_attente');
        $newStatus = $isNewValidation ? 'valide' : $s['status'];

        $sql = "UPDATE stagiaires SET 
                nom = ?, prenom = ?, email = ?, telephone = ?, date_naissance = ?, adresse = ?, 
                formation = ?, specialite = ?, type_stage = ?, modalite = ?, duree_choisie = ?, motif = ?, status = ?,
                ecole_nom = ?, ecole_directeur = ?, ecole_adresse = ?, ecole_telephone = ?, ecole_email = ?,
                binome_nom = ?, binome_prenom = ?, binome_email = ?, binome_date_naissance = ?, 
                binome_telephone = ?, binome_adresse = ?, binome_formation = ?, binome_specialite = ?,
                date_debut = ?, date_fin = ?
                WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'], $_POST['date_naissance'], $_POST['adresse'],
            $_POST['formation'], $_POST['specialite'], $_POST['type_stage'], $_POST['modalite'] ?? null, $_POST['duree_choisie'], $_POST['motif'], $newStatus,
            $_POST['ecole_nom'] ?? null, $_POST['ecole_directeur'] ?? null, $_POST['ecole_adresse'] ?? null, 
            $_POST['ecole_telephone'] ?? null, $_POST['ecole_email'] ?? null,
            $_POST['binome_nom'] ?? null, $_POST['binome_prenom'] ?? null, $_POST['binome_email'] ?? null, $_POST['binome_date_naissance'] ?? null,
            $_POST['binome_telephone'] ?? null, $_POST['binome_adresse'] ?? null, $_POST['binome_formation'] ?? null, $_POST['binome_specialite'] ?? null,
            $_POST['date_debut'] ?: null, $_POST['date_fin'] ?: null,
            $id
        ]);

        if ($isNewValidation) {
            $connection->prepare("UPDATE stagiaires SET date_validation = NOW() WHERE id = ?")->execute([$id]);
            
            $extra = [
                'date_debut' => $_POST['date_debut'],
                'date_fin'   => $_POST['date_fin'],
                'duree'      => $_POST['duree_choisie'],
                'dept_nom'   => $s['dept_nom']
            ];
            envoyer_notification_stage($_POST['email'], $_POST['prenom'] . ' ' . $_POST['nom'], $s['stage_titre'], 'valide', $extra);
        }

        set_flash('success', 'Informations mises à jour avec succès.');
        redirect('DetailStagiaire.php?id=' . $id);
    } catch (PDOException $e) {
        set_flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
    }
}

include 'header.php';
?>
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1>Modifier le profil de <?= e($s['prenom']) ?></h1>
    </div>

    <form method="post" style="max-width: 900px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= e($s['nom']) ?>" required>
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= e($s['prenom']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($s['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= e($s['telephone']) ?>" required>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance" value="<?= e($s['date_naissance']) ?>" required>
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= e($s['adresse']) ?>" required>
            </div>
            <div class="form-group">
                <label>Formation / Établissement</label>
                <input type="text" name="formation" value="<?= e($s['formation']) ?>" required>
            </div>
            <div class="form-group">
                <label>Spécialité</label>
                <input type="text" name="specialite" value="<?= e($s['specialite']) ?>" required>
            </div>
            <div class="form-group">
                <label>Type de stage</label>
                <select name="type_stage" id="type_stage" onchange="toggleFields()" required>
                    <option value="professionnel" <?= $s['type_stage'] === 'professionnel' ? 'selected' : '' ?>>Professionnel</option>
                    <option value="academique" <?= $s['type_stage'] === 'academique' ? 'selected' : '' ?>>Académique</option>
                </select>
            </div>
            <div class="form-group" id="modalite_container" style="display: <?= $s['type_stage'] === 'academique' ? 'block' : 'none' ?>;">
                <label>Modalité</label>
                <select name="modalite" id="modalite" onchange="toggleFields()">
                    <option value="solo" <?= $s['modalite'] === 'solo' ? 'selected' : '' ?>>Solo</option>
                    <option value="binome" <?= $s['modalite'] === 'binome' ? 'selected' : '' ?>>Binôme</option>
                </select>
            </div>
            <div class="form-group">
                <label>Durée du stage</label>
                <select name="duree_choisie" id="duree_choisie" required onchange="updateEndDate()">
                    <option value="2 mois" <?= ($s['duree_choisie'] ?? '') === '2 mois' ? 'selected' : '' ?>>2 mois</option>
                    <option value="3 mois" <?= ($s['duree_choisie'] ?? '') === '3 mois' ? 'selected' : '' ?>>3 mois</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date de début de stage</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= e($s['date_debut']) ?>" style="border-color: var(--primary);" onchange="updateEndDate()">
            </div>
            <div class="form-group">
                <label>Date de fin de stage</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= e($s['date_fin']) ?>" style="border-color: var(--primary);">
            </div>
        </div>

        <div class="form-group">
            <label>Motif / Objectif du stage</label>
            <select name="motif" required>
                <option value="renforcement" <?= $s['motif'] === 'renforcement' ? 'selected' : '' ?>>Renforcement de capacité</option>
                <option value="memoire" <?= $s['motif'] === 'memoire' ? 'selected' : '' ?>>Rédaction de mémoire</option>
                <option value="rapport" <?= $s['motif'] === 'rapport' ? 'selected' : '' ?>>Rédaction de rapport de stage</option>
            </select>
        </div>

        <!-- Champs École -->
        <div id="school_fields" style="display: <?= $s['type_stage'] === 'academique' ? 'block' : 'none' ?>; background: #f0f9ff; padding: 1.5rem; border-radius: 12px; border: 1px solid #bae6fd; margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">Informations de l'établissement</h3>
            <div class="form-group">
                <label>Nom de l'école</label>
                <input type="text" name="ecole_nom" value="<?= e($s['ecole_nom']) ?>">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Directeur</label>
                    <input type="text" name="ecole_directeur" value="<?= e($s['ecole_directeur']) ?>">
                </div>
                <div class="form-group">
                    <label>Email École</label>
                    <input type="email" name="ecole_email" value="<?= e($s['ecole_email']) ?>">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Adresse École</label>
                    <input type="text" name="ecole_adresse" value="<?= e($s['ecole_adresse']) ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone École</label>
                    <input type="tel" name="ecole_telephone" value="<?= e($s['ecole_telephone']) ?>">
                </div>
            </div>
        </div>

        <!-- Champs Binôme -->
        <div id="binome_fields" style="display: <?= $s['modalite'] === 'binome' ? 'block' : 'none' ?>; background: #f8fafc; padding: 1.5rem; border-radius: 12px; border: 1px dashed var(--primary); margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">Informations du Binôme</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Nom du binôme</label>
                    <input type="text" name="binome_nom" value="<?= e($s['binome_nom']) ?>">
                </div>
                <div class="form-group">
                    <label>Prénom du binôme</label>
                    <input type="text" name="binome_prenom" value="<?= e($s['binome_prenom']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Email du binôme</label>
                <input type="email" name="binome_email" value="<?= e($s['binome_email']) ?>">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Date de naissance (Binôme)</label>
                    <input type="date" name="binome_date_naissance" value="<?= e($s['binome_date_naissance']) ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone (Binôme)</label>
                    <input type="tel" name="binome_telephone" value="<?= e($s['binome_telephone']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Adresse (Binôme)</label>
                <input type="text" name="binome_adresse" value="<?= e($s['binome_adresse']) ?>">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Formation (Binôme)</label>
                    <input type="text" name="binome_formation" value="<?= e($s['binome_formation']) ?>">
                </div>
                <div class="form-group">
                    <label>Spécialité (Binôme)</label>
                    <input type="text" name="binome_specialite" value="<?= e($s['binome_specialite']) ?>">
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-success" style="flex: 1;">Enregistrer les modifications</button>
            <a href="DetailStagiaire.php?id=<?= $id ?>" class="btn" style="background: #6b7280; color: #fff; flex: 1;">Annuler</a>
        </div>
    </form>

    <script>
    function updateEndDate() {
        const debut = document.getElementById('date_debut').value;
        const dureeText = document.getElementById('duree_choisie').value;
        const finInput = document.getElementById('date_fin');

        if (debut && dureeText) {
            const date = new Date(debut);
            const moisAAjouter = parseInt(dureeText); // Extrait 2 ou 3
            date.setMonth(date.getMonth() + moisAAjouter);
            
            // Format YYYY-MM-DD pour l'input date
            finInput.value = date.toISOString().split('T')[0];
        }
    }

    function toggleFields() {
        const typeStage = document.getElementById('type_stage').value;
        const modaliteContainer = document.getElementById('modalite_container');
        const modalite = document.getElementById('modalite').value;
        const binomeFields = document.getElementById('binome_fields');
        const schoolFields = document.getElementById('school_fields');

        if (typeStage === 'academique') {
            modaliteContainer.style.display = 'block';
            schoolFields.style.display = 'block';
            if (modalite === 'binome') {
                binomeFields.style.display = 'block';
            } else {
                binomeFields.style.display = 'none';
            }
        } else {
            modaliteContainer.style.display = 'none';
            binomeFields.style.display = 'none';
            schoolFields.style.display = 'none';
        }
    }
    </script>
</body>
</html>