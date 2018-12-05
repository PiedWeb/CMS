<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class MediaAdmin extends AbstractAdmin
{
    use AdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    ];

    private $liipImage;

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject();

        //$type = $media && $media->getName() === null ? TextType::class : HiddenType::class;
        $formMapper->add('name', TextType::class, [
            'required' => false,
            'help' => 'admin.media.name.help',
            'label' => 'admin.media.name.label',
            'attr' => ['ismedia' => 1],
        ]); // ['data_class'=>null]

        $fileFieldOptions = ['required' => false, 'data_class' => null];
        if ($media && $media->getMedia() && false !== strpos($media->getMimeType(), 'image/')) {
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'small_thumb');
            $fileFieldOptions['help'] = '<a href="'.$this->liipImage->getBrowserPath($fullPath, 'default').'">';
            $fileFieldOptions['help'] .= '<img src="'.$this->liipImage->getBrowserPath($fullPath, 'thumb').'">';
            $fileFieldOptions['help'] .= '</a>';
            $fileFieldOptions['help'] .= '<br><br>Chemin:<br><code>'.$this->liipImage->getBrowserPath($fullPath, 'default').'</code>';
            $fileFieldOptions['help'] .= '<br><br>HTML:<br><code>&lt;span data-img="'.$this->liipImage->getBrowserPath($fullPath, 'default').'"&gt;'.$media->getName().'&lt;/span&gt;'.'</code>';
            $fileFieldOptions['sonata_help'] = $fileFieldOptions['help'];
            $fileFieldOptions['attr'] = ['ismedia' => 1];
            $fileFieldOptions['label'] = 'admin.media.mediaFile.label';
        }
        $formMapper->add('mediaFile', FileType::class, $fileFieldOptions); // ['data_class'=>null]
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
            $thumb = null; // feature, une icone en fonction du media (pdf/word...)
        }

        return new Metadata($media->getName(), null, $thumb);
    }
}
