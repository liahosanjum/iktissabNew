<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 2/14/17
 * Time: 10:30 AM
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

class IktUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $lookupData = $options['additional'];
        $builder->add('dob', DateType::class, array(
//                'widget' => 'single_text',

                'years' => range(date('Y') - 5, date('Y') - 77),
                'label' => 'Birthdate',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ),
                'attr' =>array('class' => '' ),


            ))
            ->add('dob_h', DateType::class, array(
                'years' => range($this->getCurrentHijYear() -5 ,$this->getCurrentHijYear() -77),
                'widget' => 'choice',
                'label' => 'Birthdate',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ),
                'attr' =>array('class' => '' ),


            ))
            ->add('maritial_status', ChoiceType::class, array(
                'label' => 'Marital Status',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'attr' =>array('class' => 'form-control formLayout' ),
                'choices' => array('Single' => 'S', 'Married' => 'M', 'Widow' => 'W', 'Divorce' => 'D'),

            ))
            ->add('job_no', ChoiceType::class, array(
                'choices' => $lookupData['jobs'],
                'label' => 'Job',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'attr' =>array('class' => 'form-control formLayout' ),

            ))
            ->add('city_no', ChoiceType::class, array(
                'choices' => $lookupData['cities'],
                'label' => 'City',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'attr' =>array('class' => 'form-control formLayout' ),
                'placeholder' => 'Select City',

            ))
            ->add('area_no', ChoiceType::class, array(
                'choices' => $lookupData['areas'],
                'label' => 'Area',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'attr' =>array('class' => 'form-control formLayout' ),
                'placeholder' => 'Select Area',
                'attr' =>array('class' => 'form-control formLayout' ),

            ))
            ->add('area_text', TextType::class, array(
                'label' => 'Area',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'attr' =>array('class' => 'form-control formLayout' ),

            ))
            ->add('language', ChoiceType::class, array(
                    'label' => 'Preffered Language',
                    'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                    'attr' =>array('class' => 'form-control formLayout' ),
                    'choices' => array('Select Language' => '', 'Arabic' => 'A', 'English' => 'E'),

                )
            )
            ->add('pur_group', ChoiceType::class, array(
                'label' => 'Shoppers',
                'label_attr' => ['class' => 'formLayout col-lg-12 col-md-12 col-sm-12 col-xs-12   form_labels'],
                'placeholder' => 'Select Shopper',
                'choices' => array('Husband' => '1', 'Wife' => '2', 'Children' => '3', 'Relative' => '4', 'Applicant' => '5', 'Servent' => '6'),
                'attr' =>array('class' => 'form-control formLayout' ),

            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Update'

            ))
            ->add('date_type', HiddenType::class)
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