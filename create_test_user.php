<?php
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/config.php';

$test_user = [
    'id' => 'test_user',
    'email' => 'test@example.com',
    'password' => password_hash('test123', PASSWORD_DEFAULT),
    'role' => 'utilisateur',
    'active' => true
];

if (!is_dir(DATA_PATH . '/users')) {
    mkdir(DATA_PATH . '/users', 0777, true);
}

file_put_contents(DATA_PATH . '/users/test_user.json', json_encode($test_user));
echo "Utilisateur test créé avec succès!\n";
echo "Email: test@example.com\n";
echo "Mot de passe: test123\n";
