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
* [Extension](#extension)
* [CookBook](#cookbook)
* [Todo](#todo)
* [Contributors](#contributors)
* [Licence](#licence)

## Installation

```
# Supposing composer is installed globally
curl https://raw.githubusercontent.com/PiedWeb/CMS/master/install-cms >> install-cms && chmod +x install-cms && ./install-cms ./my-folder
# Valid 'a' to install all recipes !

# Else, look at :
https://raw.githubusercontent.com/PiedWeb/CMS/master/install-cms
# Each step is describe.
```

## Extension

List of Bundles wich extend this one:

* [Contact](https://github.com/PiedWeb/ContactBundle)
* [Static](https://github.com/PiedWeb/StaticBundle)
* [Faq](https://github.com/PiedWeb/FaqBundle)
* ...


## Cookbook

### Override Entity without writing more than 3 lines of code
When you install a new project, entity are installed full featured.
To remove default feature or add new one, you just need to override or extends `Entity/Page.php`, `Entity/Media.php` or `Entity/User.php`.
In your app, create a new file (copying ?!) in your `src/Entity`.
Then, edit `config/packages/piedweb_cms.yaml` to correctly set your new entity.

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
It's by default activate (thanks Gedmo). To configure languages, edit `config/packages/piedweb_cms.yaml` and `config/packages/sonata_admin.yaml`

### Add js link
```
{{ jslink('Pied Web', path('piedweb_cms_homepage'))|raw }}
```

## TODO
(half in french)

### For soon
- test, test, test (ahah !)
- StaticBundle not work when you change the route with a prefix (i18n for eg)
- traduire media et user admin et admin label (services.yaml && sonata_admin)

- faciliter le doublie fichier JS/CSS avec un webpack préparé comme

### Next

- Think about implement cache :
```
cache solution https://github.com/FriendsOfSymfony/FOSHttpCacheBundle/ https://github.com/WyriHaximus/HtmlCompress
```

### Next Next

- Command line tool to generate Overriding entity (example: `piedweb:create-entity Page -traits=@PiedWebCMS\Entity\PageExtented,@PiedWebFaq\Entity\PageFaq`)
- Change translatable (gedmo) to make something more scalable and easy to use with repo (knp ?, other?)
- search for all "todo" in the code
- Extension: load block via ajax (like contact)
- Extension : charger la liste des template disponible && Permettre l'édition (la création) de views/component directement online


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
