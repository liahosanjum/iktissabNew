<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UpdateFullnameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $iktID_no   = $options['extras']['iktID_no'];
        $country_id = $options['extras']['country'];

        $builder->add('fullname_registered_iqamaid', TextType::class, array('label' => 'Registered Iqama ID/SSN'.$country_id,
            'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
            'attr' =>array('class' => 'form-control formLayout' ,'value' => $iktID_no , 'readonly' => 'readonly' ),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),
                new Assert\Regex(
                    array(
                        'pattern' => ($country_id == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                        'match' => true,
                        'message' => 'Invalid Iqama Id/SSN Number'.$country_id)
                ),)
        ))
        ->add('fullname', TextType::class, array('label' => 'Full Name',
            'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
            'attr' =>array('class' => 'form-control formLayout'),
            'constraints' => array(
             new Assert\NotBlank(array('message' => 'This field is required')))))

            ->add('token', HiddenType::class, array(
                'mapped'   => false,
                'required' => false,
            ))

        ->add('comment_fullname', TextareaType::class, array('label' => 'Comments',
            'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
              
            'attr' =>array('class' => 'form-control formLayout','maxlength' => 255),
             'constraints' => array(
             new Assert\NotBlank(array('message' => 'This field is required')),
             
                 )

        ))
        ->add('Update', SubmitType::class ,array(
             'attr' => array('class' => 'btn btn-primary'),
        ));
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