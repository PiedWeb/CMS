<?php

namespace PiedWeb\CMSBundle\Extension\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserAdmin extends AbstractAdmin
{
    use AdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function exists(string $name): bool
    {
        return method_exists($this->userClass, 'get'.$name);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        // Forbid edition of other admin account except for super admin
        if (($this->getSubject()->hasRole('ROLE_SUPER_ADMIN')
            && $this->getUser()->getId() !== $this->getSubject()->getId())) {
            throw new AccessDeniedException('u can\'t edit this user'); // TODO : do better
        }

        $now = new \DateTime();

        $formMapper
            ->with('admin.user.label.id', ['class' => 'col-md-4'])
            //->add('username')
            ->add('email', null, [
                'label' => 'admin.user.email.label',
            ])
            ->add('plainPassword', TextType::class, [
                'required' => (! $this->getSubject() || null === $this->getSubject()->getId()),
                'label' => 'admin.user.password.label',
            ])
            ->end();

        $formMapper
            ->with('admin.user.label.profile', ['class' => 'col-md-4']);

        if ($this->exists('DateOfBirth')) {
            $formMapper->add(
                'dateOfBirth',
                DatePickerType::class,
                [
                    'years' => range(1900, $now->format('Y')),
                    'dp_min_date' => '1-1-1900',
                    'dp_max_date' => $now->format('c'),
                    'required' => false,
                    'label' => 'admin.user.dateOfBirth.label',
                ]
            );
        }
        if ($this->exists('firstname')) {
            $formMapper->add(
                'firstname',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'admin.user.firstname.label',
                ]
            );
        }
        if ($this->exists('lastname')) {
            $formMapper->add(
                'lastname',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'admin.user.lastname.label',
                ]
            );
        }
        if ($this->exists('city')) {
            $formMapper->add(
                'city',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'admin.user.city.label',
                ]
            );
        }
        if ($this->exists('phone')) {
            $formMapper->add(
                'phone',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'admin.user.phone.label',
                ]
            );
        }

        $formMapper->end()

            ->with('admin.user.label.security', ['class' => 'col-md-4'])
            ->add('roles', ImmutableArrayType::class, [
                'label' => false,
                'keys' => [
                    ['0', ChoiceType::class, [
                        'required' => false,
                        'label' => 'admin.user.role.label',
                        'choices' => $this->getUser()->hasRole('ROLE_SUPER_ADMIN') ? [
                            'admin.user.role.super_admin' => 'ROLE_SUPER_ADMIN',
                            'admin.user.role.admin' => 'ROLE_ADMIN',
                            'admin.user.role.editor' => 'ROLE_EDITOR',
                            'admin.user.role.user' => 'ROLE_USER',
                        ] : [
                            'admin.user.role.admin' => 'ROLE_ADMIN',
                            'admin.user.role.editor' => 'ROLE_EDITOR',
                            'admin.user.role.user' => 'ROLE_USER',
                        ],
                    ]],
                ],
            ])
            ->end();
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
        if ($this->exists('firstname')) {
            $listMapper->add(
                'firstname',
                TextType::class,
                [
                    'editable' => true,
                    'label' => 'admin.user.firstname.label',
                ]
            );
        }
        if ($this->exists('lastname')) {
            $listMapper->add(
                'lastname',
                TextType::class,
                [
                    'editable' => true,
                    'label' => 'admin.user.lastname.label',
                ]
            );
        }

        /*
* todo
        $listMapper->add('roles[0]', null, [
                'label' => 'admin.user.role.label',
            ]);
        /**/
        $listMapper
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
            ]);
    }
}
