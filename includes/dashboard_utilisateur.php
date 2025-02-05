<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'utilisateur') {
    header('Location: login.php');
    exit();
}

// Récupérer les quiz auxquels l'utilisateur a répondu
$completed_quizzes = [];
$quiz_files = glob(DATA_PATH . '/quizzes/*.json');
foreach ($quiz_files as $file) {
    $quiz = json_decode(file_get_contents($file), true);
    if (isset($quiz['responses'][$_SESSION['user_id']])) {
        $completed_quizzes[] = $quiz;
    }
}
?>

<div class="dashboard user-dashboard">
    <h1>Mes Quiz Complétés</h1>

    <div class="quiz-grid">
        <?php if (empty($completed_quizzes)): ?>
            <p class="no-quiz">Vous n'avez pas encore répondu à des quiz.</p>
        <?php else: ?>
            <?php foreach ($completed_quizzes as $quiz): ?>
                <div class="quiz-card">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p class="quiz-creator">Créé par: <?php echo htmlspecialchars($quiz['creator_email']); ?></p>
                    
                    <?php if ($quiz['type'] === 'ecole'): ?>
                        <p class="quiz-score">Note: <?php echo $quiz['responses'][$_SESSION['user_id']]['score']; ?>/<?php echo $quiz['total_points']; ?></p>
                    <?php endif; ?>
                    
                    <div class="quiz-actions">
                        <a href="voir-reponses.php?id=<?php echo $quiz['id']; ?>" class="btn btn-small">Voir mes réponses</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
