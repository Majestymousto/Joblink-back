# JobLink Niger — Backend API

> Plateforme de mise en relation professionnelle entre candidats et entreprises au Niger.

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-En%20développement-yellow?style=flat-square)

---

## 📋 À propos du projet

JobLink Niger est une plateforme de mise en relation professionnelle qui permet aux entreprises de publier des offres d'emploi et aux candidats de consulter et postuler. Les entreprises peuvent également consulter les profils des candidats.

Ce dépôt contient uniquement le **backend Laravel (API REST)** du projet.

### Projets liés
- **JobLink Frontend** — Vue.js (dépôt séparé — terminé)
- **JobLink Mobile** — Flutter (à venir)
- **Build CV Pro** — [buildcvpro.com](https://buildcvpro.com) — intégration optionnelle pour importer des CVs

---

## ✅ Avancement

### Jour 3 — Auth + Base de données ✅
- [x] Projet Laravel créé
- [x] Base de données MySQL configurée
- [x] Migrations créées et exécutées
  - [x] `users` (avec rôles candidate / employer / admin)
  - [x] `candidates`
  - [x] `employers`
  - [x] `job_offers`
  - [x] `applications`
  - [x] `saved_jobs`
  - [x] `messages`
- [x] Modèles Eloquent (User, Candidate, Employer, JobOffer, Application, SavedJob, Message)
- [x] `POST /api/register` — candidat et employeur
- [x] `POST /api/login` — avec vérification statut employeur
- [x] `POST /api/auth/google` — testé et validé
- [x] `POST /api/logout`
- [x] `GET /api/me`
- [x] Profil candidat créé automatiquement à l'inscription
- [x] Profil employeur créé avec statut `pending` à l'inscription
- [x] Source tracking avec header `X-Source`

### Jour 4 — Offres & Candidatures ✅
- [x] `GET /api/job-offers` — liste publique avec filtres et pagination
- [x] `GET /api/job-offers/{id}` — détail avec compteur de vues
- [x] `POST /api/job-offers` — créer une offre (employeur validé)
- [x] `PUT /api/job-offers/{id}` — modifier une offre
- [x] `DELETE /api/job-offers/{id}` — supprimer une offre
- [x] `POST /api/job-offers/{id}/apply` — postuler à une offre
- [x] `GET /api/mes-candidatures` — mes candidatures (candidat)
- [x] `DELETE /api/candidatures/{id}` — retirer une candidature
- [x] `GET /api/job-offers/{id}/candidats` — voir les candidats (employeur)
- [x] `PUT /api/candidatures/{id}/statut` — changer le statut
- [x] `GET /api/profil` — mon profil
- [x] `PUT /api/profil` — modifier mon profil
- [x] `GET /api/candidats` — liste des candidats (employeur)
- [x] `GET /api/candidats/{id}` — profil d'un candidat (employeur)
- [x] `POST /api/job-offers/{id}/save` — sauvegarder une offre
- [x] `DELETE /api/job-offers/{id}/save` — retirer des sauvegardes
- [x] `GET /api/offres-sauvegardees` — liste des offres sauvegardées

### Jour 5 — Intégration Build CV Pro ✅
- [x] `GET /api/buildcvpro/check` — vérifier si email existe sur Build CV Pro
- [x] `POST /api/buildcvpro/connect` — connecter son compte Build CV Pro
- [x] `GET /api/buildcvpro/cvs` — récupérer ses CVs depuis Build CV Pro
- [x] `DELETE /api/buildcvpro/disconnect` — déconnecter Build CV Pro
- [x] Détection automatique par email
- [x] URL Build CV Pro configurable via `.env`

### Mobile 🔨
- [ ] Auth + offres + postuler
- [ ] Profil + import CV Build CV Pro

### Finition 🔨
- [ ] Tests end-to-end
- [ ] Fix bugs critiques
- [ ] Préparation démo mémoire

---

## 🏗️ Stack technique

| Élément | Technologie |
|---|---|
| Backend | Laravel 11 |
| Auth | Laravel Sanctum |
| Google Auth | Laravel Socialite |
| Base de données | MySQL 8 |
| Frontend | Vue.js (dépôt séparé) |
| Mobile | Flutter (à venir) |

---

## 👥 Rôles

| Rôle | Description |
|---|---|
| `candidate` | Parcourir les offres, postuler, gérer son profil |
| `employer` | Publier des offres, voir les candidatures (nécessite validation admin) |
| `admin` | Gérer la plateforme, valider les comptes entreprises |

---

## 🚀 Installation

```bash
# Cloner le projet
git clone https://github.com/ton-username/joblink-backend.git
cd joblink-backend

# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé de l'application
php artisan key:generate

# Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joblink
DB_USERNAME=ton_username
DB_PASSWORD=ton_password

# Lancer les migrations
php artisan migrate

# Démarrer le serveur
php artisan serve --port=8001
```

---

## 🔐 Authentification

L'API utilise **Laravel Sanctum** avec des tokens Bearer.

```
Authorization: Bearer votre_token_ici
Accept: application/json
```

### Source Tracking

Chaque requête doit inclure le header `X-Source` :

| Valeur | Origine |
|---|---|
| `web-memoire` | Web Vue.js JobLink |
| `unknown` | Source inconnue ⚠️ |

---

## 🔗 Intégration Build CV Pro

Par défaut l'API pointe vers la version production de Build CV Pro (`buildcvpro.com`).

Pour utiliser une version locale, ajoute dans ton `.env` :

```
BUILDCVPRO_URL=http://127.0.0.1:8000/api
```

---

## 📄 Documentation API

Voir [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) pour la documentation complète des endpoints disponibles.

---

## 👨‍💻 Développeur

Développé par **Majesty** dans le cadre du mémoire de fin d'études Bachelor (Bac+4).