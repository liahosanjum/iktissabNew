<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EnterOtpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('otp', TextType::class, array('label' => 'Enter Code',
            'attr'  =>array( 'class' => 'form-control col-lg-4 input-text required-entry'),
            'constraints' => array(
            new Assert\NotBlank(array('message' => 'This Field is required')),

        )
        ))
            ->add('submit', SubmitType::class,array( 'attr' => array('class' => 'btn btn-primary')));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));
    }
}

?>