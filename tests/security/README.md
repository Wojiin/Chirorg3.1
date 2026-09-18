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

Le fuzzing s'exécute exclusivement sur la pile Docker éphémère de la CI. Il se limite aux méthodes `GET`, utilise les identifiants déterministes des fixtures définis dans `schemathesis.toml` et vérifie explicitement deux propriétés de sécurité : aucune entrée ne doit provoquer d'erreur serveur et les valeurs invalides décrites par OpenAPI doivent être rejetées.

Les paramètres de pagination sont fixés à des valeurs sûres pendant cette campagne : API Platform borne déjà `itemsPerPage` côté serveur en ramenant les dépassements au maximum configuré, tandis que le contrôle Schemathesis attend par défaut un rejet HTTP. La limite serveur reste donc active sans produire ce faux positif.

La conformité complète du contrat OpenAPI (statuts et schémas de réponse) constitue un contrôle distinct. Elle n'est volontairement pas mélangée à ce job afin qu'un écart documentaire ne masque pas une régression de sécurité. Toute campagne incluant des mutations doit employer une base jetable et des identifiants temporaires.
