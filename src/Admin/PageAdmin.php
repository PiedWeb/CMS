<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Sonata\CoreBundle\Model\Metadata;
//use PiedWeb\CMSBundle\Entity\User;
//use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Service\FeedDumpService;

class PageAdmin extends AbstractAdmin
{
    use AdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    ];

    protected $feedDumper;

    private $liipImage;

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    public function setFeedDumper(FeedDumpService $feedDumper)
    {
        $this->feedDumper = $feedDumper;
    }

    public function configure()
    {
        $this->setTemplate('edit', '@PiedWebCMS/admin/edit.html.twig');
        $this->setTemplate('show', '@PiedWebCMS/admin/show_page.html.twig');
    }

    /**
    public function getFormTheme()
    {

        return array_merge(
            parent::getFormTheme(),
            array('@PiedWebCMS/admin/edit-media.html.twig')
        );
    }**/
    protected function configureFormFields(FormMapper $formMapper)
    {
        if ($this->getSubject() && $this->getSubject()->getSlug()) {
            // Better to be in  event PostUpdate page... but it's quicker
            $this->feedDumper->dump();
        }

        $formMapper->with('admin.page.title.label', ['class' => 'col-md-9']);
        $formMapper->add('title', TextType::class, [
            'label' => 'admin.page.title.label',
            'help' => 'admin.page.title.help',
        ]);
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getH1')) { // To do on each element to permit to use admin without all page Trait.
            $formMapper->add('h1', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'input-lg'],
                'label' => 'admin.page.h1.label',
                'help' => 'admin.page.h1.help',
            ]);
        }

        $formMapper->add('slug', TextType::class, [
            'label' => 'admin.page.slug.label',
            'help' => 'admin.page.slug.help',
            'attr' => [
                ($this->getSubject()->getSlug() ? 'disabled' : 't') => '',
            ],
        ]);

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getMainImage')) {
            $formMapper->add('mainImage', \Sonata\AdminBundle\Form\Type\ModelListType::class, [
            'required' => false,
            'class' => $this->getConfigurationPool()->getContainer()->getParameter('app.entity_media'),
            'label' => 'admin.page.mainImage.label',
            'btn_edit' => false,
        ]);
        }
        $formMapper->end();

        $formMapper->with('admin.page.mainContent.label');
        $formMapper->add('mainContent', TextareaType::class, [
            'attr' => [
                'style' => 'min-height: 600px;font-size:125%;',
                'data-editor' => 'markdown',
                'data-gutter' => 0,
            ],
            'required' => false,
            'label' => ' ',
            'help' => 'admin.page.mainContent.help',
        ]);
        $formMapper->add('mainContentIsMarkdown', null, [
            'required' => false,
            'label' => 'markdown',
        ]);
        $formMapper->end();

        $formMapper->with('admin.details', ['class' => 'col-md-6 order-1']);

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getname')) {
            $formMapper->add('name', TextType::class, [
                'label' => 'admin.page.name.label',
                'required' => false,
            'help' => 'admin.page.name.help',
            ]);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getparentPage')) {
            $formMapper->add('parentPage', EntityType::class, [
            'class' => $this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'),
            'label' => 'admin.page.parentPage.label',
            'required' => false,
        ]);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getsubTitle')) {
            $formMapper->add('subTitle', TextType::class, ['label' => 'admin.page.subTitle.label', 'required' => false]);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getexcrept')) {
            $formMapper->add('excrept', TextareaType::class, [
            'required' => false,
            'label' => 'admin.page.excrept.label',
            'help' => 'admin.page.excrept.help',
        ]);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getfaq')) {
            $formMapper->add('faq', ModelAutocompleteType::class, [
            'required' => false,
             'multiple' => true,
             'class' => Faq::class,
             'property' => 'question',   // or any field in your media entity
             'label' => 'admin.page.faq.label',
             'btn_add' => true,
             'to_string_callback' => function ($entity) {//, $property) {
                 return $entity->getQuestion();
             },
         ]);
        }

        //var_dump($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page')); exit;
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getrelatedPages')) {
            $formMapper->add('relatedPages', ModelAutocompleteType::class, [
            'required' => false,
             'multiple' => true,
             'class' => $this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'),
             'property' => 'title',   // or any field in your media entity
             'label' => 'admin.page.relatedPage.label',
             'btn_add' => false,
             'to_string_callback' => function ($entity) { //, $property) {
                 return $entity->getTitle();
             },
         ]);
        }
        $formMapper->end();

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getimages')) {
            $formMapper->with('admin.page.images.label', ['class' => 'col-md-3']);
            $formMapper->add('pageHasMedias', \Sonata\CoreBundle\Form\Type\CollectionType::class, [
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

        $formMapper->with('admin.edition', ['class' => 'col-md-3']);

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getmetaRobots')) {
            $formMapper->add('metaRobots', ChoiceType::class, [
            'choices' => [
                'admin.page.metaRobots.choice.noIndex' => 'no-index, no-follow',
            ],
             'label' => 'admin.page.metaRobots.label',
            'required' => false,
        ]);
        }
        $formMapper->add('createdAt', DateTimePickerType::class, [
            //'date_format' => 'd MMMM y H:mm',
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
        $formMapper->add('updatedAt', DateTimePickerType::class, [
            //'date_format' => 'd MMMM y H:mm',
            'format' => DateTimeType::HTML5_FORMAT,
            'dp_side_by_side' => true,
            'dp_use_current' => true,
            'dp_use_seconds' => false,
            'dp_collapse' => true,
            'dp_calendar_weeks' => false,
            'dp_view_mode' => 'days',
            'dp_min_view_mode' => 'days',
             'label' => 'admin.page.updatedAt.label',
        ]);

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getauthor')) {
            $formMapper->add('author', EntityType::class, [
             'label' => 'admin.page.author.label',
             'class' => $this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'label' => 'Auteur',
             'required' => false,
        ]);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'gettemplate')) {
            $formMapper->add('template', null, [
             'label' => 'admin.page.template.label',
             'required' => false,
            ]);
        }

        $formMapper->end();
    }

    protected function configureDatagridFilters(DatagridMapper $formMapper)
    {
        $formMapper->add('title', null, ['label' => 'admin.page.title.label']);

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getH1')) {
            $formMapper->add('h1', null, ['label' => 'admin.page.h1.label']);
        }

        $formMapper->add('slug', null, ['label' => 'admin.page.slug.label']);

        $formMapper->add('mainContent', null, ['label' => 'admin.page.mainContent.label']);

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getname')) {
            $formMapper->add('name', null, ['label' => 'admin.page.mainContent.label']);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getparentPage')) {
            $formMapper->add('parentPage', null, ['label' => 'admin.page.parentPage.label']);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getsubTitle')) {
            $formMapper->add('subTitle', null, ['label' => 'admin.page.subTitle.label']);
        }

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getmetaRobots')) {
            $formMapper->add('metaRobots', null, [
            'choices' => [
                'admin.page.metaRobots.choice.noIndex' => 'no-index, no-follow',
            ],
             'label' => 'admin.page.metaRobots.label',
        ]);
        }
        /*
         * todo: implémente datepicker for orm_date in sonata
        $formMapper->add('createdAt', 'doctrine_orm_date', [
             'label' => 'admin.page.createdAt.label',
        ]);
        $formMapper->add('updatedAt', null, [
             'label' => 'admin.page.updatedAt.label',
        ]);
        */

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_page'), 'getauthor')) {
            $formMapper->add('author', null, [
             'label' => 'admin.page.author.label',
             'class' => $this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'),
             'label' => 'Auteur',
             'required' => false,
            ]);
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        //$this->setMosaicDefaultListMode();

        $listMapper->addIdentifier('title', null, [
            'label' => 'admin.page.title.label',
        ]);
        $listMapper->add('slug', null, [
            'label' => 'admin.page.slug.label',
        ]);
        $listMapper->add('updatedAt', null, [
            'format' => 'd M y (H:m)',
            'label' => 'admin.page.updatedAt.label',
        ]);
        $listMapper->add('createdAt', null, [
            'format' => 'd M y (H:m)',
            'label' => 'admin.page.createdAt.label',
        ]);
        $listMapper->add('_action', null, [
            'actions' => [
                'show' => [],
                'edit' => [],
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
            $thumb = null; // feature, une icone en fonction du media (pdf/word...)
        }

        return new Metadata($page->getTitle(), null, $thumb);
    }
}
