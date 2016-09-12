# SteamAuthBundle
Steam Authentication for Symfony

## Usage
A couple things are necessary for this bundle to work. Your user class will have to be managed by Doctrine ORM (does not support Mongo or Propel at the moment.) In the `app/config/config.yml` you will need the following parameters
```yml
steam_auth:
    steam_key: XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
    user_class: AppBundle\Entity\User
```

And your security yml firewall will need
* `steam` as a firewall option
* with a `default_route` option with the name of the route to go to once a user has logged in
* a user provider marked as `steam.user_provider`

```yml
security:
    providers:
        steamauth:
            id: steam.user_provider

    firewalls:
        main:
            steam:
                default_route: home
```

Your User class will need to implement `SteamAuthBundle\Security\User\SteamUserInterface` as well as `Symfony\Component\Security\Core\User\UserInterface`

Note that this bundle will create a new instance of your user class with an empty default constructor, will set the username, nickname, avatar and password, and will persist it to the database. This occurs when a user signs in with their steam account and do not already exist in your database.

This bundle also works with Symfony's Remember Me functionality if you wish to use it.

```yml
main:
    steam:
        default_route: home
    remember_me:
        secret: '%secret%'
```
