<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 2/21/17
 * Time: 8:24 AM
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ForgotEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $lookupData = $options['additional'];
        $builder->add('iktCardNo', IntegerType::class, array('label' => 'Iktissab ID',
            'attr' => array('maxlength' => 8),
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
            ->add('iqama', IntegerType::class, array(
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
            ->add('submit', SubmitType::class, array(
                'label' => 'Submit'
            ))
        ;

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
}