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
        $builder->add('email', EmailType::class, array('label' => 'Email Id',
            'attr' => array('class' => 'col-lg-8 form-control formLayout' ,'readonly' => 'readonly' ),
            'constraints' => array(
            new Assert\NotBlank(array('message' => 'Email is required')),
            new Assert\Email(array('message' => 'Invalid email address'))

        )
        ))
            ->add('iktCardNo', IntegerType::class, array('label' => 'Iktissab ID',
                
                'attr' => array('class' => 'col-lg-8 form-control formLayout',  'readonly' => 'readonly','maxlength'=>8 ),


                'constraints' => array(

                    new Assert\NotBlank(array('message' => 'Iktissab id  is required')),
                    new Assert\Regex(
                        array('pattern' => '/^[9,5]([0-9]){7}$/', 'match' => true, 'message' => 'Invalid Iktissab Card Number')
                    )
                )
            ))
            ->add('password', RepeatedType::class, array(
                    'label' => 'Email Id',

                    'type' => PasswordType::class,
                    'invalid_message' => 'Password fields must match',
                    'required' => true,
                    'first_options' => array('label' => 'Password','label_attr' => ['class' => 'required formLayout form_labels'], 'attr' => array('class' => 'col-lg-8 form-control formLayout')),
                    'second_options' => array('label' => 'Repeat password','label_attr' => ['class' => 'required formLayout form_labels'], 'attr' => array('class' => 'col-lg-8 form-control formLayout')),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                        new Assert\Length(array('min'=> 6, 'minMessage'=> 'Password must be greater then 6 characters'))
                    )
                )
            )
            ->add('submit', SubmitType::class,array(
                'attr' => array('class' => 'btn btn-primary')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));
    }
}
?>