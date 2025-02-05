<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /projetweb_php/login.php');
    exit();
}

// Vérifier si l'utilisateur est actif
if (!isUserActive($_SESSION['user_id'])) {
    session_destroy();
    header('Location: /projetweb_php/login.php?error=account_disabled');
    exit();
}

$error = '';
$success = '';

// Traiter la soumission du lien de quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_link'])) {
    $quiz_link = filter_input(INPUT_POST, 'quiz_link', FILTER_SANITIZE_URL);
    
    if (empty($quiz_link)) {
        $error = "Veuillez entrer un lien de quiz";
    } else {
        // Extraire l'ID du quiz du lien
        if (preg_match('/[?&]id=([^&]+)/', $quiz_link, $matches)) {
            $quiz_id = $matches[1];
            header('Location: /projetweb_php/quiz.php?id=' . $quiz_id);
            exit();
        } else {
            $error = "Le lien du quiz n'est pas valide";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzeo - Plateforme de Quiz en Ligne</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dashboard">
                <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['email']); ?></h1>
                
                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                
                <?php if (in_array($_SESSION['role'], ['ecole', 'entreprise'])): ?>
                    <!-- Actions pour écoles et entreprises -->
                    <div class="dashboard-actions">
                        <a href="/projetweb_php/create-quiz.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Créer un nouveau Quiz
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] === 'utilisateur'): ?>
                    <!-- Formulaire de participation à un quiz pour les utilisateurs -->
                    <div class="quiz-participation">
                        <h2>Participer à un Quiz</h2>
                        <form method="POST" action="" class="quiz-link-form">
                            <div class="form-group">
                                <input type="text" name="quiz_link" placeholder="Collez le lien du quiz ici" required>
                                <button type="submit" class="btn btn-primary">Participer</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Liste des quiz -->
                <div class="quiz-list">
                    <h2>Mes Quiz</h2>
                    
                    <?php
                    $quizzes = [];
                    $quiz_files = glob(DATA_PATH . '/quizzes/*.json');
                    
                    foreach ($quiz_files as $quiz_file) {
                        $quiz = json_decode(file_get_contents($quiz_file), true);
                        
                        if ($_SESSION['role'] === 'utilisateur') {
                            // Pour les utilisateurs, afficher les quiz auxquels ils ont participé
                            $user_responses = array_filter($quiz['responses'] ?? [], function($response) {
                                return $response['user_id'] === $_SESSION['user_id'];
                            });
                            if (!empty($user_responses)) {
                                $quiz['user_responses'] = array_values($user_responses);
                                $quizzes[] = $quiz;
                            }
                        } elseif (in_array($_SESSION['role'], ['ecole', 'entreprise'])) {
                            // Pour les écoles et entreprises, afficher leurs quiz créés
                            if (($quiz['creator_id'] ?? '') === $_SESSION['user_id']) {
                                $quizzes[] = $quiz;
                            }
                        }
                    }
                    ?>
                    
                    <?php if (empty($quizzes)): ?>
                        <p class="no-quiz">Aucun quiz trouvé.</p>
                    <?php else: ?>
                        <div class="quiz-grid">
                            <?php foreach ($quizzes as $quiz): ?>
                                <div class="quiz-card">
                                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <?php if (!empty($quiz['description'])): ?>
                                        <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($_SESSION['role'] === 'utilisateur'): ?>
                                        <?php
                                        $last_response = end($quiz['user_responses']);
                                        if ($last_response):
                                        ?>
                                            <div class="quiz-score">
                                                Score: <?php echo $last_response['total_score']; ?>/<?php echo $last_response['max_score']; ?>
                                            </div>
                                            <form action="/projetweb_php/manage-quiz.php" method="POST" class="quiz-actions">
                                                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                                <input type="hidden" name="action" value="clear_responses">
                                                <button type="submit" class="btn btn-secondary" onclick="return confirm('Êtes-vous sûr de vouloir effacer vos réponses pour ce quiz ?')">
                                                    Effacer mes réponses
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="quiz-stats">
                                            <p>Nombre de réponses : <?php echo count($quiz['responses'] ?? []); ?></p>
                                        </div>
                                        <div class="quiz-actions">
                                            <a href="/projetweb_php/quiz-results.php?id=<?php echo $quiz['id']; ?>" class="btn btn-secondary">
                                                Voir les résultats
                                            </a>
                                            <form action="/projetweb_php/manage-quiz.php" method="POST" class="inline-form">
                                                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                                <input type="hidden" name="action" value="delete_quiz">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce quiz et tous ses résultats ?')">
                                                    Supprimer le quiz
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
