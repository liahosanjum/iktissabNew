<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ActivateCardone extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, array('label' => 'Email Id', 'constraints' => array(

            new Assert\NotBlank(array('message' => 'Email is required')),
            new Assert\Email(array('message' => 'Invalid email'))

        )
        ))
            ->add('iktCardNo', IntegerType::class, array('label' => 'Iktissab ID',
                'constraints' => array(

                    new Assert\NotBlank(array('message' => 'Iktissab id  is required')),
                    new Assert\Regex(
                        array('pattern' => '/^[9,5]([0-9]){7}$/', 'match' => true, 'message' => 'Invalid Iktissab Card Number')
                    )
                )
            ))
//            ->add('captchaCode', 'Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType', array(
//                    'captchaConfig' => 'ExampleCaptcha',
////                    'mapped' => false,
//                    'label' => false,
//                    'attr' => array('placeholder' => 'Enter Code'),
//                    'constraints' => array(
//
//                        new Assert\NotBlank(array('message' => 'Captcha is required'))
//                    )
//                )
//            )
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));
    }
}