<p align="center"><a href="https://piedweb.com">
<img src="https://raw.githubusercontent.com/PiedWeb/piedweb-devoluix-theme/master/src/img/logo_title.png" width="200" height="200" alt="theme devoluix bootstrap 4" />
</a></p>

# CMS for Developper

Yet ! Another CMS :)

PiedWebCMS is a Symfony Bundle built with traits. It permit to get the basic functionalities for a website... to go further or to use as it.

Build on top on [Symfony 4.1](https://github.com/symfony/symfony), [VichUploader](https://github.com/dustin10/VichUploaderBundle), [LiipImagine](https://github.com/liip/LiipImagineBundle), [Sonata Admin](https://github.com/sonata-project/SonataAdminBundle), [FOSUser](https://github.com/FriendsOfSymfony/FOSUserBundle) and more (see [composer.json](https://github.com/PiedWeb/CMS/blob/master/composer.json)).

If you are interested in, demo is coming soon, stay watching.

* [Installation](#installation)
    * [Packagist](https://packagist.org/packages/piedweb/cms-bundle)
* [CookBook](#cookbook)
* [Todo](#todo)
* [Contributors](#contributors)
* [Licence](#licence)

## Installation

```
# Fresh install
composer create-project piedweb/skeleton ./my-project
# Accept all recipes
# Delete useless just created files in config/routes

# ---
# Install like a bundle
composer require piedweb/cms-bundle
# Then udpate your config file (by copying the from this bundle)
# ---

# Check .env is configure (Database)
# to use it immediatly set DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"

# Instal front end assets
yarn
yarn encore dev
```

## Usage

### You can use it as is

```bash
# Create Database:
php bin/console doctrine:schema:create

# Add an admin user :
php bin/console fos:user:create admin@example.tld admin@example.tld p@ssword
php bin/console fos:user:promote admin@example.tld ROLE_SUPER_ADMIN

# Edit config/services.yaml and remove the two first lines (parameters:\n   locale: 'en')
# It's erasing the next importing parameters. TODO: remove this step

# Install Sonata Front-End Assets
php bin/console assets:install

# Launch Server and Play
php bin/console server:run

# optional: change default app parameters `config/parameters.yml`
```

### Or you can...

... customize everything by [overriding any part of the bundle](https://symfony.com/doc/current/bundles/override.html)
(You will need to read a bit of code because it's not yet full documented)

## Extension

List of Bundles wich extend this one:

* [Contact](https://github.com/PiedWeb/ContactBundle)
* ...


## Cookbook

### Override Entity without writing more than 3 lines of code
When you install a new project, entity are installed full featured.
To remove default feature or add new one, you just need to override `Entity/Page.php`, `Entity/Faq.php`, `Entity/Media.php` or `Entity/User.php`.
Copy the file in your `src/Entity` and edit what you want.

### Edit the navbar
- Override default navbar by creating `page.html.twig` in your project at `src/Resources/PiedWebCMSBundle/views`
```
{% extends '@PiedWebCMS/page/page.html.twig' %}

{% block navbar %}
    {% include '@PiedWebCMS/component/_menu.html.twig' with {'logo':{'anchor':logo_alt}} only %}
{% endblock %}
```

### Create a redirection
From the admin, Add a Page > Put Title and Slug as you want > Put mainContent as `Location: http://example.eg/` or `Location: http://example.eg/ 302`
Your redirection will be at the normal page route with the slug set `domain.com/slug`


### i18n (internationalization)
It's by default activate (thanks Gedmo). To configure languages, edit `config/parameters.yaml` and `config/packages/sonata_admin.yaml`


## TODO
(half in french)

### For soon
- Handle the error on non requestion running for `stof_doctrine_extensions.listener.translatable` `setTranslatableLocale` in config/services.yaml
- Créer une recette (recipe flex) pour enlever du skeleton config/routes & co et les ajouter via flex (et essayer Skeleton)
- test, test, test (ahah !)
- search for all "todo" in the code
- "Afficher" dans PageAdmin ne fonctionne pas sur les pages i18n (renvoie vers la page default_locale)
- la suppression d'une page qui était parente d'une autre, va créer des erreurs (et l'impossibilité d'éditer la page fille)
- idem image ?

### Next
- admin homepage sexiest (__or redirect to AdminPage__ cf https://github.com/sonata-project/SonataAdminBundle/issues/5297)

- un lien javascript pour les pages avec metaRobots 'no-index' (extension twig pour générer les liens!)

- Edition d'une Page (à Réfléchir !)
```
--- Minimum: intégrer ACE pour mainContent (https://ace.c9.io/ (https://github.com/heygrady/ace-mode-twig))
--- Inspiration: https://github.com/codecasts/codecasts
--- Charger la liste des template disponible && Permettre l'édition (la création) de views/component directement online
--- Une gestion du mainContent via un drag'n Drop de component
--- quitter l'admin traditionnel pour un ediit on the page et intégrer un éditeur enrichi ?
--- la gestion de componenent directement dans l'admin
```
- une interface d'upload d'images plus efficaces (drag'n drop, multiple, url, import...)
- identifier la couleur d'une image et l'enregistrer dans Media pour faire un placeholder cohérent
- l'upload d'une image via une page ne s'ajoute pas automatiquement (sonata)

- gérer un breadcrumb (function twig ou juste component) #Easy

- CACHE :
```
-- Be able to generate like a static website
   domain.tld on static/
   admin.domain.tld on public (so website stille avalaible, add httpsswd)
   method: new function in PageController, listAllPages, store in ../static, copy assets (yarn, php assets)
   update: cron? (berk) || update queue after each modification => Bundle
   deploy another place (see sculpin => https://sculpin.io/getstarted/#deploy-sculpin)
   todo: remove contact form & ajax footer from default install

-- Manage antother cache solution https://github.com/FriendsOfSymfony/FOSHttpCacheBundle/ https://github.com/WyriHaximus/HtmlCompress
```

### Next Next

- Change translatable (gedmo) to make something more scalable and easy to use with repo (knp ?, other?)

- Create interface for entity Traits

- More pluggable :
```
- Move twig in a separate Bundle
- FAQ in a separate Bundle
- Media in a separate Bundle (?)
```

- free default theme

## Contributors

No rules (yet) to contribute. It's a small individual project.
If you use it, or just copy a piece of it, I will be glad to know it (contact@robin-d.fr) :)

* [Robin](https://www.robin-d.fr/) / [Pied Web](https://piedweb.com)
* ...

Check coding standard before to commit :
```
php-cs-fixer fix src --rules=@Symfony --verbose
php-cs-fixer fix src --rules='{"array_syntax": {"syntax": "short"}}' --verbose
```

## License

MIT (see the LICENSE file for details)

/!\ If you use it as it with assets file, it's depending on [piedweb-devoluix-theme](https://github.com/PiedWeb/piedweb-devoluix-theme) wich is not MIT. (see  [todo](#todo) : I plan to release it with a totaly free default theme)
