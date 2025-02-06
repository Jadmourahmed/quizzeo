<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /projetweb_php/login.php');
    exit();
}

$error = '';
$quiz_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$quiz_file = DATA_PATH . '/quizzes/' . $quiz_id . '.json';

// Vérifier si le quiz existe
if (!file_exists($quiz_file)) {
    header('Location: /projetweb_php/index.php');
    exit();
}

$quiz = json_decode(file_get_contents($quiz_file), true);

// Vérifier si le quiz est terminé
if ($quiz['status'] === 'termine' && $_SESSION['role'] === 'utilisateur') {
    header('Location: /projetweb_php/index.php');
    exit();
}

// Traiter la soumission du formulaire d'identification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'start_quiz') {
    $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
    $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
    
    if (empty($firstname) || empty($lastname)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $_SESSION['quiz_user'] = [
            'firstname' => $firstname,
            'lastname' => $lastname
        ];
    }
}

// Traiter la soumission des réponses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'])) {
    $answers = $_POST['answers'];
    $total_score = 0;
    $max_score = 0;
    $user_answers = [];
    
    foreach ($quiz['questions'] as $index => $question) {
        $user_answer = $answers[$index] ?? '';
        $score = 0;
        $max_score += $question['points'];
        
        // Calculer le score pour chaque question
        if ($question['type'] === 'multiple') {
            $user_answer = isset($answers[$index]) ? (array)$answers[$index] : [];
            $correct_answers = $question['correct_answers'];
            
            // Vérifier si toutes les réponses sont correctes
            $correct = true;
            foreach ($correct_answers as $i => $is_correct) {
                if ($is_correct && !in_array($i, $user_answer)) {
                    $correct = false;
                    break;
                }
                if (!$is_correct && in_array($i, $user_answer)) {
                    $correct = false;
                    break;
                }
            }
            
            if ($correct) {
                $score = $question['points'];
            }
        } else {
            // Pour les questions à choix unique
            if (isset($user_answer) && (string)$user_answer === (string)$question['correct_answer']) {
                $score = $question['points'];
            }
        }
        
        $user_answers[] = [
            'question_text' => $question['text'],
            'user_answer' => $user_answer,
            'correct_answer' => $question['type'] === 'multiple' ? $question['correct_answers'] : $question['correct_answer'],
            'score' => $score,
            'max_points' => $question['points']
        ];
        
        $total_score += $score;
    }
    
    // Enregistrer la réponse
    if (!isset($quiz['responses'])) {
        $quiz['responses'] = [];
    }
    
    $quiz['responses'][] = [
        'user_id' => $_SESSION['user_id'],
        'user_email' => $_SESSION['email'],
        'firstname' => $_SESSION['quiz_user']['firstname'] ?? '',
        'lastname' => $_SESSION['quiz_user']['lastname'] ?? '',
        'answers' => $user_answers,
        'total_score' => $total_score,
        'max_score' => $max_score,
        'submitted_at' => date('Y-m-d H:i:s')
    ];
    
    // Sauvegarder les modifications
    file_put_contents($quiz_file, json_encode($quiz));
    
    // Nettoyer la session
    unset($_SESSION['quiz_user']);
    
    // Rediriger vers la page des résultats
    header('Location: /projetweb_php/index.php?quiz_completed=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css?v=<?php echo uniqid(); ?>"><?php // Consistent CSS link with unique identifier ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="quiz-container">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($quiz['show_right_answers']) && $quiz['show_right_answers']): ?>
                <div class="quiz-info-note">
                    <p>📝 Note : Les réponses correctes seront affichées après le quiz.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] === 'utilisateur' && !isset($_SESSION['quiz_user'])): ?>
                <!-- Formulaire d'identification -->
                <div class="quiz-identification">
                    <h2>Identification</h2>
                    <p>Veuillez entrer votre nom et prénom avant de commencer le quiz.</p>
                    
                    <form method="POST" action="" class="identification-form">
                        <input type="hidden" name="action" value="start_quiz">
                        
                        <div class="form-group">
                            <label for="firstname">Prénom</label>
                            <input type="text" id="firstname" name="firstname" required
                                   value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="lastname">Nom</label>
                            <input type="text" id="lastname" name="lastname" required
                                   value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Commencer le Quiz</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Afficher le quiz -->
                <form method="POST" action="" class="quiz-form">
                    <?php foreach ($quiz['questions'] as $index => $question): ?>
                        <div class="question">
                            <h3>Question <?php echo $index + 1; ?></h3>
                            <p><?php echo htmlspecialchars($question['text']); ?></p>
                            
                            <?php if ($question['type'] === 'multiple'): ?>
                                <?php foreach ($question['options'] as $option_index => $option): ?>
                                    <div class="option">
                                        <input type="checkbox" 
                                               id="q<?php echo $index; ?>_<?php echo $option_index; ?>"
                                               name="answers[<?php echo $index; ?>][]"
                                               value="<?php echo $option_index; ?>">
                                        <label for="q<?php echo $index; ?>_<?php echo $option_index; ?>">
                                            <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($question['options'] as $option_index => $option): ?>
                                    <div class="option">
                                        <input type="radio"
                                               id="q<?php echo $index; ?>_<?php echo $option_index; ?>"
                                               name="answers[<?php echo $index; ?>]"
                                               value="<?php echo $option_index; ?>"
                                               required>
                                        <label for="q<?php echo $index; ?>_<?php echo $option_index; ?>">
                                            <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary">Soumettre les réponses</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
