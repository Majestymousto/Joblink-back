# JobLink Niger — Documentation API

> Version : **v6.0** — Mise à jour : 28 Mai 2026
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
| Super Admin | 👑 | Niveau supérieur — ne peut pas être supprimé ni suspendu |

---

## 🔐 AUTHENTIFICATION

---

### `POST /api/register`
🌐 **Public** — Créer un nouveau compte.

> ⚠️ Le rôle `admin` n'est **pas accepté** ici. Les admins sont créés uniquement via `POST /api/admin/users`.
>
> ⚠️ **v6.0** — L'inscription ne retourne plus de token. Un OTP est envoyé par email. Le compte doit être vérifié via `POST /api/email/verify` avant de pouvoir se connecter.

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

**Réponse 201 :**
```json
{
    "message": "Inscription réussie. Un code de vérification a été envoyé à votre adresse email.",
    "email": "moussa@example.com"
}
```

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

### `POST /api/email/verify`
🌐 **Public** — Vérifier son email avec le code OTP reçu.

**Body :**
```json
{
    "email": "moussa@example.com",
    "otp": "259237"
}
```

**Réponse 200 — Succès :**
```json
{
    "message": "Email vérifié avec succès.",
    "token": "1|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "Moussa Diallo",
        "email": "moussa@example.com",
        "role": "candidate"
    }
}
```

**Réponses d'erreur :**
| Code | Message | Cause |
|---|---|---|
| 422 | Code OTP invalide | Mauvais code saisi |
| 422 | Le code OTP a expiré | Code de plus de 10 minutes |
| 422 | Ce compte est déjà vérifié | Email déjà confirmé |

---

### `POST /api/email/resend`
🌐 **Public** — Renvoyer un nouveau code OTP.

**Body :**
```json
{
    "email": "moussa@example.com"
}
```

**Réponse 200 :**
```json
{
    "message": "Un nouveau code de vérification a été envoyé à votre adresse email."
}
```

**Réponse 429 — Anti-spam :**
```json
{
    "message": "Veuillez attendre avant de demander un nouveau code."
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

**Réponse 403 — Email non vérifié :**
```json
{
    "message": "Veuillez vérifier votre adresse email avant de vous connecter.",
    "status": "unverified",
    "email": "moussa@example.com"
}
```
> ➡️ Rediriger vers l'écran de vérification OTP avec l'email.

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
| `sort` | string | `recent` (par défaut) ou `salary` |

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

### `GET /api/employer/job-offers`
🔒 **Protégé** | 👔 **Employeur** — Liste de toutes les offres de l'employeur connecté (tous statuts).

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "titre": "Développeur Web Full Stack",
            "type_contrat": "cdi",
            "statut": "active",
            "localisation": "Niamey",
            "created_at": "2026-04-26T10:00:00.000000Z"
        }
    ]
}
```

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

## 🏢 ENTREPRISES

---

### `GET /api/entreprises`
🌐 **Public** — Liste paginée des entreprises validées (12 par page).

> Seules les entreprises avec `statut: active` sont retournées.

**Query params :**
| Param | Type | Description |
|---|---|---|
| `search` | string | Recherche par nom ou secteur |
| `secteur` | string | Filtrer par secteur d'activité |
| `ville` | string | Filtrer par ville |
| `per_page` | integer | Nombre de résultats par page (défaut : 12) |

