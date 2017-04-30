<?php
namespace AppBundle\Form;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ActivateCardoneType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $country_id =  $options['extras']['country'];
        $builder->add('email', EmailType::class, array('label' => 'Email', 'constraints' => array(

            new Assert\NotBlank(array('message' => 'This field is required')),
            new Assert\Email(array('message' => 'Invalid email'))

        )
        ))
            ->add('iktCardNo', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array('label' => 'Iktissab ID',
                'attr' =>array( 'maxlength' => 8),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => '/^[9,5]([0-9]){7}$/',
                            'match' => true,
                            'message' => 'Invalid Iktissab Card Number')),)
            ))
            ->add('captchaCode', CaptchaType::class, array(

                'label' => 'Captcha', 'captchaConfig' => 'FormCaptcha',
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required'))),
            ))
            ->add('status', CheckboxType::class, array(
                'label'    => 'Status',
                'mapped' => false,
                'required' => true,
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required'))),
            ))

            ->add('submit', SubmitType::class, array('label'=>"Step One", 'attr' => array('class' => 'btn btn-primary button-2x')) );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate')
        ));
        $resolver->setRequired('extras');
    }
}