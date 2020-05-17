<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\Form\Type\CollectionType;

class PageAdmin extends AbstractAdmin
{
    use AdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
        '_per_page' => 256,
    ];

    protected $perPageOptions = [16, 250, 1000];

    protected $maxPerPage = 1000;

    protected $liipImage;

    protected $defaultLocale;

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->listModes['tree'] = [
            'class' => 'fa fa-sitemap',
        ];
    }

    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    public function configure()
    {
        $this->setTemplate('edit', '@PiedWebCMS/admin/page_edit.html.twig');
        $this->setTemplate('show', '@PiedWebCMS/admin/page_show.html.twig');
    }

    /**
     * Check if page entity's item $name exist.
     */
    protected function exists(string $name): bool
    {
        return method_exists($this->getContainer()->getParameter('app.entity_page'), 'get' . $name);
    }

    protected function configureFormFieldsBlockDetails(FormMapper $formMapper): FormMapper
    {
        $formMapper->with('admin.details', ['class' => 'col-md-5']);

        if ($this->exists('parentPage')) {
            $formMapper->add('parentPage', EntityType::class, [
                'class' => $this->getContainer()->getParameter('app.entity_page'),
                'label' => 'admin.page.parentPage.label',
                'required' => false,
            ]);
        }

        $this->configueFormFieldTranslations($formMapper);

        $formMapper->add('createdAt', DateTimePickerType::class, [
            'format' => DateTimeType::HTML5_FORMAT,
            'dp_side_by_side' => true,
            'dp_use_current' => true,
            'dp_use_seconds' => false,
            'dp_collapse' => true,
            'dp_calendar_weeks' => false,
            'dp_view_mode' => 'days',
            'dp_min_view_mode' => 'days',
            'label' => 'admin.page.createdAt.label',
        ]);

        if ($this->exists('name')) {
            $formMapper->add('name', TextType::class, [
                'label' => 'admin.page.name.label',
                'required' => false,
                'help' => 'admin.page.name.help',
            ]);
        }

        if ($this->exists('excrept')) {
            $formMapper->add('excrept', TextareaType::class, [
                'required' => false,
                'label' => 'admin.page.excrept.label',
                'help' => 'admin.page.excrept.help',
            ]);
        }

        if ($this->exists('relatedPages')) {
            $formMapper->add('relatedPages', ModelAutocompleteType::class, [
                'required' => false,
                'multiple' => true,
                'class' => $this->getContainer()->getParameter('app.entity_page'),
                'property' => 'title', // or any field in your media entity
                'label' => 'admin.page.relatedPage.label',
                'btn_add' => false,
                'to_string_callback' => function ($entity) {
                    return $entity->getTitle();
                },
            ]);
        }

        $this->configureFormFieldOtherProperties($formMapper);

        $formMapper->end();

        return $formMapper;
    }

    protected function configureFormFieldsBlockContent(FormMapper $formMapper): FormMapper
    {
        $formMapper->with('admin.page.mainContent.label', ['class' => 'col-md-7']);
        $formMapper->add('mainContent', TextareaType::class, [
            'attr' => [
                'style' => 'min-height: 80vh;font-size:125%; max-width:900px',
                'data-editor' => 'markdown',
                'data-gutter' => 0,
            ],
            'required' => false,
            'label' => ' ',
            'help' => 'admin.page.mainContent.help',
        ]);
        $formMapper->add('mainContentIsMarkdown', null, [
            'required' => false,
            'label' => 'admin.page.markdown.label',
            'help' => 'admin.page.markdown.help',
        ]);
        $formMapper->end();

        return $formMapper;
    }

    public function configureFormFieldOtherProperties(FormMapper $formMapper): FormMapper
    {
        return !$this->exists('otherProperties') ? $formMapper : $formMapper->add('otherProperties', null, [
            'required' => false,
            'attr' => [
                'style' => 'min-height: 10vh;font-size:125%;',
                'data-editor' => 'yaml',
            ],
            'label' => 'admin.page.otherProperties.label',
            'help' => 'admin.page.otherProperties.help',
        ]);
    }

    public function configueFormFieldTranslations(FormMapper $formMapper): FormMapper
    {
        return $formMapper->add('translations', ModelAutocompleteType::class, [
            'required' => false,
            'multiple' => true,
            'class' => $this->getContainer()->getParameter('app.entity_page'),
            'property' => 'slug',
            'label' => 'admin.page.translations.label',
            'help' => 'admin.page.translations.help',
            'btn_add' => false,
            'to_string_callback' => function ($entity) {
                return $entity->getLocale()
                    ? $entity->getLocale() . ' (' . $entity->getSlug() . ')'
                    : $entity->getSlug(); // switch for getLocale
                // todo : remove it in next release and leave only get locale
                // todo : add a clickable link to the other admin
            },
        ]);
    }

    protected function configureFormFieldsBlockTitle(FormMapper $formMapper): FormMapper
    {
        $formMapper->with('admin.page.title.label', ['class' => 'col-md-7']);
        $formMapper->add('title', TextType::class, [
            'label' => 'admin.page.title.label',
            'help' => 'admin.page.title.help',
            'attr' => ['class' => 'titleToMeasure'],
        ]);

        // Method existance is checked on each element to permit to use admin without all page Trait.
        if ($this->exists('H1')) {
            $formMapper->add('h1', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'input-lg'],
                'label' => 'admin.page.h1.label',
                'help' => 'admin.page.h1.help',
            ]);
        }

        if ($this->exists('MainImage')) {
            $formMapper->add('mainImage', \Sonata\AdminBundle\Form\Type\ModelListType::class, [
                'required' => false,
                'class' => $this->getContainer()->getParameter('app.entity_media'),
                'label' => 'admin.page.mainImage.label',
                'btn_edit' => false,
            ]);
        }
        $formMapper->end();

        $formMapper->with('admin.page.params.label', ['class' => 'col-md-5']);
        $formMapper->add('slug', TextType::class, [
            'label' => 'admin.page.slug.label',
            'help' => $this->getSubject() && $this->getSubject()->getSlug()
                ? '<span class="btn btn-link" onclick="toggleDisabled()" id="disabledLinkSlug">
                    <i class="fa fa-unlock"></i></span>
                    <script>function toggleDisabled() {
                        $(".slug_disabled").first().removeAttr("disabled");
                        $(".slug_disabled").first().focus();
                        $("#disabledLinkSlug").first().remove();
                    }</script>'
                : 'admin.page.slug.help',
            'attr' => [
                'class' => 'slug_disabled',
                ($this->getSubject() ? ($this->getSubject()->getSlug() ? 'disabled' : 't') : 't') => '',
            ],
        ]);
        if ($this->exists('Locale')) {
            $formMapper->add('locale', TextType::class, [
                'label' => 'admin.page.locale.label',
                'help' => 'admin.page.locale.help',
            ]);
        }

        if ($this->exists('metaRobots')) {
            $formMapper->add('metaRobots', ChoiceType::class, [
                'choices' => [
                    'admin.page.metaRobots.choice.noIndex' => 'noindex',
                ],
                'label' => 'admin.page.metaRobots.label',
                'required' => false,
            ]);
        }

        $formMapper->end();

        return $formMapper;
    }

    protected function configureFormFieldsBlockImages(FormMapper $formMapper): FormMapper
    {
        if ($this->exists('images')) {
            $formMapper->with('admin.page.images.label', ['class' => 'col-md-5']);
            $formMapper->add(
                'pageHasMedias',
                CollectionType::class,
                [
                    'by_reference' => false,
                    'required' => false,
                    'label' => ' ',
                    'type_options' => [
                        'delete' => true,
                    ],
                ],
                [
                    'allow_add' => false,
                    'allow_delete' => true,
                    'btn_add' => false,
                    'btn_catalogue' => false,
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                    //'link_parameters' => ['context' => $context],
                    'admin_code' => 'piedweb.admin.pagehasmedia',
                ]
            );
            $formMapper->end();
        }

        return $formMapper;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->configureFormFieldsBlockTitle($formMapper);
        $this->configureFormFieldsBlockContent($formMapper);
        $this->configureFormFieldsBlockDetails($formMapper);
        $this->configureFormFieldsBlockImages($formMapper);
    }

    protected function configureDatagridFilters(DatagridMapper $formMapper)
    {
        $formMapper->add('locale', null, ['label' => 'admin.page.locale.label']);
        $formMapper->add('slug', null, ['label' => 'admin.page.slug.label']);

        $formMapper->add('title', null, ['label' => 'admin.page.title.label']);

        if ($this->exists('H1')) {
            $formMapper->add('h1', null, ['label' => 'admin.page.h1.label']);
        }

        $formMapper->add('mainContent', null, ['label' => 'admin.page.mainContent.label']);

        if ($this->exists('name')) {
            $formMapper->add('name', null, ['label' => 'admin.page.name.label']);
        }

        if ($this->exists('parentPage')) {
            $formMapper->add('parentPage', null, ['label' => 'admin.page.parentPage.label']);
        }

        if ($this->exists('metaRobots')) {
            $formMapper->add('metaRobots', null, [
                'choices' => [
                    'admin.page.metaRobots.choice.noIndex' => 'noindex',
                ],
                'label' => 'admin.page.metaRobots.label',
            ]);
        }

        if ($this->exists('author')) {
            $formMapper->add('author', null, [
                'label' => 'admin.page.author.label',
                'class' => $this->getContainer()->getParameter('app.entity_user'),
                'required' => false,
            ]);
        }
    }

    public function preUpdate($page)
    {
        $page->setUpdatedAt(new \Datetime());
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->add('slug', null, [
            'label' => 'admin.page.slug.label',
        ]);
        $listMapper->addIdentifier('title', 'html', [
            'label' => 'admin.page.title.label',
        ]);
        $listMapper->add('updatedAt', null, [
            'format' => 'd/m à H:m',
            'label' => 'admin.page.updatedAt.label',
        ]);
        $listMapper->add('createdAt', null, [
            'format' => 'd/m/y',
            'label' => 'admin.page.createdAt.label',
        ]);
        $listMapper->add('metaRobots', null, [
            'label' => 'admin.page.metaRobots.label',
        ]);
        $listMapper->add('_action', null, [
            'actions' => [
                'show' => [],
                'delete' => [],
            ],
            'row_align' => 'right',
            'header_class' => 'text-right',
            'label' => 'admin.action',
        ]);
    }

    public function getObjectMetadata($page)
    {
        $media = $page->getMainImage();
        if (null !== $media && false !== strpos($media->getMimeType(), 'image/')) {
            $fullPath = '/' . $media->getRelativeDir() . '/' . $media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'thumb');
        } else {
            $thumb = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaGVpZ2h0PSIzMnB4IiB2ZXJzaW9uP
                SIxLjEiIHZpZXdCb3g9IjAgMCAzMiAzMiIgd2lkdGg9IjMycHgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIg
                eG1sbnM6c2tldGNoPSJodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2gvbnMiIHhtbG5zOnhsaW5rPSJodHRwOi8
                vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48dGl0bGUvPjxkZXNjLz48ZGVmcy8+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub
                2RkIiBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSI+PGcgZmlsbD0iIzkyOTI5MiIgaWQ9Imljb24
                tMjEtZXllLWhpZGRlbiI+PHBhdGggZD0iTTguMTA4Njk4OTEsMjAuODkxMzAxMSBDNC42MTcyMDgxNiwxOC44MzAxMTQ3IDMsMT
                YgMywxNiBDMywxNiA3LDkgMTYsOSBDMTcuMzA0NTEwNyw5IDE4LjUwMzk3NTIsOS4xNDcwNjQ2NiAxOS42MDE0Mzg4LDkuMzk4
                NTYxMjIgTDE4Ljc1MTkwMTcsMTAuMjQ4MDk4MyBDMTcuODk3MTQ4NCwxMC4wOTAwNTQ2IDE2Ljk4MDA5MjksMTAgMTYsMTAgQzg
                sMTAgNC4xOTk5NTExNywxNiA0LjE5OTk1MTE3LDE2IEM0LjE5OTk1MTE3LDE2IDUuNzE0NzI4MDgsMTguMzkxNzIyNSA4Ljg0ND
                kyNzEzLDIwLjE1NTA3MjkgTDguMTA4Njk4OTEsMjAuODkxMzAxMSBMOC4xMDg2OTg5MSwyMC44OTEzMDExIEw4LjEwODY5ODkxLD
                IwLjg5MTMwMTEgWiBNMTIuMzk4NTYxLDIyLjYwMTQzOSBDMTMuNDk2MDI0NiwyMi44NTI5MzU2IDE0LjY5NTQ4OTIsMjMuMDAwMD
                AwMSAxNiwyMyBDMjUsMjIuOTk5OTk5IDI5LDE2IDI5LDE2IEMyOSwxNiAyNy4zODI3OTE4LDEzLjE2OTg4NTYgMjMuODkxMzAwOC
                wxMS4xMDg2OTkyIEwyMy4xNTUwNzI3LDExLjg0NDkyNzMgQzI2LjI4NTI3MTksMTMuNjA4Mjc3NiAyNy44MDAwNDg4LDE2IDI3Lj
                gwMDA0ODgsMTYgQzI3LjgwMDA0ODgsMTYgMjQsMjEuOTk5OTk5IDE2LDIyIEMxNS4wMTk5MDcsMjIuMDAwMDAwMSAxNC4xMDI4N
                TE1LDIxLjkwOTk0NTUgMTMuMjQ4MDk4MSwyMS43NTE5MDE5IEwxMi4zOTg1NjEsMjIuNjAxNDM5IEwxMi4zOTg1NjEsMjIuNjAxN
                DM5IEwxMi4zOTg1NjEsMjIuNjAxNDM5IFogTTE5Ljg5ODY1MzEsMTUuMTAxMzQ2OSBDMTkuOTY0OTY1OCwxNS4zOTAyMTE1IDIwL
                DE1LjY5MTAxNDQgMjAsMTYgQzIwLDE4LjIwOTEzOTEgMTguMjA5MTM5MSwyMCAxNiwyMCBDMTUuNjkxMDE0NCwyMCAxNS4zOTAyM
                TE1LDE5Ljk2NDk2NTggMTUuMTAxMzQ2OSwxOS44OTg2NTMxIEwxNiwxOSBDMTYuNzY3NzY2OSwxOS4wMDAwMDAxIDE3LjUzNTUzM
                zksMTguNzA3MTA2OCAxOC4xMjEzMjAzLDE4LjEyMTMyMDMgQzE4LjcwNzEwNjgsMTcuNTM1NTMzOSAxOS4wMDAwMDAxLDE2Ljc2N
                zc2NjkgMTksMTYgTDE5Ljg5ODY1MzEsMTUuMTAxMzQ2OSBMMTkuODk4NjUzMSwxNS4xMDEzNDY5IEwxOS44OTg2NTMxLDE1LjEwM
                TM0NjkgWiBNMTYuODk4NjUzMSwxMi4xMDEzNDY5IEMxNi42MDk3ODg1LDEyLjAzNTAzNDIgMTYuMzA4OTg1NiwxMiAxNiwxMiBDM
                TMuNzkwODYwOSwxMiAxMiwxMy43OTA4NjA5IDEyLDE2IEMxMiwxNi4zMDg5ODU2IDEyLjAzNTAzNDIsMTYuNjA5Nzg4NSAxMi4xM
                DEzNDY5LDE2Ljg5ODY1MzEgTDEzLDE2IEMxMi45OTk5OTk5LDE1LjIzMjIzMzEgMTMuMjkyODkzMiwxNC40NjQ0NjYxIDEzLjg3O
                DY3OTcsMTMuODc4Njc5NyBDMTQuNDY0NDY2MSwxMy4yOTI4OTMyIDE1LjIzMjIzMzEsMTIuOTk5OTk5OSAxNiwxMyBMMTYuODk4N
                jUzMSwxMi4xMDEzNDY5IEwxNi44OTg2NTMxLDEyLjEwMTM0NjkgTDE2Ljg5ODY1MzEsMTIuMTAxMzQ2OSBaIE0yNCw3IEw3LDI0I
                Ew4LDI1IEwyNSw4IEwyNCw3IEwyNCw3IFoiIGlkPSJleWUtaGlkZGVuIi8+PC9nPjwvZz48L3N2Zz4=';
        }

        return new Metadata($page->getTitle(), null, $thumb);
    }
}
