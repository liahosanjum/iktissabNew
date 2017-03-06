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

class MobileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $iktID_no   = $options['extras']['iktID_no'];
        $country_id = $options['extras']['country'];
        if($country_id == 'sa'){ $ext = '966';}else{$ext = '0020'; }

        $builder->add('iqamaid_mobile', TextType::class, array(
            'label' => 'Registered Iqama ID/SSN',
            'attr' =>array('value' => $iktID_no , 'readonly' => 'readonly' , 'maxlength' => ($country_id == 'sa') ? 10 : 14 ),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        'pattern' => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                        'match' => true,
                        'message' => 'Invalid Iqama/SSN Number')),)))
            ->add('ext', TextType::class, array(
                'label' => ' ',
                'attr' => array('value'=> $ext , 'readonly' => 'readonly'),

            ))
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'attr' => array('maxlength'=> ($country_id == 'sa') ? 9 : 10),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($country_id == 'sa') ? '/^[5]([0-9]){8}$/' : '/^[1]([0-9]){9}$/',
                            'match' => true,
                            'message' =>  ($country_id == 'sa') ? "Please enter 9 digits mobile number starting with 5" : "Please enter 10 digits mobile number starting with 1")
                    ),

                )
            ))
            ->add('comment_mobile', TextareaType::class, array('label' => 'Comments',
                'attr' =>array('maxlength' => 255),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')))))
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