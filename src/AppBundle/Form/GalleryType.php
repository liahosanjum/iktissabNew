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

class GalleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('atitle' , TextType::class, array('label' => 'Title Arabic','required' => true,
                'attr' => array(
                    'class' => 'form-control'
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )

            ))
            ->add('etitle' , TextType::class, array('label' => 'Title English','required' => true,'attr' => array(
                'class' => 'form-control'
            ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )

                ))
            ->add('adesc'  , TextareaType::class, array('label' => 'Description Arabic','required' => true,'attr' => array(
                'class' => 'form-control'
            ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
                ))
            ->add('edesc'  , TextareaType::class, array('label' => 'Description English',
                'required' => true,
                'attr'     => array(
                'class'    => 'form-control'
            ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
                ))

            ->add('image', FileType::class, array('label' => 'Image (.jpg , .png only)' , 'data_class' => null,
                'required'    => true,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('status', CheckboxType::class, array(
                'label'    => 'Status',
                'mapped'   => false,
                'required' => false,
            ))



            ->add('display', ChoiceType::class, array(
                'label' => 'Select Type ',
                'attr' => array(
                    'class' => 'form-control'
                ),
                'choices' => array( 'Slider' => '1', 'Banner' => '2' ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('save', SubmitType::class, array('label' => 'Add Image','attr' => array(
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
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'country', 'locale'
        ));

    }



}