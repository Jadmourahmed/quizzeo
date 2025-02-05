# Quizzeo - Plateforme de Quiz en PHP

Quizzeo est une application web de quiz développée en PHP qui permet aux écoles et aux entreprises de créer et gérer des quiz, et aux utilisateurs de participer à ces quiz.

## Fonctionnalités

- **Système d'authentification**
  - Inscription et connexion des utilisateurs
  - Différents rôles : utilisateur, école, entreprise

- **Gestion des Quiz**
  - Création de quiz avec questions à choix unique ou multiple
  - Attribution de points par question
  - Suivi des résultats
  - Option pour terminer un quiz

- **Interface Utilisateur**
  - Design moderne et responsive
  - Affichage des résultats en temps réel
  - Interface intuitive pour la création de quiz

## Installation

1. Clonez ce dépôt dans votre dossier web :
   ```bash
   git clone https://github.com/votre-username/quizzeo.git
   ```

2. Configurez votre serveur web (Apache) pour pointer vers le dossier du projet

3. Créez les dossiers nécessaires :
   ```bash
   mkdir -p data/quizzes data/users
   chmod 777 data/quizzes data/users
   ```

4. Copiez le fichier de configuration :
   ```bash
   cp includes/config.example.php includes/config.php
   ```

5. Modifiez le fichier `includes/config.php` selon vos besoins

## Structure du Projet

```
projetweb_php/
├── css/
│   └── style.css
├── data/
│   ├── quizzes/
│   └── users/
├── includes/
│   ├── config.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── create-quiz.php
├── index.php
├── login.php
├── quiz.php
├── quiz-results.php
└── register.php
```

## Technologies Utilisées

- PHP 7.4+
- HTML5
- CSS3
- JavaScript
- JSON (pour le stockage des données)

## Sécurité

- Protection contre les injections
- Validation des entrées utilisateur
- Sessions sécurisées
- Protection CSRF

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :

1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commit vos changements
4. Push vers la branche
5. Créer une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
