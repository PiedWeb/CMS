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
        $fileFieldOptions = ['required' => false, 'data_class' => null];
        $media = $this->getSubject();

        //$type = $media && $media->getName() === null ? TextType::class : HiddenType::class;
        $formMapper->add('name', TextType::class, [
            'required' => false,
            'help' => '<small>Choisissez un à plusieurs mots décrivant votre fichier. N\'hésitez pas à utiliser un `patern` pour mieux vous y retrouver (ex: #Attribute - Description)</small>',
        ]); // ['data_class'=>null]

        if ($media && $media->getMedia() && false !== strpos($media->getMimeType(), 'image/')) {
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'small_thumb');
            $fileFieldOptions['help'] = '<a href="'.$this->liipImage->getBrowserPath($fullPath, 'default').'">';
            $fileFieldOptions['help'] .= '<img src="'.$this->liipImage->getBrowserPath($fullPath, 'small_thumb').'">';
            $fileFieldOptions['help'] .= '</a>';
        }
        $formMapper->add('mediaFile', FileType::class, $fileFieldOptions); // ['data_class'=>null]
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('createdAt');
        $datagridMapper->add('name');
        $datagridMapper->add('media');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setMosaicDefaultListMode();

        $listMapper->add('name');
        $listMapper->add('media');
        $listMapper->add('createdAt');
        $listMapper->add('mainColor');
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
