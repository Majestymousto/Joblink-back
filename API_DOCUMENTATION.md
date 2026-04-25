# JobLink Niger — Documentation API

> Version : **v0.2** — Mise à jour : 25 Avril 2026
> 
> ⚠️ Seuls les endpoints testés et validés sont documentés ici.

---

## 🔗 URL de base

```
http://localhost:8000/api
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
| `401` | Non authentifié |
| `403` | Non autorisé |
| `422` | Erreur de validation |

---

## ✅ Endpoints disponibles

---

### `POST /api/register`
🌐 **Public**

Créer un nouveau compte candidat ou employeur.

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
            "user_id": 2,
            "nom_entreprise": "Optimus Engineering SARL",
            "statut": "pending"
        }
    }
}
```

> ⚠️ Le compte employeur est créé avec `statut: pending`. Il doit être validé par un admin avant de pouvoir se connecter.

---

### `POST /api/login`
🌐 **Public**

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
    "token": "3|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com",
        "role": "candidate",
        "profile": { ... }
    }
}
```

**Réponse 401 — Mauvais identifiants :**
```json
{
    "message": "Identifiants invalides."
}
```

**Réponse 403 — Employeur en attente :**
```json
{
    "message": "Votre compte est en attente de validation.",
    "status": "pending"
}
```

**Réponse 403 — Employeur rejeté :**
```json
{
    "message": "Votre compte a été rejeté.",
    "status": "rejected"
}
```

---

### `POST /api/auth/google`
🌐 **Public**

Connexion ou inscription via Google. Le client obtient le token Google et l'envoie à cette route. Si l'email existe déjà, le compte est connecté. Sinon, un nouveau compte est créé automatiquement.

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
        "profile": {
            "id": 3,
            "user_id": 3,
            "titre_poste": null,
            "bio": null,
            "competences": null,
            "buildcvpro_token": null,
            "buildcvpro_email": null
        }
    }
}
```

**Réponse 401 — Token Google invalide :**
```json
{
    "message": "Token Google invalide."
}
```

---

### `POST /api/logout`
🔒 **Protégé**

Révoque le token actuel.

**Réponse 200 :**
```json
{
    "message": "Déconnecté avec succès."
}
```

---

### `GET /api/me`
🔒 **Protégé**

Retourne les informations de l'utilisateur connecté.

**Réponse 200 — Candidat :**
```json
{
    "id": 1,
    "name": "Moussa Diallo",
    "email": "moussa@example.com",
    "avatar": null,
    "role": "candidate",
    "profile": {
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
```

---

## 🚧 Prochains endpoints

| Endpoint | Description |
|---|---|
| `GET /api/job-offers` | Liste des offres |
| `GET /api/job-offers/{id}` | Détail d'une offre |
| `POST /api/job-offers` | Créer une offre (employeur) |
| `PUT /api/job-offers/{id}` | Modifier une offre (employeur) |
| `DELETE /api/job-offers/{id}` | Supprimer une offre (employeur) |
| `POST /api/job-offers/{id}/apply` | Postuler à une offre (candidat) |
| `GET /api/mes-candidatures` | Mes candidatures (candidat) |
| `DELETE /api/candidatures/{id}` | Retirer une candidature (candidat) |
| `GET /api/job-offers/{id}/candidats` | Voir les candidats d'une offre (employeur) |
| `PUT /api/candidatures/{id}/statut` | Changer le statut d'une candidature (employeur) |
| `GET /api/profil` | Mon profil |
| `PUT /api/profil` | Modifier mon profil |
| `GET /api/candidats` | Liste des candidats (employeur) |
| `GET /api/candidats/{id}` | Profil d'un candidat (employeur) |
| `POST /api/buildcvpro/connect` | Connecter Build CV Pro |
| `GET /api/buildcvpro/cvs` | Récupérer les CVs Build CV Pro |
| `DELETE /api/buildcvpro/disconnect` | Déconnecter Build CV Pro |
| `GET /api/buildcvpro/check` | Vérifier si email existe sur Build CV Pro |