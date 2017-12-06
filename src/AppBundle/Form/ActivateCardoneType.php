<?php
namespace AppBundle\Form;



//use Symfony\Component\BrowserKit\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;






class ActivateCardoneType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $country_id =  $options['extras']['country'];
        $builder->add('email', EmailType::class, array('label' => 'Email','attr' => array('maxlength' => 50), 'constraints' => array(

            new NotBlank(array('message' => 'This field is required')),
            new Email(array('message' => 'Invalid email address'))

        )
        ))

            ->add('iktCardNo',TextType::class, array('label' => 'Iktissab ID',
                'attr' =>array( 'maxlength' => 8),
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                    new Regex(
                        array(
                            //'pattern' => '/^[9,5]([0-9]){7}$/', basit code commented by sohail
                            'pattern' => ($country_id == 'sa') ? '/^[9]([0-9]){7}$/' : '/^[5]([0-9]){7}$/',
                            'match' => true,
                            'message' => 'Invalid Iktissab Card Number')),)
            ))


            ->add('captchaCode', TextType::class, array(
                'label'                          => 'Captcha',
                'constraints'                    =>  array (
                    new NotBlank(array('message' => 'This field is required'))),
            ))

            ->add('status', CheckboxType::class, array(
                'label'                          => 'Status',
                'constraints'                    =>  array (
                    new NotBlank(array('message' => 'This field is required'))
                ),
            ))


            ->add('status', CheckboxType::class, array(
                'label'       => 'Status',
                'mapped'      => false,
                'required'    => true,
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