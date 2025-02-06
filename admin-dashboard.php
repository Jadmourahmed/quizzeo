<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Ensure only admin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php?error=unauthorized');
    exit();
}

// Handle account actions
$message = '';
$error = '';

// Delete user
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $users_dir = DATA_PATH . '/users/';
    $user_file = $users_dir . $user_id . '.json';
    
    if (file_exists($user_file)) {
        if (unlink($user_file)) {
            $message = "Utilisateur supprimé avec succès.";
        } else {
            $error = "Impossible de supprimer l'utilisateur.";
        }
    }
}

// Toggle user status
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $users_dir = DATA_PATH . '/users/';
    $user_file = $users_dir . $user_id . '.json';
    
    if (file_exists($user_file)) {
        $user_data = json_decode(file_get_contents($user_file), true);
        
        // Toggle the active status
        $user_data['active'] = !($user_data['active'] ?? true);
        
        if (file_put_contents($user_file, json_encode($user_data))) {
            $message = "Statut du compte modifié avec succès.";
        } else {
            $error = "Impossible de modifier le statut du compte.";
        }
    }
}

// Fetch all users
$users = [];
$users_dir = DATA_PATH . '/users/';
if (is_dir($users_dir)) {
    $user_files = glob($users_dir . '*.json');
    foreach ($user_files as $file) {
        $user_data = json_decode(file_get_contents($file), true);
        if ($user_data && $user_data['id'] !== 'admin') {  // Exclude admin account
            $users[] = $user_data;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - Quizzeo</title>
    <style>
        body {
            background-color: #f4f6f8;
            color: #333333;
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
        }
        .admin-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
        }
        .admin-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            margin-right: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-danger:hover {
            background-color: #a71d2a;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #333333;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .admin-actions {
            text-align: center;
            margin-top: 20px;
        }
        .btn-primary {
            background-color: #4A90E2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tableau de Bord Admin</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php echo ($user['active'] ?? true) ? 'Actif' : 'Désactivé'; ?>
                        </td>
                        <td>
                            <a href="?action=toggle_status&user_id=<?php echo urlencode($user['id']); ?>" 
                               class="btn btn-warning" 
                               onclick="return confirm('Voulez-vous vraiment changer le statut de ce compte ?')">
                                <?php echo ($user['active'] ?? true) ? 'Désactiver' : 'Activer'; ?>
                            </a>
                            <a href="?action=delete&user_id=<?php echo urlencode($user['id']); ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Voulez-vous vraiment supprimer ce compte ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="admin-actions">
            <a href="logout.php" class="btn btn-primary">Déconnexion</a>
        </div>
    </div>
</body>
</html>
