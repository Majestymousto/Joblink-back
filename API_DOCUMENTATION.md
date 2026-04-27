# JobLink Niger — Documentation API

> Version : **v2.0** — Mise à jour : 26 Avril 2026
>
> ✅ Tous les endpoints documentés ici sont testés et validés.

---

## 🔗 URL de base

```
http://127.0.0.1:8001/api
```

---

## 📦 Headers requis

| Header | Valeur | Obligatoire |
|---|---|---|
| `Accept` | `application/json` | ✅ |
| `Content-Type` | `application/json` | ✅ (POST/PUT JSON) |
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
| Public | 🌐 | Accessible sans authentification |
| Candidat | 👤 | Peut postuler, gérer son profil |
| Employeur | 👔 | Peut publier des offres, voir les candidatures |
| Admin | 🔑 | Gère la plateforme |

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
            "titre_poste": null,
            "bio": null,
            "competences": null,
            "cv_path": null,
            "buildcvpro_token": null
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
        "role": "employer",
        "profile": {
            "nom_entreprise": "Optimus Engineering SARL",
            "statut": "pending"
        }
    }
}
```
> ⚠️ Compte employeur créé avec `statut: pending` — validation admin requise.

---

### `POST /api/login`
🌐 **Public** — Se connecter.

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
🌐 **Public** — Connexion via Google.

**Body :**
```json
{
    "token": "google_access_token_ici",
    "role": "candidate"
}
```

**Réponse 200 :**
```json
{
    "token": "4|xxxxxxxxxxxxxxxx",
    "user": {
        "name": "Moussa Google",
        "email": "moussa@gmail.com",
        "avatar": "https://lh3.googleusercontent.com/...",
        "role": "candidate"
    }
}
```

---

### `POST /api/logout`
🔒 **Protégé** — Révoque le token.

```json
{ "message": "Déconnecté avec succès." }
```

---

### `GET /api/me`
🔒 **Protégé** — Infos de l'utilisateur connecté.

```json
{
    "id": 1,
    "name": "Moussa Diallo",
    "email": "moussa@example.com",
    "role": "candidate",
    "profile": { ... }
}
```

---

## 📄 OFFRES D'EMPLOI

---

### `GET /api/job-offers`
🌐 **Public** — Liste des offres avec filtres (8 par page).

**Query params :**
| Param | Type | Description |
|---|---|---|
| `query` | string | Recherche titre/description |
| `type_contrat` | string | cdi, cdd, stage, freelance, alternance |
| `secteur` | string | Secteur d'activité |
| `localisation` | string | Ville |
| `sort` | string | `recent` ou `company` |

---

### `GET /api/job-offers/{id}`
🌐 **Public** — Détail d'une offre. Incrémente les vues.

---

### `POST /api/job-offers`
🔒 **Protégé** | 👔 **Employeur validé** — Créer une offre.

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

---

### `PUT /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire** — Modifier une offre.

---

### `DELETE /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire** — Supprimer une offre.

---

### `POST /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat** — Sauvegarder une offre.

---

### `DELETE /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat** — Retirer des sauvegardes.

---

### `GET /api/offres-sauvegardees`
🔒 **Protégé** | 👤 **Candidat** — Liste des offres sauvegardées.

---

## 📝 CANDIDATURES

---

### `POST /api/job-offers/{id}/apply`
🔒 **Protégé** | 👤 **Candidat** — Postuler à une offre.

**Body :**
```json
{
    "message": "Je suis très intéressé...",
    "cv_path": "cvs/mon-cv.pdf",
    "buildcvpro_cv_id": "9"
}
```
> Envoyer soit `cv_path` soit `buildcvpro_cv_id`.

**Réponse 201 :**
```json
{
    "data": {
        "id": 1,
        "status": "pending",
        "message": "Je suis très intéressé..."
    },
    "message": "Candidature envoyée avec succès !"
}
```

---

### `GET /api/mes-candidatures`
🔒 **Protégé** | 👤 **Candidat** — Mes candidatures.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "status": "pending",
            "date": "26 Apr 2026",
            "job": {
                "titre": "Développeur Web Full Stack",
                "entreprise": "Optimus Engineering SARL"
            }
        }
    ]
}
```

---

### `DELETE /api/candidatures/{id}`
🔒 **Protégé** | 👤 **Candidat** — Retirer une candidature (statut `pending` uniquement).

---

### `GET /api/job-offers/{id}/candidats`
🔒 **Protégé** | 👔 **Employeur propriétaire** — Voir les candidats.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "status": "pending",
            "candidat": {
                "name": "Moussa Diallo",
                "titre_poste": "Développeur Web",
                "competences": ["Laravel", "Vue.js"]
            }
        }
    ]
}
```

---

### `PUT /api/candidatures/{id}/statut`
🔒 **Protégé** | 👔 **Employeur** — Changer le statut.

**Body :**
```json
{ "status": "interview" }
```
> Accepte : `pending`, `interview`, `accepted`, `rejected`

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
    "titre_poste": "Développeur Web",
    "bio": "Passionné par le développement...",
    "telephone": "+227 96 12 34 56",
    "localisation": "Niamey",
    "competences": ["Laravel", "Vue.js"]
}
```

---

### `POST /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — Uploader un CV PDF.

> ⚠️ Envoyer en `multipart/form-data` pas en JSON !

**Form Data :**
```
cv → fichier PDF (max 5MB)
```

**Réponse 201 :**
```json
{
    "message": "CV uploadé avec succès !",
    "cv_path": "cvs/xxxxxxxx.pdf",
    "cv_url": "http://127.0.0.1:8001/storage/cvs/xxxxxxxx.pdf"
}
```

