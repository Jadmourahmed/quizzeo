<?php
session_start();
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/config.php';
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Mise à jour du profil
    if ($user) {
        $user['email'] = $email;
        if (!empty($current_password) && !empty($new_password)) {
            if (password_verify($current_password, $user['password'])) {
                $user['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                $success = "Profil mis à jour avec succès!";
            } else {
                $error = "Mot de passe actuel incorrect";
            }
        }
        file_put_contents(DATA_PATH . '/users/' . $_SESSION['user_id'] . '.json', json_encode($user));
        $success = "Profil mis à jour avec succès!";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Quizzeo</title>
    <link rel="stylesheet" href="/projetweb_php/css/style.css?v=<?php echo uniqid(); ?>"><?php // Consistent CSS link with unique identifier ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="profile-container">
            <h1>Mon Profil</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="profile-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel (laisser vide si inchangé)</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe (laisser vide si inchangé)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <div class="form-group">
                    <label>Rôle</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                </div>
                
                <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
