<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class MobileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $iktID_no   = $options['extras']['iktID_no'];
        $country_id = $options['extras']['country'];
        if($country_id == 'sa'){ $ext = '+966';}else{$ext = '002'; }

        $builder->add('iqamaid_mobile', TextType::class, array(
            'label' => 'Registered Iqama ID/SSN'.$country_id,'label_attr' => ['class' => 'formLayout col-lg-9 col-md-12 col-sm-12 col-xs-12   form_labels'],
            'attr' =>array('class' => 'form-control col-lg-9  col-md-10  col-sm-10 formLayout' ,  'value' => $iktID_no , 'readonly' => 'readonly' , 'maxlength' => ($country_id == 'sa') ? 10 : 14 ),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        'pattern' => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                        'match' => true,
                        'message' => 'Invalid Iqama Id/SSN Number'.$country_id)),)))
            ->add('ext', TextType::class, array(
                'label' => 'Country-Code:','label_attr' => ['class' => ' formControl-ext formLayout col-lg-6 col-md-6 col-sm-6 col-xs-2   form_labels'],
                'attr' => array('class' => 'mobile_ext form-control-modified formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   ' , 'value'=> $ext , 'readonly' => 'readonly'),

            ))
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile','label_attr' => ['class' => 'formControl-ext  formLayout col-lg-9 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'attr' => array('class' => 'formControl form-control formLayout  col-lg-9 col-md-10 col-sm-10 col-xs-10   form_labels' , 'maxlength'=> ($country_id == 'sa') ? 9 : 11),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($country_id == 'sa') ? '/^[5]([0-9]){8}$/' : '/^[0]([0-9]){10}$/',
                            'match' => true,
                            'message' =>  ($country_id == 'sa') ? "Please enter 9 digits mobile number starting with 5" : "Please enter 11 digits mobile number starting with 0")
                    ),)))

           
            
            ->add('comment_mobile', TextareaType::class, array('label' => 'Comments',
                'label_attr' => ['class' => 'formControl formLayout  form_labels'],

                'attr' =>array('class' => 'formControl  col-lg-12 col-md-12 col-sm-12 col-xs-12 ' , 'maxlength' => 255),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')))))
            ->add( 'Update', SubmitType::class ,array(
                'attr' => array('class' => 'col-lg-3 btn btn-primary'),));
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