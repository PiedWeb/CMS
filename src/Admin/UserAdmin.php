<?php
namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

use FOS\UserBundle\Model\UserManagerInterface;

use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\CoreBundle\Form\Type\ImmutableArrayType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormTypeInterface;

class UserAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper): void
    {
        // define group zoning
        $formMapper
            ->tab('User')
                ->with('Profile', ['class' => 'col-md-6'])->end()
                ->with('General', ['class' => 'col-md-6'])->end()
            ->end()
            ->tab('Security')
                ->with('Status', ['class' => 'col-md-4'])->end()
                ->with('Roles', ['class' => 'col-md-12'])->end()
            ->end()
        ;

        $now = new \DateTime();

        $formMapper
            ->tab('User')
                ->with('General')
                    //->add('username')
                    ->add('email')
                    ->add('plainPassword', TextType::class, [
                        'required' => (!$this->getSubject() || null === $this->getSubject()->getId()),
                    ])
                ->end()
                /**/
                ->with('Profile')
                    ->add('dateOfBirth', DatePickerType::class, [
                        'years' => range(1900, $now->format('Y')),
                        'dp_min_date' => '1-1-1900',
                        'dp_max_date' => $now->format('c'),
                        'required' => false,
                    ])
                    ->add('firstname', TextType::class, ['required' => false])
                    ->add('lastname', TextType::class, ['required' => false])
                    ->add('city', TextType::class, ['required' => false])
                    //->add('country', TextType::class, ['required' => false])
                    ->add('phone', TextType::class, ['required' => false])
                ->end()
            ->end()
            ->tab('Security')
                ->with('Status')
                    ->add('enabled', null, ['required' => false])
                ->end()
                /*
                ->with('Groups')
                    ->add('groups', ModelType::class, [
                        'required' => false,
                        'expanded' => true,
                        'multiple' => true,
                    ])
                ->end()
                */
                ->with('Roles')
                    ->add('roles', ImmutableArrayType::class, [
                        'keys' => [
                            ['0', TextType::class, ['required' => false]],
                        ]
                    ])
                ->end()

            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('id')
            ->add('email')
            //->add('groups')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('email')
            ->add('firstname', null, ['editable' => true])
            ->add('lastname', null, ['editable' => true])
            ->add('roles') // TODO : sexiest
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt')
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
                'row_align' => 'right',
                'header_class' => 'text-right',
                'label' => 'admin.action',
            ])
        ;
    }
}
