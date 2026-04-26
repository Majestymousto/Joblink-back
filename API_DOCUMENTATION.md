# JobLink Niger — Documentation API

> Version : **v1.0** — Mise à jour : 26 Avril 2026
>
> ✅ Tous les endpoints documentés ici sont testés et validés.

---

## 🔗 URL de base

```
http://localhost:8001/api
```

---

## 📦 Headers requis

| Header | Valeur | Obligatoire |
|---|---|---|
| `Accept` | `application/json` | ✅ |
| `Content-Type` | `application/json` | ✅ (POST/PUT) |
| `Authorization` | `Bearer {token}` | ✅ (routes protégées) |
| `X-Source` | `web-memoire` | Recommandé |

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

## 👥 Rôles

| Rôle | Symbole | Description |
|---|---|---|
| Candidat | 👤 | Peut postuler, gérer son profil |
| Employeur | 👔 | Peut publier des offres, voir les candidatures |
| Public | 🌐 | Accessible sans authentification |

---

## 🔐 AUTHENTIFICATION

---

### `POST /api/register`
🌐 **Public** — Créer un nouveau compte.

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

**Réponse 201 — Candidat :**
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
            "telephone": null,
            "localisation": null,
            "competences": null,
            "cv_path": null,
            "buildcvpro_token": null,
            "buildcvpro_email": null
        }
    }
}
```

**Réponse 201 — Employeur :**
```json
{
    "token": "2|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 2,
        "name": "Optimus Engineering",
        "email": "optimus@example.com",
        "avatar": null,
        "role": "employer",
        "profile": {
            "id": 1,
            "nom_entreprise": "Optimus Engineering SARL",
            "statut": "pending"
        }
    }
}
```
> ⚠️ Le compte employeur est créé avec `statut: pending`. Il doit être validé par un admin avant de pouvoir se connecter.

---

### `POST /api/login`
🌐 **Public** — Se connecter avec email et mot de passe.

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
    "token": "3|xxxxxxxxxxxxxxxx",
    "user": { ... }
}
```

**Réponse 403 — Employeur en attente :**
```json
{
    "message": "Votre compte est en attente de validation.",
    "status": "pending"
}
```

---

