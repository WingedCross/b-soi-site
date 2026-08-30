# Plan de dev — Refonte de b-soi.fr (WordPress + GitHub)

Statut : **validé pour lancement de l'implémentation** (en attente de ton feu vert sur les points marqués ⚠️)
Dernière mise à jour : 24/08/2026

---

## 1. Objectif du projet

Faire évoluer [b-soi.fr](https://b-soi.fr/) — actuellement un WordPress avec le thème gratuit *Radio Station* fraîchement installé — vers :

- un **thème enfant custom** basé sur **Twenty Twenty-Five** (dernier thème par défaut WordPress disponible — WordPress n'a finalement pas sorti de "Twenty Twenty-Six", voir [twentig.com](https://twentig.com/wordpress-twenty-twenty-six/)), appliquant la nouvelle charte graphique (charbon, orange feu, bleu désaturé, gris, blanc cassé, beige, vert mousse) ;
- un **diffuseur radio en direct** (widget RadioKing existant, ré-intégré proprement et re-stylé) ;
- un **agenda d'événements** (plugin *The Events Calendar*, actuellement absent) ;
- un **code versionné sur GitHub**, avec un flux **dev local → staging → production** et un **déploiement automatisé** vers l'hébergement Gandi ;
- un site que le propriétaire de la webradio continue de gérer **au quotidien depuis l'admin WordPress standard** (contenu, événements), pendant que les évolutions techniques passent par **Claude Code → GitHub → déploiement auto**.

## 2. État des lieux (analyse technique de l'existant)

| Élément | Constat |
|---|---|
| Thème actif | *Radio Station* (thème gratuit, installé ce matin — pas d'historique à préserver dessus) |
| Thème de base retenu | *Twenty Twenty-Five*, déjà installé sur le site |
| Diffuseur radio | Widget **RadioKing**, intégré en HTML brut dans une page *Diffuseur* (iframe + script, pas de plugin) — station : `b-soi`, couleurs actuelles `#1e7fcb` / `#ffffff` |
| Agenda événements | Aucun plugin installé, aucune donnée existante à migrer |
| Hébergement | Gandi — Hébergement Web, PHP 8.4, MySQL 8.4, accès SFTP confirmé, accès **SSH présent mais sans clé configurée** |
| Accès Git natif Gandi | Probable (Gandi expose un accès `ssh+git://` sur ses hébergements web) mais **non encore vérifié** ⚠️ — à valider en tout premier lieu de l'implémentation |
| Sous-domaine de staging | Aucun pour l'instant, à créer |
| Environnement local | Aucun (tu interviens pour le compte du propriétaire, pas d'installation WordPress locale existante) |
| Sauvegarde | En cours de réalisation de ton côté |
| Dépôt GitHub | Compte créé, dépôt à initialiser avec la structure ci-dessous |

## 3. Architecture cible

### 3.1 Le thème

Thème **enfant** de Twenty Twenty-Five plutôt que thème 100 % from-scratch :

- WordPress continue de maintenir le thème parent (sécurité, compatibilité éditeur de blocs) ;
- on ne surcharge que ce qui change réellement : `theme.json` (palette, typographie), `style.css`, quelques templates (`front-page.html` ou `.php` selon l'approche classique/FSE retenue), les fichiers `inc/` pour le lecteur radio et l'agenda ;
- le prototype déjà livré (palette `theme.json`, styles `.wr-*`, shortcode `[wr_radio_player]`, template agenda) est directement réutilisable — il sera **adapté en thème enfant** au lieu de thème autonome lors de l'implémentation.

### 3.2 Lecteur radio

On conserve RadioKing (aucune raison d'en changer, le flux fonctionne déjà) mais on :
- déplace la configuration dans le **Customizer** (`Diffuseur radio`) plutôt qu'en HTML brut dans une page, pour que la couleur du widget suive la charte sans retoucher le code à chaque fois ;
- régénère l'URL d'intégration RadioKing avec `c=%23CC5500` (orange feu) et `c2=%23FAFAFA` (blanc cassé) à la place de `#1e7fcb`/`#ffffff` ;
- garde la page *Diffuseur* existante fonctionnelle pendant la transition (aucune rupture de service pendant le chantier).

### 3.3 Agenda

Installation de **The Events Calendar** (gratuit, WordPress.org) + template `page-agenda.php` déjà prototypé, restylé à la charte. Comme il n'y a aucune donnée existante, aucune migration n'est nécessaire — c'est le point le plus simple du projet.

## 4. Environnements

```
Local (Docker)  --push feature/*-->  GitHub  --PR & merge-->  develop  --deploy auto-->  staging.b-soi.fr
                                                   |
                                                   PR & merge (après validation staging)
                                                   v
                                                 main  --deploy auto-->  b-soi.fr (prod)
```

**Local** — aucun environnement WordPress local n'existe aujourd'hui ; comme le flux prévu est *Claude Code → commit → push*, il est fortement recommandé d'avoir un WordPress local pour itérer vite sans toucher au staging à chaque micro-changement. Proposition : un `docker-compose.yml` avec les mêmes versions que la prod (PHP 8.4, MySQL 8.4), lancé en une commande, aucune installation de WordPress "en dur" nécessaire sur ta machine. Ce sera mis en place à la première étape de l'implémentation.

**Staging** — un sous-domaine (proposition : `staging.b-soi.fr`) rattaché en *virtual host* supplémentaire sur le même hébergement Gandi (Gandi permet de lier plusieurs adresses/vhosts à un hébergement, voir [docs.gandi.net](https://docs.gandi.net/fr/hebergement_web/gestion_hebergement/gerer_hwg.html)), avec sa propre base de données WordPress (copie de la prod). Déployé automatiquement à chaque merge sur `develop`. C'est là que le propriétaire de b-soï (et toi) validez visuellement chaque changement avant qu'il ne touche le site réel.

**Production** — `b-soi.fr` tel qu'il existe aujourd'hui. Déployé automatiquement uniquement depuis `main`, donc uniquement après une validation manuelle sur staging.

⚠️ Point à vérifier ensemble en tout début d'implémentation : que l'offre Gandi actuelle permette bien d'ajouter un second vhost sur le même hébergement (selon le plan souscrit, ça peut nécessiter une petite mise à niveau). Si ce n'est pas possible, deux solutions de repli existent : un sous-dossier protégé par mot de passe (`b-soi.fr/staging`) ou une validation uniquement en local avant merge sur `main` (staging "virtuel").

## 5. Dépôt GitHub

```
b-soi-site/
├── theme/webradio-child/       ← thème enfant Twenty Twenty-Five
│   ├── style.css                (en-tête avec "Template: twentytwentyfive")
│   ├── theme.json                (palette + typographie, surcharge du parent)
│   ├── functions.php
│   ├── inc/
│   │   ├── customizer.php        (réglages diffuseur radio + réseaux sociaux)
│   │   └── radio-player.php      (shortcode [wr_radio_player])
│   ├── page-agenda.php
│   └── assets/
├── docker-compose.yml            ← environnement local (PHP 8.4 / MySQL 8.4)
├── .github/workflows/
│   ├── deploy-staging.yml        (déclenché sur push develop)
│   └── deploy-production.yml     (déclenché sur push main)
├── docs/
│   └── PLAN-DEV.md               ← ce document
└── README.md
```

**Ce qui est versionné** : uniquement le code du thème (+ workflows + docs). **Ce qui ne l'est jamais** : `wp-config.php`, le dossier `uploads/`, la base de données, les identifiants — ces éléments restent propres à chaque environnement (déjà couvert par le `.gitignore` du prototype).

**Convention de branches** :
- `main` → production (`b-soi.fr`)
- `develop` → staging (`staging.b-soi.fr`)
- `feature/nom-du-changement` → branches de travail pour Claude Code, fusionnées dans `develop` via Pull Request

## 6. Déploiement automatisé

### 6.1 Vérification préalable (étape 0 de l'implémentation)

1. Générer une paire de clés SSH dédiée au déploiement (`ssh-keygen -t ed25519 -C "deploy-b-soi"`).
2. Ajouter la **clé publique** dans le panneau Gandi (section accès SSH de l'hébergement).
3. Tester en local : `ssh {web_hosting_id}@git.{datacenter_id}.gpaas.net` — si ça répond, l'accès Git natif Gandi est confirmé et on l'utilise (voir §6.2). Sinon, on bascule sur un déploiement SFTP classique (repli déjà prototypé dans la première version livrée).

### 6.2 Scénario retenu si le Git natif Gandi fonctionne (préférable)

Chaque environnement (staging/prod) a son propre dépôt Git côté Gandi. Le workflow GitHub Actions, à chaque push sur la branche correspondante :
1. checkout du code ;
2. push du dossier `theme/webradio-child` vers le remote Gandi (`ssh+git://{web_hosting_id}@git.{datacenter}.gpaas.net/{repo}.git`) ;
3. déclenche la mise en ligne via la commande `ssh ... "deploy {repo}.git"`.

Avantage : déploiement versionné côté serveur, possibilité de revenir en arrière facilement.

### 6.3 Scénario de repli (SFTP)

Si le Git natif n'est finalement pas disponible sur l'offre actuelle : on reprend le workflow SFTP déjà prêt dans le prototype initial (`FTP-Deploy-Action`), avec des secrets GitHub distincts pour staging et production (`STAGING_SFTP_*` / `PROD_SFTP_*`).

## 7. Sauvegarde & sécurité

- Backup fichiers + base de données avant toute action sur le site en cours (déjà en cours de ton côté — à confirmer terminé avant qu'on touche à quoi que ce soit sur `b-soi.fr`).
- Aucune action n'est faite directement sur la production : tout passe par staging d'abord.
- Le déploiement en production nécessite une Pull Request `develop → main` validée manuellement (pas de merge automatique).

## 8. Migration du contenu existant

- Recréer l'équivalent de la page *Diffuseur* avec le nouveau shortcode `[wr_radio_player]` (couleurs mises à jour) — l'ancienne page reste en place jusqu'à validation complète sur staging, puis peut être supprimée ou redirigée.
- Vérifier les menus, widgets et pages existantes du thème *Radio Station* à recréer manuellement dans le nouveau thème (le thème ayant été installé ce matin, l'impact devrait être minime).
- Aucune donnée d'agenda à migrer (fonctionnalité inexistante actuellement).

## 9. Flux de travail au quotidien (une fois le projet en place)

**Pour le propriétaire de b-soï** : rien ne change dans ses habitudes — il continue de créer ses articles et, une fois *The Events Calendar* installé, ses événements, directement depuis l'admin WordPress.

**Pour les évolutions techniques (toi + Claude Code)** :
1. développement en local avec Claude Code (environnement Docker) ;
2. commit + push sur une branche `feature/...` ;
3. Pull Request vers `develop` → déploiement automatique sur `staging.b-soi.fr` ;
4. validation visuelle (toi, éventuellement le propriétaire) ;
5. Pull Request `develop → main` → déploiement automatique sur `b-soi.fr`.

## 10. Prochaines actions (dans l'ordre, phase d'implémentation)

1. Confirmer la fin de la sauvegarde du site actuel.
2. Vérifier l'accès Git natif Gandi (§6.1) — détermine le mécanisme de déploiement définitif.
3. Créer le sous-domaine de staging et le lier en vhost sur l'hébergement Gandi.
4. Initialiser le dépôt GitHub avec la structure du §5 (adaptation du prototype en thème enfant Twenty Twenty-Five).
5. Mettre en place l'environnement Docker local.
6. Configurer les secrets et les deux workflows GitHub Actions (staging + production).
7. Développer le thème enfant : palette, lecteur radio (Customizer + shortcode), page agenda.
8. Installer et configurer *The Events Calendar* sur staging.
9. Déployer et valider sur staging avec le propriétaire de la radio.
10. Déployer en production, courte session de prise en main pour le propriétaire si besoin.

---

Dis-moi si ce plan te convient tel quel, ou s'il y a des points à ajuster (notamment le §4 sur le staging et le §6 sur le mécanisme de déploiement, qui dépendent de vérifications à faire côté Gandi) — dès validé, on attaque l'étape 1.

**Sources consultées** :
- [Twenty Twenty-Six Theme: Why It Doesn't Exist — twentig.com](https://twentig.com/wordpress-twenty-twenty-six/)
- [Comment utiliser Git avec votre Hébergement Web — Gandi Documentation](https://docs.gandi.net/en/web_hosting/connection/git.html)
- [Comment gérer un Hébergement Web Gandi — Gandi Documentation](https://docs.gandi.net/fr/hebergement_web/gestion_hebergement/gerer_hwg.html)
