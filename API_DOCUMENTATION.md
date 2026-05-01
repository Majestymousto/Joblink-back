# JobLink Niger — Documentation API

> Version : **v3.0** — Mise à jour : 01 Mai 2026
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

> ⚠️ Le rôle `admin` n'est **pas accepté** ici pour des raisons de sécurité. Les admins sont créés uniquement via `POST /api/admin/users`.

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
> `role` accepte : `candidate` ou `employer` uniquement.

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
> ⚠️ Compte employeur créé avec `statut: pending` — validation admin requise avant de pouvoir se connecter.

**Réponse 422 — Validation échouée :**
```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

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
🌐 **Public** — Connexion via Google.

**Body :**
```json
{
    "token": "google_access_token_ici",
    "role": "candidate"
}
```
> `role` est optionnel. Si absent et que l'utilisateur n'existe pas encore, il sera créé en `candidate` par défaut.

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

**Réponse 401 — Token Google invalide :**
```json
{
    "message": "Token Google invalide."
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
🔒 **Protégé** — Infos de l'utilisateur connecté.

**Réponse 200 :**
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
| `query` | string | Recherche dans le titre et la description |
| `type_contrat` | string | `cdi`, `cdd`, `stage`, `freelance`, `alternance` |
| `secteur` | string | Secteur d'activité |
| `localisation` | string | Ville |
| `sort` | string | `recent` (par défaut) ou `company` |

---

### `GET /api/job-offers/{id}`
🌐 **Public** — Détail d'une offre. Incrémente automatiquement le compteur de vues.

**Réponse 404 :**
```json
{
    "message": "Offre introuvable."
}
```

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
🔒 **Protégé** | 👔 **Propriétaire de l'offre** — Modifier une offre.

---

### `DELETE /api/job-offers/{id}`
🔒 **Protégé** | 👔 **Propriétaire de l'offre** — Supprimer une offre.

---

### `POST /api/job-offers/{id}/save`
🔒 **Protégé** | 👤 **Candidat** — Sauvegarder une offre.

**Réponse 409 — Déjà sauvegardée :**
```json
{
    "message": "Offre déjà sauvegardée."
}
```

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
> Envoyer soit `cv_path` (CV uploadé) soit `buildcvpro_cv_id` (CV depuis BuildCVPro). Les deux sont optionnels.

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

**Réponse 409 — Déjà postulé :**
```json
{
    "message": "Vous avez déjà postulé à cette offre."
}
```

---

### `GET /api/mes-candidatures`
🔒 **Protégé** | 👤 **Candidat** — Liste de mes candidatures.

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
🔒 **Protégé** | 👤 **Candidat** — Retirer une candidature.

> ⚠️ Uniquement possible si le statut est `pending`.

**Réponse 403 — Statut non pending :**
```json
{
    "message": "Impossible de retirer une candidature déjà traitée."
}
```

---

### `GET /api/job-offers/{id}/candidats`
🔒 **Protégé** | 👔 **Employeur propriétaire de l'offre** — Voir les candidats.

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
🔒 **Protégé** | 👔 **Employeur** — Changer le statut d'une candidature.

**Body :**
```json
{
    "status": "interview"
}
```
> Valeurs acceptées : `pending`, `interview`, `accepted`, `rejected`

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

> ⚠️ Envoyer en `multipart/form-data`, pas en JSON !

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

---

### `DELETE /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — Supprimer le CV uploadé.

**Réponse 200 :**
```json
{
    "message": "CV supprimé avec succès !"
}
```

---

### `GET /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — URL de téléchargement du CV uploadé.

**Réponse 200 :**
```json
{
    "cv_url": "http://127.0.0.1:8001/storage/cvs/xxxxxxxx.pdf"
}
```

**Réponse 404 — Pas de CV :**
```json
{
    "message": "Aucun CV uploadé."
}
```

---

### `GET /api/candidats`
🔒 **Protégé** | 👔 **Employeur** — Liste des candidats (12 par page).

---

### `GET /api/candidats/{id}`
🔒 **Protégé** | 👔 **Employeur** — Profil détaillé d'un candidat.

---

## 🎓 EXPÉRIENCES & FORMATIONS DU CANDIDAT *(nouveau v3.0)*

> Ces endpoints remplacent le stockage local côté frontend. Toutes les expériences et formations sont désormais persistées en base de données.

---

### `GET /api/candidat/experiences`
🔒 **Protégé** | 👤 **Candidat** — Lister toutes ses expériences professionnelles.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "intitule_poste": "Développeur Full Stack",
            "entreprise": "Optimus Engineering",
            "date_debut": "2023-01-01",
            "date_fin": null,
            "poste_actuel": true,
            "description": "Développement d'applications web avec Laravel et Vue.js.",
            "created_at": "2026-04-26T10:00:00.000000Z",
            "updated_at": "2026-04-26T10:00:00.000000Z"
        }
    ]
}
```

---

### `POST /api/candidat/experiences`
🔒 **Protégé** | 👤 **Candidat** — Ajouter une expérience professionnelle.

**Body :**
```json
{
    "intitule_poste": "Développeur Full Stack",
    "entreprise": "Optimus Engineering",
    "date_debut": "2023-01-01",
    "date_fin": null,
    "poste_actuel": true,
    "description": "Développement d'applications web avec Laravel et Vue.js."
}
```

| Champ | Type | Requis | Description |
|---|---|---|---|
| `intitule_poste` | string | ✅ | Titre du poste occupé |
| `entreprise` | string | ✅ | Nom de l'entreprise |
| `date_debut` | date (YYYY-MM-DD) | ✅ | Date de début du poste |
| `date_fin` | date (YYYY-MM-DD) | ❌ | Date de fin — null si poste actuel |
| `poste_actuel` | boolean | ❌ | `true` = toujours en poste. Si `true`, `date_fin` est automatiquement vidée |
| `description` | string | ❌ | Détail des missions |

**Réponse 201 :**
```json
{
    "message": "Expérience ajoutée avec succès.",
    "data": {
        "id": 1,
        "intitule_poste": "Développeur Full Stack",
        "entreprise": "Optimus Engineering",
        "date_debut": "2023-01-01",
        "date_fin": null,
        "poste_actuel": true,
        "description": "Développement d'applications web avec Laravel et Vue.js."
    }
}
```

**Réponse 422 — Validation échouée :**
```json
{
    "message": "The intitule poste field is required.",
    "errors": {
        "intitule_poste": ["The intitule poste field is required."]
    }
}
```

---

### `PUT /api/candidat/experiences/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Modifier une expérience.

