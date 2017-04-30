<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UpdateEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $email_current   = $options['extras']['email'];



        $builder->add('currentemail', EmailType::class, array(
            'label' => 'Current Email',
            'label_attr' => ['class' => 'formLayout col-lg-8 col-md-8 col-sm-8 col-xs-13   form_labels'],
            'attr' =>array('value' => $email_current , 'readonly' => 'readonly', 'class' => 'form-control  col-lg-8 col-md-8 col-sm-8 col-xs-13 required'),
            'constraints' => array(
                new Assert\NotBlank(array('message' => 'This field is required')),)))
            ->add('newemail', RepeatedType::class, [
                'type' => TextType::class,
                'invalid_message' => 'New email and confirm email fields must match',
                'required' => true,
                'first_options' => array('attr' =>array('class' => 'form-control formLayout'), 'label' => 'New Email', 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels' ]),
                'second_options' => array('attr' =>array('class' => 'form-control formLayout'), 'label' => 'Confirm New Email', 'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels']),
                'options' => array('attr' => array('class' => 'form-control')),
                'constraints' => array (
                    new NotBlank(array('message' =>  'This field is required')),
                )
            ])



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