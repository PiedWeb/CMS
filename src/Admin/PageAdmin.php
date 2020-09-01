<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
        return method_exists($this->getContainer()->getParameter('pwc.entity_page'), 'get'.$name);
    }

    protected function configureFormFieldsBlockDetails(FormMapper $formMapper): FormMapper
    {
        $formMapper->with('admin.details', ['class' => 'col-md-5']);

        if ($this->exists('parentPage')) {
            $formMapper->add('parentPage', EntityType::class, [
                'class' => $this->getContainer()->getParameter('pwc.entity_page'),
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
                'help_html' => true,
                'help' => 'admin.page.name.help',
            ]);
        }

        if ($this->exists('excrept')) {
            $formMapper->add('excrept', TextareaType::class, [
                'required' => false,
                'label' => 'admin.page.excrept.label',
                'help_html' => true,
                'help' => 'admin.page.excrept.help',
            ]);
        }

        if ($this->exists('relatedPages')) {
            $formMapper->add('relatedPages', ModelAutocompleteType::class, [
                'required' => false,
                'multiple' => true,
                'class' => $this->getContainer()->getParameter('pwc.entity_page'),
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
            'help_html' => true,
            'help' => 'admin.page.mainContent.help',
        ]);
        $formMapper->add('mainContentIsMarkdown', null, [
            'required' => false,
            'label' => 'admin.page.markdown.label',
            'help_html' => true,
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
                'style' => 'min-height:15vh',
                'data-editor' => 'yaml',
            ],
            'label' => 'admin.page.otherProperties.label',
            'help_html' => true,
            'help' => 'admin.page.otherProperties.help',
        ]);
    }

    protected function getHosts()
    {
        return array_keys($this->getContainer()->getParameter('pwc.apps'));
    }

    public function configureFormFieldHost(FormMapper $formMapper): FormMapper
    {
        if (null === $this->getSubject()->getHost()) {
            $this->getSubject()->setHost($this->getHosts()[0]);
        }

        return $formMapper->add('host', ChoiceType::class, [
            'choices' => array_combine($this->getHosts(), $this->getHosts()),
            'required' => false,
            'label' => 'admin.page.host.label',
            'empty_data' => $this->getHosts()[0],
        ]);
    }

    public function configueFormFieldTranslations(FormMapper $formMapper): FormMapper
    {
        return $formMapper->add('translations', ModelAutocompleteType::class, [
            'required' => false,
            'multiple' => true,
            'class' => $this->getContainer()->getParameter('pwc.entity_page'),
            'property' => 'slug',
            'label' => 'admin.page.translations.label',
            'help_html' => true,
            'help' => 'admin.page.translations.help',
            'btn_add' => false,
            'to_string_callback' => function ($entity) {
                return $entity->getLocale()
                    ? $entity->getLocale().' ('.$entity->getSlug().')'
                    : $entity->getSlug(); // switch for getLocale
                // todo : remove it in next release and leave only get locale
                // todo : add a clickable link to the other admin
            },
        ]);
    }

    protected function configureFormFieldsBlockTitle(FormMapper $formMapper): FormMapper
    {
        $formMapper
            ->with('admin.page.title.label', ['class' => 'col-md-7']);

        $formMapper->add('title', TextType::class, [
            'label' => 'admin.page.title.label',
            'help_html' => true,
            'help' => 'admin.page.title.help',
            'attr' => ['class' => 'titleToMeasure'],
        ]);

        // Method existance is checked on each element to permit to use admin without all page Trait.
        if ($this->exists('H1')) {
            $formMapper->add('h1', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'input-lg'],
                'label' => 'admin.page.h1.label',
                'help_html' => true,
                'help' => 'admin.page.h1.help',
            ]);
        }

        if ($this->exists('MainImage')) {
            $formMapper->add('mainImage', \Sonata\AdminBundle\Form\Type\ModelListType::class, [
                'required' => false,
                'class' => $this->getContainer()->getParameter('pwc.entity_media'),
                'label' => 'admin.page.mainImage.label',
                'btn_edit' => false,
            ]);
        }
        $formMapper->end();

        return $formMapper;
    }

    protected function configureFormFieldBlockParams(FormMapper $formMapper)
    {
        $formMapper->with('admin.page.params.label', ['class' => 'col-md-5']);

        $formMapper->add('slug', TextType::class, [
            'label' => 'admin.page.slug.label',
            'help_html' => true,
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

        if ($this->exists('Host') && count($this->getHosts()) > 1) {
            $this->configureFormFieldHost($formMapper);
        }

        if ($this->exists('Locale')) {
            $formMapper->add('locale', TextType::class, [
                'label' => 'admin.page.locale.label',
                'help_html' => true,
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
        $this->configureFormFieldBlockParams($formMapper);
        $this->configureFormFieldsBlockContent($formMapper);
        $this->configureFormFieldsBlockDetails($formMapper);
        $this->configureFormFieldsBlockImages($formMapper);
    }

    protected function configureDatagridFilters(DatagridMapper $formMapper)
    {
        $formMapper->add('locale', null, ['label' => 'admin.page.locale.label']);

        if (count($this->getHosts()) > 1) {
            $formMapper->add('host', null, ['label' => 'admin.page.host.label']);
        }

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
                'class' => $this->getContainer()->getParameter('pwc.entity_user'),
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
        $listMapper->addIdentifier('title', 'html', [
            'label' => 'admin.page.title.label',
            'template' => '@PiedWebCMS/admin/base_list_field.html.twig',
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
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'thumb');
        } else {
            $thumb = self::$thumb;
        }

        return new Metadata($page->getTitle(), null, $thumb);
    }
}
