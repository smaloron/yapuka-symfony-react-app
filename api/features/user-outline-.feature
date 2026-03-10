Feature: Des utilisateurs authentifiés peuvent agir sur le système

  Scenario Outline: Les utilisateurs suivants peuvent voir la liste
    Given un utilisateur existe avec l'email "<email>" et le mot de passe "password"
    When j'envoie une requête authentifiée GET sur "/api/users"
    Then le code de réponse est 200
    And la réponse contient un utilisateur avec l'email "<email>"

    Examples:

      | email             |
      | demo@yapuka.dev   |
      | user2@yapuka.dev  |
      | user3@yapuka.dev  |