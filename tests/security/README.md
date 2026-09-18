# Tests de sécurité ChirOrg

Les contrôles de sécurité sont répartis entre plusieurs niveaux :

- `backend/tests/Functional/Api/SecuriteOwaspApiTest.php` : authentification, injection, propriétés inattendues et autorisation ;
- `frontend/e2e/security.spec.js` : non-exécution d'une charge XSS persistée ;
- workflow `Security audit` : fuzzing léger du contrat OpenAPI avec Schemathesis.

## Correspondance OWASP

| Contrôle                                             | Référence                 |
| ---------------------------------------------------- | ------------------------- |
| JWT falsifié et authentification                     | OWASP A07:2025            |
| Accès d'un utilisateur aux fonctions administratives | OWASP A01:2025, API5:2023 |
| Identifiants et propriétés inattendues               | API1:2023, API3:2023      |
| Charges SQL traitées comme du texte                  | OWASP A05:2025            |
| Charge HTML affichée sans exécution                  | OWASP A05:2025            |
| Bornes et formats générés depuis OpenAPI             | API4:2023, A10:2025       |

Le fuzzing s'exécute exclusivement sur la pile Docker éphémère de la CI. Le mode par défaut se limite aux méthodes `GET`. Toute campagne incluant des mutations doit employer une base jetable et des identifiants temporaires.