**Exemple curl :**
```bash
curl -X POST http://127.0.0.1:8001/api/profil/cv \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json" \
  -F "cv=@/chemin/vers/cv.pdf"
```

---

### `DELETE /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — Supprimer le CV.

```json
{ "message": "CV supprimé avec succès !" }
```

---

### `GET /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — URL de téléchargement du CV.

```json
{
    "cv_url": "http://127.0.0.1:8001/storage/cvs/xxxxxxxx.pdf"
}
```

---

### `GET /api/candidats`
🔒 **Protégé** | 👔 **Employeur** — Liste des candidats (12 par page).

---

### `GET /api/candidats/{id}`
🔒 **Protégé** | 👔 **Employeur** — Profil d'un candidat.

---

## 💬 MESSAGES

---

### `GET /api/messages`
🔒 **Protégé** — Liste des conversations.

**Réponse 200 — Candidat :**
```json
{
    "data": [
        {
            "application_id": 2,
            "job": {
                "id": 2,
                "titre": "Développeur Web Full Stack"
            },
            "entreprise": {
                "id": 1,
                "nom": "Optimus Engineering SARL"
            },
            "dernier_message": "Bonjour, je suis très motivé...",
            "unread": 1
        }
    ]
}
```

---

### `GET /api/messages/{applicationId}`
🔒 **Protégé** — Messages d'une conversation. Marque automatiquement les messages comme lus.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "content": "Bonjour, je suis très motivé !",
            "from_me": true,
            "read": true,
            "created_at": "10:00"
        },
        {
            "id": 2,
            "content": "Merci, nous voudrions vous rencontrer.",
            "from_me": false,
            "read": true,
            "created_at": "10:07"
        }
    ]
}
```

---

### `POST /api/messages/{applicationId}`
🔒 **Protégé** — Envoyer un message.

**Body :**
```json
{
    "content": "Bonjour, je suis très motivé pour rejoindre votre équipe !"
}
```

**Réponse 201 :**
```json
{
    "data": {
        "id": 1,
        "content": "Bonjour...",
        "from_me": true,
        "read": false,
        "created_at": "10:00"
    },
    "message": "Message envoyé !"
}
```

---

## 🔗 INTÉGRATION BUILD CV PRO

---

### `GET /api/buildcvpro/check`
🔒 **Protégé** | 👤 **Candidat** — Vérifie si l'email existe sur Build CV Pro.

**Réponse 200 :**
```json
{
    "exists": true,
    "user": {
        "id": 7,
        "name": "Moussa Diallo",
        "email": "moussa@test.com"
    }
}
```

---

### `POST /api/buildcvpro/connect`
🔒 **Protégé** | 👤 **Candidat** — Connecter son compte Build CV Pro.

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

### `GET /api/buildcvpro/cvs`
🔒 **Protégé** | 👤 **Candidat connecté** — Récupérer ses CVs.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 9,
            "title": "Mon premier CV",
            "template": "classic",
            "created_at": "2026-04-25"
        }
    ]
}
```

---

### `DELETE /api/buildcvpro/disconnect`
🔒 **Protégé** | 👤 **Candidat** — Déconnecter Build CV Pro.

```json
{ "message": "Compte BuildCVPro déconnecté." }
```

---

## 🔑 ADMIN

---

### `GET /api/admin/stats`
🔒 **Protégé** | 🔑 **Admin** — Statistiques globales.

**Réponse 200 :**
```json
{
    "data": {
        "total_users": 5,
        "total_candidats": 3,
        "total_employeurs": 1,
        "employeurs_pending": 0,
        "employeurs_actifs": 1,
        "total_offres": 1,
        "offres_actives": 1,
        "total_candidatures": 2
    }
}
```

---

### `GET /api/admin/employers`
🔒 **Protégé** | 🔑 **Admin** — Liste de tous les employeurs (15 par page).

---

### `GET /api/admin/employers/pending`
🔒 **Protégé** | 🔑 **Admin** — Employeurs en attente de validation.

---

### `POST /api/admin/employers/{id}/validate`
🔒 **Protégé** | 🔑 **Admin** — Valider un compte employeur.

**Réponse 200 :**
```json
{ "message": "Compte employeur validé avec succès !" }
```

---

### `POST /api/admin/employers/{id}/reject`
🔒 **Protégé** | 🔑 **Admin** — Rejeter un compte employeur.

**Body :**
```json
{
    "raison": "Documents incomplets et informations non vérifiables."
}
```

**Réponse 200 :**
```json
{ "message": "Compte employeur rejeté." }
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

| Action | 🌐 Public | 👤 Candidat | 👔 Employeur | 🔑 Admin |
|---|---|---|---|---|
| Voir les offres | ✅ | ✅ | ✅ | ✅ |
| Postuler | ❌ | ✅ | ❌ | ❌ |
| Sauvegarder une offre | ❌ | ✅ | ❌ | ❌ |
| Uploader un CV | ❌ | ✅ | ❌ | ❌ |
| Connecter Build CV Pro | ❌ | ✅ | ❌ | ❌ |
| Publier une offre | ❌ | ❌ | ✅ | ✅ |
| Voir les candidatures | ❌ | Les siennes | ✅ | ✅ |
| Voir les profils candidats | ❌ | ❌ | ✅ | ✅ |
| Envoyer des messages | ❌ | ✅ | ✅ | ❌ |
| Valider un employeur | ❌ | ❌ | ❌ | ✅ |
| Voir les stats | ❌ | ❌ | ❌ | ✅ |