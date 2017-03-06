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

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('old_password', TextType::class, ['label' => "Enter Current Password", 'label_attr' => ['class' => 'CUSTOM_LABEL_CLASS'], 'attr' => ['class' => 'form-control col-lg-12'],
        'constraints' => array
        (
            new NotBlank(array('message' => 'This field is required')),
        ),
    ])
        ->add('new_password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Password fields must match',
            'required' => true,
            'first_options' => array('label' => 'New Password', 'label_attr' => ['class' => 'required CUSTOM_LABEL_CLASS']),
            'second_options' => array('label' => 'Confirm New Password', 'label_attr' => ['class' => ' required CUSTOM_LABEL_CLASS']),
            'options' => array('attr' => array('class' => 'form-control')),
            'constraints' => array(
                new NotBlank(array('message' => 'This field is required')),
            )
        ])
        ->add('Update', SubmitType::class ,array(
            'attr' => array('class' => 'btn btn-primary'),
        ) );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));

    }
}
?>