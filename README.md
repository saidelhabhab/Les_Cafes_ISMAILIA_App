# Les_Cafes_ISMAILIA_App

> 🛠️ Application de gestion développée avec **Laravel** (Back-end) et **Vue.js** (Front-end)

## 🌍 Description

**Les_Cafes_ISMAILIA_App** est une application web complète qui permet de gérer les achats de café en provenance de différents pays, ainsi que la vente de produits catégorisés à base de café. L'application permet aussi la génération d'analyses mensuelles et annuelles, la gestion des clients, et la génération de codes-barres pour les factures et les produits.

## 🚀 Fonctionnalités principales

- 📦 **Gestion des achats** de café par pays (ex. : Sierra Leone, Brésil, Colombie…)
- 🧾 **Gestion des ventes** avec création de factures détaillées
- 📊 **Statistiques et analyses** des ventes par mois et par année
- 👥 **Gestion des clients**
- 🧠 **Système de génération automatique de codes-barres** pour les produits et les factures
- 📁 **Catégorisation des produits**
- 📐 **Unités multiples prises en charge** : tonnes, kilogrammes, grammes

## 🛠️ Stack technique

- **Backend** : Laravel 10
- **Frontend** : Vue.js 3 avec Vite
- **Base de données** : MySQL
- **Authentification** : Sanctum + JWT (si applicable)
- **Notifications** : SweetAlert

## 📸 Aperçus

**

## ⚙️ Installation locale

```bash
# Clone le repo
git clone https://github.com/saidelhabhab/Les_Cafes_ISMAILIA_App

# Va dans le dossier backend
cd backend

# Installe les dépendances Laravel
composer install

# Copie le fichier .env et configure la base de données
cp .env.example .env
php artisan key:generate

# Exécute les migrations
php artisan migrate --seed

# Lance le serveur
php artisan serve



# Va dans le dossier frontend
cd frontend

# Installe les dépendances Vue
npm install

# Lance le serveur de développement
npm run dev