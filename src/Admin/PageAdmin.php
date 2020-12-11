<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;

class PageAdmin extends AbstractAdmin
{
    use AdminTrait;
    use PageAdminFormFieldsTrait;

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

    /**
     * Check if page entity's item $name exist.
     */
    protected function exists(string $name): bool
    {
        return method_exists($this->pageClass, 'get'.$name);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        // Next : load this from configuration
        $mainFields = ['h1', 'mainContent', 'mainContentIsMarkdown'];
        $columnFields = [
            'admin.page.state.label' => ['createdAt', 'metaRobots',],
            'admin.page.permanlien.label' => ['host', 'slug', 'parentPage',],
            'admin.page.extended.label' => ['expand' => true, 'fields' => ['mainImage', 'name', 'title',  'excrept',]],
            'admin.page.translations.label' => [ 'locale', 'translations',],
            'admin.page.otherProperties.label' => ['expand' => true, 'fields' => ['otherProperties',]],
            'admin.page.images.label' => ['images',],
        ];

        $formMapper->with('admin.page.mainContent.label', ['class' => 'col-md-9 mainFields']);
        foreach ($mainFields as $field) {
            $func = 'configureFormField'.ucfirst($field);
            $this->$func($formMapper);
        }
        $formMapper->end();

        foreach ($columnFields as $k => $block) {
            $fields = $block['fields'] ?? $block;
            $class = isset($block['expand']) ? 'expand' : '';
            $formMapper->with($k, ["class" => "col-md-3 columnFields ".$class]);
            foreach($fields as $field) {
                $func = 'configureFormField'.ucfirst($field);
                $this->$func($formMapper);
            }

            $formMapper->end();
        }
    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
        $instance->setLocale($this->defaultLocale);

        return $instance;
    }

    protected function configureDatagridFilters(DatagridMapper $formMapper)
    {
        $formMapper->add('locale', null, ['label' => 'admin.page.locale.label']);

        if (count($this->getHosts()) > 1) {
            $formMapper->add('host', null, ['label' => 'admin.page.host.label']);
        }

        $formMapper->add('h1', null, ['label' => 'admin.page.h1.label']);

        $formMapper->add('mainContent', null, ['label' => 'admin.page.mainContent.label']);

        $formMapper->add('slug', null, ['label' => 'admin.page.slug.label']);

        $formMapper->add('title', null, ['label' => 'admin.page.title.label']);

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
    }

    public function preUpdate($page)
    {
        $page->setUpdatedAt(new \Datetime());
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('h1', 'html', [
            'label' => 'admin.page.title.label',
            'template' => '@PiedWebCMS/admin/page_list_titleField.html.twig',
        ]);
        $listMapper->add('updatedAt', null, [
            'format' => 'd/m à H:m',
            'label' => 'admin.page.updatedAt.label',
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
