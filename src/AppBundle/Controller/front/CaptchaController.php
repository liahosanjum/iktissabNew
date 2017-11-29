<?php

namespace AppBundle\Controller\Front;



use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;


use Symfony\Component\HttpFoundation\Response;


class CaptchaController extends Controller
{

    /**
     * @Route("/{_country}/{_locale}/test", name="test")
     * @param Request $request
     */

    public function testAction(Request $request)
    {
        //$response->headers->set('Cache-Control', 'private');
        //$response->headers->set('Content-type', mime_content_type($filename));
        //$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        //$response->headers->set('Content-length', filesize($filename));
        // Send headers before outputting anything
        //$response->sendHeaders();
        $config       = array();
        //$commFunction = new FunctionsController();
        //$filename     = $commFunction->saveTextAsImage($config);
        $filename   = $this->saveTextAsImage($config);
        $response     = new Response();
        $file_name    = $response->setContent($filename);
        // $response;
        return $this->render('front/test.html.twig', array('message' => $this->get('translator')->trans('No record found'), 'data' => $file_name));
    }

    /**
     * Method convert given text to PNG image and returs
     * file name
     * @param type $text Text
     * @return string File Name
     */
    public function saveTextAsImage($config = array()) {
        // Create the image
        $config = array();
        $bg_path   = $this->get('kernel')->getRootDir()  . '/../web/backgrounds/';
        $font_path = $this->get('kernel')->getRootDir()  . '/../web/fonts/captcha-fonts/';
        // Default values
        $captcha_config = array(
            'code'        => '',
            'min_length'  => 4,
            'max_length'  => 4,
            'backgrounds' => array(
                $bg_path . '45-degree-fabric.png',
                $bg_path . 'cloth-alike.png',
                $bg_path . 'grey-sandbag.png',
                $bg_path . 'kinda-jean.png',
                $bg_path . 'polyester-lite.png',
                $bg_path . 'stitched-wool.png',
                $bg_path . 'white-carbon.png',
                $bg_path . 'white-wave.png'
            ),
            'fonts'     => array(
                $font_path . 'times_new_yorker.ttf'
            ),
            'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 28,
            'max_font_size' => 28,
            'color' => '#666',
            'angle_min' => 0,
            'angle_max' => 10,
            'shadow' => true,
            'shadow_color' => '#fff',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => 1
        );

        // Overwrite defaults with custom config values
        if( is_array($config) ) {
            foreach( $config as $key => $value ) $captcha_config[$key] = $value;
        }

        // Restrict certain values
        if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
        if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
        if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
        if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
        if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
        if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];

        // Generate CAPTCHA code if not set by user
        if( empty($captcha_config['code']) ) {
            $captcha_config['code'] = '';
            $length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
            while( strlen($captcha_config['code']) < $length ) {
                $captcha_config['code'] .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
            }
        }
        $imageCreator = imagecreatetruecolor(100, 30);
        // Create some colors
        $white        = imagecolorallocate($imageCreator, 255, 255, 255);
        $grey         = imagecolorallocate($imageCreator, 128, 128, 128);
        $black        = imagecolorallocate($imageCreator, 0, 0, 0);
        imagefilledrectangle($imageCreator, 0, 0, 399, 29, $white);
        $this->get('session')->set('_CAPTCA', $captcha_config['code']);
        $this->get('kernel')->getRootDir() . '/../web';
        $font = $this->get('kernel')->getRootDir() . '/../web/fonts/times_new_yorker.ttf'; //'arial.ttf';
        // Add some shadow to the text
        imagettftext($imageCreator, 20, 0, 11, 21, $grey, $font, $captcha_config['code']);
        // Add the text
        imagettftext($imageCreator, 20, 0, 10, 20, $black, $font, $captcha_config['code']);
        // Using imagepng() results in clearer text compared with imagejpeg()
        $file_name = $this->get('kernel')->getRootDir() .'/../web/img/ikt22.png';
        imagepng($imageCreator, $file_name);
        imagedestroy($imageCreator);
        return $file_name;
    }





}






