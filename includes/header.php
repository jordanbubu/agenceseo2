<?php if (isLoggedIn()): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Gestion SEO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Tableau de bord</a>
                </li>
                <?php if (checkRole('admin')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">Utilisateurs</a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="navbar-nav">
                <span class="nav-item nav-link text-light">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a class="nav-link" href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>