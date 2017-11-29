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

class CmsPagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('page_title' , TextType::class, array('label' => 'Title','required' => true,
                'attr' => array(
                    'class' => 'form-control'
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),)

            ))

            ->add('page_content' , TextareaType::class, array('label' => 'Description','required' => true,'attr' => array(
                'class' => 'form-control'
            ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )))
            
            ->add('url_path'  , TextType::class, array('label' => 'Enter url separated by hypen i.e ( about-us ) ','required' => true,'attr' => array(
                'class' => 'form-control'
            ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )))

            ->add('brochure', FileType::class, array('label' => 'Image' ,'data_class' => null))


            ->add('country', ChoiceType::class, array(
                'attr' => array(
                    'class' => 'form-control col-lg-3'
                ),
                'label' => 'Select Country:',
                'choices' => array('Select Country ' => '', 'Saudi Arabia' => 'sa', 'Egypt ' => 'eg'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('language', ChoiceType::class, array(
                'attr' => array(
                    'class' => 'form-control col-lg-3'
                ),
                'label' => 'Select language:',
                'choices' => array('Select language ' => '', 'English' => 'en', 'Arabic ' => 'ar'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('type', ChoiceType::class, array(
                'attr' => array(
                    'class' => 'form-control col-lg-3'
                ),
                'label' => 'Select Type:',
                'choices' => array('Select Type ' => '', 'cms' => 'cms', 'news' => 'news'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('status', CheckboxType::class, array(
                'label'    => 'Status',
                'mapped'   => false,
                'required' => false,
            ))

            ->add('save', SubmitType::class, array('label' => 'Create Page','attr' => array(
                'class' => 'form-control cms-button' ,
            )));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'validate' => 'validate',
                'var' => null
            ),
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