### `POST /api/auth/google`
🌐 **Public** — Connexion ou inscription via Google.

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
    "token": "4|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 3,
        "name": "Moussa Google",
        "email": "moussa@gmail.com",
        "avatar": "https://lh3.googleusercontent.com/...",
        "role": "candidate",
        "profile": { ... }
    }
}
```

---

### `POST /api/logout`
🔒 **Protégé** — Révoque le token actuel.

**Réponse 200 :**
```json
{
    "message": "Déconnecté avec succès."
}
```

---

### `GET /api/me`
🔒 **Protégé** — Retourne les informations de l'utilisateur connecté.

**Réponse 200 :**
```json
{
    "id": 1,
    "name": "Moussa Diallo",
    "email": "moussa@example.com",
    "avatar": null,
    "role": "candidate",
    "profile": { ... }
}
```

---

## 📄 OFFRES D'EMPLOI

---

### `GET /api/job-offers`
🌐 **Public** — Liste des offres actives avec filtres et pagination (8 par page).

**Query params :**
| Param | Type | Description |
|---|---|---|
| `query` | string | Recherche par titre ou description |
| `type_contrat` | string | cdi, cdd, stage, freelance, alternance |
| `secteur` | string | Secteur d'activité |
| `localisation` | string | Ville |
| `sort` | string | `recent` (défaut) ou `company` |

**Réponse 200 :**
```json
{
    "current_page": 1,
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
            "vues": 5,
            "candidatures_count": 2,
            "employer": {
                "nom_entreprise": "Optimus Engineering SARL"
            }
        }
    ],
    "total": 10,
    "per_page": 8
}
```

---

### `GET /api/job-offers/{id}`
🌐 **Public** — Détail d'une offre. Incrémente automatiquement le compteur de vues.

**Réponse 200 :**
```json
{
    "data": {
        "id": 1,
        "titre": "Développeur Web Full Stack",
        "description": "<p>Description complète...</p>",
        "requirements": ["Bac+3 minimum", "2 ans d'expérience"],
        "perks": [{ "label": "Salaire compétitif", "desc": "..." }],
        "type_contrat": "cdi",
        "localisation": "Niamey",
        "salaire": "150 000 – 200 000 FCFA",
        "vues": 6,
        "employer": { ... }
    }
}
```

---

### `POST /api/job-offers`
🔒 **Protégé** | 👔 **Employeur validé uniquement** — Créer une offre.

**Body :**
```json
{
    "titre": "Développeur Web Full Stack",
    "description": "<p>Description...</p>",
    "excerpt": "Résumé court...",
    "type_contrat": "cdi",
    "localisation": "Niamey",
    "secteur": "tech",
    "salaire": "150 000 FCFA",
    "experience": "2 – 4 ans",
    "competences": ["Laravel", "Vue.js"],
    "requirements": ["Bac+3 minimum"],
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

### `PUT /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire uniquement** — Modifier une offre.

**Réponse 200 :**
```json
{
    "message": "Offre mise à jour !"
}
```

---

### `DELETE /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire uniquement** — Supprimer une offre.

**Réponse 200 :**
```json
{
    "message": "Offre supprimée !"
}
```

---

### `POST /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat uniquement** — Sauvegarder une offre.

**Réponse 200 :**
```json
{
    "message": "Offre sauvegardée !"
}
```

---

### `DELETE /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat uniquement** — Retirer une offre des sauvegardes.

**Réponse 200 :**
```json
{
    "message": "Offre retirée des sauvegardes !"
}
```

---

### `GET /api/offres-sauvegardees`
🔒 **Protégé** | 👤 **Candidat uniquement** — Liste des offres sauvegardées.

---

## 📝 CANDIDATURES

---

### `POST /api/job-offers/{id}/apply`
🔒 **Protégé** | 👤 **Candidat uniquement** — Postuler à une offre.

**Body :**
```json
{
    "message": "Je suis très intéressé par ce poste...",
    "cv_path": "cv/mon-cv.pdf",
    "buildcvpro_cv_id": "9"
}
```
> Envoyer soit `cv_path` soit `buildcvpro_cv_id`.

**Réponse 201 :**
```json
{
    "data": {
        "id": 1,
        "candidate_id": 1,
        "job_offer_id": 2,
        "status": "pending",
        "message": "Je suis très intéressé..."
    },
    "message": "Candidature envoyée avec succès !"
}
```

**Réponse 409 — Déjà postulé :**
```json
{
    "message": "Vous avez déjà postulé à cette offre."
}
```

---

### `GET /api/mes-candidatures`
🔒 **Protégé** | 👤 **Candidat uniquement** — Liste de mes candidatures.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "status": "pending",
            "date": "26 Apr 2026",
            "message": "Je suis très intéressé...",
            "job": {
                "id": 2,
                "titre": "Développeur Web Full Stack",
                "type_contrat": "cdi",
                "localisation": "Niamey",
                "entreprise": "Optimus Engineering SARL"
            }
        }
    ]
}
```

---

### `DELETE /api/candidatures/{id}`
🔒 **Protégé** | 👤 **Candidat uniquement** — Retirer une candidature.

> ⚠️ Possible uniquement si statut = `pending`.

**Réponse 200 :**
```json
{
    "message": "Candidature retirée."
}
```

---

### `GET /api/job-offers/{id}/candidats`
🔒 **Protégé** | 👔 **Employeur propriétaire uniquement** — Voir les candidats d'une offre.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "status": "pending",
            "date": "26 Apr 2026",
            "message": "...",
            "candidat": {
                "id": 1,
                "name": "Moussa Diallo",
                "email": "moussa@example.com",
                "titre_poste": "Développeur Web",
                "localisation": "Niamey",
                "competences": ["Laravel", "Vue.js"]
            }
        }
    ]
}
```

---

