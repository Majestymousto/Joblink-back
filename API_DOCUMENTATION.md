# JobLink Niger — Documentation API

> Version actuelle : **v0.3** — Mise à jour : 25 Avril 2026

---

## 🔗 URL de base

```
http://localhost:8000/api
```

---

## 🔐 Authentification

Toutes les routes protégées nécessitent un token Bearer :

```
Authorization: Bearer votre_token_ici
Accept: application/json
X-Source: flutter-memoire
```

---

## 📦 Headers requis

| Header | Valeur | Obligatoire |
|---|---|---|
| `Accept` | `application/json` | ✅ |
| `Content-Type` | `application/json` | ✅ (POST/PUT) |
| `Authorization` | `Bearer {token}` | ✅ (routes protégées) |
| `X-Source` | `flutter-memoire` ou `web-memoire` | Recommandé |

---

## ⚠️ Codes d'erreur

| Code | Signification |
|---|---|
| `200` | Succès |
| `201` | Ressource créée |
| `400` | Requête invalide |
| `401` | Non authentifié |
| `403` | Non autorisé |
| `404` | Ressource introuvable |
| `409` | Conflit (ex: déjà postulé) |
| `422` | Erreur de validation |
| `500` | Erreur serveur |

---

## ✅ ENDPOINTS DISPONIBLES

---

### 🔐 Authentification

---

#### `POST /api/register`
Créer un nouveau compte.

**Body :**
```json
{
    "name": "Moussa Diallo",
    "email": "moussa@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "candidate"
}
```

> `role` accepte : `candidate` ou `employer`

**Réponse 201 :**
```json
{
    "token": "1|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com",
        "avatar": null,
        "role": "candidate",
        "profile": {
            "id": 1,
            "user_id": 1,
            "titre_poste": null,
            "bio": null,
            "competences": null,
            "buildcvpro_token": null,
            "buildcvpro_email": null
        }
    }
}
```

> ⚠️ Si `role = employer`, le compte est créé avec `statut: pending` et doit être validé par un admin avant de pouvoir se connecter.

---

#### `POST /api/login`
Se connecter avec email et mot de passe.

**Body :**
```json
{
    "email": "moussa@example.com",
    "password": "password123"
}
```

**Réponse 200 :**
```json
{
    "token": "2|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com",
        "role": "candidate",
        "profile": { ... }
    }
}
```

**Réponse 403 (employeur en attente) :**
```json
{
    "message": "Votre compte est en attente de validation.",
    "status": "pending"
}
```

---

#### `POST /api/auth/google`
Connexion ou inscription via Google. Flutter gère le login Google côté client et envoie le token à cette route.

**Body :**
```json
{
    "token": "google_access_token_ici",
    "role": "candidate"
}
```

> `role` est optionnel — utilisé uniquement si c'est un nouveau compte.

**Réponse 200 :**
```json
{
    "token": "3|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 2,
        "name": "Moussa Google",
        "email": "moussa@gmail.com",
        "avatar": "https://lh3.googleusercontent.com/...",
        "role": "candidate",
        "profile": { ... }
    }
}
```

---

#### `POST /api/logout`
🔒 **Protégé**

Révoque le token actuel.

**Réponse 200 :**
```json
{
    "message": "Déconnecté avec succès."
}
```

---

#### `GET /api/me`
🔒 **Protégé**

Retourne les informations de l'utilisateur connecté.

**Réponse 200 :**
```json
{
    "id": 1,
    "name": "Moussa Diallo",
    "email": "moussa@example.com",
    "avatar": null,
    "role": "candidate",
    "profile": {
        "titre_poste": "Développeur Web",
        "bio": "Passionné par le développement...",
        "competences": ["Laravel", "Vue.js"],
        "buildcvpro_connected": true
    }
}
```

---

### 📄 Offres d'emploi

---

#### `GET /api/job-offers`
🌐 **Public**

Liste des offres d'emploi actives avec filtres.

