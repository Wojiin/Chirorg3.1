# Tests de charge ChirOrg

Le scénario `smoke.js` effectue uniquement des lectures authentifiées sur une instance isolée. Il ne doit jamais être lancé contre la production sans autorisation explicite.

## Exécution locale

Après démarrage de la pile de développement et création d'un compte dédié :

```bash
mkdir -p tests/load/results
docker run --rm --network host \
  -e API_BASE_URL=http://127.0.0.1:8080/api \
  -e TEST_EMAIL=charge@chirorg.test \
  -e TEST_PASSWORD='mot-de-passe-temporaire' \
  -e CHIRORG_VUS=2 \
  -e CHIRORG_DURATION=30s \
  -v "$PWD/tests/load:/scripts:ro" \
  -v "$PWD/tests/load/results:/results" \
  grafana/k6:1.8.1 run \
  --summary-export=/results/smoke-summary.json \
  /scripts/smoke.js
```

Le workflow `Performance` automatise cette procédure sur une base éphémère. Les seuils initiaux sont un taux d'erreur inférieur à 1 %, plus de 99 % de contrôles réussis et un p95 inférieur à 800 ms, ou 1 000 ms pour l'agrégat des programmes.
