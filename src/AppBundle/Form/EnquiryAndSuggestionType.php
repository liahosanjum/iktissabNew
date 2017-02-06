<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnquiryAndSuggestionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array('label'=>"Name"))
            ->add('job', TextType::class, array('label'=>"Job"))
            ->add('mobile', TextType::class, array('label'=>"Mobile"))
            ->add('email', TextType::class, array('label'=>"Email"))
            ->add('reason', TextType::class, array('label'=>"Reason"))
            ->add('comments', TextareaType::class, array('label'=>"Comments"))
            //->add('country', TextType::class, array('label'=>"Country"))
            ->add('submit', SubmitType::class, array('label'=>"Submit"));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EnquiryAndSuggestion'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_enquiryandsuggestion';
    }


}
