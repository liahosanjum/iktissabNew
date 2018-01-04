<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/1/17
 * Time: 2:49 PM
 */

namespace AppBundle\Form;


// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\DateType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\FileType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
// use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Component\Validator\Constraints\NotBlank;
// use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FormSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('formtype', ChoiceType::class, array(
                'label' => 'Select Type',
            'attr' => array(
                'class' => 'form-control'
            ),
                'choices' => array('Select Form Type' => '',
                    'Inquiries And Suggestion' => 'Inquiries And Suggestion', 'Contact Us' => 'Contact Us',
                'Faqs Form' => 'Faqs Form'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('status', ChoiceType::class, array(
                'label' => 'Status',
                'attr' => array(
                    'class' => 'form-control'
                ),
                'choices' => array('Status' => '', 'Active' => '1', 'In-Active' => '0'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('country', ChoiceType::class, array(
                    'label' => 'Country ',
                'attr' => array(
                    'class' => 'form-control'
                ),
                    'choices' => array('Country ' => '', 'Saudi Arabia' => 'sa', 'Egypt ' => 'eg'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
            ))


            ->add('submissions', ChoiceType::class, array(
                'label' => 'Submissions',
                'attr' => array(
                    'class' => 'form-control'
                ),
                'choices' => array('Submissions' => '', 'Every Hour' => '1', 'Every Day' => '24', 'Weekly' => '168', 'Monthly' => '720'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('limitto' , TextType::class, array('label' => 'Limit To',
                'attr' => array(  'maxlength' => 8,
                    'class' => 'form-control'
                ),'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' =>  '/^[1-9]([0-9])*$/',
                            'match' => true,
                            'message' => 'Invalid Data')),
                )
            ))

            ->add('token', HiddenType::class, array(
                'mapped'   => false,
                'required' => false,

            ))



            ->add('save', SubmitType::class, array('label' => 'Submit','attr' => array(
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
        //$resolver->setRequired('additional');               // Requires that currentOrg be set by the caller.
        //$resolver->setAllowedTypes('additional', 'array');  // Validates the type(s) of option(s) passed.
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'country', 'locale'
        ));

    }



}