**Query params :**
| Param | Type | Description |
|---|---|---|
| `query` | string | Recherche par titre, description |
| `type_contrat` | string | cdi, cdd, stage, freelance, alternance |
| `secteur` | string | Secteur d'activité |
| `localisation` | string | Ville |
| `sort` | string | `recent` (défaut) ou `company` |

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "titre": "Développeur Web Full Stack",
            "excerpt": "Rejoignez notre équipe tech...",
            "type_contrat": "cdi",
            "secteur": "tech",
            "localisation": "Niamey",
            "salaire": "150 000 – 200 000 FCFA",
            "experience": "2 – 4 ans",
            "competences": ["Laravel", "Vue.js"],
            "vues": 12,
            "candidatures_count": 3,
            "employer": {
                "nom_entreprise": "Optimus Engineering",
                "logo": null,
                "ville": "Niamey"
            }
        }
    ],
    "total": 10,
    "per_page": 8,
    "current_page": 1
}
```

---

#### `GET /api/job-offers/{id}`
🌐 **Public**

Détail d'une offre. Incrémente automatiquement le compteur de vues.

**Réponse 200 :**
```json
{
    "data": {
        "id": 1,
        "titre": "Développeur Web Full Stack",
        "description": "<p>Description complète...</p>",
        "requirements": ["Bac+3 minimum", "2 ans d'expérience"],
        "perks": [
            { "label": "Salaire compétitif", "desc": "..." }
        ],
        "type_contrat": "cdi",
        "secteur": "tech",
        "localisation": "Niamey",
        "salaire": "150 000 – 200 000 FCFA",
        "experience": "2 – 4 ans",
        "competences": ["Laravel", "Vue.js"],
        "date_expiration": "2026-03-30",
        "statut": "active",
        "vues": 13,
        "employer": { ... }
    }
}
```

---

#### `POST /api/job-offers`
🔒 **Protégé** | 👔 **Employeur validé uniquement**

Créer une nouvelle offre d'emploi.

**Body :**
```json
{
    "titre": "Développeur Web Full Stack",
    "description": "<p>Description complète...</p>",
    "type_contrat": "cdi",
    "localisation": "Niamey",
    "secteur": "tech",
    "salaire": "150 000 FCFA",
    "experience": "2 – 4 ans",
    "competences": ["Laravel", "Vue.js"],
    "requirements": ["Bac+3 minimum"],
    "perks": [{ "label": "Salaire compétitif", "desc": "..." }],
    "date_expiration": "2026-06-30"
}
```

**Réponse 201 :**
```json
{
    "data": { ... },
    "message": "Offre créée avec succès !"
}
```

---

#### `PUT /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire uniquement**

Modifier une offre.

---

#### `DELETE /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire uniquement**

Supprimer une offre.

---

#### `POST /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat uniquement**

Sauvegarder une offre.

---

#### `DELETE /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat uniquement**

Retirer une offre des sauvegardes.

---

#### `GET /api/offres-sauvegardees`
🔒 **Protégé** | 👤 **Candidat uniquement**

Liste des offres sauvegardées.

---

### 📝 Candidatures

---

#### `POST /api/job-offers/{id}/apply`
🔒 **Protégé** | 👤 **Candidat uniquement**

Postuler à une offre.

**Body :**
```json
{
    "message": "Je suis très intéressé par ce poste...",
    "cv_path": "cv/mon-cv.pdf",
    "buildcvpro_cv_id": "cv_123"
}
```

> Envoyer soit `cv_path` (CV uploadé manuellement) soit `buildcvpro_cv_id` (CV depuis BuildCVPro).

**Réponse 201 :**
```json
{
    "data": {
        "id": 1,
        "status": "pending",
        "job_offer_id": 1,
        "candidate_id": 1
    },
    "message": "Candidature envoyée avec succès !"
}
```

---

#### `GET /api/mes-candidatures`
🔒 **Protégé** | 👤 **Candidat uniquement**

