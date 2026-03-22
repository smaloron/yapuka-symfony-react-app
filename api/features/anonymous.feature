Feature: Un utilisateur anonyme ne peut pas faire grand chose

  Scenario: Lister les utilisateurs nécessite une authentification
  When j'envoie une requête http GET sur "/api/users" sans être authentifié
  Then le code de réponse est 401