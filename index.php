<?php include 'header.php'; ?>
<div class="card" style="text-align: center; padding: 4rem 3rem; max-width: 700px; margin: 2rem auto;">
    <img src="logo.png" alt="Logo" style="width: 100%; max-height: 250px; object-fit: contain; margin-bottom: 2rem; border-radius: 20px; box-shadow: var(--shadow); display: block; margin: 0 auto 2rem; padding: 20px; background: rgba(255,255,255,0.1);">
    <h1 style="color: var(--primary); margin-bottom: 1rem;">Gestion des Stages</h1>
    <p style="font-size: 1.2rem; color: var(--text-muted); margin-bottom: 3rem; line-height: 1.6;">
        Plateforme de recrutement et de suivi des stagiaires.
    </p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
        <a href="ListStage.php" class="btn btn-primary" style="justify-content: center; padding: 1.5rem; font-size: 1.1rem;">
            <span style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                🚀 Postulant<br>
                <small style="font-weight: normal; font-size: 0.85rem; opacity: 0.9;">Trouver un stage</small>
            </span>
        </a>
        <a href="login.php" class="btn btn-success" style="justify-content: center; padding: 1.5rem; font-size: 1.1rem;">
            <span style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                🔑 Espace Privé<br>
                <small style="font-weight: normal; font-size: 0.85rem; opacity: 0.9;">Accès Gestionnaire</small>
            </span>
        </a>
    </div>
</div>
</body>
</html>
