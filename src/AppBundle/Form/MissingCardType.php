<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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

class MissingCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $iktID_no   = $options['extras']['iktID_no'];
        $country_id = $options['extras']['country'];
        $builder->add('missingcard_registered_iqamaid', TextType::class, array(
            'label' => 'Registered Iqama ID/SSN','label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
            'attr' =>array('class' => 'form-control formLayout' , 'value' => $iktID_no , 'readonly' => 'readonly' , 'maxlength' => ($country_id == 'sa') ? 10 : 14 ),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        'pattern' => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                        'match' => true,
                        'message' => 'Invalid Iqama Id/SSN Number')),)))

            ->add('new_iktissab_id', RepeatedType::class, [
                'type' => TextType::class,
                'invalid_message' => 'New Iktissab id and comfirm iktissab id must be same',
                'required' => true,
                'first_options'  => array('attr' =>array('class' => 'form-control formLayout' , 'maxlength' => ($country_id == 'sa') ? 8 : 8 ),'label' => 'New Iktissab ID',  'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
                'second_options' => array('attr' =>array('class' => 'form-control formLayout' , 'maxlength' => ($country_id == 'sa') ? 8 : 8 ),'label' => 'Confirm Iktissab ID', 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
                'options' => array('attr' => array('class' => 'form-control')),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($country_id == 'sa') ? '/^[9]([0-9]){7}$/' : '/^[5]([0-9]){7}$/',
                            'match' => true,
                            'message' =>  ($country_id == 'sa') ? "Please enter valid iktissab id starting with 9" : "Please enter valid iktissab id starting with 5")
                    ),
                )
            ])




            ->add('comment_missingcard', TextareaType::class, array('label' => 'Comments' , 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12 form_labels'],
                'attr' =>array('maxlength' => 455, 'class' => 'form-control formLayout'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')))))
            ->add('Update', SubmitType::class ,array(
                'attr' => array('class' => 'btn btn-primary'),
            ) );
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