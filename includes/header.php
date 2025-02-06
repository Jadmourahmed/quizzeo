<header class="main-header">
    <div class="container">
        <nav class="nav-menu">
            <div class="logo">
                <a href="/projetweb_php/index.php" class="logo-text">
                    <span class="logo-q">Q</span><span class="logo-u">U</span><span class="logo-i">I</span><span class="logo-z">ZZE</span><span class="logo-o">O</span>
                </a>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <ul class="nav-links">
                    <li><a href="/projetweb_php/index.php">Tableau de bord</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="/projetweb_php/admin.php">Administration</a></li>
                    <?php endif; ?>
                    <?php if (in_array($_SESSION['role'], ['ecole', 'entreprise'])): ?>
                        <li><a href="/projetweb_php/create-quiz.php">Créer un Quiz</a></li>
                    <?php endif; ?>
                    <li><a href="/projetweb_php/profile.php">Mon Profil</a></li>
                    <li><a href="/projetweb_php/logout.php">Déconnexion</a></li>
                </ul>
            <?php endif; ?>
        </nav>
    </div>
</header>