> Seuls les champs envoyés sont mis à jour (`sometimes`). Pas besoin de tout renvoyer.

**Body (exemple partiel) :**
```json
{
    "intitule_poste": "Lead Developer",
    "poste_actuel": false,
    "date_fin": "2025-12-31"
}
```

**Réponse 200 :**
```json
{
    "message": "Expérience mise à jour.",
    "data": { ... }
}
```

**Réponse 404 — Introuvable ou n'appartient pas au candidat :**
```json
{
    "message": "Expérience introuvable."
}
```

---

### `DELETE /api/candidat/experiences/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Supprimer une expérience.

**Réponse 200 :**
```json
{
    "message": "Expérience supprimée."
}
```

**Réponse 404 :**
```json
{
    "message": "Expérience introuvable."
}
```

---

### `GET /api/candidat/formations`
🔒 **Protégé** | 👤 **Candidat** — Lister toutes ses formations.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "diplome": "Licence en Informatique",
            "etablissement": "Université de Niamey",
            "annee_debut": 2019,
            "annee_fin": 2022,
            "created_at": "2026-04-26T10:00:00.000000Z",
            "updated_at": "2026-04-26T10:00:00.000000Z"
        }
    ]
}
```

---

### `POST /api/candidat/formations`
🔒 **Protégé** | 👤 **Candidat** — Ajouter une formation.

**Body :**
```json
{
    "diplome": "Licence en Informatique",
    "etablissement": "Université de Niamey",
    "annee_debut": 2019,
    "annee_fin": 2022
}
```

| Champ | Type | Requis | Description |
|---|---|---|---|
| `diplome` | string | ✅ | Intitulé du diplôme ou de la formation |
| `etablissement` | string | ✅ | Nom de l'école ou de l'établissement |
| `annee_debut` | integer | ❌ | Année de début (ex: 2019) |
| `annee_fin` | integer | ❌ | Année de fin (ex: 2022). Doit être ≥ `annee_debut` |

**Réponse 201 :**
```json
{
    "message": "Formation ajoutée avec succès.",
    "data": {
        "id": 1,
        "diplome": "Licence en Informatique",
        "etablissement": "Université de Niamey",
        "annee_debut": 2019,
        "annee_fin": 2022
    }
}
```

---

### `PUT /api/candidat/formations/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Modifier une formation.

