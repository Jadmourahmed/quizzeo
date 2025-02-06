<?php
$files = [
    'register.php',
    'quiz.php',
    'quiz-results.php',
    'profile.php',
    'manage-quiz.php',
    'login.php',
    'create_test_user.php',
    'create-quiz.php',
    'check_permissions.php',
    'admin-dashboard.php',
    'index.php',
    'enter-quiz.php'
];

foreach ($files as $file) {
    $filepath = __DIR__ . '/' . $file;
    $content = file_get_contents($filepath);
    
    // Replace relative includes with absolute path includes
    $updated_content = preg_replace(
        [
            '/require_once\s*[\'"]includes\/config\.php[\'"]/m', 
            '/require_once\s*[\'"]includes\/functions\.php[\'"]/m'
        ],
        [
            '$project_root = dirname(__FILE__);' . "\n" . 'require_once $project_root . \'/includes/config.php\'',
            '$project_root = dirname(__FILE__);' . "\n" . 'require_once $project_root . \'/includes/functions.php\''
        ],
        $content
    );
    
    // Add project_root definition if not already present
    if (strpos($updated_content, '$project_root = dirname(__FILE__);') === false) {
        $updated_content = preg_replace('/^<\?php\s*/', "<?php\n// Determine the absolute path to the project root\n\$project_root = dirname(__FILE__);\n", $updated_content);
    }
    
    file_put_contents($filepath, $updated_content);
    echo "Updated $file\n";
}
echo "All files updated successfully!";
?>
