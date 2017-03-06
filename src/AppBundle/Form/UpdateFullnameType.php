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

class UpdateFullnameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $iktID_no   = $options['extras']['iktID_no'];
        $country_id = $options['extras']['country'];

        $builder->add('fullname_registered_iqamaid', TextType::class, array('label' => 'Registered Iqama ID/SSN',
            'attr' =>array('value' => $iktID_no , 'readonly' => 'readonly' ),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        'pattern' => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                        'match' => true,
                        'message' => 'Invalid Iqama/SSN Number')
                ),)
        ))
        ->add('fullname', TextType::class, array('label' => 'Full Name',
            'constraints' => array(
             new Assert\NotBlank(array('message' => 'This field is required')))))

        ->add('comment_fullname', TextareaType::class, array('label' => 'Comments',
             'attr' =>array('maxlength' => 255),
             'constraints' => array(
             new Assert\NotBlank(array('message' => 'This field is required')))))
        ->add('Update', SubmitType::class ,array(
             'attr' => array('class' => 'btn btn-primary'),
        ));
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