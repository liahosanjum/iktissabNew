<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('old_password', TextType::class, ['label' => "Enter Current Password",
            'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels'],
            'attr' => ['class' => 'form-control   formLayout'],
        'constraints' => array
        (
            new NotBlank(array('message' => 'This field is required')),
        ),
    ])
        ->add('new_password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Password fields must match',
            'required' => true,
            'first_options' => array('attr' =>array('class' => 'form-control col-lg-12 col-md-12 col-sm-12 col-xs-12     formLayout'), 'label' => 'New Password', 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
            'second_options' => array('attr' =>array('class' => 'form-control col-lg-12 col-md-12 col-sm-12 col-xs-12   formLayout'), 'label' => 'Confirm New Password', 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
            'options' => array('attr' => array('class' => 'form-control')),
            'constraints' => array(
                new NotBlank(array('message' => 'This field is required')),
            )
        ])
        ->add('Update', SubmitType::class ,array(
            'attr' => array('class' => 'btn col-lg-4 btn-primary '),
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