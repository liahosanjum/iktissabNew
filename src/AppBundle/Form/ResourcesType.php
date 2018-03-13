<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/1/17
 * Time: 2:49 PM
 */

namespace AppBundle\Form;


// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\DateType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
// use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

// use Symfony\Component\Validator\Constraints\NotBlank;
// use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('resource_name', ChoiceType::class, array(
                'attr' => array(
                    'class' => 'form-control col-lg-3'
                ),
                'label' => 'Select Resource:',
                'choices' => array('Select Resource ' => '',
                    'View CMS' => 'MANAGE_CMS_VIEW',
                    'Add CMS Page'  => 'MANAGE_CMS_ADD',
                    'Edit CMS page'  => 'MANAGE_CMS_EDIT',
                    'Delete CMS Page'  => 'MANAGE_CMS_DELETE',
                    'View NEWS' => 'MANAGE_NEWS_VIEW',
                    'Add NEWS Page'  => 'MANAGE_NEWS_ADD',
                    'Edit NEWS page'  => 'MANAGE_NEWS_EDIT',
                    'Delete NEWS Page'  => 'MANAGE_NEWS_DELETE'



                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),

                )
            ))

            ->add('assigned_to', ChoiceType::class, array(
                    'attr'  => array(
                    'class' => 'form-control col-lg-3'
                ),
                'label' => 'Select Resource:',
                'choices' => array('Select Roles ' => '',
                    'CMS Editor'    => 'EDITOR_ROLE',
                    'News Editor'   => 'EDITOR_ROLE2'
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),

                )
            ))


            ->add('token', HiddenType::class, array(
                'mapped'   => false,
                'required' => false,
            ))

            ->add('status', CheckboxType::class, array(
                'label'    => 'Status',
                'mapped'   => false,
                'required' => false,
            ))


            ->add('save', SubmitType::class, array('label' => 'Create','attr' => array(
                'class' => 'form-control res-button' ,
            )));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'novalidate' => 'novalidate',
                'var' => null
            ),
            'csrf_protection' => false,
        ));
        // $resolver->setRequired('additional');               // Requires that currentOrg be set by the caller.
        // $resolver->setAllowedTypes('additional', 'array');  // Validates the type(s) of option(s) passed.
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'country', 'locale'
        ));

    }



}