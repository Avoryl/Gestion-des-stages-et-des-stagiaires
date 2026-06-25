<?php
require_once 'config.php';
require_role('gestionnaire');

$connection = db();

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $connection->prepare("DELETE FROM departements WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Département supprimé.');
    redirect('GestionDepartements.php');
}

// Récupérer le département à modifier
$editDept = null;
if (isset($_GET['edit'])) {
    $stmt = $connection->prepare("SELECT * FROM departements WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editDept = $stmt->fetch();
}

// Ajout
if (isset($_POST['save'])) {
    $nom = trim($_POST['nom']);
    $sigle = strtoupper(trim($_POST['sigle']));
    if ($nom !== '' && $sigle !== '') {
        $stmt = $connection->prepare("INSERT INTO departements (nom, sigle) VALUES (?, ?)");
        $stmt->execute([$nom, $sigle]);
        set_flash('success', 'Département ajouté.');
    }
    redirect('GestionDepartements.php');
}

// Modification
if (isset($_POST['update'])) {
    $nom = trim($_POST['nom']);
    $sigle = strtoupper(trim($_POST['sigle']));
    if ($nom !== '' && $sigle !== '') {
        $stmt = $connection->prepare("UPDATE departements SET nom = ?, sigle = ? WHERE id = ?");
        $stmt->execute([$nom, $sigle, (int)$_POST['id']]);
        set_flash('success', 'Département mis à jour.');
    }
    redirect('GestionDepartements.php');
}

$depts = $connection->query("SELECT * FROM departements ORDER BY nom ASC")->fetchAll();
include 'header.php';
?>
    <h1>Gestion des Départements</h1>

    <div class="table-container" style="max-width: 600px; margin: 0 auto 2rem;">
        <table>
            <thead>
                <tr>
                    <th>Nom du Département</th>
                    <th>Sigle</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($depts as $d): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= e($d['nom']) ?></td>
                        <td><span class="badge" style="background: #e2e8f0; padding: 4px 8px; border-radius: 6px; font-weight: 700;"><?= e($d['sigle']) ?></span></td>
                        <td style="text-align: right; display: flex; gap: 5px; justify-content: flex-end;">
                            <a class="btn btn-primary" href="?edit=<?= $d['id'] ?>#form-edit" style="padding: 0.5rem; font-size: 0.8rem;">Modifier</a>
                            <a class="btn btn-danger" href="?delete=<?= $d['id'] ?>" onclick="return confirm('Supprimer ce département ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($depts)): ?>
                    <tr><td colspan="2" style="text-align: center;">Aucun département créé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <form id="form-edit" method="post" style="max-width: 600px;">
        <?php if($editDept): ?><input type="hidden" name="id" value="<?= $editDept['id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label for="nom">Nom complet (ex: Ressources Humaines)</label>
            <input type="text" id="nom" name="nom" required placeholder="Ex: Direction des Systèmes d'Information" value="<?= e($editDept['nom'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="sigle">Sigle (ex: RH)</label>
            <input type="text" id="sigle" name="sigle" required placeholder="Ex: DSI" maxlength="50" value="<?= e($editDept['sigle'] ?? '') ?>">
        </div>
        <button type="submit" name="<?= $editDept ? 'update' : 'save' ?>" class="btn btn-primary" style="width: 100%;"><?= $editDept ? 'Mettre à jour' : 'Ajouter le département' ?></button>
        <?php if($editDept): ?><a href="GestionDepartements.php" class="btn" style="width: 100%; margin-top: 10px; background: #6b7280; color: #fff;">Annuler</a><?php endif; ?>
    </form>
</body>
</html>