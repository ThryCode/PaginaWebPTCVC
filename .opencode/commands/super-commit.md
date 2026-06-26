---
description: "Limpia y organiza commits pendientes: ejecuta tests, squashea commits locales no pusheados, rebasea con remoto y prepara commits limpios para push sin colisiones."
---

## Super Commit — Flujo completo

### 1. Diagn&oacute;stico
Ejecuta en orden:
- `git status` — cambios sin stage, archivos sin trackear
- `git log --oneline -20` — historia reciente
- `git log --oneline @{u}..HEAD` — commits locales NO pusheados
- `git stash list` — stashes pendientes

### 2. Tests
Ejecuta `php vendor/bin/phpunit tests/`
- Si fallan: detener y reportar. NO continuar.
- Solo continuar si pasan.

### 3. Staging inteligente
Si hay cambios sin stage:
- NO stager archivos no relacionados autom&aacute;ticamente
- Si son cambios menores (typos, whitespace), stagearlos con el commit anterior via `git commit --amend`
- Si son cambios sustanciales, preguntar al usuario c&oacute;mo agruparlos

### 4. Rebase limpiador (si hay commits locales NO pusheados)
- Identificar cu&aacute;ntos commits locales no est&aacute;n en remoto
- Si son varios commits pequeños/desordenados:
  - Squashear los commits con mensajes relacionados
  - Dejar commits separados solo si representan cambios tem&aacute;ticamente distintos
- NO hacer rebase si hay conflictos potenciales altos

### 5. Rebase con remoto
- `git fetch origin`
- Si `HEAD` est&aacute; detr&aacute;s de `origin/main` (o rama actual):
  - `git rebase origin/{rama}` para evitar merge commits
- Si hay conflictos: detener y reportar

### 6. Tests post-rebase
Ejecutar tests de nuevo despu&eacute;s del rebase.

### 7. Resumen final
```
## Resumen Super Commit
- Commits locales antes: {n} → Despu&eacute;s: {n}
- Tests: {PASS/FAIL}
- Staged: {archivos}
- Push listo: {S&iacute;/No — conflicto pendiente}
- Rama: {nombre}

### Commits resultantes
{lista de commits finales}

### Pr&oacute;ximo paso sugerido
git push
```
