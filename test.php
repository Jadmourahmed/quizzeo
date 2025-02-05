<?php
echo "Test de la page de déconnexion\n";
echo "Chemin du fichier logout.php : " . __DIR__ . "/logout.php\n";
echo "Le fichier existe : " . (file_exists(__DIR__ . "/logout.php") ? "Oui" : "Non") . "\n";
?>
