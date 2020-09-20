[![Codacy Badge](https://app.codacy.com/project/badge/Grade/f2e1b1a99d7349aab3852a440d0be7fe)](https://www.codacy.com/manual/kindertheo/P7-BileMo?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=kindertheo/P7-BileMo&amp;utm_campaign=Badge_Grade)

[![Maintainability](https://api.codeclimate.com/v1/badges/2f617c7197ace54fe059/maintainability)](https://codeclimate.com/github/kindertheo/P7-BileMo/maintainability)

Créez un web service exposant une API

## Installation

*   Clonez ou téléchargez le repository GitHub :
```system
git clone https://github.com/kindertheo/P7-BileMo.git
```
*   Configurez vos variables d'environnement telles que la connexion à la base de données .env

*   Installez les dépendances avec Composer :
```system
composer install
```

*   Créez la structure de la base de données :
```system
php bin/console doctrine:schema:create
```

*   Créez les fixtures vous permettant de tester :
```system
php bin/console doctrine:fixtures:load
```

*   Accédez à l'aide de l'API :
127.0.0.1:8000/doc (en fonction de l'adresse d'hébergement de l'API)

*   Se connecter et obtenir un token :
Requête GET sur http://127.0.0.1:8000/login

``` 
{"username": "user@user.com", "password": "password"}
{"username": "client@client.com", "password": "password"}
{"username": "admin@admin.com", "password": "password"}
```