> Seuls les champs envoyés sont mis à jour.

**Body (exemple partiel) :**
```json
{
    "annee_fin": 2023
}
```

**Réponse 200 :**
```json
{
    "message": "Formation mise à jour.",
    "data": { ... }
}
```

**Réponse 404 :**
```json
{
    "message": "Formation introuvable."
}
```

---

### `DELETE /api/candidat/formations/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Supprimer une formation.

**Réponse 200 :**
```json
{
    "message": "Formation supprimée."
}
```

**Réponse 404 :**
```json
{
    "message": "Formation introuvable."
}
```

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
🔒 **Protégé** — Envoyer un message dans une conversation.

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
🔒 **Protégé** | 👤 **Candidat** — Vérifie si l'email de l'utilisateur existe sur Build CV Pro.

**Réponse 200 — Email trouvé :**
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

**Réponse 200 — Email non trouvé :**
```json
{
    "exists": false
}
```

---

### `POST /api/buildcvpro/connect`
🔒 **Protégé** | 👤 **Candidat** — Connecter son compte Build CV Pro. Le token retourné est stocké dans le profil candidat.

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

**Réponse 401 — Mauvais identifiants BuildCVPro :**
```json
{
    "message": "Identifiants BuildCVPro invalides."
}
```

**Réponse 500 — BuildCVPro inaccessible :**
```json
{
    "message": "Impossible de contacter BuildCVPro."
}
```

---

