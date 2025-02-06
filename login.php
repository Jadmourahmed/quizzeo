<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Vérifier si les champs sont remplis
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Chercher l'utilisateur dans le dossier des utilisateurs
        $users_dir = DATA_PATH . '/users/';
        $found_user = false;
        
        if (is_dir($users_dir)) {
            $files = glob($users_dir . '*.json');
            foreach ($files as $file) {
                $user_data = json_decode(file_get_contents($file), true);
                if ($user_data && $user_data['email'] === $email) {
                    
                    if (password_verify($password, $user_data['password'])) {
                        if (isset($user_data['active']) && $user_data['active'] === false) {
                            $error = "Votre compte a été désactivé";
                        } else {
                            $_SESSION['user_id'] = $user_data['id'];
                            $_SESSION['email'] = $user_data['email'];
                            $_SESSION['role'] = $user_data['role'] ?? 'user'; // Default to 'user' if role not set
                            
                            // Debug logging
                            error_log("Login successful. Role: " . $_SESSION['role']);
                            
                            // Redirect based on role
                            if ($_SESSION['role'] === 'admin') {
                                header('Location: /projetweb_php/admin-dashboard.php');
                            } else {
                                header('Location: /projetweb_php/index.php');
                            }
                            exit();
                        }
                    } else {
                        $error = "Mot de passe incorrect";
                    }
                    $found_user = true;
                    break;
                }
            }
        }
        
        if (!$found_user) {
            $error = "Aucun compte trouvé avec cet email";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css?v=<?php echo uniqid(); ?>"><?php // Unique identifier to bypass cache ?>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo">
                <a href="/projetweb_php/index.php" class="logo-text">
                    <span class="logo-q">Q</span><span class="logo-u">U</span><span class="logo-i">I</span><span class="logo-z">ZZE</span><span class="logo-o">O</span>
                </a>
            </div>
            <h1>Connexion</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                <div class="alert alert-success">Inscription réussie ! Vous pouvez maintenant vous connecter.</div>
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
                
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
            
            <p class="auth-links">
                Pas encore de compte ? <a href="/projetweb_php/register.php">Créer un compte</a>
            </p>
        </div>
    </div>
</body>
</html>
