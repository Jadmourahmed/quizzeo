<?php
session_start();
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/config.php';
$project_root = dirname(__FILE__);
require_once $project_root . '/includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /projetweb_php/login.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $quiz_id = $_POST['quiz_id'] ?? '';
    
    if (empty($quiz_id)) {
        $error = "ID du quiz manquant";
    } else {
        $quiz_file = DATA_PATH . '/quizzes/' . $quiz_id . '.json';
        
        if (!file_exists($quiz_file)) {
            $error = "Quiz introuvable";
        } else {
            $quiz = json_decode(file_get_contents($quiz_file), true);
            
            // Vérifier si l'utilisateur a le droit de gérer ce quiz
            if ($_SESSION['role'] === 'utilisateur' && $action === 'clear_responses') {
                // Vérification supplémentaire des données
                if (!isset($quiz['creator_id']) || !isset($quiz['responses'])) {
                    $error = "Impossible de supprimer les réponses. Données du quiz invalides.";
                } else {
                    // Initialiser le tableau des réponses supprimées par l'utilisateur si non existant
                    if (!isset($quiz['deleted_user_responses'])) {
                        $quiz['deleted_user_responses'] = [];
                    }

                    // Trouver et déplacer les réponses de l'utilisateur
                    $remaining_responses = [];
                    foreach ($quiz['responses'] as $response) {
                        if ($response['user_id'] === $_SESSION['user_id']) {
                            // Ajouter la réponse aux réponses supprimées
                            $quiz['deleted_user_responses'][] = $response;
                        } else {
                            // Conserver les réponses des autres utilisateurs
                            $remaining_responses[] = $response;
                        }
                    }

                    // Mettre à jour les réponses du quiz
                    $quiz['responses'] = $remaining_responses;
                    
                    if (file_put_contents($quiz_file, json_encode($quiz))) {
                        $success = "Vos réponses ont été supprimées de votre compte";
                    } else {
                        $error = "Erreur lors de la suppression de vos réponses";
                    }
                }
            } elseif (in_array($_SESSION['role'], ['ecole', 'entreprise']) && $action === 'delete_quiz') {
                // Pour les écoles et entreprises, vérifier si c'est leur quiz
                if ($quiz['creator_id'] === $_SESSION['user_id']) {
                    if (unlink($quiz_file)) {
                        $success = "Le quiz et tous ses résultats ont été supprimés avec succès";
                    } else {
                        $error = "Erreur lors de la suppression du quiz";
                    }
                } else {
                    $error = "Vous n'avez pas les droits pour supprimer ce quiz";
                }
            }
        }
    }
}

// Rediriger avec un message
$redirect_url = '/projetweb_php/index.php';
if (!empty($success)) {
    $redirect_url .= '?success=' . urlencode($success);
} elseif (!empty($error)) {
    $redirect_url .= '?error=' . urlencode($error);
}

header('Location: ' . $redirect_url);
exit();
