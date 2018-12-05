<?php

namespace PiedWeb\CMSBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\CoreBundle\Form\Type\ImmutableArrayType;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $now = new \DateTime();

        $formMapper
            ->with('admin.user.label.id', ['class' => 'col-md-4'])
                    //->add('username')
                    ->add('email', null, [
                        'label' => 'admin.user.email.label',
                    ])
                    ->add('plainPassword', TextType::class, [
                        'required' => (!$this->getSubject() || null === $this->getSubject()->getId()),
                        'label' => 'admin.user.password.label',
                    ])
            ->end()
        ;

        $formMapper
            ->with('admin.user.label.profile', ['class' => 'col-md-4'])
        ;

        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getDateOfBirth')) {
            $formMapper->add('dateOfBirth', DatePickerType::class, [
                'years' => range(1900, $now->format('Y')),
                'dp_min_date' => '1-1-1900',
                'dp_max_date' => $now->format('c'),
                'required' => false,
                'label' => 'admin.user.dateOfBirth.label',
            ]);
        }
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getfirstname')) {
            $formMapper->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'admin.user.firstname.label',
            ]);
        }
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getlastname')) {
            $formMapper->add('lastname', TextType::class, [
                'required' => false,
                'label' => 'admin.user.lastname.label',
            ]);
        }
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getcity')) {
            $formMapper->add('city', TextType::class, [
                'required' => false,
                'label' => 'admin.user.city.label',
            ]);
        }
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getphone')) {
            $formMapper->add('phone', TextType::class, [
                'required' => false,
                'label' => 'admin.user.phone.label',
            ]);
        }

        $formMapper->end()

            ->with('admin.user.label.security', ['class' => 'col-md-4'])
                ->add('enabled', null, [
                    'required' => false,
                    'label' => 'admin.user.enabled.label',
                ])

                /*
                ->with('Groups')
                    ->add('groups', ModelType::class, [
                        'required' => false,
                        'expanded' => true,
                        'multiple' => true,
                    ])
                ->end()
                */

                ->add('roles', ImmutableArrayType::class, [
                    'label' => false,
                    'keys' => [
                        ['0', ChoiceType::class, [
                            'required' => false,
                            'label' => 'admin.user.role.label',
                            'choices' => [
                                'admin.user.role.admin' => 'ROLE_SUPER_ADMIN',
                                'admin.user.role.user' => 'ROLE_USER',
                            ],
                        ]],
                    ],
                ])

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
            ->add('email', null, [
                'label' => 'admin.user.email.label',
            ]);
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getfirstname')) {
            $listMapper->add('firstname', TextType::class, [
                'editable' => true,
                'label' => 'admin.user.firstname.label',
            ]);
        }
        if (method_exists($this->getConfigurationPool()->getContainer()->getParameter('app.entity_user'), 'getlastname')) {
            $listMapper->add('lastname', TextType::class, [
                'editable' => true,
                'label' => 'admin.user.lastname.label',
            ]);
        }

        /**todo
        $listMapper->add('roles[0]', null, [
                'label' => 'admin.user.role.label',
            ]);
        /**/
        $listMapper
            ->add('enabled', null, [
                'editable' => true,
                'label' => 'admin.user.enabled.label',
            ])
            ->add('createdAt', null, [
                'editable' => true,
                'label' => 'admin.user.createdAt.label',
            ])
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
