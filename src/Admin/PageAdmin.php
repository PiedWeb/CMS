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
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Sonata\CoreBundle\Model\Metadata;
use PiedWeb\CMSBundle\Entity\Page;
use PiedWeb\CMSBundle\Entity\Faq;
use PiedWeb\CMSBundle\Entity\User;
use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Service\FeedDumpService;

/*
 * To Do
 */
class PageAdmin extends AbstractAdmin
{
    use AdminTrait;

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

    protected function configureFormFields(FormMapper $formMapper)
    {
        if ($this->getSubject() && $this->getSubject()->getSlug()) {
            // Better to be in  event PostUpdate page... but it's quicker
            $this->feedDumper->dump();
        }

        $formMapper->with('admin.page.title.label');
        $formMapper->add('title', TextType::class, [
            'label' => 'admin.page.title.label',
            'help' => 'admin.page.title.help',
        ]);
        if (method_exists(Page::class, 'getH1')) { // To do on each element to permit to use admin without Page...Trait.
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

        if (method_exists(Page::class, 'getMainImage')) {
            $formMapper->add('mainImage', ModelType::class, [
            'required' => false,
            'class' => Media::class,
            'label' => 'admin.page.mainImage.label',
        ]);
        }
        $formMapper->end();

        $formMapper->with('admin.page.mainContent.label');
        $formMapper->add('mainContent', TextareaType::class, [
            'attr' => ['style' => 'min-height: 600px;font-size:125%;'],
            'required' => false,
            'label' => ' ',
            'help' => 'admin.page.mainContent.help',
        ]);
        $formMapper->add('mainContentIsMarkdown', null, [
            'required' => false,
            'label' => 'markdown',
        ]);
        $formMapper->end();

        $formMapper->with('admin.details', ['class' => 'col-md-9 order-1']);

        if (method_exists(Page::class, 'getname')) {
            $formMapper->add('name', TextType::class, [
                'label' => 'admin.page.name.label',
                'required' => false,
            ]);
        }

        if (method_exists(Page::class, 'getparentPage')) {
            $formMapper->add('parentPage', EntityType::class, [
            'class' => Page::class,
            'label' => 'admin.page.parentPage.label',
            'required' => false,
        ]);
        }

        if (method_exists(Page::class, 'getsubTitle')) {
            $formMapper->add('subTitle', TextType::class, ['label' => 'admin.page.subTitle.label', 'required' => false]);
        }

        if (method_exists(Page::class, 'getexcrept')) {
            $formMapper->add('excrept', TextareaType::class, [
            'required' => false,
            'label' => 'admin.page.excrept.label',
        ]);
        }

        if (method_exists(Page::class, 'getfaq')) {
            $formMapper->add('faq', ModelAutocompleteType::class, [
            'required' => false,
             'multiple' => true,
             'class' => Faq::class,
             'property' => 'question',   // or any field in your media entity
             'label' => 'admin.page.faq.label',
             'btn_add' => true,
             'to_string_callback' => function ($entity, $property) {
                 return $entity->getQuestion();
             },
         ]);
        }

        if (method_exists(Page::class, 'getrelatedPages')) {
            $formMapper->add('relatedPages', ModelAutocompleteType::class, [
            'required' => false,
             'multiple' => true,
             'class' => Page::class,
             'property' => 'title',   // or any field in your media entity
             'label' => 'admin.page.relatedPage.label',
             'btn_add' => false,
             'to_string_callback' => function ($entity, $property) {
                 return $entity->getTitle();
             },
         ]);
        }

        if (method_exists(Page::class, 'getimages')) {
            $formMapper->add('images', ModelAutocompleteType::class, [
            'required' => false,
             'multiple' => true,
             'class' => Media::class,
             'property' => 'media',
             'label' => 'admin.page.images.label',
             'btn_add' => true,
             'to_string_callback' => function ($entity, $property) {
                 return $entity->getName();
             //'<img src="'.$this->getConfigurationPool()->getContainer()->getParameter('img_dir').'/'.$entity->getImage().'" style="max-width:200px; max-height: 200px;">';
             },
         ]);
        }
        $formMapper->end();

        $formMapper->with('admin.edition', ['class' => 'col-md-3 order-2']);

        if (method_exists(Page::class, 'getmetaRobots')) {
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

        if (method_exists(Page::class, 'getauthor')) {
            $formMapper->add('author', EntityType::class, [
             'label' => 'admin.page.author.label',
             'class' => User::class, 'label' => 'Auteur',
             'required' => false,
        ]);
        }

        if (method_exists(Page::class, 'gettemplate')) {
            $formMapper->add('template', null, [
             'label' => 'admin.page.template.label',
             'required' => false,
            ]);
        }

        $formMapper->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('title');
        $datagridMapper->add('createdAt');
        $datagridMapper->add('updatedAt');
        $datagridMapper->add('mainContent');
        $datagridMapper->add('metaRobots', null, [
            'choices' => ['admin.page.metaRobots.choice.noIndex' => 'no-index, no-follow',],
        ]);
        $datagridMapper->add('author');
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
