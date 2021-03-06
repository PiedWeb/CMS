# UPDATE with BC BREAK

## Update to ...

- Delete symlinks in `config/packages`

- Update database

  ```
  bin/console make:migration && bin/console doctrine:migrations:migrate
  ```

- Update config by moving static under apps where first host is static.domain.

  ```
  ...
   apps:
     - {hosts: [mywebsite.com, edit.mywebsite.com], base_url: https://mywebsite.com}
  ```

- Delete `feed*.xml` and `sitemap*` files in `public`

- Maybe twig config file throw an error, read & fix

- Search for all `<!--` in `mainContent` and put them in `customProperties`'s textarea

## Update to 0.0.84

- Remove staticBundle (if you use it), it's now part from core

```
composer remove piedweb/static-bundle
```

- Update routes in `config/routes/routes.yaml`

- Move `app.static_domain` from `config/services.yaml` to `config/packages/piedweb_cms.yaml` as

  ```piedweb_cms:
         static:
             domain: mydomain.tld
  ```

- If you were overriding StaticBundle, see commit #9f96f67 to update your code

- See `main.js` in ThemeComponent, see this [commit](https://github.com/PiedWeb/ThemeComponentBundle/commit/91be82bed6032e30116b8147a7729c8cce9e0de9)
  And add to your twig config :

  ```
          "%kernel.projet_dir%/vendor/piedweb/theme-component-bundle/src/Resources/views": PiedWebThemeComponent
  ```

- Moving from SwiftMailer to symfony/mailer, switch to MAILER_DSN in `.env`

## Update to 0.0.59

- Update database

  ```
  bin/console make:migration && bin/console doctrine:migrations:migrate
  ```

- to activate email notifer

  - params default mailer (`.env`)
  - params `email_to_notify` in `piedweb_cms.yaml` config file.

- remove FOSUser :

  - update security if it's not a link (see current `src/Resources/config/packages/security.yaml`)
  - update roles's field in user table with json format : `["ROLE_SUPER_ADMIN"]`
    ```
    sqlite3 var/app.db
    UPDATE `user` SET `roles`= '["ROLE_SUPER_ADMIN"]';
    UPDATE `page` SET `locale`= 'fr';
    ```

- remove from composer.json

  ```
              "require": "4.4.*"
  ```

  and bump php to 7.3

- remove `localized_page` from `config/routes.yaml`

## Update to 0.0.27

- Create symlink for config file
  ```
  ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/doctrine.yaml config/packages/doctrine.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/framework.yaml config/packages/framework.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/liip_imagine.yaml config/packages/liip_imagine.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/security.yaml config/packages/security.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/sonata_admin.yaml config/packages/sonata_admin.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/sonata_translation.yaml config/packages/sonata_translation.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/sonata_translation.yaml config/packages/sonata_translation.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/translation.yaml config/packages/translation.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/twig.yaml config/packages/twig.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/vich_uploader.yaml config/packages/vich_uploader.yaml
  ```
- Edit piedweb_cms to add base_url parameter /!\ big deal for static website

- Edit sonata_translation to set correct languages
