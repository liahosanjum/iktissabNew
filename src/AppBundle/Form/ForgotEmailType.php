<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 2/21/17
 * Time: 8:24 AM
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;


class ForgotEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $lookupData = $options['additional'];
        $country_id = $lookupData['country'];

        $builder->add('iktCardNo', TextType::class, array('label' => 'Iktissab ID',

            'label_attr' => ['class' => 'form_labels form_labels col-lg-12 formLayout col-md-12 col-sm-12 col-xs-12 nopadding'],
            'attr' => array('maxlength' => 8 , 'class' => 'form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout'),
            'constraints' => array(

                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        //'pattern' => '/^[9,5]([0-9]){7}$/', basit code commented by sohail
                        'pattern' => ($country_id == 'sa') ? '/^[9]([0-9]){7}$/' : '/^[5]([0-9]){7}$/',

                        'match' => true,
                        'message' => 'Invalid Iktissab Card Number')
                )
            )
        ))
            ->add('iqama', TextType::class, array(
                'label' => 'Registered Iqama ID/SSN'.$lookupData['country'],
                'attr' => array( 'class' => 'form-control-modified col-lg-12 col-md-12 col-sm-12 col-xs-12 formLayout',
                'maxlength' => ($lookupData['country'] == 'sa') ? 10 : 14 ),
                'label_attr' => ['class' => 'form_labels inq-form form-separator formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12    form_labels nopadding'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($lookupData['country'] == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => 'Invalid Iqama Id/SSN Number'.$lookupData['country'])
                    ),
//                    new Assert\Callback([
//                        'callback' => [$this, 'validateIqama']
//                    ])
                )
            ))

            ->add('captchaCode', CaptchaType::class, array(

                'label' => 'Captcha',
                'label_attr' => [  'class' => 'forgot-email-captcha form_labels'  ],
                'captchaConfig' => 'FormCaptcha',
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                    new ValidCaptcha(array("message"=>"Invalid captcha code"))),
            ))

            ->add('submit', SubmitType::class, array(

                'attr' => array('class' => 'btn btn-primary mobile-form-mags'),
                'label' => 'Submit'
            ));

    }

    /**
     * @Assert\Callback
     */
    public function validateIqama($iqama, ExecutionContextInterface $context)
    {


        $evenSum = 0;
        $oddSum = 0;
        $entireSum = 0;
        for ($i = 0; $i < strlen($iqama); $i++) {
            $temp = '';
            if ($i % 2) { // odd number

                $oddSum = $oddSum + $iqama[$i];

            } else {
                //even
                $multE = $iqama[$i] * 2;
                if (strlen($multE) > 1) {
                    $temp = (string)$multE;
                    $evenSum = $evenSum + ($temp[0] + $temp[1]);
                } else {
                    $evenSum = $evenSum + $multE;
                }
            }
        }
        $entireSum = $evenSum + $oddSum;
        if (($entireSum % 10) == 0) {
            // valid
        } else {
            $context->buildViolation('Iqama Id/SSN is not valid')
                ->atPath('iqama')
                ->addViolation();
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'novalidate' => 'novalidate',
                'var' => null
            ),
        ));
        $resolver->setRequired('additional'); // Requires that currentOrg be set by the caller.
        $resolver->setAllowedTypes('additional', 'array'); // Validates the type(s) of option(s) passed.
    }
}