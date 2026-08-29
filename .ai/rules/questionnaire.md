---
paths:
  - 'resources/views/questionnaire/**'
---

# Questionnaire

## Ne jamais re-encoder old() JSON dans les champs cachés
old('project_types') renvoie la string JSON brute du dernier POST. Le passer tel quel à json_encode() double-encode la valeur et casse la validation (project_types.*.in). Normaliser côté contrôleur (json_decode si string) et passer un tableau à la vue. De plus, le home présélectionne tous les types sauf « autre » : aucun champ de précision n'existe là, donc sélectionner « autre » rend other_description obligatoire et bloque l'envoi.
