<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/1/17
 * Time: 2:49 PM
 */

namespace AppBundle\Form;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EmailSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('email' , TextType::class, array('label' => 'Email ',
                'attr' => array(
                    'class' => 'form-control'
                ),'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Email(array('message' => 'Invalid email address')),
                    new Assert\Regex(
                        array(
                            'pattern' =>  '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
                            'match' => true,
                            'message' => 'Invalid Data')),
                )
            ))
            ->add('type', ChoiceType::class, array(
                'label' => 'Select Form Type',
                'attr' => array(
                    'class' => 'form-control'
                ),
                'choices' => array('Select Form Type' => '', 'Inquiries And Suggestion' => 'Inquiries And Suggestion', 'Contact Us ' => 'Contact Us'),
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

            ->add('technical', ChoiceType::class, array(
                'label' => 'Select Technical Type',
                'attr' => array(
                    'class' => 'form-control'
                ),
                'choices' => array('Select Technical Type' => '', 'Active' => '1', 'In-Active' => '0'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            //->add('brochure', FileType::class, array('label' => 'Brochure (PDF file)' ))

            ->add('other', ChoiceType::class, array(
                'label' => 'Select Type Other',
                'attr' => array(
                    'class' => 'form-control'
                ),
                'choices' => array('Select Type Other' => '', 'Active' => '1', 'In-Active' => '0'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))

            ->add('token', HiddenType::class, array(
                'mapped'   => false,
                'required' => false,

            ))



            ->add('save', SubmitType::class, array('label' => 'Add','attr' => array(
                'class' => 'form-control cms-button ' ,
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