**Réponse 200 :**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "nom_entreprise": "Optimus Engineering SARL",
            "type_entreprise": "SARL",
            "secteur": "tech",
            "description": "Entreprise spécialisée en développement logiciel.",
            "logo": null,
            "ville": "Niamey",
            "pays": "Niger",
            "site_web": "https://optimus.ne",
            "annee_creation": 2018,
            "nombre_employes": "50-100",
            "offres_actives": 3
        }
    ],
    "per_page": 12,
    "total": 5
}
```

---

### `GET /api/entreprises/{id}`
🌐 **Public** — Détail complet d'une entreprise avec ses offres actives.

> Seules les entreprises avec `statut: active` sont accessibles. Les informations sensibles (`responsable_nom`, `responsable_email`, `numero_identification`, `raison_rejet`) ne sont pas exposées.

**Réponse 200 :**
```json
{
    "data": {
        "id": 1,
        "nom_entreprise": "Optimus Engineering SARL",
        "type_entreprise": "SARL",
        "secteur": "tech",
        "description": "Entreprise spécialisée en développement logiciel.",
        "logo": null,
        "ville": "Niamey",
        "pays": "Niger",
        "adresse": "Avenue de la République",
        "email_contact": "contact@optimus.ne",
        "telephone": "+227 20 00 00 00",
        "site_web": "https://optimus.ne",
        "annee_creation": 2018,
        "nombre_employes": "50-100",
        "offres": [
            {
                "id": 1,
                "titre": "Développeur Web Full Stack",
                "type_contrat": "cdi",
                "localisation": "Niamey",
                "created_at": "2026-04-26T10:00:00.000000Z"
            }
        ]
    }
}
```

**Réponse 404 — Introuvable ou non validée :**
```json
{
    "message": "Entreprise introuvable."
}
```

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

---

### `DELETE /api/candidatures/{id}`
🔒 **Protégé** | 👤 **Candidat** — Retirer une candidature.

> ⚠️ Uniquement possible si le statut est `pending`.

---

### `PUT /api/candidatures/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Modifier sa candidature.

> ⚠️ Uniquement possible si l'employeur **n'a pas encore ouvert** la candidature (`is_opened: false`).

**Body :**
```json
{
    "message": "Nouveau message de motivation mis à jour.",
    "cv_path": "cvs/nouveau-cv.pdf",
    "buildcvpro_cv_id": "abc123"
}
```

**Réponse 200 :**
```json
{
    "message": "Candidature modifiée avec succès.",
    "data": { ... }
}
```

**Réponse 403 — Déjà consultée :**
```json
{
    "message": "Vous ne pouvez plus modifier cette candidature, elle a déjà été consultée par l'employeur."
}
```

---

### `GET /api/job-offers/{id}/candidats`
🔒 **Protégé** | 👔 **Employeur propriétaire de l'offre** — Voir les candidats reçus.

> ⚠️ **v6.0** — Marque automatiquement toutes les candidatures comme `is_opened: true` à la consultation. Supporte les filtres et le tri.

**Query params :**

