# Bilemo

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/35f1968e4eb94b86a37fbb505cb88d0c)](https://app.codacy.com/app/JeanD34/p7?utm_source=github.com&utm_medium=referral&utm_content=JeanD34/p7&utm_campaign=Badge_Grade_Dashboard)


This REST API provides a catalog of mobiles for our clients, and the possibility to manage their users.

[Visit the api doc](https://bilemo.jeandescorps.fr/doc)

## Build With

- Symfony 4.2.7
- LexikJWTAuthenticationBundle
- JMSSerializerBundle
- BazingaHateoasBundle
- NelmioApiDocBundle


## Installation

1 - Clone or download the project

```https://github.com/JeanD34/p7.git```

2 - Update your database identifiers in bilemo/.env

````DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name````

3 - Install composer -> [Composer installation doc](https://getcomposer.org/download/)

4 - Run composer.phar to install dependencies

```php bin/console composer.phar update```

5 - Import bilemo.sql to your database, it contains data set

6 - Don't forget to add a JWT_PASSPHRASE in bilemo/.env

```JWT_PASSPHRASE=YourPassPhrase```

7 - Generate the JWTAuthentication SSH keys ([Official documentation](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#installation
))

 ```
 mkdir -p config/jwt
 openssl genrsa -out config/jwt/private.pem -aes256 4096
 openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Usage

Login link :

```/login```

An client account is already available, use it to test the API :

```
{
   "email" : "client.1@gmail.com",
   "password" : "Client.1!"
}
```

An admin account is already available, use it to test the API :

```
{
   "email" : "bilemo@gmail.com",
   "password" : "Bilemo.1!"
}
```

## Documentation

You can see the full documentation here => [Bilemo Api Documentation](https://bilemo.jeandescorps.fr/doc)
