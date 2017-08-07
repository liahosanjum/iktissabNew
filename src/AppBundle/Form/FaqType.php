<?php

namespace AppBundle\Form;


// use AppBundle\Entity\EnquiryAndSuggestion;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;


class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $options['extras']['country'];
        $builder->add('email', TextType::class, array('label' => 'Email',
            'label_attr' => ['class' => 'required formLayout form_labels'],
            'attr' => array('class' => 'col-lg-8 form-control formLayout'),
                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
                    new Email(array("message"    => 'Invalid email address'))
            )))
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'label_attr' => ['class' => 'required formLayout  form_labels'],
                'attr' => array('class' => 'form-control formLayout' ,'maxlength' => ($country == 'sa') ? 10 : 14),
                'constraints' => array (
                    new NotBlank(array('message' =>  'This field is required')),
                    new Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^([0-9]){10}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($country == 'sa' ? '10' : '14' )." digits")
                    )
                 )
            ))

            ->add('question', TextareaType::class, array('label' => 'Ask your Question',
                'label_attr' => ['class' => 'required formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12  form_labels'],
                'attr' => array('class' => 'form-control formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12'),
                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
                )))
            ->add('captchaCode', CaptchaType::class, array('label' => 'Captcha', 'captchaConfig' => 'FormCaptcha'))
            ->add('submit', SubmitType::class, array('label'=>"Submit", 'attr' => array('class' => 'btn btn-primary')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

            'attr' => array('novalidate' => 'novalidate')
        ));
        $resolver->setRequired('extras');
    }




}
