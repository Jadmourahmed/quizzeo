<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer la liste des utilisateurs
$users = [];
$user_files = glob(DATA_PATH . '/users/*.json');
foreach ($user_files as $file) {
    if (basename($file) !== 'admin.json') {
        $users[] = json_decode(file_get_contents($file), true);
    }
}

// Récupérer la liste des quiz
$quizzes = [];
$quiz_files = glob(DATA_PATH . '/quizzes/*.json');
foreach ($quiz_files as $file) {
    $quizzes[] = json_decode(file_get_contents($file), true);
}
?>

<div class="dashboard admin-dashboard">
    <h1>Tableau de bord Administrateur</h1>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Utilisateurs</h3>
            <p class="stat-number"><?php echo count($users); ?></p>
        </div>
        <div class="stat-card">
            <h3>Quiz</h3>
            <p class="stat-number"><?php echo count($quizzes); ?></p>
        </div>
    </div>

    <section class="users-section">
        <h2>Gestion des Utilisateurs</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo $user['active'] ? 'Actif' : 'Inactif'; ?></td>
                        <td>
                            <button class="btn btn-small" onclick="toggleUserStatus('<?php echo $user['id']; ?>')">
                                <?php echo $user['active'] ? 'Désactiver' : 'Activer'; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="quizzes-section">
        <h2>Gestion des Quiz</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Créateur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['creator_email']); ?></td>
                        <td><?php echo $quiz['active'] ? 'Actif' : 'Inactif'; ?></td>
                        <td>
                            <button class="btn btn-small" onclick="toggleQuizStatus('<?php echo $quiz['id']; ?>')">
                                <?php echo $quiz['active'] ? 'Désactiver' : 'Activer'; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
function toggleUserStatus(userId) {
    // TODO: Implement AJAX call to toggle user status
    alert('Fonctionnalité en cours de développement');
}

function toggleQuizStatus(quizId) {
    // TODO: Implement AJAX call to toggle quiz status
    alert('Fonctionnalité en cours de développement');
}
</script>
