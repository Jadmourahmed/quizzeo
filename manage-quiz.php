<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

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
                // Pour les utilisateurs simples, supprimer uniquement leurs propres réponses
                $new_responses = array_filter($quiz['responses'], function($response) {
                    return $response['user_id'] !== $_SESSION['user_id'];
                });
                $quiz['responses'] = array_values($new_responses);
                
                if (file_put_contents($quiz_file, json_encode($quiz))) {
                    $success = "Vos réponses ont été supprimées avec succès";
                } else {
                    $error = "Erreur lors de la suppression de vos réponses";
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
