Cookbook
============

## Installation and Personalization

### Editing an entity
When you install a new project, entities are installed full featured.
To remove default feature or add new one, you just need to edit `Entity/Page.php`, `Entity/Media.php` or `Entity/User.php` in your `src` folder.
Look at [PiedWeb\CMSBundle\Entity\Page.php](https://github.com/PiedWeb/CMS/blob/master/src/Entity/Page.php) & co for full traits list.

### Edit the navbar
Override default navbar by creating `page.html.twig` in your project at `src/Resources/PiedWebCMSBundle/views`
```
{% extends '@PiedWebCMS/page/page.html.twig' %}

{% block navbar %}
    {% include '@PiedWebCMS/component/_menu.html.twig' with {'logo':{'anchor':logo_alt}} only %}
{% endblock %}
```

### i18n (internationalization)
It's by default activate (thanks Gedmo). To configure languages, edit `config/packages/piedweb_cms.yaml` and `config/packages/sonata_admin.yaml`

Make internal link
```twig
{{ homepage() }}
{{ page('my-slug') }}
```

### Canonical with base domain

In your `config/packages/twig.yaml` add
```
twig:
    ...
    globals:
        ...
        app_base_url: https://mydomain.tdl`
```

### Optimize CSS

Activate purge css commented code in `webpack.config.js`

### Install CKEditor

https://gist.github.com/RobinDev/c81d6fe3f859c21865c7e100cca9e654

## Maintaining

### Update all cached image

Command line

```
bin/console media:cache:generate
```

## Editor Tips

### Create a redirection
From the admin, Add a Page > Put Title and Slug as you want > Put mainContent as `Location: http://example.eg/` or `Location: http://example.eg/ 302`
Your redirection will be at the normal page route with the slug set `domain.com/slug`

### Add js link
```
{{ jslink('Pied Web', path('piedweb_cms_homepage'))|raw }}
```

### Add a lazy load image image (with [tyrol](https://github.com/PiedWeb/piedweb-tyrol-free-bootstrap-4-theme/blob/master/src/js/helpers.js#L3))
```
<span data-img="/media/thumb/media/2018/mon-image.jpg">Mon Image</span>
```

### Add a paragraph under the title in default theme
```
my paragraphe under the title
<!--break-->
following content
```

### Render children page as blog timeline
```twig
<div class=row>
{% include '@PiedWebCMS/component/_pages_list.html.twig' with {pages: page.childrenPages|reverse} only %}
</div>

// Or children of children (like Blog Index on a two level organizations
{% set pages = [] %}
{% for p in page.childrenPages|reverse %}
{% set pages = pages|merge(p.childrenPages) %}
{% endfor %}
```

### Line break in Markdown

Add two spaces at the end of the line [#](https://github.com/michelf/php-markdown/blob/lib/test/resources/markdown.mdtest/Markdown%20Documentation%20-%20Syntax.text#L184).

### Rich Content

You can set specific content in your main content and grab it later in your theme. Eg with `subtitle` :
```twig
// My main content :
<!--"subtitle"--Mon sous-titre !--/-->

// To grab it in a twig file:
{{ page.getEmc('subtitle') }}
```
