<?php
require_once 'config.php';
try {
    $connection = db();
    $query = $connection->prepare("SELECT s.*, d.nom as dept_nom, d.sigle as dept_sigle FROM stages s LEFT JOIN departements d ON s.departement_id = d.id ORDER BY d.nom ASC, s.titre ASC");
    $query->execute();
    $stages = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Regroupement par département
    $groupedStages = [];
    foreach ($stages as $stage) {
        $deptName = !empty($stage['dept_nom']) ? $stage['dept_nom'] . ' (' . $stage['dept_sigle'] . ')' : 'Autres / Non classés';
        $groupedStages[$deptName][] = $stage;
    }
} catch (PDOException $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
}
?>
<?php include 'header.php'; ?>
    <div style="text-align: center; margin-bottom: 2rem;">
        <img src="logo.png" alt="Logo" style="width: 200px; height: auto; margin-bottom: 1rem; border-radius: 20px; box-shadow: var(--shadow);">
        <h1>Offres de stages disponibles</h1>
        <a href="index.php" class="btn" style="background: #6b7280; color: #fff; padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 600;">← Retour à l'accueil</a>
    </div>

    <?php if (empty($groupedStages)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="font-size: 1.2rem; color: var(--text-muted);">Désolé, aucune offre de stage n'est disponible pour le moment. Revenez plus tard !</p>
        </div>
    <?php endif; ?>

    <?php foreach ($groupedStages as $dept => $deptStages): ?>
        <h2 style="margin: 2rem 0 1rem; color: var(--primary); border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: 5px;">
            🏢 <?= e($dept) ?>
        </h2>
        <div class="table-container" style="margin-bottom: 3rem;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Durée</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deptStages as $stage): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= e($stage['titre']); ?></td>
                            <td><?= nl2br(e($stage['description'])); ?></td>
                            <td><span style="white-space: nowrap;"><?= e($stage['duree']); ?></span></td>
                            <td><a class="btn btn-primary" href="postuler.php?id=<?= (int)$stage['id']; ?>">Postuler</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</html>
