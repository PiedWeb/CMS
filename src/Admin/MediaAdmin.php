<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

        $formMapper->with('Media', [
            'class' => 'col-md-6',
        ])
            ->add('name', TextType::class, [
                    'required' => false,
                    'help' => 'admin.media.name.help',
                    'label' => 'admin.media.name.label',
                    'attr' => ['ismedia' => 1, 'class' => 'col-md-6'],
                ])
            ->add('mediaFile', FileType::class, [
                'label' => 'admin.media.mediaFile.label',
            ])
        ->end();

        $formMapper->with('i18n', [
            'class' => 'col-md-6',
        ])
            ->add('names', null, [
                'required' => false,
                'help' => 'admin.media.names.help',
                'label' => 'admin.media.names.label',
                'attr' => ['ismedia' => 1, 'class' => 'col-md-6'],
            ])
        ->end();
        if ($media && $media->getMedia()) {
            $formMapper->with('Aperçu', [
                'class' => 'col-md-12',
                'description' => $this->showMediaPreview(),
            ])->end();

            if ($this->issetRelatedPages()) {
                $formMapper->with('Related', [
                'class' => 'col-md-12',
                'description' => $this->showRelatedPages(),
            ])->end();
            }
        }

        /*
        //$type = $media && $media->getName() === null ? TextType::class : HiddenType::class;
        $formMapper->add('name', TextType::class, [
        'required' => false,
        'help' => 'admin.media.name.help',
        'label' => 'admin.media.name.label',
        'attr' => ['ismedia' => 1, 'class' => 'col-md-6'],
        ]); // ['data_class'=>null]

        $formMapper->add('names', null, [
        'required' => false,
        'help' => 'admin.media.names.help',
        'label' => 'admin.media.names.label',
        'attr' => ['ismedia' => 1, 'class' => 'col-md-6'],
        ]);**/

        /*
        $fileFieldOptions = ['required' => false, 'data_class' => null];
        if ($media && $media->getMedia()) {
            $fileFieldOptions['help'] = $this->showImagePreview();
                //$fileFieldOptions['sonata_help'] = $fileFieldOptions['help'];
                //$fileFieldOptions['attr'] = ['ismedia' => 1];
                //$fileFieldOptions['label'] = 'admin.media.mediaFile.label';

            $fileFieldOptions['help'] .= $this->showRelatedPages();
        }
        **/

        //$formMapper->add('mediaFile', FileType::class, $fileFieldOptions); // ['data_class'=>null]
    }

    protected function showMediaPreview(): string
    {
        $media = $this->getSubject();

        $template = false !== strpos($media->getMimeType(), 'image/') ?
            '@PiedWebCMS/admin/media_show.preview_image.html.twig'
            : '@PiedWebCMS/admin/media_show.preview.html.twig';

        return $this->getContainer()->get('twig')->render($template, [
                'media' => $media,
        ]);
    }

    protected function issetRelatedPages(): bool
    {
        $relatedPages = $this->getRelatedPages();

        if (
            !empty($relatedPages['content'])
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
            ->getRepository($this->getContainer()->getParameter('app.entity_page'))
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
        $media = $this->getSubject();

        return $this->getContainer()->get('twig')->render(
            '@PiedWebCMS/admin/media_show.relatedPages.html.twig',
            $this->getRelatedPages()
        );
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        /*
         * todo: implémente datepicker for orm_date in sonata
        $datagridMapper->add('createdAt', null, [
            'label' => 'admin.media.createdAt.label',
        ]);
        */
        $datagridMapper->add('name', null, [
            'label' => 'admin.media.name.label',
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

        return new Metadata($media->getName(), null, $thumb);
    }
}
