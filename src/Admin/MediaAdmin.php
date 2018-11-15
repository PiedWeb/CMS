<?php
namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class MediaAdmin extends AbstractAdmin
{
    use AdminTrait;

    private $liipImage;

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $fileFieldOptions = ['required' => false, 'data_class'=>null];
        $media = $this->getSubject();

        //$type = $media && $media->getName() === null ? TextType::class : HiddenType::class;
        $formMapper->add('name', TextType::class, [
            'required' => false,
            'help' => '<small>Choisissez un à plusieurs mots décrivant votre fichier. N\'hésitez pas à utiliser un `patern` pour mieux vous y retrouver (ex: #Attribute - Description)</small>',
        ]); // ['data_class'=>null]

        if ($media && $media->getMedia() && strpos($media->getMimeType(), 'image/') !==false) {
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'small_thumb');
            $fileFieldOptions['help'] = '<a href="'.$this->liipImage->getBrowserPath($fullPath, 'default').'">';
            $fileFieldOptions['help'] .= '<img src="'.$this->liipImage->getBrowserPath($fullPath, 'small_thumb').'">';
            $fileFieldOptions['help'] .= '</a><div style=display:none>';
            foreach (['thumb','height_300','xs','sm','md','lg','xl','default'] as $format) { // TODO, récupérer la liste en auto/parameters
                $fileFieldOptions['help'] .=  '<img src="'.$this->liipImage->getBrowserPath($fullPath, $format).'">';
            }
            $fileFieldOptions['help'] .= '</div>';
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
        $listMapper ->add('_action', null, [
                'actions' => [
                'edit' => [],
                'delete' => [],
            ]
        ]);
    }

    public function getObjectMetadata($media)
    {
        if (strpos($media->getMimeType(), 'image/') !==false) {
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();
            $thumb = $this->liipImage->getBrowserPath($fullPath, 'thumb');
        } else {
            $thumb = null; // feature, une icone en fonction du media (pdf/word...)
        }
        return new Metadata($media->getName(), null, $thumb);
    }
}