### `GET /api/buildcvpro/cvs`
🔒 **Protégé** | 👤 **Candidat connecté** — Récupérer la liste de ses CVs depuis Build CV Pro.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 9,
            "title": "Mon premier CV",
            "template": "classic",
            "is_public": true,
            "share_url": "https://buildcvpro.com/cv/abc123",
            "created_at": "2026-04-25"
        }
    ]
}
```

**Réponse 400 — Compte non connecté :**
```json
{
    "message": "Compte BuildCVPro non connecté."
}
```

**Réponse 401 — Token expiré :**
```json
{
    "message": "Token BuildCVPro expiré. Veuillez vous reconnecter."
}
```

---

### `GET /api/buildcvpro/cvs/{id}` *(nouveau v3.0)*
🔒 **Protégé** | 👤 **Candidat connecté** — Récupérer les détails complets d'un CV BuildCVPro ainsi que son `share_url` pour la prévisualisation.

> Le `share_url` retourné peut être utilisé directement dans un `<iframe>` ou ouvert dans un nouvel onglet pour la preview. Le téléchargement est géré côté frontend via `window.print()`.

**Réponse 200 :**
```json
{
    "data": {
        "id": 9,
        "title": "Mon premier CV",
        "template": "classic",
        "share_url": "https://buildcvpro.com/cv/abc123",
        "data": {
            "personal": {
                "first_name": "Moussa",
                "last_name": "Diallo",
                "title": "Développeur Web"
            },
            "skills": [
                { "name": "Laravel", "level": 5 },
                { "name": "Vue.js", "level": 4 }
            ],
            "experiences": [],
            "educations": []
        }
    }
}
```

**Réponse 400 — Compte non connecté :**
```json
{
    "message": "Compte BuildCVPro non connecté."
}
```

**Réponse 401 — Token expiré :**
```json
{
    "message": "Token BuildCVPro expiré. Veuillez vous reconnecter."
}
```

**Réponse 404 — CV introuvable :**
```json
{
    "message": "CV introuvable."
}
```

---

### `DELETE /api/buildcvpro/disconnect`
🔒 **Protégé** | 👤 **Candidat** — Déconnecter Build CV Pro. Supprime le token stocké.

**Réponse 200 :**
```json
{
    "message": "Compte BuildCVPro déconnecté."
}
```

---

## 🔑 ADMIN

---

### `GET /api/admin/stats`
🔒 **Protégé** | 🔑 **Admin** — Statistiques globales de la plateforme.

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
🔒 **Protégé** | 🔑 **Admin** — Liste paginée de tous les employeurs (15 par page).

---

### `GET /api/admin/employers/pending`
🔒 **Protégé** | 🔑 **Admin** — Employeurs en attente de validation.

---

### `POST /api/admin/employers/{id}/validate`
🔒 **Protégé** | 🔑 **Admin** — Valider un compte employeur. Passe le statut à `active`.

**Réponse 200 :**
```json
{
    "message": "Compte employeur validé avec succès !"
}
```

**Réponse 404 :**
```json
{
    "message": "Entreprise introuvable."
}
```

---

### `POST /api/admin/employers/{id}/reject`
🔒 **Protégé** | 🔑 **Admin** — Rejeter un compte employeur avec une raison obligatoire.

**Body :**
```json
{
    "raison": "Documents incomplets et informations non vérifiables."
}
```

**Réponse 200 :**
```json
{
    "message": "Compte employeur rejeté."
}
```

---

### `GET /api/admin/users` *(nouveau v3.0)*
🔒 **Protégé** | 🔑 **Admin** — Liste paginée de tous les utilisateurs avec filtres.

**Query params :**
| Param | Type | Description |
|---|---|---|
| `search` | string | Recherche par nom ou email |
| `role` | string | Filtrer par rôle : `admin`, `candidate`, `employer` |
| `status` | string | `active` ou `inactive` |
| `per_page` | integer | Nombre de résultats par page (défaut : 15) |

**Réponse 200 :**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "Moussa Diallo",
            "email": "moussa@example.com",
            "role": "candidate",
            "is_active": true,
            "created_at": "26 Apr 2026"
        }
    ],
    "per_page": 15,
    "total": 5
}
```

---

### `GET /api/admin/users/{id}` *(nouveau v3.0)*
🔒 **Protégé** | 🔑 **Admin** — Voir le détail d'un utilisateur.

**Réponse 200 :**
```json
{
    "data": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com",
        "role": "candidate",
        "is_active": true,
        "created_at": "26 Apr 2026"
    }
}
```

**Réponse 404 :**
```json
{
    "message": "Utilisateur introuvable."
}
```

---

### `POST /api/admin/users` *(nouveau v3.0)*
🔒 **Protégé** | 🔑 **Admin** — Créer un utilisateur avec n'importe quel rôle, y compris `admin`.

> C'est le **seul moyen** de créer un compte admin. Le `POST /api/register` public ne l'autorise pas.

**Body :**
```json
{
    "name": "Admin Secondaire",
    "email": "admin2@example.com",
    "password": "Password1",
    "role": "admin"
}
```

| Champ | Type | Requis | Description |
|---|---|---|---|
| `name` | string | ✅ | Nom complet |
| `email` | string | ✅ | Email unique |
| `password` | string | ✅ | Min 8 caractères, majuscule + chiffre requis |
| `role` | string | ✅ | `admin`, `candidate` ou `employer` |

