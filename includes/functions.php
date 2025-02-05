<?php
// Fonction pour sauvegarder un utilisateur
function saveUser($userData) {
    $id = uniqid();
    $userData['id'] = $id;
    $filePath = DATA_PATH . '/users/' . $id . '.json';
    file_put_contents($filePath, json_encode($userData));
    return $id;
}

// Fonction pour récupérer un utilisateur
function getUser($id) {
    $filePath = DATA_PATH . '/users/' . $id . '.json';
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return null;
}

// Fonction pour sauvegarder un quiz
function saveQuiz($quizData) {
    $id = uniqid();
    $quizData['id'] = $id;
    $filePath = DATA_PATH . '/quizzes/' . $id . '.json';
    file_put_contents($filePath, json_encode($quizData));
    return $id;
}

// Fonction pour récupérer un quiz
function getQuiz($id) {
    $filePath = DATA_PATH . '/quizzes/' . $id . '.json';
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return null;
}

// Fonction pour lister les quiz d'un utilisateur
function getUserQuizzes($userId) {
    $quizzes = [];
    $files = glob(DATA_PATH . '/quizzes/*.json');
    
    foreach ($files as $file) {
        $quiz = json_decode(file_get_contents($file), true);
        if ($quiz['creator_id'] === $userId) {
            $quizzes[] = $quiz;
        }
    }
    
    return $quizzes;
}

// Fonction pour vérifier si un utilisateur est actif
function isUserActive($userId) {
    $user = getUser($userId);
    return $user && isset($user['active']) && $user['active'] === true;
}

// Fonction pour générer un lien de quiz
function generateQuizLink($quizId) {
    return 'quiz.php?id=' . $quizId;
}

// Fonction pour valider le rôle d'un utilisateur
function validateUserRole($role) {
    $validRoles = ['admin', 'ecole', 'entreprise', 'utilisateur'];
    return in_array($role, $validRoles);
}

// Fonction pour nettoyer les entrées utilisateur
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
