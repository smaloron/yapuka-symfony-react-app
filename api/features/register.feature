Feature: Inscription d'un utilisateur

Scenario: Inscription réussie avec des données valides
When je m'inscrit sur le site :

    """
    {
      "email": "nouveau@yapuka.dev",
      "username": "Nouveau User",
      "password": "password123"
    }
    """

Then le code de réponse est 201
And la réponse JSON contient la clé "token"

Scenario: Connexion réussie avec des identifiants valides
When j'envoie une requête authentifiée POST sur "/api/auth/login" avec le corps :

    """
    {
      "email": "nouveau@yapuka.dev",
      "password": "password123"
    }
    """

Then le code de réponse est 200
And la réponse JSON contient la clé "token"