**Réponse 201 :**
```json
{
    "message": "Utilisateur créé avec succès.",
    "data": {
        "id": 6,
        "name": "Admin Secondaire",
        "email": "admin2@example.com",
        "role": "admin",
        "is_active": true
    }
}
```

**Réponse 422 — Email déjà pris :**
```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### `PUT /api/admin/users/{id}` *(nouveau v3.0)*
🔒 **Protégé** | 🔑 **Admin** — Modifier un utilisateur.

> Seuls les champs envoyés sont mis à jour. Le mot de passe est optionnel.

**Body :**
```json
{
    "name": "Nouveau Nom",
    "email": "nouveau@example.com",
    "password": "NewPassword1",
    "role": "employer"
}
```

**Réponse 200 :**
```json
{
    "message": "Utilisateur mis à jour avec succès.",
    "data": { ... }
}
```

**Réponse 404 :**
```json
{
    "message": "Utilisateur introuvable."
}
```

---

### `DELETE /api/admin/users/{id}` *(nouveau v3.0)*
🔒 **Protégé** | 🔑 **Admin** — Supprimer un utilisateur définitivement.

> ⚠️ Un admin ne peut pas supprimer son propre compte.

**Réponse 200 :**
```json
{
    "message": "Utilisateur supprimé avec succès."
}
```

**Réponse 403 — Auto-suppression :**
```json
{
    "message": "Vous ne pouvez pas supprimer votre propre compte."
}
```

**Réponse 404 :**
```json
{
    "message": "Utilisateur introuvable."
}
```

---

### `PATCH /api/admin/users/{id}/toggle-status` *(nouveau v3.0)*
🔒 **Protégé** | 🔑 **Admin** — Activer ou désactiver un compte utilisateur. Bascule automatiquement entre `true` et `false`.

> ⚠️ Un admin ne peut pas désactiver son propre compte.

**Réponse 200 — Compte désactivé :**
```json
{
    "message": "Compte désactivé avec succès.",
    "data": {
        "id": 3,
        "name": "Moussa Diallo",
        "is_active": false
    }
}
```

**Réponse 200 — Compte activé :**
```json
{
    "message": "Compte activé avec succès.",
    "data": {
        "id": 3,
        "name": "Moussa Diallo",
        "is_active": true
    }
}
```

**Réponse 403 — Auto-désactivation :**
```json
{
    "message": "Vous ne pouvez pas désactiver votre propre compte."
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

| Action | 🌐 Public | 👤 Candidat | 👔 Employeur | 🔑 Admin |
|---|---|---|---|---|
| Voir les offres | ✅ | ✅ | ✅ | ✅ |
| Postuler | ❌ | ✅ | ❌ | ❌ |
| Sauvegarder une offre | ❌ | ✅ | ❌ | ❌ |
| Uploader un CV | ❌ | ✅ | ❌ | ❌ |
| Connecter Build CV Pro | ❌ | ✅ | ❌ | ❌ |
| Gérer expériences / formations | ❌ | ✅ | ❌ | ❌ |
| Publier une offre | ❌ | ❌ | ✅ | ✅ |
| Voir les candidatures | ❌ | Les siennes | ✅ | ✅ |
| Voir les profils candidats | ❌ | ❌ | ✅ | ✅ |
| Envoyer des messages | ❌ | ✅ | ✅ | ❌ |
| Valider un employeur | ❌ | ❌ | ❌ | ✅ |
| Gérer les utilisateurs | ❌ | ❌ | ❌ | ✅ |
| Voir les stats | ❌ | ❌ | ❌ | ✅ |

---

## 📋 Changelog

| Version | Date | Changements |
|---|---|---|
| v1.0 | Déc 2025 | Version initiale |
| v2.0 | 26 Avr 2026 | Ajout messages, BuildCVPro, CV upload |
| v3.0 | 01 Mai 2026 | Ajout gestion utilisateurs admin, expériences & formations candidat, preview CV BuildCVPro |