<?php
require_once 'config.php';
require_login();

try {
    $connection = db();
} catch (PDOException $e) {
    die("Erreur de connexion : " . htmlspecialchars($e->getMessage()));
}

// Récupération des statistiques
try {
    $totalStages = $connection->query("SELECT COUNT(*) FROM stages")->fetchColumn();
    $totalStagiaires = $connection->query("SELECT COUNT(*) FROM stagiaires")->fetchColumn();
    $totalValides = $connection->query("SELECT COUNT(*) FROM stagiaires WHERE status = 'valide'")->fetchColumn();
    $totalRefuses = $connection->query("SELECT COUNT(*) FROM stagiaires WHERE status = 'refuse'")->fetchColumn();

    // Liste des stagiaires avec leur stage
    $query = $connection->query("
    SELECT s.id, s.nom, s.prenom, s.email, st.titre AS stage_name, s.status
    FROM stagiaires s
    LEFT JOIN stages st ON s.stage_id = st.id
    ORDER BY s.id DESC LIMIT 10
");
    $stagiaires = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . htmlspecialchars($e->getMessage()));
}
include 'header.php';
?>
    <h1>Tableau de Bord</h1>

    <div class="stats">
        <div class="card">
            <p>Stages</p>
            <h2><?= (int)$totalStages; ?></h2>
        </div>
        <div class="card">
            <p>Postulants</p>
            <h2><?= (int)$totalStagiaires; ?></h2>
        </div>
        <div class="card">
            <p>Validés</p>
            <h2 style="color: var(--success);"><?= (int)$totalValides; ?></h2>
        </div>
        <div class="card">
            <p>Refusés</p>
            <h2 style="color: var(--danger);"><?= (int)$totalRefuses; ?></h2>
        </div>
    </div>

    <h2 style="margin: 40px 0 20px;">Candidatures récentes</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Stage</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stagiaires as $stagiaire): ?>
                    <tr>
                        <td><strong><?= e($stagiaire['nom']); ?></strong> <?= e($stagiaire['prenom']); ?></td>
                        <td><?= e($stagiaire['email']); ?></td>
                        <td><?= e($stagiaire['stage_name']); ?></td>
                        <td>
                            <span style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; 
                                background: <?= $stagiaire['status'] === 'valide' ? '#dcfce7' : ($stagiaire['status'] === 'refuse' ? '#fee2e2' : '#fef9c3') ?>;
                                color: <?= $stagiaire['status'] === 'valide' ? '#166534' : ($stagiaire['status'] === 'refuse' ? '#991b1b' : '#854d0e') ?>;">
                                <?= e($stagiaire['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($stagiaires)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucune candidature enregistrée pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
