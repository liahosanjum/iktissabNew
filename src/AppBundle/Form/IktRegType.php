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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class IktRegType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $lookupData = $options['additional'];
        $mobileVal =
        $builder->add('iktCardNo', IntegerType::class, array('label' => 'Iktissab ID', 'disabled' => true,
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
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('email', EmailType::class, array('label' => 'Email', 'disabled' => true,
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match',
                    'required' => true,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat password'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('gender', ChoiceType::class, array(
                    'label' => 'Gender',
                    'choices' => array('Gender' => '', 'Male' => 'M', 'Female' => 'F'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )

            )
            ->add('nationality', EntityType::class, array(
                    'class' => 'AppBundle\Entity\Nationality',
                    'choice_label' => ($lookupData['locale'] == 'en') ? 'adesc' : 'edesc',
                    'label' => 'Nationality',
                    'empty_data' => null,
                    'placeholder' => 'Select Nationality',
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('dob', DateType::class, array(
//                'widget' => 'single_text',
                'years' => range(date('Y') - 5, date('Y') - 77),
                'label' => 'Birthdate',
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )

            ))
            ->add('maritial_status', ChoiceType::class, array(
                'label' => 'Marital Status',
                'choices' => array('Single' => 'S', 'Married' => 'M', 'Widow' => 'W', 'Divorce' => 'D'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('iqama', TextType::class, array(
                'label' => 'Iqama/SSN Number',
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
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('city_no', ChoiceType::class, array(
                'choices' => $lookupData['cities'],
                'label' => 'City',
                'placeholder' => 'Select Country',
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('area_no', ChoiceType::class, array(
                'choices' => $lookupData['areas'],
                'label' => 'Area',
                'placeholder' => 'Select Area'
            ))
            ->add('language', ChoiceType::class, array(
                    'label' => 'Preffered Language',
                    'choices' => array('Select Language' => '', 'Arabic' => 'A', 'English' => 'E'),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'This field is required')),
                    )
                )
            )
            ->add('street', TextType::class, array('label' => 'Street'))
            ->add('houseno', TextType::class, array('label' => 'House Number'))
            ->add('pobox', TextType::class, array('label' => 'PO Box'))
            ->add('zip', TextType::class, array('label' => 'Zip Code'))
            ->add('tel_office', TextType::class, array('label' => 'Telephone (Office)'))
            ->add('tel_home', TextType::class, array('label' => 'Telephone (Home)'))
            ->add('mobile', TextType::class, array(
                'label' => 'Mobile',
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                    new Assert\Regex(
                        array(
                            'pattern' => ($lookupData['country'] == 'sa') ? '/^[5]([0-9]){8}$/' : '/^([0-9]){14}$/',
                            'match' => true,
                            'message' => "Mobile Number Must be ".($lookupData['country'] == 'sa' ? '9' : '14' )." digits")
                    ),

                )
            ))
            ->add('pur_group', ChoiceType::class, array(
                'label' => 'Shoppers',
                'placeholder' => 'Select Shopper',
                'choices' => array('Husband' => '1', 'Wife' => '2', 'Children' => '3', 'Relative' => '4', 'Applicant' => '5', 'Servent' => '6'),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'This field is required')),
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Next step'
            ));

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

}