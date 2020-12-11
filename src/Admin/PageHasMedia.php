<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PageHasMedia extends AbstractAdmin
{
    private $liipImage;

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject() ? $this->getSubject()->getMedia() : null;

        $help = null;
        if ($media && $media->getMedia() && false !== strpos($media->getMimeType(), 'image/')) {
            $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();

            $help = '<a href="'.$this->routeGenerator->generate('admin_app_media_edit', ['id'=> $media->getId()]).'" target=_blank>';
            $help .= '<img src="'.$this->liipImage->getBrowserPath($fullPath, 'thumb').'" style="width:100%; max-width:300px">';
            $help .= '</a>';
            $help .= '<pre onclick="copyElementText(this);" class="btn" style="font-size: 80%; text-overflow: ellipsis; margin-top:10px; max-width: 160px; white-space: nowrap; overflow: hidden;">!['.str_replace(['[', '"', ']'], ' ', $media->getName()).']('.$this->liipImage->getBrowserPath($fullPath, 'default').')</pre>';
        }

        $formMapper
            ->add(
                'media',
                ModelListType::class,
                [
                'required' => false,
                'btn_delete' => false,
                'btn_edit' => false,
                'btn_add' => (!$media) ? ' ' : false,
                'btn_list' => (!$media) ? ' ' : false,
                'sonata_help' => $help,
                ]
            )
            ->add('position', HiddenType::class);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('media')
            ->add('page');
    }
}
