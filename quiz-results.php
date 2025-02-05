<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /projetweb_php/login.php');
    exit();
}

$quiz_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$quiz_file = DATA_PATH . '/quizzes/' . $quiz_id . '.json';

// Vérifier si le quiz existe
if (!file_exists($quiz_file)) {
    header('Location: /projetweb_php/index.php');
    exit();
}

$quiz = json_decode(file_get_contents($quiz_file), true);

// Vérifier les permissions
if ($_SESSION['role'] === 'utilisateur') {
    // Les utilisateurs ne peuvent voir que leurs propres résultats
    $user_responses = array_filter($quiz['responses'], function($response) {
        return $response['user_id'] === $_SESSION['user_id'];
    });
    if (empty($user_responses)) {
        header('Location: /projetweb_php/index.php');
        exit();
    }
    $responses = array_values($user_responses);
} elseif (in_array($_SESSION['role'], ['ecole', 'entreprise'])) {
    // Les créateurs peuvent voir tous les résultats
    if ($quiz['creator_id'] !== $_SESSION['user_id']) {
        header('Location: /projetweb_php/index.php');
        exit();
    }
    $responses = $quiz['responses'];
} else {
    header('Location: /projetweb_php/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - <?php echo htmlspecialchars($quiz['title']); ?> - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="quiz-results">
            <h1>Résultats : <?php echo htmlspecialchars($quiz['title']); ?></h1>
            
            <?php if (empty($responses)): ?>
                <p>Aucune réponse trouvée.</p>
            <?php else: ?>
                <?php if ($_SESSION['role'] === 'utilisateur'): ?>
                    <!-- Affichage des résultats pour l'utilisateur -->
                    <?php $response = end($responses); ?>
                    <div class="user-result">
                        <div class="score-summary">
                            <h2>Votre score</h2>
                            <div class="score-display">
                                <span class="score"><?php echo $response['total_score']; ?></span>
                                <span class="separator">/</span>
                                <span class="max-score"><?php echo $response['max_score']; ?></span>
                            </div>
                            <div class="score-percentage">
                                <?php echo round(($response['total_score'] / $response['max_score']) * 100); ?>%
                            </div>
                        </div>
                        
                        <div class="answers-detail">
                            <h2>Détail des réponses</h2>
                            <?php foreach ($response['answers'] as $index => $answer): ?>
                                <div class="answer-item <?php echo $answer['score'] > 0 ? 'correct' : 'incorrect'; ?>">
                                    <h3>Question <?php echo $index + 1; ?></h3>
                                    <p class="question-text"><?php echo htmlspecialchars($answer['question_text']); ?></p>
                                    
                                    <div class="answer-details">
                                        <p class="user-answer">
                                            <strong>Votre réponse :</strong>
                                            <?php
                                            if (is_array($answer['user_answer'])) {
                                                $selected_options = array_map(function($idx) use ($quiz, $index) {
                                                    return $quiz['questions'][$index]['options'][$idx];
                                                }, $answer['user_answer']);
                                                echo implode(', ', array_map('htmlspecialchars', $selected_options));
                                            } else {
                                                echo htmlspecialchars($quiz['questions'][$index]['options'][$answer['user_answer']] ?? 'Non répondu');
                                            }
                                            ?>
                                        </p>
                                        
                                        <p class="correct-answer">
                                            <strong>Bonne(s) réponse(s) :</strong>
                                            <?php
                                            if (is_array($answer['correct_answer'])) {
                                                $correct_options = array_map(function($idx) use ($quiz, $index) {
                                                    return $quiz['questions'][$index]['options'][$idx];
                                                }, array_keys(array_filter($answer['correct_answer'])));
                                                echo implode(', ', array_map('htmlspecialchars', $correct_options));
                                            } else {
                                                echo htmlspecialchars($quiz['questions'][$index]['options'][$answer['correct_answer']] ?? '');
                                            }
                                            ?>
                                        </p>
                                        
                                        <p class="points">
                                            Points : <?php echo $answer['score']; ?>/<?php echo $answer['max_points']; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Affichage des résultats pour les écoles et entreprises -->
                    <div class="results-summary">
                        <h2>Résumé des résultats</h2>
                        <p>Nombre total de participants : <?php echo count($responses); ?></p>
                        <?php
                        $total_scores = array_column($responses, 'total_score');
                        $max_score = $responses[0]['max_score'];
                        $average_score = array_sum($total_scores) / count($total_scores);
                        ?>
                        <p>Score moyen : <?php echo round($average_score, 2); ?>/<?php echo $max_score; ?> (<?php echo round(($average_score / $max_score) * 100); ?>%)</p>
                    </div>
                    
                    <div class="results-table">
                        <h2>Détail par participant</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($responses as $response): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($response['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($response['firstname']); ?></td>
                                        <td><?php echo htmlspecialchars($response['user_email']); ?></td>
                                        <td>
                                            <?php echo $response['total_score']; ?>/<?php echo $response['max_score']; ?>
                                            (<?php echo round(($response['total_score'] / $response['max_score']) * 100); ?>%)
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($response['submitted_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="actions">
                <a href="/projetweb_php/index.php" class="btn btn-secondary">Retour au tableau de bord</a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
