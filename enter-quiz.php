<?php
// Determine the absolute path to the project root
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/config.php';
require_once $project_root . '/includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /projetweb_php/login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <title>Répondre à un Quiz - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css?v=<?php echo uniqid(); ?>"><?php // Consistent CSS link with unique identifier ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="enter-quiz-container">
            <h1>Répondre à un Quiz</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="enter-quiz-form">
                <div class="form-group">
                    <label for="quiz_link">Lien du Quiz</label>
                    <input type="url" id="quiz_link" name="quiz_link" required 
                           placeholder="Collez le lien du quiz ici"
                           value="<?php echo isset($_POST['quiz_link']) ? htmlspecialchars($_POST['quiz_link']) : ''; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Accéder au Quiz</button>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
