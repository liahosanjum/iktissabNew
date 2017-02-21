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
                'attr' => array('maxlength'=> ($country == 'sa') ? 10 : 14)))
            ->add('email', TextType::class, array('label' => 'Email'))
            ->add('reason', ChoiceType::class, array('label'=>"Reason", "choices"=>array(
                        "Complaint" => EnquiryAndSuggestion::COMPLAINT,
                        "Enquiry"=> EnquiryAndSuggestion::ENQUIRY,
                        "Suggestion"=>EnquiryAndSuggestion::SUGGESTION,
                        "Technical Support"=>EnquiryAndSuggestion::TECHNICAL_SUPPORT
            )))
            ->add('comments', TextareaType::class, array('label'=>"Comments"))
            //->add('country', TextType::class, array('label'=>"Country"))
            ->add('captchaCode', CaptchaType::class, array('label' => 'Captcha', 'captchaConfig' => 'FormCaptcha'))
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
