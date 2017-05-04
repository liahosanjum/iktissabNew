<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 1/1/17
 * Time: 2:49 PM
 */

namespace AppBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class IktRegType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $lookupData = $options['additional'];
        // change made by sohail from TextType to hiddenType for iktCardNo
        $builder->add('iktCardNo', HiddenType::class, array('label' => 'Iktissab Card Id Activation', 'disabled' => true,
            'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
            'attr' =>array('maxlength'=>8 , 'class' => 'col-lg-8 form-control formLayout',),
            'constraints' => array(

                new Assert\NotBlank(array('message' => 'Iktissab id  is required')),
                new Assert\Regex(
                    array(
                        'pattern' => '/^[9,5]([0-9]){7}$/',
                        'match' => true,
                        'message' => 'Invalid Iktissab Card Number')
                )
            )
        ))

            ->add('fullName', TextType::class, array('label' => 'Full name',
                    'label_attr' => ['class' => 'formLayout    form_labels'],
                    'attr' =>array('maxlength' => 100,'class' => 'col-lg-8 form-control formLayout'),

                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                        new Assert\Length(array('min' => 5, 'max'=>100, 'minMessage'=> "Length is too small", 'maxMessage' => "Length is too big"))
                    )
                )
            )
















            ->add('email', RepeatedType::class, [
                'type' => TextType::class,
                'invalid_message' => 'New email and confirm email fields must match',
                'required' => true,
                'first_options' => array('attr' =>array('class' => 'form-control formLayout form_labels'),
                    'label' => 'New Email', 'label_attr' => ['class' => 'required formLayout   form_labels' ]),
                'second_options' => array('attr' =>array('class' => 'form-control formLayout form_labels'), 'label' => 'Confirm New Email', 'label_attr' => ['class' => 'required formLayout   form_labels']),
                'options' => array('attr' => array('class' => 'form-control')),
                'constraints' => array (
                    new NotBlank(array('message' =>  'This field is required')),
                )
            ])
            ->add('password', RepeatedType::class, array(
                    'label_attr' => ['class' => 'formLayout col-lg-6 col-md-6 col-sm-6 col-xs-12   form_labels required'],
                    'attr' =>array('class' => 'form-control formLayout'),

                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match',
                    'required' => true,
                    'first_options' => array('label' => 'Password','label_attr' => ['class' => 'required formLayout    form_labels'], 'attr' =>array('class' => 'form-control   formLayout form_labels')),
                    'second_options' => array('label' => 'Repeat password','label_attr' => ['class' => ' required formLayout    form_labels'],'attr' => array('class' => 'form-control  formLayout form_labels')),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                        new Assert\Length(array('min'=> 6, 'minMessage'=> 'Password must be greater then 6 characters'))
                    )
                )
            )
            ->add('gender', ChoiceType::class, array(
                    'label' => 'Gender',
                    'label_attr' => ['class' => 'formLayout    form_labels'],
                    'attr' =>array('class' => 'form-control-modified col-lg-10  col-md-10  col-sm-10 formLayout'),
                    'choices' => array('Gender' => '', 'Male' => 'M', 'Female' => 'F'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )

            )
            ->add('nationality', EntityType::class, array(
                    'class' => 'AppBundle\Entity\Nationality',
                    'choice_label' => ($lookupData['locale'] == 'en') ? 'adesc' : 'edesc',
                    'label' => 'Nationality','attr' =>array('class' => 'form-control-modified col-lg-10  col-md-10  col-sm-10 formLayout'),
                    'label_attr' => ['class' => 'formLayout    form_labels'],
                    'empty_data' => null,
                    'placeholder' => 'Select Nationality',
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('dob', DateType::class, array(
//                'widget' => 'single_text',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels dob_label'],
                'years' => range(date('Y') - 5, date('Y') - 77),
                'label' => 'Birthdate',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels dob_label required'],
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )

            ))
            ->add('dob_h', DateType::class, array(
                'years' => range($this->getCurrentHijYear() -5 ,$this->getCurrentHijYear() -77),
                'widget' => 'choice',
                'label' => 'Birthdate',

                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels dob_label'],
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )

            ))
            ->add('maritial_status', ChoiceType::class, array(
                'label' => 'Marital Status',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels '],

                'choices' => array('Single' => 'S', 'Married' => 'M', 'Widow' => 'W', 'Divorce' => 'D'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('iqama', TextType::class, array(
                'label' => 'Iqama/SSN Number',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'attr' =>array('maxlength' => ($lookupData['country'] == 'sa') ? 10 : 15),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($lookupData['country'] == 'sa') ? '/^[1,2]([0-9]){9}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => 'Invalid Iqama/SSN Number')
                    ),
//                    new Assert\Callback([
//                        'callback' => [$this, 'validateIqama']
//                    ])
                )
            ))
            ->add('job_no', ChoiceType::class, array(
                'choices' => $lookupData['jobs'],
                'label' => 'Job',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('city_no', ChoiceType::class, array(
                'choices' => $lookupData['cities'],
                'label' => 'City',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'placeholder' => 'Select City',
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('area_no', ChoiceType::class, array(
                'choices' => $lookupData['areas'],
                'label' => 'Area',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'placeholder' => 'Select Area',
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('area_text', TextType::class, array(
                'label' => 'Area',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'attr' =>array('maxlength'=>50),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('language', ChoiceType::class, array(
                    'label' => 'Preffered Language',
                    'label_attr' => ['class' => 'formLayout    form_labels'],
                    'choices' => array('Select Language' => '', 'Arabic' => 'A', 'English' => 'E'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'attr' => array('maxlength'=> ($lookupData['country'] == 'sa') ? 9 : 11),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($lookupData['country'] == 'sa') ? '/^[5]([0-9]){8}$/' : '/^([0-9]){10}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($lookupData['country'] == 'sa' ? '9' : '14' )." digits")
                    ),

                )
            ))
            ->add('pur_group', ChoiceType::class, array(
                'label' => 'Shoppers',
                'label_attr' => ['class' => 'formLayout    form_labels'],
                'placeholder' => 'Select Shopper',
                'choices' => array('Husband' => '1', 'Wife' => '2', 'Children' => '3', 'Relative' => '4', 'Applicant' => '5', 'Servent' => '6'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('submit', SubmitType::class, array( 'attr' => array('class' => 'btn btn-primary'),
                'label' => 'Next step'
            ))
            ->add('date_type', HiddenType::class, array(
                'data' => 'g'
            ))
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'novalidate' => 'novalidate',
                'var' => null
            ),
        ));
        $resolver->setRequired('additional'); // Requires that currentOrg be set by the caller.
        $resolver->setAllowedTypes('additional', 'array'); // Validates the type(s) of option(s) passed.
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'country', 'locale'
        ));

    }

    /**
     * @Assert\Callback
     */
    public function validateIqama($iqama, ExecutionContextInterface $context)
    {
        $evenSum = 0;
        $oddSum = 0;
        $entireSum = 0;
        for ($i = 0; $i < strlen($iqama); $i++) {
            $temp = '';
            if ($i % 2) { // odd number

                $oddSum = $oddSum + $iqama[$i];

            } else {
                //even
                $multE = $iqama[$i] * 2;
                if (strlen($multE) > 1) {
                    $temp = (string)$multE;
                    $evenSum = $evenSum + ($temp[0] + $temp[1]);
                } else {
                    $evenSum = $evenSum + $multE;
                }
            }
        }
        $entireSum = $evenSum + $oddSum;
        if (($entireSum % 10) == 0) {
            // valid
        } else {
            $context->buildViolation('Iqama Number is invalid')
                ->atPath('iqama')
                ->addViolation();
        }


    }
    public function getCurrentHijYear(){
        $reference_year = array('gyear'=>2017, 'hyear'=>1438);
        $current_year = date('Y');
        $islamicYear = ($current_year - $reference_year['gyear']) + $reference_year['hyear'];
        return $islamicYear;
    }

}