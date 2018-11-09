

TODO :

- Créer un bundle FAQ (mais comment alors gérer l'administation)
- les @ORM sur les traits servent-il à quelque chose ?
- Changer Media pour https://github.com/sonata-project/SonataMediaBundle ?
- Virer \Entity\Contact ou faire un autre bundle ?!
- /!\ J'ai enlever la propriété $pageType... faire sans !
- Upload image, vérifier lors de la validation si name n'existe, sinon post-fixer
   https://stackoverflow.com/questions/14781688/doctrine-check-if-record-exists-based-on-field
  Ou ajouter un canonicalName = time + name
- ContactController: Activer l'envoi des mails (décommenter + tester)
- dans #ovveride fos user#l/ayout.html.twig remplacer max-width:250px; margin:auto; par col-lg-1 et tester
- gérer le menu/le footer : gérer les blocks
- créer une fonction twig pour faire facilement appel à _page_list_ type {{ page_list('titre', 'description',  page.childrenPage) }}
- gérer un breadcrumb

Questions:
- Est-ce que les trait des Bundle permettent l'héritage des annotations ???
-- Il semble pour Doctrine : https://medium.com/@galopintitouan/using-traits-to-compose-your-doctrine-entities-9b516335119b
