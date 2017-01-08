<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/27/16
 * Time: 1:40 PM
 */

namespace AppBundle\Twig;

use AppBundle\AppConstant;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserlangExtension extends \Twig_Extension
{
    private $containerInterface;
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('iktlang', array($this, 'iktLangFormat') )
        );
    }

    public function iktLangFormat($lang)
    {
        $locale = $this->containerInterface->get('request_stack')->getCurrentRequest()->getLocale();
        if($locale == 'en'){
            if($lang == 'E'){
                return AppConstant::IKT_USER_LremANG_EN_EN;
            }elseif($lang == 'A'){
                return AppConstant::IKT_USER_LANG_EN_AR;
            }
        }else{
            if($lang == 'E'){
                return AppConstant::IKT_USER_LANG_AR_EN;
            }elseif($lang == 'A'){
                return AppConstant::IKT_USER_LANG_AR_AR;
            }
        }

    }

    public function getName()
    {
        return 'iktlang_extension';
    }
}