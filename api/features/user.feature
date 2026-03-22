Feature: Liste des utilisateurs
  En tant qu'admin
  Je veux consulter la liste des utilisateurs
  Afin de savoir qui est connecté


  Background:
    Given un utilisateur existe avec l'email "demo@yapuka.dev" et le mot de passe "password"
    And Je suis connecté en tant que "demo@yapuka.dev"

  Scenario:  Un utilisateur connecté peut voir la liste des utilisateurs
    When j'envoie une requête http GET sur "/api/users"
    Then le code de la réponse est 200
    And la réponse contient au moins un utilisateur


