<?php

namespace AppBundle\Form;


use AppBundle\Entity\EnquiryAndSuggestion;
use AppBundle\Entity\FormSettings;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;



class EnquiryAndSuggestionType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $options['extras']['country'];



        $builder ->add('name', TextType::class, array('label' => 'Full name',
            'label_attr' => ['class' => 'required inq-form  formLayout form_labels'],
            'attr' => array('class' => 'col-lg-8  form-control formLayout'),
            'constraints' => array(
                new Assert\NotBlank(array('message' =>  'This field is required'))

            )
        ))

            ->add('job', TextType::class, array('label' => 'Job' ,
                'label_attr' => ['class' => 'formLayout inq-form inq_form_job form_labels'],
                'attr' => array('class' => 'col-lg-4 inq_form_job form-control-modified  formLayout'),

            ))


            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'label_attr' => ['class' => 'inq-form formControl-ext formLayout    form_labels'],
                'attr' => array('class' => 'formControl form-control formLayout     form_labels' , 'maxlength'=> ($country == 'sa') ? 9 : 11),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Regex(
                        array(
                            'pattern' => ($country == 'sa') ? '/^[5]([0-9]){8}$/' : '/^[0]([0-9]){10}$/',
                            'match' => true,
                            'message' =>  ($country == 'sa') ? "Please enter 9 digits mobile number starting with 5" : "Please enter 11 digits mobile number starting with 0")
                    ),)))



            ->add('email', EmailType::class, array('label' => 'Email' ,
                'label_attr' => ['class' => 'required inq-form formLayout form_labels'],
                'attr' => array('class' => 'col-lg-8 form-control formLayout'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Email(array('message' => 'Invalid email address'))
                )))


            ->add('reason', ChoiceType::class, array('label'=>"Reason",
                'label_attr' => ['class' => 'required formLayout inq-form inq_form_reason form_labels'],
                'attr' => array('class' => 'col-lg-4 inq_form_reason form-control-modified  formLayout'),

                "choices"=>array(
                    "Complaint" => EnquiryAndSuggestion::COMPLAINT,
                    "Enquiry"=> EnquiryAndSuggestion::ENQUIRY,
                    "Suggestion"=>EnquiryAndSuggestion::SUGGESTION,
                    "Technical Support"=>EnquiryAndSuggestion::TECHNICAL_SUPPORT
                )))


            ->add('comments', TextareaType::class, array('label'=>"Comments",
                'label_attr' => ['class' => 'required formLayout inq-form form_labels'],
                'attr' => array('class' => 'col-lg-8 form-control formLayout'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )


            ))

            ->add('captchaCode', CaptchaType::class, array(
                'label_attr' => ['class' => 'required formLayout inq-form-captcha form_labels'],
                'label' => 'Captcha', 'captchaConfig' => 'FormCaptcha',
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new ValidCaptcha(array("message"=>"Invalid captcha code"))),


            ));

        //->add('country', TextType::class, array('label'=>"Country"))
       

        $builder->add('source', HiddenType::class, array('label' => 'Source' ,
            'attr' =>array('value' => 'W'),))





            ->add('submit', SubmitType::class, array('label'=>"Submit" , 'attr' => array('class' => 'btn btn-primary')));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EnquiryAndSuggestion',
            'attr' => array('novalidate' => 'novalidate'),
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
