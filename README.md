# JobLink Niger — Backend API

> Plateforme de mise en relation professionnelle entre candidats et entreprises au Niger.

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-En%20développement-yellow?style=flat-square)

---

## 📋 À propos du projet

JobLink Niger est une plateforme de mise en relation professionnelle qui permet aux entreprises de publier des offres d'emploi et aux candidats de postuler. Le projet intègre également une connexion avec **Build CV Pro** pour permettre aux candidats d'importer leurs CVs directement.

### Projets liés
- **JobLink Frontend** — Vue.js (terminé)
- **JobLink Backend** — Laravel API REST (ce dépôt — en cours)
- **Build CV Pro** — [buildcvpro.com](https://buildcvpro.com) (SaaS de génération de CV)

---

## ✅ Avancement

### Jour 1-2 — Build CV Pro APIs
- [x] Laravel Sanctum installé et configuré
- [x] Google Auth via Socialite
- [x] Source tracking avec header `X-Source`
- [x] `POST /api/register`
- [x] `POST /api/login`
- [x] `POST /api/auth/google`
- [x] `POST /api/logout`
- [x] `GET /api/me`
- [x] `POST /api/check-email`
- [x] `GET /api/resumes`
- [x] `GET /api/resumes/{id}`
- [x] `POST /api/resumes`
- [x] `PUT /api/resumes/{id}`
- [x] `DELETE /api/resumes/{id}`
- [x] `GET /api/resumes/{id}/download` (PDF)
- [x] `GET /api/cover-letters`
- [x] `GET /api/cover-letters/{id}`
- [x] `POST /api/cover-letters`
- [x] `PUT /api/cover-letters/{id}`
- [x] `DELETE /api/cover-letters/{id}`

### Jour 3 — Backend JobLink (Auth)
- [x] Projet Laravel créé
- [x] Base de données MySQL configurée
- [x] Migrations créées et executées
  - [x] `users` (avec rôles candidate/employer/admin)
  - [x] `candidates`
  - [x] `employers`
  - [x] `job_offers`
  - [x] `applications`
  - [x] `saved_jobs`
  - [x] `messages`
- [x] Modèles créés (User, Candidate, Employer, JobOffer, Application, SavedJob, Message)
- [x] `POST /api/register` (candidat + employeur)
- [x] `POST /api/login` (avec vérification statut employeur)
- [x] `POST /api/auth/google`
- [x] `POST /api/logout`
- [x] `GET /api/me`
- [x] Profil candidat créé automatiquement à l'inscription
- [x] Profil employeur créé avec statut `pending` à l'inscription
- [x] Source tracking avec header `X-Source`

### Jour 4 — Offres & Candidatures
- [ ] `GET /api/job-offers` (liste publique avec filtres)
- [ ] `GET /api/job-offers/{id}` (détail)
- [ ] `POST /api/job-offers` (créer — entreprise)
- [ ] `PUT /api/job-offers/{id}` (modifier — entreprise)
- [ ] `DELETE /api/job-offers/{id}` (supprimer — entreprise)
- [ ] `POST /api/job-offers/{id}/apply` (postuler — candidat)
- [ ] `GET /api/mes-candidatures` (candidat)
- [ ] `DELETE /api/candidatures/{id}` (retirer — candidat)
- [ ] `GET /api/job-offers/{id}/candidats` (voir candidats — entreprise)
- [ ] `PUT /api/candidatures/{id}/statut` (changer statut — entreprise)

### Jour 5 — Intégration Build CV Pro
- [ ] `POST /api/buildcvpro/connect`
- [ ] `GET /api/buildcvpro/cvs`
- [ ] `DELETE /api/buildcvpro/disconnect`
- [ ] `GET /api/buildcvpro/check`
- [ ] Détection automatique par email

### Jour 6-7 — Flutter Build CV Pro
- [ ] Auth + liste CVs
- [ ] Génération CV + Lettre de motivation

### Jour 8-9 — Flutter JobLink
- [ ] Auth + offres + postuler
- [ ] Profil + import CV Build CV Pro

### Jour 10 — Finition
- [ ] Tests end-to-end
- [ ] Fix bugs critiques
- [ ] Préparation démo

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
php artisan serve
```

---

## 🔐 Authentification

L'API utilise **Laravel Sanctum** avec des tokens Bearer.

```
Authorization: Bearer votre_token_ici
```

### Source Tracking

Chaque requête doit inclure le header `X-Source` pour identifier l'origine :

| Valeur | Origine |
|---|---|
| `flutter-memoire` | App Flutter JobLink |
| `web-memoire` | Web Vue.js JobLink |
| `unknown` | Source inconnue ⚠️ |

---

## 👥 Rôles

| Rôle | Description |
|---|---|
| `candidate` | Peut parcourir les offres, postuler, gérer son profil |
| `employer` | Peut publier des offres, voir les candidatures (nécessite validation admin) |
| `admin` | Gère la plateforme, valide les comptes entreprises |

---

## 🔗 Intégration Build CV Pro

Les candidats peuvent connecter leur compte [Build CV Pro](https://buildcvpro.com) pour importer leurs CVs générés directement dans JobLink Niger.

---

## 📄 Documentation API

Voir le fichier `API_DOCUMENTATION.md` pour la documentation complète des endpoints disponibles.

---

## 👨‍💻 Développeur

Développé par **Majesty** dans le cadre du mémoire de fin d'études Bachelor (Bac+4).