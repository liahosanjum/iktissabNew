<?php

namespace AppBundle\Form;


use AppBundle\Entity\EnquiryAndSuggestion;
use AppBundle\Entity\FormSettings;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;


class EnquiryAndSuggestionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $options['extras']['country'];

        $builder ->add('name', TextType::class, array('label' => 'Full name'))
            ->add('job', TextType::class, array('label' => 'Job'))

            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',

                'attr' => array('maxlength'=> ($country == 'sa') ? 10 : 14   )  ,
                'constraints' => array(

                    new NotBlank(array('message' => 'This field is required')),
                    new Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^([0-9]){10}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($country == 'sa' ? '10' : '14' )." digits")
                    ),)))

            ->add('email', EmailType::class, array('label' => 'Email' ,
                    'constraints' => array(
                        new NotBlank(array('message' => 'Email is required')),
                        new Email(array('message' => 'Invalid email'))
                    )))
            ->add('reason', ChoiceType::class, array('label'=>"Reason",


                "choices"=>array(
                        "Complaint" => EnquiryAndSuggestion::COMPLAINT,
                        "Enquiry"=> EnquiryAndSuggestion::ENQUIRY,
                        "Suggestion"=>EnquiryAndSuggestion::SUGGESTION,
                        "Technical Support"=>EnquiryAndSuggestion::TECHNICAL_SUPPORT
            )))
            ->add('comments', TextareaType::class, array('label'=>"Comments",
                'constraints' => array(
                    new NotBlank(array('message' => 'This field is required')),
                )

            ))
            //->add('country', TextType::class, array('label'=>"Country"))
            ->add('captchaCode', CaptchaType::class, array(

                'label' => 'Captcha', 'captchaConfig' => 'FormCaptcha',
                'constraints' => array(
                    new NotBlank(array('message' => 'Email is required'))),
                ))

            ->add('source', HiddenType::class, array('label' => 'Source' ,
                    'attr' =>array('value' => 'W'),))





            ->add('submit', SubmitType::class, array('label'=>"Submit"));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EnquiryAndSuggestion',
            'attr' => array('novalidate' => 'novalidate')
        ));
        $resolver->setRequired('extras');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_enquiryandsuggestion';
    }


}
