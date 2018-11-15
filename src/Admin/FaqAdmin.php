<?php
namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FaqAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('category', TextType::class);
        $formMapper->add('question', TextType::class);
        $formMapper->add('answer', TextareaType::class, ['attr' =>['style' => 'min-height: 200px;']]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('category');
        $datagridMapper->add('question');
        $datagridMapper->add('answer');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->add('id');
        $listMapper->add('question', null, [
            'editable' => true,
        ]);
        $listMapper->add('category', null, [
            'editable' => true,
        ]);
        $listMapper->add('answer', null, [
            'editable' => true,
        ]);
        $listMapper ->add('_action', null, [
                'actions' => [
                'edit' => [],
                'delete' => [],
            ]
        ]);
    }
}