### `PUT /api/candidatures/{id}/statut`
🔒 **Protégé** | 👔 **Employeur uniquement** — Changer le statut d'une candidature.

**Body :**
```json
{
    "status": "interview"
}
```
> `status` accepte : `pending`, `interview`, `accepted`, `rejected`

**Réponse 200 :**
```json
{
    "message": "Statut mis à jour !"
}
```

---

## 👤 PROFIL

---

### `GET /api/profil`
🔒 **Protégé** — Mon profil complet.

**Réponse 200 — Candidat :**
```json
{
    "id": 1,
    "name": "Moussa Diallo",
    "email": "moussa@example.com",
    "role": "candidate",
    "titre_poste": "Développeur Web",
    "bio": "Passionné par le développement...",
    "telephone": "+227 96 12 34 56",
    "localisation": "Niamey",
    "competences": ["Laravel", "Vue.js"],
    "buildcvpro_connected": true
}
```

---

### `PUT /api/profil`
🔒 **Protégé** — Modifier mon profil.

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

**Réponse 200 :**
```json
{
    "message": "Profil mis à jour !"
}
```

---

### `GET /api/candidats`
🔒 **Protégé** | 👔 **Employeur uniquement** — Liste des profils candidats (12 par page).

---

### `GET /api/candidats/{id}`
🔒 **Protégé** | 👔 **Employeur uniquement** — Profil d'un candidat.

**Réponse 200 :**
```json
{
    "data": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com",
        "titre_poste": "Développeur Web",
        "bio": "Passionné par le développement...",
        "localisation": "Niamey",
        "competences": ["Laravel", "Vue.js"],
        "telephone": "+227 96 12 34 56"
    }
}
```

---

## 🔗 INTÉGRATION BUILD CV PRO

---

### `GET /api/buildcvpro/check`
🔒 **Protégé** | 👤 **Candidat uniquement** — Vérifie si l'email du candidat connecté existe sur Build CV Pro.

**Réponse 200 — Email trouvé :**
```json
{
    "exists": true,
    "user": {
        "id": 7,
        "name": "Moussa Diallo",
        "email": "moussa@test.com",
        "avatar": null
    }
}
```

**Réponse 200 — Email non trouvé :**
```json
{
    "exists": false
}
```

---

### `POST /api/buildcvpro/connect`
🔒 **Protégé** | 👤 **Candidat uniquement** — Connecter son compte Build CV Pro.

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

**Réponse 401 — Mauvais identifiants :**
```json
{
    "message": "Identifiants BuildCVPro invalides."
}
```

---

### `GET /api/buildcvpro/cvs`
🔒 **Protégé** | 👤 **Candidat connecté à Build CV Pro** — Récupérer ses CVs.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 9,
            "title": "Mon premier CV",
            "template": "classic",
            "is_public": false,
            "share_url": null,
            "created_at": "2026-04-25",
            "updated_at": "2026-04-25"
        }
    ]
}
```

---

### `DELETE /api/buildcvpro/disconnect`
🔒 **Protégé** | 👤 **Candidat uniquement** — Déconnecter son compte Build CV Pro.

**Réponse 200 :**
```json
{
    "message": "Compte BuildCVPro déconnecté."
}
```

---

## 📊 Statuts des candidatures

| Statut | Description |
|---|---|
| `pending` | En attente de réponse |
| `interview` | Entretien programmé |
| `accepted` | Candidature acceptée |
| `rejected` | Candidature refusée |

---

## 🔒 Permissions par rôle

| Action | 🌐 Public | 👤 Candidat | 👔 Employeur |
|---|---|---|---|
| Voir les offres | ✅ | ✅ | ✅ |
| Postuler | ❌ | ✅ | ❌ |
| Sauvegarder une offre | ❌ | ✅ | ❌ |
| Publier une offre | ❌ | ❌ | ✅ |
| Voir les candidatures | ❌ | Les siennes | ✅ |
| Voir les profils candidats | ❌ | ❌ | ✅ |
| Connecter Build CV Pro | ❌ | ✅ | ❌ |