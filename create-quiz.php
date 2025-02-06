<?php
session_start();
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/config.php';
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/functions.php';

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['ecole', 'entreprise'])) {
    header('Location: /projetweb_php/login.php');
    exit();
}

$error = '';
$success = '';

// Traiter la création du quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $questions = [];
    
    if (empty($title)) {
        $error = "Le titre du quiz est requis";
    } else {
        // Traiter chaque question
        foreach ($_POST['questions'] as $index => $question_data) {
            $question_text = filter_var($question_data['text'], FILTER_SANITIZE_STRING);
            $question_type = filter_var($question_data['type'], FILTER_SANITIZE_STRING);
            $question_points = filter_var($question_data['points'], FILTER_VALIDATE_INT);
            $options = array_map('trim', $question_data['options']);
            $options = array_filter($options, 'strlen');
            
            // Vérifier les données de la question
            if (empty($question_text) || empty($options)) {
                $error = "La question " . ($index + 1) . " est incomplète";
                break;
            }
            
            // Traiter les bonnes réponses
            if ($question_type === 'multiple') {
                $correct_answers = [];
                if (isset($question_data['correct_answers']) && is_array($question_data['correct_answers'])) {
                    foreach ($options as $opt_index => $option) {
                        $correct_answers[$opt_index] = in_array($opt_index, $question_data['correct_answers']);
                    }
                }
                if (empty($correct_answers) || !in_array(true, $correct_answers)) {
                    $error = "Veuillez sélectionner au moins une bonne réponse pour la question " . ($index + 1);
                    break;
                }
                $questions[] = [
                    'text' => $question_text,
                    'type' => $question_type,
                    'points' => $question_points,
                    'options' => array_values($options),
                    'correct_answers' => $correct_answers
                ];
            } else {
                $correct_answer = isset($question_data['correct_answer']) ? (int)$question_data['correct_answer'] : -1;
                if ($correct_answer < 0 || $correct_answer >= count($options)) {
                    $error = "Veuillez sélectionner la bonne réponse pour la question " . ($index + 1);
                    break;
                }
                $questions[] = [
                    'text' => $question_text,
                    'type' => 'single',
                    'points' => $question_points,
                    'options' => array_values($options),
                    'correct_answer' => $correct_answer
                ];
            }
        }
        
        if (empty($error)) {
            // Créer le quiz
            $quiz_data = [
                'id' => uniqid(),
                'title' => $title,
                'description' => $description,
                'creator_id' => $_SESSION['user_id'],
                'creator_email' => $_SESSION['email'],
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'en_cours',
                'questions' => $questions,
                'responses' => [],
                'show_right_answers' => filter_input(INPUT_POST, 'show_right_answers', FILTER_VALIDATE_BOOLEAN)
            ];
            
            // Sauvegarder le quiz
            $quiz_file = DATA_PATH . '/quizzes/' . $quiz_data['id'] . '.json';
            if (!is_dir(dirname($quiz_file))) {
                mkdir(dirname($quiz_file), 0777, true);
            }
            
            if (file_put_contents($quiz_file, json_encode($quiz_data, JSON_PRETTY_PRINT))) {
                $success = "Quiz créé avec succès !";
                $quiz_link = 'http://' . $_SERVER['HTTP_HOST'] . '/projetweb_php/quiz.php?id=' . $quiz_data['id'];
            } else {
                $error = "Erreur lors de la création du quiz";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Quiz - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css?v=<?php echo uniqid(); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="create-quiz">
            <h1>Créer un nouveau Quiz</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <p>Lien du quiz : <input type="text" value="<?php echo htmlspecialchars($quiz_link); ?>" readonly></p>
                    <button onclick="copyQuizLink(this)" class="btn btn-secondary">Copier le lien</button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="quiz-form" id="createQuizForm">
                <div class="form-group">
                    <label for="title">Titre du Quiz*</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="show_right_answers">
                        <input type="checkbox" id="show_right_answers" name="show_right_answers" value="1">
                        Afficher les réponses correctes après le quiz
                    </label>
                </div>
                
                <div id="questions">
                    <!-- Les questions seront ajoutées ici dynamiquement -->
                </div>
                
                <button type="button" class="btn btn-secondary" onclick="addQuestion()">Ajouter une question</button>
                <button type="submit" class="btn btn-primary">Créer le Quiz</button>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <template id="questionTemplate">
        <div class="question-box">
            <h3>Question <span class="question-number"></span></h3>
            
            <div class="form-group">
                <label>Question*</label>
                <input type="text" name="questions[INDEX][text]" required>
            </div>
            
            <div class="form-group">
                <label>Type de question</label>
                <select name="questions[INDEX][type]" onchange="updateQuestionType(this)">
                    <option value="single">Choix unique</option>
                    <option value="multiple">Choix multiple</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Points</label>
                <input type="number" name="questions[INDEX][points]" value="1" min="1" required>
            </div>
            
            <div class="options">
                <label>Options de réponse*</label>
                <div class="option-list">
                    <!-- Les options seront ajoutées ici -->
                </div>
                <button type="button" class="btn btn-secondary" onclick="addOption(this)">Ajouter une option</button>
            </div>
            
            <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Supprimer la question</button>
        </div>
    </template>
    
    <template id="optionTemplate">
        <div class="option-item">
            <input type="text" name="questions[INDEX][options][]" placeholder="Option de réponse" required>
            <div class="correct-answer">
                <input type="radio" name="questions[INDEX][correct_answer]" value="OPTION_INDEX">
                <label>Bonne réponse</label>
            </div>
            <button type="button" class="btn btn-danger btn-small" onclick="removeOption(this)">×</button>
        </div>
    </template>
    
    <script>
    let questionCount = 0;
    
    function addQuestion() {
        const template = document.getElementById('questionTemplate');
        const questions = document.getElementById('questions');
        const questionBox = template.content.cloneNode(true);
        
        // Mettre à jour les index
        questionCount++;
        questionBox.querySelector('.question-number').textContent = questionCount;
        updateIndexes(questionBox, 'INDEX', questionCount - 1);
        
        // Ajouter deux options par défaut
        const optionList = questionBox.querySelector('.option-list');
        addOption(optionList.parentElement, true);
        addOption(optionList.parentElement, true);
        
        questions.appendChild(questionBox);
    }
    
    function addOption(button, isInit = false) {
        const questionBox = button.closest('.question-box');
        const optionList = questionBox.querySelector('.option-list');
        const template = document.getElementById('optionTemplate');
        const optionItem = template.content.cloneNode(true);
        
        const questionIndex = questionBox.querySelector('[name^="questions["]').name.match(/\d+/)[0];
        const optionIndex = optionList.children.length;
        
        updateIndexes(optionItem, 'INDEX', questionIndex);
        updateIndexes(optionItem, 'OPTION_INDEX', optionIndex);
        
        optionList.appendChild(optionItem);
        
        // Mettre à jour le type de sélection
        const questionType = questionBox.querySelector('[name$="[type]"]').value;
        updateQuestionType(questionBox.querySelector('[name$="[type]"]'));
    }
    
    function removeQuestion(button) {
        const questionBox = button.closest('.question-box');
        questionBox.remove();
        updateQuestionNumbers();
    }
    
    function removeOption(button) {
        const optionItem = button.closest('.option-item');
        const optionList = optionItem.parentElement;
        if (optionList.children.length > 2) {
            optionItem.remove();
            updateOptionIndexes(optionList);
        } else {
            alert('Chaque question doit avoir au moins deux options');
        }
    }
    
    function updateQuestionNumbers() {
        const questions = document.querySelectorAll('.question-box');
        questions.forEach((question, index) => {
            question.querySelector('.question-number').textContent = index + 1;
            updateIndexes(question, 'INDEX', index);
        });
        questionCount = questions.length;
    }
    
    function updateQuestionType(select) {
        const questionBox = select.closest('.question-box');
        const options = questionBox.querySelectorAll('.correct-answer input');
        const type = select.value;
        
        options.forEach(input => {
            if (type === 'multiple') {
                input.type = 'checkbox';
                input.name = input.name.replace('correct_answer', 'correct_answers[]');
            } else {
                input.type = 'radio';
                input.name = input.name.replace('correct_answers[]', 'correct_answer');
            }
        });
    }
    
    function updateIndexes(element, placeholder, index) {
        element.querySelectorAll('[name*="' + placeholder + '"]').forEach(input => {
            input.name = input.name.replace(placeholder, index);
        });
    }
    
    function updateOptionIndexes(optionList) {
        optionList.querySelectorAll('.option-item').forEach((option, index) => {
            const radio = option.querySelector('input[type="radio"], input[type="checkbox"]');
            if (radio) {
                radio.value = index;
            }
        });
    }
    
    function copyQuizLink(button) {
        const input = button.previousElementSibling.querySelector('input');
        input.select();
        document.execCommand('copy');
        
        const originalText = button.textContent;
        button.textContent = 'Copié !';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('copied');
        }, 2000);
    }
    
    // Ajouter une première question au chargement
    document.addEventListener('DOMContentLoaded', () => {
        addQuestion();
    });
    </script>
</body>
</html>
