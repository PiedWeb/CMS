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

## Editor Tips

### Create a redirection
From the admin, Add a Page > Put Title and Slug as you want > Put mainContent as `Location: http://example.eg/` or `Location: http://example.eg/ 302`
Your redirection will be at the normal page route with the slug set `domain.com/slug`

### Add js link
```
{{ jslink('Pied Web', path('piedweb_cms_homepage'))|raw }}
```
