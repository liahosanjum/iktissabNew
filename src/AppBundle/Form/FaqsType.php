<?php

namespace AppBundle\Form;


use AppBundle\Entity\EnquiryAndSuggestion;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;


class FaqsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = 'sa';
        $builder ->add('email', TextType::class, array('label' => 'Full name'))
            ->add('email', TextType::class, array('label' => 'Email',

                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
                    new Email(array("message"=> 'Invalid email address'))
                )))


            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'attr' => array('maxlength'=> ($country == 'sa') ? 10 : 14)))
            ->add('question', TextType::class, array('label' => 'Ask your Question',
                'constraints' => array(
                    new NotBlank(array('message' =>  'This field is required')),
                )))


            ->add('captchaCode', CaptchaType::class, array('label' => 'Captcha', 'captchaConfig' => 'FormCaptcha'))



            ->add('submit', SubmitType::class, array('label'=>"Submit"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

            'attr' => array('novalidate' => 'novalidate')
        ));
    }




}
