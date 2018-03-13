<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
// use Symfony\Component\Validator\Constraints\Email;
// use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;

class IqamassnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $iktID_no   = $options['extras']['iktID_no'];
        $country_id = $options['extras']['country'];

        $builder->add('iqamassn_registered', TextType::class, array(
            'label' => 'Registered Iqama ID/SSN'.$country_id,
            'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
            'attr' =>array('readonly' => 'readonly' , 'value' => $iktID_no  , 'maxlength' => ($country_id == 'sa') ? 10 : 14  ,'class' => 'form-control formLayout' ),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        'pattern' => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                        'match' => true,
                        'message' => 'Invalid Iqama Id/SSN Number'.$country_id)),


                )))
            ->add('iqamassn_new', RepeatedType::class, [
                'type' => TextType::class,

                'invalid_message' => 'Iqama/SSN fields must match'.$country_id,
                'required' => true,
                'first_options'  => array('attr' =>array('class' => 'form-control formLayout' , 'maxlength' => ($country_id == 'sa') ? 10 : 14 ),  'label' => 'New Iqama ID/SSN'.$country_id, 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
                'second_options' => array( 'attr' =>array('class' => 'form-control formLayout' ,'maxlength' => ($country_id == 'sa') ? 10 : 14 ), 'label' => 'Confirm New Iqama ID/SSN'.$country_id, 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
                'options' => array('attr' => array('class' => 'form-control')),
                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
                    new Assert\Regex(
                    array (
                            'pattern'   => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                            'match'     => true,
                            'message'   => 'Invalid Iqama Id/SSN Number'.$country_id)),)])
            ->add('comment_iqamassn', TextareaType::class, array('label' => 'Comments',  'label_attr' => ['class' => 'formLayout required col-lg-12 col-md-12 col-sm-12 col-xs-12 form_labels'],
                'attr'          => array('maxlength' => 255 , 'class' => 'form-control formLayout'),
                'constraints'   => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),


                    )

            ))

            ->add('token', HiddenType::class, array(
                'mapped'   => false,
                'required' => false,
            ))

            ->add( 'Update', SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary ikt-ssn'),));
    }





    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('novalidate' => 'novalidate'),
            'csrf_protection' => false,
        ));
        $resolver->setRequired('extras');
    }
}
?>