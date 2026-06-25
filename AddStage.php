<?php
require_once 'config.php';

require_role('gestionnaire');
try {
    $connection = db();

    $depts = $connection->query("SELECT * FROM departements ORDER BY nom ASC")->fetchAll();

    // Vérifier si un stage doit être supprimé
    if (isset($_GET['delete'])) {
        $stageId = $_GET['delete'];
        $query = $connection->prepare("DELETE FROM stages WHERE id = :id");
        $query->bindParam(':id', $stageId, PDO::PARAM_INT);
        $query->execute();

        set_flash('success', 'Stage supprimé avec succès !');
        redirect('AddStage.php');
    }

    // Récupérer le stage à modifier
    $editStage = null;
    if (isset($_GET['edit'])) {
        $stmt = $connection->prepare("SELECT * FROM stages WHERE id = ?");
        $stmt->execute([(int)$_GET['edit']]);
        $editStage = $stmt->fetch();
    }

    // Affichage des stages
    $query = $connection->prepare("SELECT s.*, d.nom as dept_nom, d.sigle as dept_sigle FROM stages s LEFT JOIN departements d ON s.departement_id = d.id");
    $query->execute();
    $stages = $query->fetchAll(PDO::FETCH_ASSOC);

    // Traitement du formulaire d'ajout de stage
    if (isset($_POST['save'])) {
        $query = $connection->prepare("INSERT INTO stages(titre, description, duree, departement_id) VALUES (:titre, :description, :duree, :dept_id)");
        $query->bindParam(':titre', $_POST['title']);
        $query->bindParam(':description', $_POST['description']);
        $query->bindParam(':duree', $_POST['durée']);
        $query->bindParam(':dept_id', $_POST['departement_id']);
        $query->execute();

        set_flash('success', 'Stage ajouté avec succès.');
        redirect('AddStage.php');
    }

    // Traitement de la modification
    if (isset($_POST['update'])) {
        $query = $connection->prepare("UPDATE stages SET titre = :titre, description = :description, duree = :duree, departement_id = :dept_id WHERE id = :id");
        $query->execute([
            ':titre' => $_POST['title'],
            ':description' => $_POST['description'],
            ':duree' => $_POST['durée'],
            ':dept_id' => $_POST['departement_id'],
            ':id' => $_POST['stage_id']
        ]);
        set_flash('success', 'Stage mis à jour avec succès.');
        redirect('AddStage.php');
    }
} catch (PDOException $e) {
    set_flash('error', 'Erreur : ' . $e->getMessage());
}

include 'header.php';
?>
    <h1>Gestion des offres de stages</h1>

    <div class="table-container">
        <table style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Département</th>
                    <th>Description</th>
                    <th>Durée</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stages as $stage): ?>
                    <tr>
                        <td><?= e($stage['titre']); ?></td>
                        <td><small><?= e($stage['dept_nom'] ?? 'Aucun') ?> <?= isset($stage['dept_sigle']) ? '('.e($stage['dept_sigle']).')' : '' ?></small></td>
                        <td><?= nl2br(e($stage['description'])); ?></td>
                        <td><?= e($stage['duree']); ?></td>
                        <td style="display: flex; gap: 5px;">
                            <a class="btn btn-primary" href="?edit=<?= $stage['id']; ?>#form-edit" style="padding: 0.5rem; font-size: 0.8rem;">Modifier</a>
                            <a class="btn btn-danger" href="?delete=<?= $stage['id']; ?>" onclick="return confirm('Supprimer ce stage ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($stages)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucune offre de stage n'est publiée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2 id="form-edit" style="text-align: center; margin: 30px 0 20px;"><?= $editStage ? 'Modifier le stage' : 'Ajouter un nouveau stage' ?></h2>
    <form method="post" style="margin-bottom: 50px;">
        <?php if($editStage): ?><input type="hidden" name="stage_id" value="<?= $editStage['id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label for="title">Titre du stage</label>
            <input type="text" id="title" name="title" placeholder="Ex: Développeur PHP" required value="<?= e($editStage['titre'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="departement_id">Département</label>
            <select id="departement_id" name="departement_id" required>
                <option value="">-- Sélectionner un département --</option>
                <?php foreach ($depts as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (isset($editStage['departement_id']) && $editStage['departement_id'] == $d['id']) ? 'selected' : '' ?>><?= e($d['nom']) ?> (<?= e($d['sigle']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description des missions</label>
            <textarea id="description" name="description" rows="4" required><?= e($editStage['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="durée">Durée du stage</label>
            <select id="durée" name="durée" required>
                <option value="">-- Sélectionner la durée --</option>
                <option value="2 mois" <?= (isset($editStage['duree']) && $editStage['duree'] == '2 mois') ? 'selected' : '' ?>>2 mois</option>
                <option value="3 mois" <?= (isset($editStage['duree']) && $editStage['duree'] == '3 mois') ? 'selected' : '' ?>>3 mois</option>
            </select>
        </div>
        <button type="submit" name="<?= $editStage ? 'update' : 'save' ?>" class="btn btn-primary" style="width: 100%;"><?= $editStage ? 'Mettre à jour' : 'Enregistrer l\'offre' ?></button>
        <?php if($editStage): ?><a href="AddStage.php" class="btn" style="width: 100%; margin-top: 10px; background: #6b7280; color: #fff;">Annuler</a><?php endif; ?>
    </form>
</html>