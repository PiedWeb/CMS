UPDATE with BC BREAK
==================

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
      UPDATE `user` SET `roles`= '["ROLE_SUPER_ADMIN"]' WHERE _rowid_='2';
      ```


## Update to 0.0.27

-   Create symlink for config file
    ```
    ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/doctrine.yaml config/packages/doctrine.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/framework.yaml config/packages/framework.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/liip_imagine.yaml config/packages/liip_imagine.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/security.yaml config/packages/security.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/sonata_admin.yaml config/packages/sonata_admin.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/sonata_translation.yaml config/packages/sonata_translation.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/sonata_translation.yaml config/packages/sonata_translation.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/translation.yaml config/packages/translation.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/twig.yaml config/packages/twig.yaml && ln -s -f ../../vendor/piedweb/cms-bundle/src/Resources/config/packages/vich_uploader.yaml config/packages/vich_uploader.yaml
    ```
- Edit piedweb_cms to add base_url parameter /!\ big deal for static website

- Edit sonata_translation to set correct languages
