<?php

namespace PiedWeb\CMSBundle\Extension\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PageHasMediaAdmin extends AbstractAdmin implements PageHasMediaAdminInterface
{
    private $liipImage;

    public function setLiipImage($liipImage)
    {
        $this->liipImage = $liipImage;
    }

    protected function getMedialHelp($media)
    {
        if (! ($media && $media->getMedia() && false !== strpos($media->getMimeType(), 'image/'))) {
            return null;
        }

        $fullPath = '/'.$media->getRelativeDir().'/'.$media->getMedia();

        $editUrl = $this->routeGenerator->generate('admin_app_media_edit', ['id' => $media->getId()]);
        $thumbUrl = $this->liipImage->getBrowserPath($fullPath, 'thumb');
        $defaultUrl = $this->liipImage->getBrowserPath($fullPath, 'default');

        $help = '<a href="'.$editUrl.'" target=_blank>';
        $help .= '<img src="'.$thumbUrl.'" style="width:100%; max-width:300px">';
        $help .= '</a>';
        $help .= '<pre onclick="copyElementText(this);" class="btn"';
        $help .= ' style="font-size:80%;text-overflow:ellipsis;margin-top:10px;max-width:160px;white-space:nowrap;';
        $help .= 'overflow:hidden">';
        $help .= '!['.str_replace(['[', '"', ']'], ' ', $media->getName()).']('.$defaultUrl.')';
        $help .= '</pre>';

        return $help;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject() ? $this->getSubject()->getMedia() : null;

        $formMapper
            ->add(
                'media',
                ModelListType::class,
                [
                'required' => false,
                'btn_delete' => false,
                'btn_edit' => false,
                'btn_add' => (! $media) ? ' ' : false,
                'btn_list' => (! $media) ? ' ' : false,
                'sonata_help' => $this->getMedialHelp($media),
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
