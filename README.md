# b-soi-site — thème WordPress de la webradio B-Soï

Thème enfant de **Twenty Twenty-Five**, versionné sur GitHub, développé pour [b-soi.fr](https://b-soi.fr/) :

- un **lecteur radio en direct** (widget RadioKing existant, réintégré via le Customizer et recoloré) ;
- une **page agenda** branchée sur le plugin **The Events Calendar**, restylée à la charte graphique ;
- une **palette de couleurs** enregistrée dans `theme.json`, disponible directement dans l'éditeur de blocs WordPress ;
- un environnement de **dev local Docker** (PHP 8.4 / MySQL 8.4, aligné sur l'hébergement Gandi de production) ;
- un flux **dev → staging → production** avec déploiement automatisé.

Le plan de dev complet (analyse technique, architecture des environnements, mécanisme de déploiement) est dans [`docs/PLAN-DEV.md`](docs/PLAN-DEV.md) — ce README couvre la prise en main pratique.

## Palette de couleurs

| Rôle | Couleur | Code |
|---|---|---|
| Texte principal / fonds sombres | Noir charbon | `#36454F` |
| Accent principal (boutons, live) | Orange feu | `#CC5500` |
| Accent doux (tags, badges) | Bleu désaturé 25 % | `#A6DCED` |
| Séparateurs, fonds intermédiaires | Gris moyen | `#7F7F7F` |
| Fond clair, zones de texte | Blanc cassé | `#FAFAFA` |
| Accent tertiaire | Beige chaud | `#D9CBAA` |
| Liens secondaires | Vert mousse | `#6B8E23` |

Définies dans `theme/webradio-child/theme.json` (palette de l'éditeur, appliquée automatiquement aux titres, boutons, liens du thème parent) et complétées par les variables CSS `--wr-*` dans `assets/css/components.css` pour les composants sur-mesure (lecteur radio, agenda).

## Arborescence du dépôt

```
b-soi-site/
├── theme/webradio-child/        ← thème enfant de Twenty Twenty-Five
│   ├── style.css                  (en-tête du thème, header "Template: twentytwentyfive")
│   ├── theme.json                  (palette + typographie + template custom "Agenda")
│   ├── functions.php
│   ├── templates/
│   │   ├── front-page.html         (accueil : diffuseur + agenda + actus, en blocs Gutenberg)
│   │   └── page-agenda.html        (page agenda, appliquée auto au slug "agenda")
│   ├── inc/
│   │   ├── customizer.php          (réglages diffuseur radio + réseaux sociaux)
│   │   └── radio-player.php        (shortcode [wr_radio_player])
│   └── assets/css/components.css
├── docker-compose.yml            ← environnement local (PHP 8.4 / MySQL 8.4)
├── .github/workflows/            ← déploiement staging / production (à finaliser, voir plan)
├── docs/PLAN-DEV.md
├── .gitignore
└── README.md
```

## Développement local

```bash
docker compose up -d
```

- Site : http://localhost:8080 (suivre l'installation WordPress à la première ouverture)
- phpMyAdmin : http://localhost:8081
- Le thème est monté en direct depuis `theme/webradio-child/` — toute modification (avec Claude Code ou à la main) est visible immédiatement après rechargement de la page, sans rebuild.

Une fois l'installation locale terminée :
1. **Apparence > Thèmes**, activer **WebRadio (enfant de Twenty Twenty-Five)**.
2. Installer le plugin **The Events Calendar** (**Extensions > Ajouter**) pour activer la page Agenda.
3. **Réglages > Permaliens**, choisir une structure autre que "Simple" (nécessaire pour que `/agenda/` fonctionne).
4. Créer une page intitulée **Agenda** (slug `agenda`) — le template dédié s'applique automatiquement.
5. **Apparence > Personnaliser > Diffuseur radio** : les valeurs par défaut reprennent déjà le widget RadioKing de b-soï recoloré ; ajuster si besoin.

## Déploiement

Voir [`docs/PLAN-DEV.md`](docs/PLAN-DEV.md) §6 pour le détail. Deux workflows GitHub Actions sont prévus (`deploy-staging.yml` sur push vers `develop`, `deploy-production.yml` sur push vers `main`), finalisés une fois le mécanisme confirmé côté Gandi (Git natif ou repli SFTP).

## Convention de branches

- `main` → production (`b-soi.fr`)
- `develop` → staging (`staging.b-soi.fr`, à créer)
- `feature/nom-du-changement` → branches de travail, fusionnées dans `develop` via Pull Request
