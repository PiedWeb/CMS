<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaAdmin extends AbstractAdmin
{
    use AdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    ];

    private $liipImage;
    private $relatedPages;

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject();

        $formMapper->with('Media', ['class' => 'col-md-6'])
            ->add('name', TextType::class, [
                    'required' => false,
                    'help_html' => true,
                    'help' => 'admin.media.name.help',
                    'label' => 'admin.media.name.label',
                    'attr' => ['ismedia' => 1, 'class' => 'col-md-6'],
            ])
            ->add('mediaFile', FileType::class, [
                    'label' => 'admin.media.mediaFile.label',
                ])
            ->end();

        $formMapper->with('i18n', ['class' => 'col-md-6'])
            ->add('names', null, [
                    'required' => false,
                     'help_html' => true, 'help' => 'admin.media.names.help',
                    'label' => 'admin.media.names.label',
                    'attr' => ['ismedia' => 1, 'class' => 'col-md-6'],
            ])
            ->end();

        if ($media && $media->getMedia()) {
            $formMapper->with('admin.media.preview.label', [
                    'class' => 'col-md-12',
                    'description' => $this->showMediaPreview(),
                    //'empty_message' => false, // to uncomment when sonataAdmin 3.62 is released
                ])->end();

            if ($this->issetRelatedPages()) {
                $formMapper->with('admin.media.related.label', [
                        'class' => 'col-md-12',
                        'description' => $this->showRelatedPages(),
                        //'empty_message' => false, /// to uncomment when sonataAdmin 3.62 is released
                ])->end();
            }
        }
    }

    protected function showMediaPreview(): string
    {
        $media = $this->getSubject();

        $template = false !== strpos($media->getMimeType(), 'image/') ?
            '@PiedWebCMS/admin/media_show.preview_image.html.twig'
            : '@PiedWebCMS/admin/media_show.preview.html.twig';

        return $this->getContainer()->get('twig')->render(
            $template,
            [
                'media' => $media,
            ]
        );
    }

    protected function issetRelatedPages(): bool
    {
        $relatedPages = $this->getRelatedPages();

        if (!empty($relatedPages['content'])
            || $relatedPages['gallery']->count() > 0
            || $relatedPages['mainImage']->count() > 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    protected function getRelatedPages(): ?array
    {
        if (null !== $this->relatedPages) {
            return $this->relatedPages;
        }

        $media = $this->getSubject();

        $pages = $this->getConfigurationPool()->getContainer()->get('doctrine')
            ->getRepository($this->getContainer()->getParameter('pwc.entity_page'))
            ->getPagesUsingMedia($this->liipImage->getBrowserPath($media->getFullPath(), 'default'));

        $this->relatedPages = [
            'content' => $pages,
            'gallery' => $media->getPageHasMedias(),
            'mainImage' => $media->getMainImagePages(),
        ];

        return $this->relatedPages;
    }

    protected function showRelatedPages(): string
    {
        return $this->getContainer()->get('twig')->render(
            '@PiedWebCMS/admin/media_show.relatedPages.html.twig',
            $this->getRelatedPages()
        );
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name', null, [
            'label' => 'admin.media.name.label',
        ]);
        $datagridMapper->add('names', null, [
            'label' => 'admin.media.names.label',
        ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setMosaicDefaultListMode();

        $listMapper->add('name', null, [
            'label' => 'admin.media.name.label',
        ]);
        $listMapper->add('createdAt', null, [
            'label' => 'admin.media.createdAt.label',
            'format' => 'd/m/y',
        ]);
        $listMapper->add('mainColor', null, [
            'label' => 'admin.media.mainColor.label',
        ]);
        $listMapper->add('_action', null, [
            'actions' => [
                'edit' => [],
                'delete' => [],
            ],
        ]);
    }

    public function getObjectMetadata($media)
    {
        if (false !== strpos($media->getMimeType(), 'image/')) {
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'thumb');
        } else {
            $thumb = self::$thumb;
        }

        return new Metadata($media->getName(), null, $thumb);
    }
}
