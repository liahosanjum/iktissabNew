<?php

namespace AppBundle\Form;

use AppBundle\Entity\ContentPageTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentPageTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, array('label'=>'Page Title'))
            ->add('content', TextareaType::class, array('label'=>'Page Content'));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array( 'data_class'=>ContentPageTranslation::class)
        );
    }

    public function getName()
    {
        return 'app_bundle_page_content_translation_type';
    }
}