Liste de mes candidatures.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "status": "pending",
            "date": "25 Avr 2026",
            "message": "Je suis intéressé...",
            "job": {
                "id": 1,
                "titre": "Développeur Full Stack",
                "type_contrat": "CDI",
                "localisation": "Niamey",
                "entreprise": "Optimus Engineering"
            }
        }
    ]
}
```

---

#### `DELETE /api/candidatures/{id}`
🔒 **Protégé** | 👤 **Candidat uniquement**

Retirer une candidature. Possible uniquement si statut `pending`.

---

#### `GET /api/job-offers/{id}/candidats`
🔒 **Protégé** | 👔 **Employeur propriétaire uniquement**

Voir les candidats d'une offre.

---

#### `PUT /api/candidatures/{id}/statut`
🔒 **Protégé** | 👔 **Employeur uniquement**

Changer le statut d'une candidature.

**Body :**
```json
{
    "status": "interview"
}
```

> `status` accepte : `pending`, `interview`, `accepted`, `rejected`

---

### 👤 Profil

---

#### `GET /api/profil`
🔒 **Protégé**

Mon profil complet (candidat ou employeur).

---

#### `PUT /api/profil`
🔒 **Protégé**

Modifier mon profil.

**Body (candidat) :**
```json
{
    "name": "Moussa Diallo",
    "titre_poste": "Développeur Web",
    "bio": "Passionné par le développement...",
    "telephone": "+227 96 12 34 56",
    "localisation": "Niamey",
    "competences": ["Laravel", "Vue.js", "MySQL"]
}
```

---

#### `GET /api/candidats`
🔒 **Protégé** | 👔 **Employeur uniquement**

Liste des profils candidats.

---

#### `GET /api/candidats/{id}`
🔒 **Protégé** | 👔 **Employeur uniquement**

Voir le profil d'un candidat.

---

### 🔗 Intégration Build CV Pro

---

#### `GET /api/buildcvpro/check`
🔒 **Protégé** | 👤 **Candidat uniquement**

Vérifie si l'email du candidat connecté existe sur Build CV Pro. Utilisé pour la liaison automatique des comptes.

**Réponse 200 :**
```json
{
    "exists": true,
    "user": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com"
    }
}
```

---

#### `POST /api/buildcvpro/connect`
🔒 **Protégé** | 👤 **Candidat uniquement**

Connecter son compte Build CV Pro avec ses identifiants.

**Body :**
```json
{
    "email": "moussa@buildcvpro.com",
    "password": "password123"
}
```

**Réponse 200 :**
```json
{
    "message": "Compte BuildCVPro connecté avec succès !",
    "email": "moussa@buildcvpro.com"
}
```

---

#### `GET /api/buildcvpro/cvs`
🔒 **Protégé** | 👤 **Candidat connecté à Build CV Pro**

Récupérer la liste des CVs depuis Build CV Pro.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Mon CV Développeur",
            "template": "classic",
            "created_at": "2026-04-01"
        }
    ]
}
```

---

#### `DELETE /api/buildcvpro/disconnect`
🔒 **Protégé** | 👤 **Candidat uniquement**

Déconnecter son compte Build CV Pro.

---

## 🚧 ENDPOINTS À VENIR

| Endpoint | Description | Jour |
|---|---|---|
| `GET /api/messages` | Liste des conversations | J4 |
| `POST /api/messages` | Envoyer un message | J4 |
| `GET /api/admin/employers` | Gérer les entreprises | J4 |
| `POST /api/admin/employers/{id}/validate` | Valider une entreprise | J4 |

---

## 📊 Statuts des candidatures

| Statut | Description |
|---|---|
| `pending` | En attente de réponse |
| `interview` | Entretien programmé |
| `accepted` | Candidature acceptée |
| `rejected` | Candidature refusée |

---

## 👥 Rôles et permissions

| Action | Candidat | Employeur | Admin |
|---|---|---|---|
| Voir les offres | ✅ | ✅ | ✅ |
| Postuler | ✅ | ❌ | ❌ |
| Publier une offre | ❌ | ✅ | ✅ |
| Voir les candidatures | ❌ | ✅ | ✅ |
| Connecter Build CV Pro | ✅ | ❌ | ❌ |
| Valider un compte entreprise | ❌ | ❌ | ✅ |