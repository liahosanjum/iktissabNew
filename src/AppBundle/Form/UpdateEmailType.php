<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;

class UpdateEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $email_current   = $options['extras']['email'];



        $builder->add('currentemail', EmailType::class, array(
            'label' => 'Current Email',
            'attr' =>array('value' => $email_current , 'readonly' => 'readonly'),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),)))
            ->add('newemail', RepeatedType::class, [
                'type' => TextType::class,
                'invalid_message' => 'New email and confirm email fields must match',
                'required' => true,
                'first_options' => array('label' => 'New Email', 'label_attr' => ['class' => 'CUSTOM_LABEL_CLASS']),
                'second_options' => array('label' => 'Confirm New Email', 'label_attr' => ['class' => 'CUSTOM_LABEL_CLASS']),
                'options' => array('attr' => array('class' => 'form-control')),
                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
                    

                )
            ])



            ->add( 'Update', SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary'),));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));
        $resolver->setRequired('extras');
    }
}
?>