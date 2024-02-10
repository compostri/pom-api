# Introduction

**Composteur-api** décrit l'API destinée à Compostri pour gérer leur parc de composteurs.

## Stack technique

- [PHP 7.3.x](https://www.php.net/manual/fr/migration73.php)
- [Symfony](https://symfony.com/)

# Pré-requis

1. Disposer d'un environnement permettant de créer des BDD MySQL (comme [Mamp](https://www.mamp.info/fr/), [Wamp](http://www.wampserver.com/), etc.)
2. Créer une nouvelle BDD, par exemple **composteurs**
3. Configurer votre serveur HTTP préféré pour accéder à l'API. Par exemple sous Apache :

```
<VirtualHost *:80>
  ServerName composteur-api.test
  ServerAlias composteur-api.test
  DocumentRoot "/chemin-vers-votre-depot/composteur-api/public"
  <Directory "/chemin-vers-votre-depot/composteur-api/public/">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>

```
4. Modifier votre fichier `hosts` pour pouvoir accéder à votre `ServerName`
```
127.0.0.1	composteur-api.test
```

# Installation en local

1. Cloner le dépot
2. Exécuter `composer install`
3. Dupliquer le fichier `.env.dist` en `.env`
4. Vérifier que la variable `APP_ENV` est bien à `dev`
5. Vérifier que la variable `DATABASE_URL` correspond à votre serveur mysql, et que `db_name` a bien été remplacé
6. Générer via openSSL les clés JWT publique/privée
```
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
7. Vérifier que la variable `JWT_PASSPHRASE` correspond bien à la passphrase utilisée pour la génération des clés
8. Lancer le script de migration 
```
bin/console doctrine:migrations:migrate
```
9. _Pour developer en local lancer les fixtures
```
php bin/console doctrine:fixtures:load
```
