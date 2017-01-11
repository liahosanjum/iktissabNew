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

class IktCustomerInfo extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, array('label' => 'Email Id', 'constraints' => array(

            new Assert\NotBlank(array('message' => 'Email is required')),
            new Assert\Email(array('message' => 'Invalid email'))

        )
        ))
            ->add('iktCardNo', IntegerType::class, array('label' => 'Iktissab ID', 'disabled' => true,
                'constraints' => array(

                    new Assert\NotBlank(array('message' => 'Iktissab id  is required')),
                    new Assert\Regex(
                        array('pattern' => '/^[9,5]([0-9]){7}$/', 'match' => true, 'message' => 'Invalid Iktissab Card Number')
                    )
                )
            ))
            ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match',
                    'required' => true,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat password'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));
    }
}
?>