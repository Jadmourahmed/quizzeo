<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $captcha_response = $_POST['captcha_response'];
    $captcha_answer = $_SESSION['captcha_answer'];
    
    // Validation des données
    if (empty($email) || empty($password) || empty($role)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($captcha_response != $captcha_answer) {
        $error = "Le captcha est incorrect";
    } else {
        // Vérifier si l'email existe déjà
        $users_dir = DATA_PATH . '/users/';
        $email_exists = false;
        
        if (is_dir($users_dir)) {
            $files = glob($users_dir . '*.json');
            foreach ($files as $file) {
                $user_data = json_decode(file_get_contents($file), true);
                if ($user_data && $user_data['email'] === $email) {
                    $email_exists = true;
                    break;
                }
            }
        }
        
        if ($email_exists) {
            $error = "Cet email est déjà utilisé";
        } else {
            // Créer le dossier users s'il n'existe pas
            if (!is_dir($users_dir)) {
                mkdir($users_dir, 0777, true);
            }
            
            // Créer le nouvel utilisateur
            $user_data = [
                'id' => uniqid(),
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Sauvegarder l'utilisateur
            $file_path = $users_dir . $user_data['id'] . '.json';
            if (file_put_contents($file_path, json_encode($user_data))) {
                // Rediriger vers la page de connexion
                header('Location: login.php?registered=1');
                exit();
            } else {
                $error = "Erreur lors de la création du compte. Veuillez réessayer.";
            }
        }
    }
}

// Generate simple math captcha
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha_answer'] = $num1 + $num2;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css?v=<?php echo uniqid(); ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="logo">
            <a href="/projetweb_php/index.php" class="logo-text">
                <span class="logo-q">Q</span><span class="logo-u">U</span><span class="logo-i">I</span><span class="logo-z">ZZE</span><span class="logo-o">O</span>
            </a>
        </div>
        <div class="auth-box">
            <h1>Créer un compte Quizzeo</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Type de compte</label>
                    <select id="role" name="role" required>
                        <option value="">Sélectionnez un type de compte</option>
                        <option value="ecole" <?php echo (isset($_POST['role']) && $_POST['role'] === 'ecole') ? 'selected' : ''; ?>>École</option>
                        <option value="entreprise" <?php echo (isset($_POST['role']) && $_POST['role'] === 'entreprise') ? 'selected' : ''; ?>>Entreprise</option>
                        <option value="utilisateur" <?php echo (isset($_POST['role']) && $_POST['role'] === 'utilisateur') ? 'selected' : ''; ?>>Utilisateur simple</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Captcha: Combien font <?php echo $num1; ?> + <?php echo $num2; ?> ?</label>
                    <input type="number" name="captcha_response" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Créer mon compte</button>
            </form>
            
            <p class="auth-links">
                Déjà inscrit ? <a href="login.php">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
