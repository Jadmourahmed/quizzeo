<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
    header('Location: login.php');
    exit();
}

$quizzes = getUserQuizzes($_SESSION['user_id']);
?>

<div class="dashboard company-dashboard">
    <div class="dashboard-header">
        <h1>Tableau de bord Entreprise</h1>
        <a href="create-quiz.php" class="btn btn-primary">Créer un nouveau Quiz</a>
    </div>

    <div class="quiz-grid">
        <?php if (empty($quizzes)): ?>
            <p class="no-quiz">Vous n'avez pas encore créé de quiz. Commencez par en créer un !</p>
        <?php else: ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p class="quiz-status">Status: <?php echo $quiz['status']; ?></p>
                    <p class="quiz-responses">Réponses: <?php echo count($quiz['responses'] ?? []); ?></p>
                    
                    <div class="quiz-actions">
                        <?php if ($quiz['status'] === 'en_cours'): ?>
                            <button class="btn btn-small" onclick="terminerQuiz('<?php echo $quiz['id']; ?>')">Terminer</button>
                        <?php endif; ?>
                        
                        <?php if ($quiz['status'] === 'termine'): ?>
                            <a href="statistiques.php?id=<?php echo $quiz['id']; ?>" class="btn btn-small">Voir les statistiques</a>
                        <?php endif; ?>
                        
                        <button class="btn btn-small share-quiz" data-link="<?php echo generateQuizLink($quiz['id']); ?>">
                            Copier le lien
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function terminerQuiz(quizId) {
    if (confirm('Êtes-vous sûr de vouloir terminer ce quiz ? Les participants ne pourront plus y répondre.')) {
        // TODO: Implement AJAX call to end quiz
        alert('Fonctionnalité en cours de développement');
    }
}
</script>