| Param | Type | Exemple | Description |
|---|---|---|---|
| `competence` | string | `?competence=php` | Filtre par compétence du candidat |
| `localisation` | string | `?localisation=Niamey` | Filtre par ville du candidat |
| `sort_by` | string | `?sort_by=experiences` | Tri : `experiences` ou `date` (défaut) |
| `sort_dir` | string | `?sort_dir=asc` | Ordre : `asc` ou `desc` (défaut) |

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "status": "pending",
            "is_opened": true,
            "date": "28 Mai 2026",
            "message": "Je suis très intéressé...",
            "candidat": {
                "id": 3,
                "name": "Moussa Diallo",
                "email": "moussa@example.com",
                "avatar": null,
                "titre_poste": "Développeur Web",
                "localisation": "Niamey",
                "competences": ["Laravel", "Vue.js"],
                "nb_experiences": 3
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

---

### `PUT /api/profil`
🔒 **Protégé** — Modifier mon profil.

---

### `POST /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — Uploader un CV PDF.

> ⚠️ Envoyer en `multipart/form-data`, pas en JSON !

---

### `DELETE /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — Supprimer le CV uploadé.

---

### `GET /api/profil/cv`
🔒 **Protégé** | 👤 **Candidat** — URL de téléchargement du CV uploadé.

---

### `POST /api/profil/logo`
🔒 **Protégé** | 👔 **Employeur** — Uploader le logo de l'entreprise.

> ⚠️ Envoyer en `multipart/form-data`, pas en JSON !

**Form Data :**
```
logo → fichier image JPEG/PNG/WebP (max 2MB)
```

**Réponse 200 :**
```json
{
    "message": "Logo mis à jour.",
    "logo": "http://127.0.0.1:8001/storage/logos/xxxxxxxx.png"
}
```

---

### `GET /api/candidats`
🔒 **Protégé** | 👔 **Employeur** — Liste des candidats (12 par page).

---

### `GET /api/candidats/{id}`
🔒 **Protégé** | 👔 **Employeur** — Profil détaillé d'un candidat.

---

## 🎓 EXPÉRIENCES & FORMATIONS DU CANDIDAT

---

### `GET /api/candidat/experiences`
🔒 **Protégé** | 👤 **Candidat** — Lister toutes ses expériences professionnelles.

---

### `POST /api/candidat/experiences`
🔒 **Protégé** | 👤 **Candidat** — Ajouter une expérience professionnelle.

---

### `PUT /api/candidat/experiences/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Modifier une expérience.

---

### `DELETE /api/candidat/experiences/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Supprimer une expérience.

---

### `GET /api/candidat/formations`
🔒 **Protégé** | 👤 **Candidat** — Lister toutes ses formations.

---

### `POST /api/candidat/formations`
🔒 **Protégé** | 👤 **Candidat** — Ajouter une formation.

---

### `PUT /api/candidat/formations/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Modifier une formation.

---

### `DELETE /api/candidat/formations/{id}`
🔒 **Protégé** | 👤 **Candidat propriétaire** — Supprimer une formation.

---

## 💬 MESSAGES

---

### `GET /api/messages`
🔒 **Protégé** — Liste des conversations.

---

### `GET /api/messages/{applicationId}`
🔒 **Protégé** — Messages d'une conversation. Marque automatiquement les messages comme lus.

---

### `POST /api/messages/{applicationId}`
🔒 **Protégé** — Envoyer un message dans une conversation.

---

## ⭐ AVIS

---

### `POST /api/avis`
🔒 **Protégé** | 👤 **Candidat** — Soumettre un avis (note + commentaire).

> ⚠️ Un utilisateur ne peut soumettre qu'un seul avis. Le statut est toujours `pending` à la création.

**Body :**
```json
{
    "note": 4,
    "commentaire": "Très bonne plateforme, facile à utiliser !",
    "context": "page_accueil"
}
```

| Champ | Type | Requis | Description |
|---|---|---|---|
| `note` | integer | ✅ | Note de 1 à 5 |
| `commentaire` | string | ✅ | Texte de l'avis |
| `context` | string | ❌ | Origine de l'avis (ex: `page_accueil`, `apres_candidature`) |

**Réponse 201 :**
```json
{
    "message": "Avis soumis avec succès.",
    "data": {
        "id": 1,
        "note": 4,
        "commentaire": "Très bonne plateforme, facile à utiliser !",
        "context": "page_accueil",
        "status": "pending"
    }
}
```

**Réponse 409 — Avis déjà soumis :**
```json
{
    "message": "Vous avez déjà soumis un avis."
}
```

**Réponse 422 — Validation échouée :**
```json
{
    "message": "The note field is required.",
    "errors": {
        "note": ["The note field is required."]
    }
}
```

---

### `GET /api/stats/public`
🌐 **Public** — Statistiques globales visibles sur la page d'accueil.

**Réponse 200 :**
```json
{
    "total_candidats": 42,
    "total_entreprises": 8,
    "total_offres": 15
}
```

---

### `GET /api/avis/approved`
🌐 **Public** — Retourne uniquement les avis approuvés.

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "note": 4,
            "commentaire": "Très bonne plateforme, facile à utiliser !",
            "context": "page_accueil",
            "created_at": "08 Mai 2026",
            "auteur": {
                "name": "Moussa Diallo",
                "avatar": null
            }
        }
    ]
}
```

---

## 🔗 INTÉGRATION BUILD CV PRO

---

### `GET /api/buildcvpro/check`
🔒 **Protégé** | 👤 **Candidat** — Vérifie si l'email existe sur Build CV Pro.

---

### `POST /api/buildcvpro/connect`
🔒 **Protégé** | 👤 **Candidat** — Connecter son compte Build CV Pro.

---

### `GET /api/buildcvpro/cvs`
🔒 **Protégé** | 👤 **Candidat connecté** — Récupérer la liste de ses CVs.

---

### `GET /api/buildcvpro/cvs/{id}`
🔒 **Protégé** | 👤 **Candidat connecté** — Détails complets d'un CV et son `share_url`.

---

### `DELETE /api/buildcvpro/disconnect`
🔒 **Protégé** | 👤 **Candidat** — Déconnecter Build CV Pro.

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

### `GET /api/admin/stats/weekly`
🔒 **Protégé** | 🔑 **Admin** — Stats par semaine (inscriptions, offres, candidatures).

**Query params :**
| Param | Type | Description |
|---|---|---|
| `periode` | integer | Nombre de semaines : `4`, `8`, `12`, `24`. Défaut : `4` |

**Réponse 200 :**
```json
{
    "periode": 4,
    "data": [
        {
            "semaine": "14 Avr",
            "inscriptions": 3,
            "offres": 1,
            "candidatures": 5
        },
        {
            "semaine": "21 Avr",
            "inscriptions": 2,
            "offres": 0,
            "candidatures": 3
        }
    ]
}
```

---

### `GET /api/admin/stats/region`
🔒 **Protégé** | 🔑 **Admin** — Stats par région (candidats, employeurs, offres).

**Query params :**
| Param | Type | Description |
|---|---|---|
| `type` | string | Filtrer par type : `candidats`, `employeurs`, `offres`. Si absent, retourne les 3. |

**Réponse 200 (sans filtre) :**
```json
{
    "candidats": [
        { "localisation": "Niamey", "total": 8 },
        { "localisation": "Zinder", "total": 3 }
    ],
    "employeurs": [
        { "ville": "Niamey", "pays": "Niger", "total": 5 }
    ],
    "offres": [
        { "localisation": "Niamey", "total": 12 },
        { "localisation": "Agadez", "total": 2 }
    ]
}
```

**Réponse 200 (avec `?type=offres`) :**
```json
{
    "offres": [
        { "localisation": "Niamey", "total": 12 },
        { "localisation": "Agadez", "total": 2 }
    ]
}
```

---

### `GET /api/admin/avis`
🔒 **Protégé** | 🔑 **Admin** — Récupérer tous les avis (tous statuts).

**Query params :**
| Param | Type | Description |
|---|---|---|
| `status` | string | Filtrer par statut : `pending`, `approved`, `rejected` |

**Réponse 200 :**
```json
{
    "data": [
        {
            "id": 1,
            "note": 4,
            "commentaire": "Très bonne plateforme !",
            "context": "page_accueil",
            "status": "pending",
            "created_at": "08 Mai 2026 15:09",
            "auteur": {
                "id": 1,
                "name": "Moussa Diallo",
                "email": "moussa@example.com",
                "avatar": null
            }
        }
    ]
}
```

---

### `POST /api/admin/avis/{id}/approve`
🔒 **Protégé** | 🔑 **Admin** — Approuver un avis.

**Réponse 200 :**
```json
{
    "message": "Avis approuvé avec succès."
}
```

**Réponse 409 — Déjà approuvé :**
```json
{
    "message": "Cet avis est déjà approuvé."
}
```

---

### `POST /api/admin/avis/{id}/reject`
🔒 **Protégé** | 🔑 **Admin** — Rejeter un avis.

**Réponse 200 :**
```json
{
    "message": "Avis rejeté avec succès."
}
```

**Réponse 409 — Déjà rejeté :**
```json
{
    "message": "Cet avis est déjà rejeté."
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
🔒 **Protégé** | 🔑 **Admin** — Valider un compte employeur.

---

### `POST /api/admin/employers/{id}/reject`
🔒 **Protégé** | 🔑 **Admin** — Rejeter un compte employeur.

---

### `GET /api/admin/users`
🔒 **Protégé** | 🔑 **Admin** — Liste paginée de tous les utilisateurs avec filtres.

---

### `GET /api/admin/users/{id}`
🔒 **Protégé** | 🔑 **Admin** — Voir le détail d'un utilisateur.

---

### `POST /api/admin/users`
🔒 **Protégé** | 🔑 **Admin / 👑 Super Admin** — Créer un utilisateur.

> Un `admin` peut créer des rôles : `admin`, `employer`, `candidate`.
> Un `super_admin` peut aussi créer un `super_admin`.
> Les utilisateurs créés par un admin ont `email_verified_at` renseigné automatiquement.

---

### `PUT /api/admin/users/{id}`
🔒 **Protégé** | 🔑 **Admin / 👑 Super Admin** — Modifier un utilisateur.

> ⚠️ Un `admin` ne peut **pas modifier** un `super_admin`. Seul un `super_admin` peut le faire.

---

### `DELETE /api/admin/users/{id}`
🔒 **Protégé** | 🔑 **Admin** — Supprimer un utilisateur définitivement.

> ⚠️ Un compte `super_admin` **ne peut jamais être supprimé**, même par un autre super_admin.

**Réponse 403 — Tentative de suppression d'un super_admin :**
```json
{
    "message": "Le super administrateur ne peut pas être supprimé."
}
```

---

### `PATCH /api/admin/users/{id}/toggle-status`
🔒 **Protégé** | 🔑 **Admin** — Activer ou désactiver un compte utilisateur.

> ⚠️ Un compte `super_admin` **ne peut pas être suspendu**.

**Réponse 403 — Tentative de suspension d'un super_admin :**
```json
{
    "message": "Le super administrateur ne peut pas être suspendu."
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

## 📊 Statuts des avis

| Statut | Description |
|---|---|
| `pending` | En attente de modération |
| `approved` | Approuvé — visible publiquement |
| `rejected` | Rejeté — non visible |

---

## 🔒 Permissions par rôle

| Action | 🌐 Public | 👤 Candidat | 👔 Employeur | 🔑 Admin | 👑 Super Admin |
|---|---|---|---|---|---|
| Voir les offres | ✅ | ✅ | ✅ | ✅ | ✅ |
| Voir les entreprises | ✅ | ✅ | ✅ | ✅ | ✅ |
| Voir les avis approuvés | ✅ | ✅ | ✅ | ✅ | ✅ |
| Postuler | ❌ | ✅ | ❌ | ❌ | ❌ |
| Modifier sa candidature | ❌ | ✅ (si non ouverte) | ❌ | ❌ | ❌ |
| Sauvegarder une offre | ❌ | ✅ | ❌ | ❌ | ❌ |
| Uploader un CV | ❌ | ✅ | ❌ | ❌ | ❌ |
| Connecter Build CV Pro | ❌ | ✅ | ❌ | ❌ | ❌ |
| Gérer expériences / formations | ❌ | ✅ | ❌ | ❌ | ❌ |
| Soumettre un avis | ❌ | ✅ | ❌ | ❌ | ❌ |
| Publier une offre | ❌ | ❌ | ✅ | ✅ | ✅ |
| Voir les candidatures | ❌ | Les siennes | ✅ | ✅ | ✅ |
| Filtrer/trier les candidatures | ❌ | ❌ | ✅ | ✅ | ✅ |
| Voir les profils candidats | ❌ | ❌ | ✅ | ✅ | ✅ |
| Envoyer des messages | ❌ | ✅ | ✅ | ❌ | ❌ |
| Valider un employeur | ❌ | ❌ | ❌ | ✅ | ✅ |
| Gérer les utilisateurs | ❌ | ❌ | ❌ | ✅ | ✅ |
| Créer un super_admin | ❌ | ❌ | ❌ | ❌ | ✅ |
| Supprimer / suspendre un admin | ❌ | ❌ | ❌ | ✅ | ✅ |
| Supprimer / suspendre un super_admin | ❌ | ❌ | ❌ | ❌ | ❌ |
| Voir les stats | ❌ | ❌ | ❌ | ✅ | ✅ |
| Modérer les avis | ❌ | ❌ | ❌ | ✅ | ✅ |

---

## 📋 Changelog

| Version | Date | Changements |
|---|---|---|
| v1.0 | Déc 2025 | Version initiale |
| v2.0 | 26 Avr 2026 | Ajout messages, BuildCVPro, CV upload |
| v3.0 | 01 Mai 2026 | Ajout gestion utilisateurs admin, expériences & formations candidat, preview CV BuildCVPro |
| v4.0 | 01 Mai 2026 | Ajout endpoints publics entreprises (liste + détail avec offres actives) |
| v5.0 | 08 Mai 2026 | Ajout module Avis complet (POST /api/avis, GET /api/avis/approved, GET+POST admin/avis), stats weekly et region |
| v5.1 | 24 Mai 2026 | Ajout GET /api/employer/job-offers, GET /api/stats/public, POST /api/profil/logo — correction paramètre sort |
| v6.0 | 28 Mai 2026 | Vérification email par OTP (register, email/verify, email/resend) — Modification candidature avant ouverture (PUT /candidatures/{id}) — Filtres et tri sur les candidats reçus — Hiérarchie Super Admin / Admin |
