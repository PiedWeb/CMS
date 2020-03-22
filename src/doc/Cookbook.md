Cookbook
============

## Installation and Personalization

### Editing an entity

When you install a new project, entities are installed full featured.
To remove default feature or add new one, you just need to edit `Entity/Page.php`, `Entity/Media.php` or `Entity/User.php` in your `src` folder.
Look at [PiedWeb\CMSBundle\Entity\Page.php](https://github.com/PiedWeb/CMS/blob/master/src/Entity/Page.php) & co for full traits list.

You can easily extends and override Admin as well.

### Edit the navbar

Override default navbar by creating `page.html.twig` in your project at `templates/bundles/PiedWebCMSBundle/page` using [menu parameters](https://github.com/PiedWeb/CMS/blob/master/src/Resources/views/page/_menu.html.twig)

#### i18n link on logo

```
{% set logo = page.locale == 'en' ? {'alt' : 'Alps Guide', 'href':'/en'} : {'alt':'Accompagnateur Vercors Pied Vert'} %}
```

### Make internal link

```twig
{{ homepage() }}
{{ page('my-slug') }}
```

### Optimize CSS

Activate purge css commented code in `webpack.config.js`

## Maintaining

### Update all cached image

Command line

```
bin/console media:cache:generate
```

## Editor Tips

See help in the admin (`/admin/markdown-cheatsheet`).

### Install CKEditor

https://gist.github.com/RobinDev/c81d6fe3f859c21865c7e100cca9e654
