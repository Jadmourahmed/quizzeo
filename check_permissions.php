<?php
require_once 'includes/config.php';

function checkAndCreateDirectory($path) {
    if (!file_exists($path)) {
        if (mkdir($path, 0777, true)) {
            echo "✓ Créé : $path\n";
        } else {
            echo "✗ Erreur lors de la création : $path\n";
        }
    } else {
        echo "✓ Existe déjà : $path\n";
    }
    
    if (is_writable($path)) {
        echo "✓ Accessible en écriture : $path\n";
    } else {
        echo "✗ NON accessible en écriture : $path\n";
    }
}

echo "Vérification des dossiers et permissions...\n\n";

// Vérifier le dossier data
checkAndCreateDirectory(DATA_PATH);

// Vérifier le dossier users
checkAndCreateDirectory(DATA_PATH . '/users');

// Vérifier le dossier quizzes
checkAndCreateDirectory(DATA_PATH . '/quizzes');

// Tester l'écriture d'un fichier
$test_file = DATA_PATH . '/test_write.txt';
if (file_put_contents($test_file, 'Test d\'écriture')) {
    echo "\n✓ Test d'écriture réussi\n";
    unlink($test_file);
} else {
    echo "\n✗ Échec du test d'écriture\n";
}

echo "\nTerminé !\n";
