<p align="center"><a href="https://piedweb.com" rel="dofollow">
<img src="https://raw.githubusercontent.com/PiedWeb/piedweb-devoluix-theme/master/src/img/logo_title.png" width="200" height="200" alt="Agence Communication Vercors" />
</a></p>

# CMS for Developper

Yet ! Another CMS :)

PiedWebCMS is a Symfony Bundle built with traits. It permit to get the basic functionalities for a website... to go further or to use as it.

Build on top on [Symfony 4](https://github.com/symfony/symfony), [VichUploader](https://github.com/dustin10/VichUploaderBundle), [LiipImagine](https://github.com/liip/LiipImagineBundle), [Sonata Admin](https://github.com/sonata-project/SonataAdminBundle), [FOSUser](https://github.com/FriendsOfSymfony/FOSUserBundle) and more (see [composer.json](https://github.com/PiedWeb/CMS/blob/master/composer.json)).


* [Installation](#installation)
    * [Packagist](https://packagist.org/packages/piedweb/cms-bundle)
* [Test](#test)
* [Documentation](#documentation)
* [Extension](#extension)
* [Todo](#todo)
* [Contribute](#contribute)
* [Licence](#licence)



## Installation

Supposing composer is installed globally
```
curl https://raw.githubusercontent.com/PiedWeb/CMS/master/install-cms >> install-cms && chmod +x install-cms && ./install-cms ./my-folder
# Valid 'a' to install all recipes !
```

Else, look at [`install-cms`](https://raw.githubusercontent.com/PiedWeb/CMS/master/install-cms) where each step is describe.



## Test

Front End tests are in [a different git repo](https://github.com/PiedWeb/frontend-test).

Tu run it, [install a new instance of PiedWebCMS](#installation), then you need :
```
git clone git@github.com:PiedWeb/frontend-test.git && cd frontend-test
```

Configure, in `cypress.json`, the `baseURL` of your PiedWebCMS instance.
And run the test
```
# headless
yarn run

# To open cypress :
yarn open
```



## Documentation

Not yet documented. But there is [a cookbook](https://github.com/PiedWeb/CMS/blob/master/src/doc/Cookbook.md).



## Extension

List of Bundles wich extend this one:

* [Reservation](https://github.com/PiedWeb/ReservationBundle)
* [Static](https://github.com/PiedWeb/StaticBundle)
* [Contact](https://github.com/PiedWeb/ContactBundle)
* [Faq](https://github.com/PiedWeb/FaqBundle)
* ...



## TODO

### For soon

- StaticBundle not work when you change the route with a prefix (i18n for eg)
- faciliter le doublie fichier JS/CSS avec un webpack préparé comme

### Next

- implement cache ? (`https://github.com/FriendsOfSymfony/FOSHttpCacheBundle/` `https://github.com/WyriHaximus/HtmlCompress`)
- Change translatable (gedmo) to make something more scalable and easy to use with repo (knp ?, other?)
- search for all "todo" in the code
- Extension: load block via ajax (like [Contact](https://github.com/PiedWeb/ContactBundle))
- Extension : load all templates and template blocks avalaible and permit edition online (better template organization)
- Transform image reOrganizer in list (to permit showing then horizontally)



## Contribute


### To send a pull resquest

1. Please check if test are still running without error (see [running test](#test) )
2. Check coding standard before to commit : `php-cs-fixer fix src --rules=@Symfony --verbose && php-cs-fixer fix src --rules='{"array_syntax": {"syntax": "short"}}' --verbose`


### Contributors

* [Robin](https://www.robin-d.fr/) / [Pied Web](https://piedweb.com)
* ...




## License

MIT (see the LICENSE file for details)


[![Latest Version](https://img.shields.io/github/tag/piedweb/cms.svg?style=flat&label=release)](https://github.com/PiedWeb/CMS/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](https://github.com/PiedWeb/CMS/LICENSE.md)
[![Build Status](https://img.shields.io/travis/PiedWeb/CMS/master.svg?style=flat)](https://travis-ci.org/PiedWeb/CMS)
[![Quality Score](https://img.shields.io/scrutinizer/g/piedweb/cms.svg?style=flat)](https://scrutinizer-ci.com/g/piedweb/cms)
[![Total Downloads](https://img.shields.io/packagist/dt/piedweb/cms-bundle.svg?style=flat)](https://packagist.org/packages/piedweb/cms-bundle)
