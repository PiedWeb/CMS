<p align="center"><a href="https://dev.piedweb.com" rel="dofollow">
<img src="https://raw.githubusercontent.com/PiedWeb/piedweb-devoluix-theme/master/src/img/logo_title.png" width="200" height="200" alt="PHP Packages Open Source" />
</a></p>

# NOT READY - WAIT V1

# CMS Extendable to make peace between User, Editor, Developer, Designer and SEO

[![Latest Version](https://img.shields.io/github/tag/piedweb/cms.svg?style=flat&label=release)](https://github.com/PiedWeb/CMS/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Build Status](https://img.shields.io/travis/PiedWeb/CMS/master.svg?style=flat)](https://travis-ci.org/PiedWeb/CMS)
[![Quality Score](https://img.shields.io/scrutinizer/g/piedweb/cms.svg?style=flat)](https://scrutinizer-ci.com/g/piedweb/cms)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/PiedWeb/CMS.svg?style=flat)](https://scrutinizer-ci.com/g/PiedWeb/CMS/code-structure)
[![Total Downloads](https://img.shields.io/packagist/dt/piedweb/cms-bundle.svg?style=flat)](https://packagist.org/packages/piedweb/cms-bundle)

And if your team is only you, this CMS will be perfect to marry with you as **Webmaster**.

## Main Features

### **Editor** : feel "like" in wordpress

- Multi-site, Multi-language (i18n), Multi-domain or just one simple website
- Old School, simple, functionnable default Admin
- Write as you prefer in **a rich Text editor**, in **Markdown** or directly in **html** (with **Twig** possibilities !!)
- Easily extendable ([extensions repository](#extension)) or ask a developper what you wish

### **Developer** : feel **at home** if you ever used Symfony

- Build on top on Symfony and other [fantastic well maintained packages](./composer.json)
- Build as a symfony bundle, **extendable** with symfony bundle
- **Tested** / **Traits** / **Command**

### **Designer**

- Create new theme extending other
- Stack : **Twig** / **Webpack**

### **SEO** : feel like **wikipedia**

_ Title / H1 / Description / Url Rewriting
_ i18n (`link alternate hreflang`) easy way
_ Links Watcher (dead links, redirection, etc.)
_ Links Improver (links suggestion on writing, or automatic adding)
_ Blazing Fast (static website with dynamic possibilities_)

... and more to discover, just install it in a few seconds !

## Installation

Supposing composer is installed globally

```
curl https://raw.githubusercontent.com/PiedWeb/CMS/master/install-cms >> install-cms && chmod +x install-cms && ./install-cms ./my-folder
# Valid 'a' to install all recipes !
```

Else, look at [`install-cms`](https://raw.githubusercontent.com/PiedWeb/CMS/master/install-cms) where each step is describe.

## Options

...todo describe configuration

## Update

Run `composer update`.

If you install before v1 check [Update.md](https://raw.githubusercontent.com/PiedWeb/CMS/master/UPDATE.md).

## Test

Run `phpunit`

## Documentation

### Editor

If you use the default admin, everything is documented directly inside the admin for _daily editing_.

- Advanced configuration, look at [options](#otions).
- [Extend your app with existing extension](#extension) ?
- [Customize your design with theme](#theme)

### <a name="theme"></a> Designer : Extend or Create a Theme

... todo ...

### Developer : Extend

### Developper : Understand the logic

The **core** correspond to basic functionnalities for _page_, _media_ and _user_.

Else are **extension** (wich can be disabled or move to another bundle).

The code for the core follow the [symfony's default directory structure](https://symfony.com/doc/current/best_practices.html#use-the-default-directory-structure) for the **core**. For **extension**, liberty is taken to simplify the directory structure.

## Extension

List of existing extension wich are not in the **core** :

- [Conversation](https://packagist.org/packages/piedweb/conversation)

## TODO

- [ ] Gérer un système d'extension viable pour l'admin : à l'install, créer les fichiers Admin qui étendent l'admin de base
      L'ajout d'un plugin modifie automatiquement ce nouveau fichier en ajoutant le code nécessaire (ajout d'une trait + édition d'une fonction)
      Retro-compatibilité : créer le fichier admin + le services (autowire) si il n'existe pas
- [ ] Better management assets
- [ ] Installation without composer (download composer if not installed)
- [ ] ...
- [ ] Default bootstrap 5, default Tailwind in core
- [ ] Intégrer LinksImprover (+ UX), après précédent
- [ ] name suggester : parse content, find words or multiple words used only in this doc, suggest it as potential name
- [ ] Test the code, search for all "todo" in the code,
- [ ] export/import FLAT FILES (spatie/yaml-front-matter, vérif à chaque requête pour une sync constante admin <-> flat files)
- [ ] Release V1
- [ ] Look for a better writer experience (https://github.com/front/g-editor or https://editorjs.io)
- [ ] Create a page from a Media (media edit) => button to create a new page with title = name and mainImage = Media
- [ ] Multi upload
- [ ] Intégrer Schema.org dans le backend d'une page
- [ ] Better management for social network from backend (plugin ?!)
- [ ] Scan : scanner une page en direct + scanner plus de choses (liens externes, texte alternative manquant, etc.)

- [ ] Documenter : extend your cms (short: it's like writing a symfony app, open vs code, navigate in the code)
- [ ] Documenter : create a extension (short: create a symfony bundle)
- [ ] Documenter : create a template (short: create a new extension, prepenting twig with pat..to..view..folder: @TemplateName)
- [ ] Do something with the [cookbook](https://github.com/PiedWeb/CMS/blob/master/docs/Cookbook.md)

- [ ] Settings Manager (simple textarea permitting to edit piedweb_cms config and parameters ? and rebooting cache)

### To plan

- [ ] Add https://github.com/nan-guo/Sonata-Menu-Bundle
- [ ] Move route to annotation (less extendable but more pratical with priority)
- [ ] Move media to var (and create a link ?!)

## Credits

- [PiedWeb](https://piedweb.com)
- [All Contributors](https://github.com/PiedWeb/CMS/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[![Latest Version](https://img.shields.io/github/tag/piedweb/cms.svg?style=flat&label=release)](https://github.com/PiedWeb/CMS/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Build Status](https://img.shields.io/travis/PiedWeb/CMS/master.svg?style=flat)](https://travis-ci.org/PiedWeb/CMS)
[![Quality Score](https://img.shields.io/scrutinizer/g/piedweb/cms.svg?style=flat)](https://scrutinizer-ci.com/g/piedweb/cms)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/PiedWeb/CMS.svg?style=flat)](https://scrutinizer-ci.com/g/PiedWeb/CMS/code-structure)
[![Total Downloads](https://img.shields.io/packagist/dt/piedweb/cms-bundle.svg?style=flat)](https://packagist.org/packages/piedweb/cms-bundle)
