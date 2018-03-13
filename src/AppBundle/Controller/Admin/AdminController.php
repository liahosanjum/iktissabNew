<?php
namespace AppBundle\Controller\Admin;

use AppBundle\AppConstant;
use AppBundle\Controller\Common\FunctionsController;
use AppBundle\Entity\FormSetting;
use AppBundle\Entity\Gallery;
use AppBundle\Entity\EmailSetting;
use AppBundle\Entity\Resources;
use AppBundle\Form\ResourcesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Form\CmsPagesType;
use AppBundle\Form\GalleryType;

use AppBundle\Form\EmailSettingType;
use AppBundle\Form\FormSettingType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\CmsPages;




class AdminController extends Controller
{

    private $pubkey = 'abc123';
    private $privkey = 'xyz123';

    /**
     * @Route("/admin/admin" , name= "admin_admin")
     */
    public function adminAction()
    {
        // url = /admin/index
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
           return $this->redirectToRoute('newslistall');
           // cmslistall link is made hidden due to security reason
           //return $this->redirectToRoute('cmslistall');
        }
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render(':admin:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error
        ));
    }

    /**
     * @Route("/admin/cmslist" , name = "admin_home")
     */
    public function cmsListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        else
        {
            //return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_CMS_ADD')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $cmsPage = new CmsPages();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
        // print_r($form);
        $form->handleRequest($request);
        $request->request->get('adesc');
        $cmsData = $form->getData();
        // get csrf token
        // $token = $request->get($form->getName())['_token'];
        $form->get('token')->getData();
        //$this->checkCsrfToken($form->get('token')->getData() , 'admin_cms_list');

            if ($form->isSubmitted())
            {
                  if($form->isValid()) {
                      if($funcController->checkCsrfToken($form->get('token')->getData() , 'admin_cms_list' ))
                      {
                          $funcController->setCsrfToken('admin_cms_list');
                          $session = new Session();
                          $token   = $session->get('admin_cms_list');

                          $title = strip_tags(trim($form->get('page_title')->getData()));
                          $language = strip_tags(trim($form->get('language')->getData()));
                          $url = strip_tags(trim($form->get('url_path')->getData()));
                          $type = 'cms';
                          $rec_exits = $this->chkRcd($title , $language , $url , $type);
                          if($rec_exits == false)
                          {
                              $cmsPage->setStatus(strip_tags(trim($form->get('language')->getData())));
                              $cmsPage->setpageTitle(strip_tags(trim($form->get('page_title')->getData())));
                              $content_data = strip_tags(trim($form->get('page_content')->getData()));
                              $page_content_striped = $this->strip_tags_content($content_data, '<script>', TRUE);
                              $cmsPage->setpageContent($page_content_striped);
                              $cmsPage->seturlPath(strip_tags(trim($form->get('url_path')->getData())));
                              $cmsPage->setStatus(strip_tags(trim($form->get('status')->getData())));
                              $cmsPage->setStatus(strip_tags(trim($form->get('country')->getData())));
                              $cmsPage->setType('cms');
                              $em = $this->getDoctrine()->getManager();
                              $em->persist($cmsPage);
                              $em->flush();
                              if ($cmsPage->getId())
                              {

                                  $message = $this->get('translator')->trans('Record Added successfully');
                                  return $this->render('admin/cms/cms.html.twig', array(
                                      'form' => $form->createView(), 'message' => $message, 'token' => $token,
                                      'error_cl' => 'alert-success'
                                  ));

                              }
                          }
                          else
                          {
                              $message = $this->get('translator')->trans('Url identifier already exist');
                              return $this->render('admin/cms/cms.html.twig', array(
                                  'form' => $form->createView(), 'message' => $message,
                                  'error_cl' => 'alert-danger', 'token' => $token,
                              ));
                          }
                      }
                      else
                      {
                          $this->get('security.token_storage')->setToken(null);
                          $response = new RedirectResponse($this->generateUrl('admin_admin'));
                          return $response;
                      }
                  }
                  else
                  {
                      $funcController->setCsrfToken('admin_cms_list');
                      $session = new Session();
                      $token   = $session->get('admin_cms_list');

                      return $this->render('admin/cms/cms.html.twig', array(
                          'form' => $form->createView(), 'message' => '' ,
                          'error_cl' => 'alert-success', 'token' => $token,
                      ));
                  }
            }
            else
            {
                $funcController->setCsrfToken('admin_cms_list');
                $session = new Session();
                $token   = $session->get('admin_cms_list');
                return $this->render('admin/cms/cms.html.twig', array(
                    'form' => $form->createView(), 'message' => "" ,
                    'error_cl' => 'alert-success', 'token' => $token,

                ));
            }
    }

    public function strip_tags_content($text, $tags = '', $invert = FALSE) {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if(is_array($tags) AND count($tags) > 0) {
            if($invert == FALSE) {
                return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            }
            else {
                return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
            }
        }
        elseif($invert == FALSE) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return $text;
    }

    /**
     * @Route("/admin/newslist" , name = "admin_news")
     */
    public function newsListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        if(!$funcController->isValidRule('MANAGE_NEWS_ADD'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }


        $cmsPage = new CmsPages();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
        // print_r($form);
        $form->handleRequest($request);
        $request->request->get('adesc');
        $cmsData = $form->getData();
        $form->get('token')->getData();
        if ($form->isSubmitted())
        {

            if ($form->isValid())
            {
                if($funcController->checkCsrfToken($form->get('token')->getData() , 'admin_news_list' ))
                {
                    $file = $cmsPage->getBrochure();
                    // Generate a unique name for the file before saving it
                    // testing files
                    $file = "";
                    if(!empty($file))
                    {
                        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                        // Move the file to the directory where brochures are stored
                        $file->move(
                            $this->getParameter('images_directory'),
                            $fileName
                        );

                        // Update the 'brochure' property to store the PDF file name
                        // instead of its contents
                        $cmsPage->setBrochure($fileName);
                    }
                    $cmsPage->setpageTitle(strip_tags(trim($form->get('page_title')->getData())));

                    $content_data = strip_tags(trim($form->get('page_content')->getData()));
                    $page_content_striped_edit = $this->strip_tags_content($content_data, '<script>', TRUE);
                    $cmsPage->setpageContent($page_content_striped_edit);

                    $cmsPage->seturlPath('--');

                    //$form->get('url_path')->setData(' ');
                    $cmsPage->setStatus(strip_tags(trim($form->get('status')->getData())));
                    $cmsPage->setLanguage(strip_tags(trim($form->get('language')->getData())));
                    $cmsPage->setCountry(strip_tags(trim($form->get('country')->getData())));
                    $cmsPage->setType('news');
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($cmsPage);
                    $em->flush();
                    if ($cmsPage->getId())
                    {
                        $funcController->setCsrfToken('admin_news_list');
                        $session = new Session();
                        $token = $session->get('admin_news_list');
                        $message = $this->get('translator')->trans('Record Added successfully');
                        return $this->render('admin/news/news.html.twig', array(
                            'form' => $form->createView(),'message' => $message,
                            'error_cl' => 'alert-success', 'token' => $token,
                        ));

                    }
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                }
                //m0na0hupzZJl3vDX49gDDOSqALSUfFaxcqmCO1gpLcc

            }
            else
            {
                $funcController->setCsrfToken('admin_news_list');
                $session = new Session();
                echo $token = $session->get('admin_news_list');
                return $this->render('admin/news/news.html.twig', array(
                    'form' => $form->createView(),'message' => $this->get('translator')->trans('Unable to add record'),
                    'error_cl' => 'alert-success', 'token' => $token,
                ));
            }
            //return new Response('Content page added');
        }
        else
        {
            $funcController->setCsrfToken('admin_news_list');
            $session = new Session();
            $token = $session->get('admin_news_list');

            $form->get('url_path')->setData('--');
            $form->get('type')->setData('news');

            return $this->render('admin/news/news.html.twig', array(
                'form' => $form->createView(),'message' => "",
                'error_cl' => 'alert-success', 'token' => $token,
            ));
        }
    }




    /**
     * @Route("/admin/cmslistall/", name="cmslistall")
     */
    public function cmsListAllAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        else
        {
            //return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        if(!$funcController->isValidRule('MANAGE_CMS_VIEW'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        //our data to be encoded
        $em = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findBy(array( 'type' => 'cms'));
            //->findAll();
        $data = array();
        $i = 0;
        foreach($cmspages as $cmspage)
        {
            // $password_encrypted = $this->Eencrypt($cmspage->getId(), $key);
            $data[$i]['id']        =  $funcController->EncDec( $cmspages[$i]->getId(), 'e' );
            //$data[$i]['id']      =  $cmspage->getId();
            $data[$i]['cid']       =  '123';
            //$data[$i]['id']      =  $password_encrypted ;

            $data[$i]['title'] =  $cmspage->getpageTitle();
            $data[$i]['Status'] =  $cmspage->getStatus();
            $i++;
        }

        if (!$cmspages)
        {
            return $this->render('admin/cms/cmslistall.html.twig', array(
                'data' => $data , 'message' => 'No record found' ));
        }
        else
        {
            return $this->render('admin/cms/cmslistall.html.twig', array(
                'data' => $data ,  'message' => ''   ));
        }
    }


    /**
     * @Route("/admin/newslistall/", name="newslistall")
     */
    public function newsListAllAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('admin_admin');
        }

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        if(!$funcController->isValidRule('MANAGE_NEWS'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $em = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findBy(array( 'type' => 'news'));
        $data = array();
        $i = 0;
        $page = $funcController->EncDec( $request->get('param'), 'd' );
        foreach($cmspages as $cmspage)
        {
            $cmspage;
            $encrypted =

            //$data[$i]['id']     =  $cmspages[$i]->getId();
            $data[$i]['id']     =  $funcController->EncDec( $cmspages[$i]->getId(), 'e' );
            $data[$i]['cid']    =  '123';
            $data[$i]['Atitle'] =  $cmspages[$i]->getpageTitle();
            $data[$i]['Status'] =  $cmspages[$i]->getStatus();
            $i++;
        }

        if (!$cmspages)
        {
            return $this->render('admin/news/newslistall.html.twig', array(
                'data' => $data , 'message' => 'No record found' ));
        }
        else
        {
            return $this->render('admin/news/newslistall.html.twig', array(
                'data' => $data ,  'message' => ''   ));
        }
    }





    /**
     * @Route("/admin/cmslistupdate", name="cmslistupdate")
     *
     */

    public function cmsListUpdateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        else
        {
            //return $this->redirectToRoute('admin_admin');
        }
        $em = $this->getDoctrine()->getManager();
        //$cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        $page = $funcController->EncDec( $request->get('param'), 'd' );
        if(!$funcController->isValidRule('MANAGE_CMS_EDIT'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }


        $cmsPage = $em->getRepository('AppBundle:CmsPages')
        ->findOneBy(array( 'type' => 'cms' , 'id' => $page));
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
        if(empty($cmsPage))
        {
            return $this->render('admin/cms/cmsedit.html.twig', array(
                'form' => $form->createView(),'message' => 'No data found',
                'error_cl' => 'alert-danger',
            ));
            exit;
        }
        $status = $cmsPage->getStatus();

        /*
         * this set the value field in the form by passing the values retrieved from the data
        $status = $form->get('atitle')->setData("testing1234");
        */
        if($status == 1)
        {
            $status = $form->get('status')->setData(true);
        }
        else
        {
            $status = $form->get('status')->setData(false);
        }

        $form->handleRequest($request);
        $request->get('page_content');
        $cmsData = $form->getData();
        $adesc = $form->get('status')->getData();

        // cms_pages__token
        // exit;


        if ($form->isSubmitted())
        {


            if($form->isValid())
            {

                try {


                    if ($funcController->checkCsrfToken($form->get('token')->getData(), 'admin_cms_list_upd')) {
                        $funcController->setCsrfToken('admin_cms_list_upd');
                        $session = new Session();
                        $token   = $session->get('admin_cms_list_upd');

                        $cmsPage->setpageTitle(strip_tags(trim($form->get('page_title')->getData())));
                        $content_data = trim($form->get('page_content')->getData());
                        $page_content_striped_edit = $this->strip_tags_content($content_data, '<script>', TRUE);
                        $cmsPage->setpageContent($page_content_striped_edit);
                        $cmsPage->setStatus(strip_tags(trim($form->get('status')->getData())));
                        $cmsPage->setLanguage(strip_tags(trim($form->get('language')->getData())));
                        $cmsPage->setCountry(strip_tags(trim($form->get('country')->getData())));
                        $cmsPage->setType(strip_tags(trim($form->get('type')->getData())));


                        $em = $this->getDoctrine()->getManager();
                        //$em->persist($cmsPage);
                        $em->flush();


                        if ($cmsPage->getId()) {


                            return $this->render('admin/cms/cmsedit.html.twig', array(
                                'form' => $form->createView(),
                                'message' => $this->get('translator')->trans('Record is updated'),
                                'token' => $token,
                                'error_cl' => 'alert-success',

                            ));

                        } else {
                            return $this->render('admin/cms/cmsedit.html.twig', array(
                                'form' => $form->createView(),
                                'message' => $this->get('translator')->trans('Nothing to update'),
                                'error_cl' => 'alert-danger', 'token' => $token,

                            ));
                        }
                    } else {
                        
                        $this->get('security.token_storage')->setToken(null);
                        $response = new RedirectResponse($this->generateUrl('admin_admin'));
                        return $response;
                        //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");
                    }
                }
                catch(\Exception $e){
                    $e->getMessage();
                    $funcController->setCsrfToken('admin_cms_list_upd');
                    $session = new Session();
                    $token   = $session->get('admin_cms_list_upd');

                    return $this->render('admin/cms/cmsedit.html.twig', array(
                        'form' => $form->createView(),
                        'message' => $this->get('translator')->trans('Nothing to update'),
                        'error_cl' => 'alert-danger', 'token' => $token,

                    ));
                }
            }
            else
            {
                $funcController->setCsrfToken('admin_cms_list_upd');
                $session = new Session();
                $token   = $session->get('admin_cms_list_upd');
                $message = $this->get('translator')->trans('Unable to update record');
                return $this->render('admin/cms/cmsedit.html.twig', array(
                    'form'     => $form->createView(),  'message' => $message,
                    'error_cl' => 'alert-danger', 'token' => $token,
                ));
            }
        }
        else
        {
            // $csrf = $this->get('security.csrf.token_manager'); //Symfony\Component\Security\Csrf\CsrfTokenManagerInterface
            // echo $token = $csrf->refreshToken('asdfasdfasd');
            
            $funcController->setCsrfToken('admin_cms_list_upd');
            $session = new Session();
            $token   = $session->get('admin_cms_list_upd');

            return $this->render('admin/cms/cmsedit.html.twig', array(
                'form' => $form->createView(),'message' => "",
                'error_cl' => 'alert-success', 'token' => $token ,
            ));
        }

    }


    public function setCsrfToken($token_name){
        $csrf = $this->get('security.csrf.token_manager');
        $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 61535), mt_rand(0, 61535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        $d = new \DateTime("NOW");
        $currentDate = $d->format("Y/m/d H:i:s");
        $nonce = md5($guid);
        $passwordHash   = sha1(base64_encode($nonce) . $currentDate . AppConstant::IKTISSAB_API_SECRET);
        $passwordDigest =  base64_encode($passwordHash);
        $token = $csrf->refreshToken($passwordDigest);
        $session = new Session();
        $token_name = $token_name;
        $session->set($token_name, $token);
    }

    public function checkCsrfToken($csf_token , $token_name){
        $session = new Session();
        $token_name = $token_name;
        // csrf_admin_token
        $token_val = $session->get($token_name);
        if($token_val == $csf_token)
        {
            return true;
        }
        else
        {
            return false;
        }
    }




    /**
     * @Route("/admin/newslistupdate", name="newslistupdate")
     *
     */

    public function newsListUpdateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        // MANAGE_NEWS_EDIT
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        if(!$funcController->isValidRule('MANAGE_NEWS_EDIT'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }


        $error_cl = 'alert-success';
        $em = $this->getDoctrine()->getManager();
        

        //$cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        $page = $funcController->EncDec($request->get('param') , 'd');
        $cmsPage = $em->getRepository('AppBundle:CmsPages')
        ->findOneBy(array( 'type' => 'news' , 'id' => $page));
        $form   = $this->createForm(CmsPagesType::class, $cmsPage);
        if(empty($cmsPage))
        {
            return $this->render('admin/news/newsedit.html.twig', array(
                'form' => $form->createView(),'message' => 'No data found',
                'error_cl' => 'alert-danger',
            ));
            exit;
        }
        $status = $cmsPage->getStatus();

        /*
         * this set the value field in the form by passing the values retrieved from the data
        $status = $form->get('atitle')->setData("testing1234");
        */
        if($status == 1)
        {
            $status = $form->get('status')->setData(true);
        }
        else
        {
            $status = $form->get('status')->setData(false);
        }

        $data = $form->handleRequest($request);

        $request->get('adesc');
        // $cmsData = $form->getData();
        // $adesc   = $form->get('status')->getData();


        if ($form->isSubmitted())
        {
            //$form->isValid(false);
            if ($form->isValid())
            {
                try {
                    if ($funcController->checkCsrfToken($form->get('token')->getData(), 'admin_news_list_upd')) {
                        $title = strip_tags(trim($form->get('page_title')->getData()));
                        $content_data = strip_tags(trim($form->get('page_content')->getData()));
                        $page_content_striped_edit = $this->strip_tags_content($content_data, '<script>', TRUE);
                        $content = $page_content_striped_edit;
                        $url_path = strip_tags(trim($form->get('url_path')->getData()));
                        $status = strip_tags(trim($form->get('status')->getData()));
                        $country = strip_tags(trim($form->get('country')->getData()));
                        $type = strip_tags(trim($form->get('type')->getData()));
                        $type = 'news';
                        $em = $this->getDoctrine()->getManager();
                        //$em->persist($cmsPage);
                        //$file = $form->get('brochure')->getData();
                        $file = strip_tags(trim($cmsPage->getBrochure()));
                        $file = "";
                        if ($file) {
                            $file = $cmsPage->getBrochure(); //$form->get('image')->getData(); //$cmsPage->getImage();
                            // Generate a unique name for the file before saving it
                            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                            // Move the file to the directory where brochures are stored
                            $file->move(
                                $this->getParameter('images_directory'),
                                $fileName
                            );

                            // Update the 'brochure' property to store the PDF file name
                            // instead of its contents
                            $cmsPage->getBrochure($fileName);
                            $em = $this->getDoctrine()->getManager();
                            $conn = $em->getConnection();

                            // $data_values = array($email = $email, $C_id = $C_id);
                            $status = $form->get("status")->getData();
                            $data_values = array($content,
                                $title, $status, $country, $type, $fileName, $url_path, $page);

                            $stm = $conn->executeUpdate('UPDATE cms_pages SET  page_content= ?  , 
                    page_title = ?   , status = ?  , country = ? , type = ?  , brochure = ? , url_path = ? 
                     
                    WHERE id = ?   ', $data_values);

                            $stm;
                            $funcController->setCsrfToken('admin_news_list_upd');
                            $session = new Session();
                            $token = $session->get('admin_news_list_upd');
                            if ($stm == 1) {
                                return $this->render('admin/news/newsedit.html.twig', array(
                                    'form' => $form->createView(),
                                    'token' => $token,
                                    'message' => $this->get('translator')->trans('Record is updated'),
                                ));
                            } else {
                                return $this->render('admin/news/newsedit.html.twig', array(
                                    'form' => $form->createView(),
                                    'token' => $token,
                                    'message' => $this->get('translator')->trans('Nothing to update'),
                                ));
                            }
                        } else {
                            $em = $this->getDoctrine()->getManager();
                            $conn = $em->getConnection();

                            // $data_values = array($email = $email, $C_id = $C_id);
                            $status = strip_tags(trim($form->get("status")->getData()));
                            $data_values = array($content,
                                $title, $status, $country, $type, $url_path, $page);

                            $stm = $conn->executeUpdate('UPDATE cms_pages SET  page_content = ?  ,  
                    page_title = ? ,  status = ?  , country = ? , type = ?  , url_path = ? 
                     
                    WHERE id = ? ', $data_values);

                            $stm;
                            $funcController->setCsrfToken('admin_news_list_upd');
                            $session = new Session();
                            $token = $session->get('admin_news_list_upd');
                            if ($stm == 1) {

                                return $this->render('admin/news/newsedit.html.twig', array(
                                    'form' => $form->createView(),
                                    'message' => $this->get('translator')->trans('Record is updated'),
                                    'error_cl' => 'alert-success', 'token' => $token,
                                ));
                            } else {

                                return $this->render('admin/news/newsedit.html.twig', array(
                                    'form' => $form->createView(),
                                    'message' => $this->get('translator')->trans('Nothing to update'),
                                    'error_cl' => 'alert-danger', 'token' => $token,
                                ));
                            }
                        }
                    } else {
                        $this->get('security.token_storage')->setToken(null);
                        $response = new RedirectResponse($this->generateUrl('admin_admin'));
                        return $response;
                        //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");

                    }
                }
                catch(\Exception $e)
                {
                    $e->getMessage();
                    $funcController->setCsrfToken('admin_news_list_upd');
                    $session = new Session();
                    $token   = $session->get('admin_news_list_upd');

                    return $this->render('admin/cms/cmsedit.html.twig', array(
                        'form' => $form->createView(),
                        'message' => $this->get('translator')->trans('Nothing to update'),
                        'error_cl' => 'alert-danger', 'token' => $token,

                    ));
                }

            }
            else
            {
                $funcController->setCsrfToken('admin_news_list_upd');
                $session = new Session();
                $token = $session->get('admin_news_list_upd');
                $message = $this->get('translator')->trans('Unable to update record');
                return $this->render('admin/news/newsedit.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                    'error_cl' => 'alert-success',
                ));
/*
                $this->get('security.token_storage')->setToken(null);
                $response = new RedirectResponse($this->generateUrl('admin_admin'));
                return $response;*/

            }
        }
        else
        {
            // $csrf = $this->get('security.csrf.token_manager'); //Symfony\Component\Security\Csrf\CsrfTokenManagerInterface
            // echo $token = $csrf->refreshToken('asdfasdfasd');
            $funcController->setCsrfToken('admin_news_list_upd');
            $session = new Session();
            $token = $session->get('admin_news_list_upd');
            // $form->get('token')->setData($token);
            $form->get('url_path')->setData('--');
            //$request = $this->getRequest();
            $param = $request->query->get('param');
            if($param == 1){
                $message = $this->get('translator')->trans('Record is updated');
            }
            else
            {
                $message = '';
            }
            return $this->render('admin/news/newsedit.html.twig', array(
                'form' => $form->createView(),'message' => $message,
                'error_cl' => 'alert-success', 'token' => $token,
            ));
        }
    }


    /**
     * @Route("/admin/gallerylistupdate/{page}", name="gallerylistupdate")
     *
     */

    public function galleryListUpdateAction(Request $request,$page)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        $em = $this->getDoctrine()->getManager();
        //$cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        //echo $page = $request->query->get('page');

        $cmsPage = $em->getRepository('AppBundle:Gallery')
            ->findOneBy(array('id' => $page));
        $status = $cmsPage->getStatus();
        $form = $this->createForm(GalleryType::class, $cmsPage);
        if($status == 1)
        {
            $status = $form->get('status')->setData(true);
        }
        else
        {
            $status = $form->get('status')->setData(false);
        }
        $form->handleRequest($request);
        $request->get('adesc');
        $cmsData = $form->getData();



        // echo $file = 'web/img/banners/'.$form->get('image')->getData(); //$cmsPage->getImage();
        // $file = $this->get('request')->getBasePath();
        // Generate a unique name for the file before saving it
        // $fileName =  $form->get('image')->getData() ; //md5(uniqid()) . '.' . $file->guessExtension();
        if ($form->isValid() && $form->isSubmitted())
        {
            $Atitle  = $form->get('atitle')->getData();
            $Etitle  = $form->get('etitle')->getData();
            $Adesc   = $form->get('adesc')->getData();
            $Edesc   = $form->get('edesc')->getData();



            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            //echo $cmsPage->getImage();
            //echo $form->get('image')->getData();
            if($form->get('image')->getData() ) {
            $file = $cmsPage->getImage(); //$form->get('image')->getData(); //$cmsPage->getImage();
                // Generate a unique name for the file before saving it
                 $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );

                // Update the 'brochure' property to store the PDF file name
                // instead of its contents
                $cmsPage->setImage($fileName);
                $em = $this->getDoctrine()->getManager();
                $conn = $em->getConnection();

                // $data_values = array($email = $email, $C_id = $C_id);
                $status = $form->get("status")->getData();
                $data_values = array(
                $Atitle , $Etitle , $Adesc , $Edesc  , $fileName , $status , $page  );
                $stm = $conn->executeUpdate('UPDATE gallery SET 
                atitle = ? , etitle= ?  , adesc= ?  , edesc= ?  , image = ? ,
                status = ? 
                WHERE id = ?   ', $data_values);

                $stm;
                if($stm == 1){
                    return $this->render('admin/gallery/gallerylistupdate.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                    ));
                }else
                {
                    return $this->render('admin/gallery/gallerylistupdate.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Nothing to update'),
                    ));
                }
            }
            else
            {
                $em = $this->getDoctrine()->getManager();
                $conn = $em->getConnection();

                // $data_values = array($email = $email, $C_id = $C_id);
                $status = $form->get("status")->getData();
                $data_values = array(
                    $Atitle , $Etitle , $Adesc , $Edesc , $status , $page );
                $stm = $conn->executeUpdate('UPDATE gallery SET 
                atitle = ? , etitle = ?  , adesc = ?  , edesc = ?  ,
                status = ?
                WHERE id = ?   ', $data_values);

                echo $stm;
                if($stm == 1){
                    return $this->render('admin/gallery/gallerylistupdate.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                    ));
                }else
                {
                    return $this->render('admin/gallery/gallerylistupdate.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Nothing to update'),
                    ));
                }



            }
        }
        else
        {
            //echo $this->getParameter('images_directory').'/'.$cmsPage->getImage();

            $cmsPage->setImage(
                new FileType($this->getParameter('images_directory').'/'.$cmsPage->getImage())
            );

            return $this->render('admin/gallery/gallerylistupdate.html.twig', array(
                'form' => $form->createView(),'message' => '',
            ));
        }
    }



    /**
     * @Route("/admin/gallerylistdelete/{page}", name="gallerylistdelete")
     *
     */

    public function galleryListDeleteAction(Request $request,$page)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        // delete the record
        $em = $this->getDoctrine()->getManager();
        $galleryImage = $em->getRepository('AppBundle:Gallery')->find($page);
        $em->remove($galleryImage);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('imageslistall');
    }



    /**
     * @Route("/admin/cmslistdelete", name="cmslistdelete")
     *
     */

    public function cmsListDeleteAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        else
        {
            //return $this->redirectToRoute('admin_admin');
        }
        // delete the record
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_CMS_DELETE')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $funcController->EncDec($request->get('param') , 'd');
        $cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        $em->remove($cmsPage);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('cmslistall');
    }

    /**
     * @Route("/admin/newslistdelete", name="newslistdelete")
     *
     */

    public function newsListDeleteAction(Request $request)
    {
        // delete the record
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);


        if(!$funcController->isValidRule('MANAGE_NEWS_DELETE'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $this->EncDec($request->get('param') , 'd');
        $cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        $em->remove($cmsPage);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('newslistall');

    }



    /**
     * @Route("/admin/uploadfile/", name="uploadfile")
     *
     */
    public function uploadFileAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }

        $gallery = new Gallery();

        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file
            /**
             * @var Symfony\Component\HttpFoundation\File\UploadedFile
             */
            $file = $gallery->getImage();
            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            // Move the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('images_directory'),
                $fileName
            );

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $gallery->setImage($fileName);

            $form->get('status')->getData();
            $gallery->setAtitle($form->get('atitle')->getData());
            $gallery->setEtitle($form->get('etitle')->getData());
            $gallery->setAdesc($form->get('adesc')->getData());
            $gallery->setEdesc($form->get('edesc')->getData());
            $gallery->setStatus($form->get('status')->getData());
            // $gallery->setImage($form->get('image')->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($gallery);
            $em->flush();
            if($gallery->getId()) {
                $message = $this->get('translator')->trans('Record Added successfully');
                return $this->render('admin/cms/upload.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                ));
            }
            // ... persist the $product variable or any other work
        }
        return $this->render('admin/cms/upload.html.twig' ,  array(
            'form' => $form->createView() ));

    }

    /**
    * @Route("/admin/imageslistall/", name="imageslistall")
    */
    public function imagesListAllAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('homepage', array('_country' => 'sa', '_locale' => 'en' ));
        }

        $images = $this->getDoctrine()
            ->getRepository('AppBundle:Gallery')
            ->findAll();
        //$images[0]->getId();

        $data = array();
        $i = 0;
        foreach( $images as $image )
        {
            echo "===> ".$image->getId();
            $data[$i]['id']      =  $image->getId();
            $data[$i]['image']  =  $image->getImage();
            $data[$i]['Status']  =  $image->getStatus();
            $i++;
        }
        //print_r($data);
        if (!$images)
        {
            return $this->render('admin/gallery/imageslistall.html.twig', array(
                'data' => $data , 'message' => 'No record found' ));
        }
        else
        {
            return $this->render('admin/gallery/imageslistall.html.twig', array(
                'data' => $data ,  'message' => ''   ));
        }
    }





    /**
     * @Route("/admin/settings/", name="admin_settings")
     *
     */
    public function settingsAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_EMAIL_SETTINGS_ADD')){
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }


        $settings = new EmailSetting();
        // echo '====='.$cmsPage->getAdesc();
        $form = $this->createForm(EmailSettingType::class, $settings);
        // print_r($form);
        $form->handleRequest($request);
        // $request->request->get('adesc');
        $cmsData = $form->getData();
        // echo $adesc = $form->getAdesc('adesc')->getData();
        if ($form->isSubmitted())
        {
            if($form->isValid())
            {
                if($funcController->checkCsrfToken($form->get('token')->getData() , 'admin_email_setting' ))
                {
                    $funcController->setCsrfToken('admin_email_setting');
                    $session = new Session();
                    $token = $session->get('admin_email_setting');
                    //check if the email is already added before or not
                    if($this->chkEmail(trim($form->get('email')->getData())) == false )
                    {
                        $settings->setEmail(strip_tags(trim($form->get('email')->getData())));
                        $settings->setType(strip_tags(trim($form->get('type')->getData())));
                        $settings->setCountry(strip_tags(trim($form->get('country')->getData())));
                        $settings->setTechnical(strip_tags(trim($form->get('technical')->getData())));
                        $settings->setOther(strip_tags(trim($form->get('other')->getData())));
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($settings);
                        $em->flush();
                        if ($settings->getId()) {
                            $message = $this->get('translator')->trans('Record Added successfully');
                            return $this->render('admin/settings/settings.html.twig', array(
                                'form' => $form->createView(), 'message' => $message,
                                'error_cl' => 'alert-success', 'token' => $token,
                            ));
                        } else {
                            $message = $this->get('translator')->trans('Unable to add record');
                            return $this->render('admin/settings/settings.html.twig', array(
                                'form' => $form->createView(), 'message' => $message,
                                'error_cl' => 'alert-danger', 'token' => $token,
                            ));
                        }
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('Email is already added.');
                        return $this->render('admin/settings/settings.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                            'error_cl' => 'alert-danger', 'token' => $token,
                        ));
                    }
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                }
            }
            else
            {
                /* $this->get('security.token_storage')->setToken(null);
                $response = new RedirectResponse($this->generateUrl('admin_admin'));
                return $response; */
                $funcController->setCsrfToken('admin_email_setting');
                $session = new Session();
                $token = $session->get('admin_email_setting');
                return $this->render('admin/settings/settings.html.twig',
                array(
                    'form' => $form->createView(), 'message' => $this->get('translator')->trans('Unable to add record'),
                    'error_cl' => 'alert-success', 'token' => $token ,
                ));

            }
        }
        else
        {
            $funcController->setCsrfToken('admin_email_setting');
            $session = new Session();
            $token = $session->get('admin_email_setting');
            $message = '';
            return $this->render('admin/settings/settings.html.twig',
            array(
                'form' => $form->createView(), 'message' => $message,
                'error_cl' => 'alert-success', 'token' => $token ,
            ));
        }

    }

    /**
     * @Route("/admin/settingsupdate/", name="settingsupdate")
     *
     */

    public function settingsUpdateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_EMAIL_SETTINGS_EDIT')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }



        $id = $funcController->EncDec($request->get('param'), 'd');

        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('AppBundle:EmailSetting')->find($id);
        $form = $this->createForm(EmailSettingType::class, $settings);
        if(empty($settings)){
            $funcController->setCsrfToken('admin_email_setting');
            $session = new Session();
            $token = $session->get('admin_email_setting');
            return $this->render('admin/settings/settingsedit.html.twig', array(
                'form' => $form->createView(),'message' => "No record found",
                'error_cl' => 'alert-danger', 'token' => $token,
            ));
            exit;
        }

        $type = $settings->getType();
        //$form = $this->createForm(EmailSettingType::class, $settings);
        $form->handleRequest($request);
        $settingsData = $form->getData();
        $type = $form->get('type')->getData();
        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                if($funcController->checkCsrfToken($form->get('token')->getData() , 'admin_email_setting' )) {


                    $em = $this->getDoctrine()->getManager();
                    $qb = $em->createQueryBuilder('u');
                    $qb->select('u')
                        ->from('AppBundle:EmailSetting', 'u')
                        ->where('u.id != ?1 AND u.email = ?2')
                        ->setParameters(array(1 => $id, 2 => $form->get('email')->getData()));
                    $result = $qb->getQuery()->getResult();




                    $funcController->setCsrfToken('admin_email_setting');
                    $session = new Session();
                    $token = $session->get('admin_email_setting');

                    if (empty($result))
                    {
                        $settingsData->setEmail(strip_tags(trim($form->get('email')->getData())));
                        $settingsData->setType(strip_tags(trim($form->get('type')->getData())));
                        $settingsData->setTechnical(strip_tags(trim($form->get('technical')->getData())));
                        $settingsData->setOther(strip_tags(trim($form->get('other')->getData())));
                        $settingsData->setCountry(strip_tags(trim($form->get('country')->getData())));
                        $em = $this->getDoctrine()->getManager();
                        $em->flush();



                        if ($settingsData->getId()) {
                            return $this->render('admin/settings/settingsedit.html.twig', array(
                                'form' => $form->createView(), 'message' => $this->get('translator')->trans('Record is updated'),
                                'error_cl' => 'alert-success', 'token' => $token,
                            ));
                        } else {
                            return $this->render('admin/settings/settingsedit.html.twig', array(
                                'form' => $form->createView(), 'message' => $this->get('translator')->trans('Unable to update'),
                                'error_cl' => 'alert-danger', 'token' => $token,
                            ));
                        }
                    }else {
                        return $this->render('admin/settings/settingsedit.html.twig', array(
                            'form' => $form->createView(), 'message' => $this->get('translator')->trans('Email is already added'),
                            'error_cl' => 'alert-danger','token' => $token,
                        ));
                    }
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                }
            }
            else
            {
                $funcController->setCsrfToken('admin_email_setting');
                $session = new Session();
                $token = $session->get('admin_email_setting');
                return $this->render('admin/settings/settingsedit.html.twig', array(
                    'form' => $form->createView(),'message' => $this->get('translator')->trans('Unable to update'),
                    'error_cl' => 'alert-danger', 'token' => $token,
                ));
            }
        }
        else
        {
            $funcController->setCsrfToken('admin_email_setting');
            $session = new Session();
            $token = $session->get('admin_email_setting');
            // $form->get('token')->setData($token);
            // $request = $this->getRequest();
            return $this->render('admin/settings/settingsedit.html.twig', array(
                'form' => $form->createView(),'message' => "",
                'error_cl' => 'alert-success', 'token' => $token,
            ));
        }
    }

    /**
     * @Route("/admin/settingslist/", name="settingslist")
     */
    public function settingsListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_EMAIL_SETTINGS')){
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $em = $this->getDoctrine()->getManager();

        $settingsList = $this->getDoctrine()
            ->getRepository('AppBundle:EmailSetting')
            ->findAll();



        $data = array();
        $i = 0;
        if(!empty($settingsList))
        {
            foreach ($settingsList as $list) {

                $data[$i]['Id']    =         $funcController->EncDec( $settingsList[$i]->getId(), 'e' );
                $data[$i]['cid']        = $settingsList[$i]->getId();
                // $data[$i]['Id']      = $settingsList[$i]->getId();
                // $data[$i]['Id']      = $settingsList[$i]->getId();
                $data[$i]['Email']      = $settingsList[$i]->getEmail();
                $data[$i]['Type']       = $settingsList[$i]->getType();
                $data[$i]['Technical']  = $settingsList[$i]->getTechnical();
                $data[$i]['Other'] = $settingsList[$i]->getOther();
                // country id is sa and eg
                if ($settingsList[$i]->getCountry() == 'sa') {
                    $data[$i]['Country'] = "Saudi Arabia";
                } else {
                    $data[$i]['Country'] = "Egypt";
                }
                $i++;
            }

        }
        else
        {
            return $this->render('admin/settings/settingslistall.html.twig', array(
                'data' => $data, 'message' => $this->get('translator')->trans('No record found')
            ));
        }

        return $this->render('admin/settings/settingslistall.html.twig', array(
        'data' => $data,'message' => '',

        ));

    }


    /**
     * @Route("/admin/settingsdelete", name="settingsdelete")
     *
     */

    public function settingsDeleteAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_EMAIL_SETTINGS_DELETE'))
        {
            $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
            return $response;
        }

        // delete the record
        $em = $this->getDoctrine()->getManager();
        

        $id = $funcController->EncDec($request->get('param'), 'd');
        $settingDel = $em->getRepository('AppBundle:EmailSetting')->find($id);
        $em->remove($settingDel);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('settingslist');

    }


    /**
     * @Route("/admin/formdisplaysettings/", name="admin_formdisplaysettings")
     */
    public function formDisplaySettingAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_FORM_VIEW'))
        {
            $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
            return $response;
        }




        $displayList = $this->getDoctrine()
            ->getRepository('AppBundle:FormSetting')
            ->findAll();
        $data = array();


        $i = 0;
        //exit;
        foreach($displayList as $list)
        {
            // $data[$i]['Id']          =  $displayList[$i]->getId();
            $data[$i]['Id']          =  $funcController->EncDec( $displayList[$i]->getId(), 'e' );
            $data[$i]['cid']         =  '123';
            $data[$i]['Status']      =  $displayList[$i]->getStatus();
            $data[$i]['FormType']    =  $displayList[$i]->getFormType();
            $data[$i]['Country']     =  $displayList[$i]->getCountry();
            $data[$i]['Submissions'] =  $displayList[$i]->getSubmissions();
            $data[$i]['Limitto']     =  $displayList[$i]->getLimitto();
            if($displayList[$i]->getCountry() == 'sa')
            {
                $data[$i]['Country'] =  "Saudi Arabia";
            }
            else
            {
                $data[$i]['Country'] =  "Egypt";
            }
            $i++;
        }
        if($displayList == '' && $displayList == null)
        {
            $message = 'No record found';
        }else{ $message = ''; }

        return $this->render('admin/settings/formdisplaysettings.html.twig', array(
                'data' => $data , 'message' => $message));

    }

    /**
     * @Route("/admin/addformdisplaysetting/", name="admin_addformdisplaysetting")
     *
     */
    public function addFormDisplaySettingAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_FORM_ADD'))
        {
            $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
            return $response;
        }



        $displaysettings = new FormSetting();
        $form = $this->createForm(FormSettingType::class, $displaysettings);
        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                if($funcController->checkCsrfToken($form->get('token')->getData() , 'admin_addformdisplay_setting' ))
                {
                    $displaysettings->setStatus($form->get('status')->getData());
                    //
                    $formtype_form = strip_tags(trim($form->get('formtype')->getData()));
                    $country_form = strip_tags(trim($form->get('country')->getData()));

                    $this->chkForm($country_form , $formtype_form);

                    $funcController->setCsrfToken('admin_addformdisplay_setting');
                    $session = new Session();
                    $token = $session->get('admin_addformdisplay_setting');

                    if($this->chkForm($country_form , $formtype_form) == false ) {
                        $displaysettings->setFormType(strip_tags(trim($form->get('formtype')->getData())));
                        $displaysettings->setCountry(strip_tags(trim($form->get('country')->getData())));
                        $displaysettings->setSubmissions(strip_tags(trim($form->get('submissions')->getData())));
                        $displaysettings->setLimitto(strip_tags(trim($form->get('limitto')->getData())));
                        $displaysettings->setStatus(strip_tags(trim($form->get('status')->getData())));

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($displaysettings);
                        $em->flush();
                        if ($displaysettings->getId()) {
                            $message =  $this->get('translator')->trans('Record Added successfully');
                            return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
                                'form' => $form->createView(), 'message' => $message,
                                'error_cl' => 'alert-success', 'token' => $token,
                            ));
                        } else {

                            $message =  $this->get('translator')->trans('Unable to update');
                            return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
                                'form' => $form->createView(), 'message' => $message,
                                'error_cl' => 'alert-danger', 'token' => $token,
                            ));
                        }
                    }
                    else
                    {
                        $message =  $this->get('translator')->trans('Duplicate record found');
                        return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                            'error_cl' => 'alert-danger','token' => $token,
                        ));
                    }
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                    //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");
                }
            }
            else
            {

                $funcController->setCsrfToken('admin_addformdisplay_setting');
                $session = new Session();
                $token = $session->get('admin_addformdisplay_setting');

                return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
                    'form' => $form->createView(), 'message' => "" ,
                    'error_cl' => 'alert-success', 'token' => $token,
                ));
                //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");
            }
        }
        else
        {
            $funcController->setCsrfToken('admin_addformdisplay_setting');
            $session = new Session();
            $token = $session->get('admin_addformdisplay_setting');
            // $form->get('token')->setData($token);
            // $request = $this->getRequest();

            return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
                'form' => $form->createView(), 'message' => "" ,
                'error_cl' => 'alert-success', 'token'   => $token,
            ));
        }

    }



    /**
     * @Route("/admin/formdisplaysettingsupdate", name="admin_formdisplaysettingsupdate")
     *
     */

    public function formDisplaySettingsUpdateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_FORM_EDIT'))
        {
            $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        

        //echo $id = $request->request->get('param');
        $id = $funcController->EncDec( $request->get('param') , 'd' );
        $settings = $em->getRepository('AppBundle:FormSetting')->find($id);
        $form = $this->createForm(FormSettingType::class, $settings);
        if(empty($settings)){
            $this->setCsrfToken('admin_addformdisplay_setting_upd');
            $session = new Session();
            $token = $session->get('admin_addformdisplay_setting_upd');
            return $this->render('admin/settings/updateformdisplaysettings.html.twig', array(
                'form'     => $form->createView(),'message' => 'No record found' ,
                'error_cl' => 'alert-danger', 'token' => $token,
            ));
            exit;
        }
        $form->handleRequest($request);
        $settingsData = $form->getData();
        $type = $form->get('formtype')->getData();

        if($form->isSubmitted())
        {
            if($form->isValid())
            {
                if($this->checkCsrfToken($form->get('token')->getData() , 'admin_addformdisplay_setting_upd' ))
                {
                    $this->setCsrfToken('admin_addformdisplay_setting_upd');
                    $session = new Session();
                    $token = $session->get('admin_addformdisplay_setting_upd');

                    $settingsData->setStatus(strip_tags(trim($form->get('status')->getData())));
                    $settingsData->setFormType(strip_tags(trim($form->get('formtype')->getData())));
                    $settingsData->setCountry(strip_tags(trim($form->get('country')->getData())));
                    $settingsData->setSubmissions(strip_tags(trim($form->get('submissions')->getData())));
                    $settingsData->setLimitto(strip_tags(trim($form->get('limitto')->getData())));
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    if ($settingsData->getId())
                    {
                        $message = $this->get('translator')->trans('Record is updated');
                        return $this->render('admin/settings/updateformdisplaysettings.html.twig', array(
                            'form' => $form->createView(),'message' => $message ,
                            'error_cl' => 'alert-success', 'token' => $token,
                        ));
                    }
                    else
                    {
                        $message = $this->get('translator')->trans('Unable to update record');
                        return $this->render('admin/settings/updateformdisplaysettings.html.twig', array(
                            'form' => $form->createView(),'message' => $message ,
                            'error_cl' => 'alert-success', 'token' => $token,
                        ));
                    }
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                }
            }
            else
            {
                $this->setCsrfToken('admin_addformdisplay_setting_upd');
                $session = new Session();
                $token = $session->get('admin_addformdisplay_setting_upd');

                $message = $this->get('translator')->trans('Unable to update record');

                return $this->render('admin/settings/updateformdisplaysettings.html.twig', array(
                    'form' => $form->createView(),'message' => $message ,
                    'error_cl' => 'alert-danger', 'token' => $token,
                ));

                /*$this->get('security.token_storage')->setToken(null);
                $response = new RedirectResponse($this->generateUrl('admin_admin'));
                return $response;*/
            }
        }
        else
        {
            $this->setCsrfToken('admin_addformdisplay_setting_upd');
            $session = new Session();
            $token = $session->get('admin_addformdisplay_setting_upd');
            //$form->get('token')->setData($token);
            //$request = $this->getRequest();

            return $this->render('admin/settings/updateformdisplaysettings.html.twig', array(
                'form' => $form->createView(),'message' => "" ,
                'error_cl' => 'alert-danger', 'token' => $token,
            ));
        }
    }

    /**
     * @Route("/admin/formdisplaysettingsdelete", name="admin_formdisplaysettingsdelete")
     *
     */

    public function formDisplaySettingsDeleteAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        // delete the record
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_FORM_DELETE'))
        {
            $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
            return $response;
        }
        // delete the record


        $id = $funcController->EncDec($request->get('param'),'d');
        $em = $this->getDoctrine()->getManager();
        $settingDel = $em->getRepository('AppBundle:FormSetting')->find($id);
        $em->remove($settingDel);
        $em->flush();
        return $this->redirectToRoute('admin_formdisplaysettings');

    }

    /**
     * @Route("/admin/loogout", name="admin_loogout")
     *
     */
    public function loogoutAction(Request $request){


        $user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $acrivityLog = $this->get('app.activity_log');
        $acrivityLog->logLogoutEvent($user,'admin');
        $this->get('security.token_storage')->setToken(null);
        $response = new RedirectResponse($this->generateUrl('admin_admin'));
        return $response;
    }


    public function chkRcd($title , $language , $url , $type)
    {
        $title      =  $title;
        $language   =  $language;
        $url_path   =  $url;
        $type       =  $type;
        /*
        $title      = 'test';
        $language   =  'en'; //$language;
        $url_path   =  'script-test' ;  //$url;
        $type       =  'cms'; //$type;
        */

        $em = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
        ->getRepository('AppBundle:CmsPages')
        ->findBy(array( 'type' => $type , 'url_path' => $url_path , 'language' => $language ));
        if(!empty($cmspages))
        {
            return true;
        }
        else
        {
            return false;
        }

    }


    public function chkRcdRole($assignedResource , $assignedRole , $page = ""  )
    {
        $em = $this->getDoctrine()->getManager();
        if($page !="" )
        {
           $qb = $em->createQueryBuilder('u');
           $qb->select('u')
                ->from('AppBundle:Resources', 'u')
                ->where('u.id != ?1 AND u.resourceName = ?2')
                ->setParameters(array(1 => $page , 2 => 'MANAGE_CMS_VIEW'));
                $result =  $qb->getQuery()->getResult();
                //$result[0]->getId();

            if(!empty($result)){
                return true;
            }
            else {

                return false;
            }
              

        }
        else {
            $resoureces = $this->getDoctrine()
                ->getRepository('AppBundle:Resources')
                ->findBy(array('resourceName' => $assignedResource, 'assignedTo' => $assignedRole));

            if (!empty($resoureces)) {
                return true;
            } else {
                return false;
            }
        }

    }




    /**
     * @Route("/admin/test123", name="admin_chkformmm1")
     *
     */

    public function test123Action()
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();
        /****************/
        $stm  = $conn->prepare("SELECT id FROM cms_pages WHERE  id != ? AND url_path = ?  ");
        $stm->bindValue(1, 25);
        $stm->bindValue(2, 'test-script2');
        // $stm->bindValue(2, 'script-test');
        $stm->execute();
        $result  = $stm->fetchAll();
        $num =  count($result);
        if($num >= 2)
        {
            echo 'can not update';
            return false;
            // can updat
        }
        else
        {
            echo 'can  update';
            return true;
            // no update
        }
        // WXrjqnmXijodfi3NOMlO04hA8EuL6g_ro0j7Yv6NnJ0
        // WXrjqnmXijodfi3NOMlO04hA8EuL6g_ro0j7Yv6NnJ0
    }


    public function chkEmail($email )
    {

        $em       = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:EmailSetting')
            ->findBy(array( 'email' => $email ));
        if(!empty($cmspages))
        {
            return true;
        }
        else
        {
            return false;
        }

    }



    /**
     * @Route("/admin/chkform", name="admin_chkform")
     *
     */
    public function chkForm($country , $formtype)
    {
        $country   =  $country;
        $formtype       =  $formtype;
        $em = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:FormSetting')
            ->findBy(array( 'formtype' => $formtype , 'country' => $country ));
        if(!empty($cmspages))
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    /**
     * @Route("/admin/test1234", name="admin_test1234")
     *
     */
    public function test1234Action()
    {
        //our data to be encoded
        $key = 'bRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU=';
        $password_plain = 'abc123';
        echo $password_plain . "<br>";
        $password_encrypted = $this->Eencrypt($password_plain, $key);
        echo $password_encrypted . "<br>";
        $password_decrypted = $this->Ddecrypt($password_encrypted, $key);
        echo $password_decrypted . "<br>";
    }

    private function Eencrypt($data, $key) {
        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private function Ddecrypt($data, $key) {
        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }

    function EncDec( $string, $action = 'e' ) {
        // you may change these values to your own
        $secret_key = 'abc123abcnmkgk';
        $secret_iv  = 'gfhjdkslwlsa';

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }
        return $output;
    }


    /**
     * @Route("/admin/resourceslist" , name = "admin_resources")
     */
    public function resourcesAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }
        else
        {
            //return $this->redirectToRoute('admin_admin');
        }
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_CMS_ADD')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $resource = new Resources();
        $form = $this->createForm(ResourcesType::class, $resource);
        // print_r($form);
        $form->handleRequest($request);
        $resData = $form->getData();
        $form->get('token')->getData();

        if ($form->isSubmitted())
        {
            if($form->isValid()) {
                if($funcController->checkCsrfToken($form->get('token')->getData() , 'admin_resources_list' ))
                {
                    $resource_name = strip_tags(trim($form->get('resource_name')->getData()));
                    $assigned_to = strip_tags(trim($form->get('assigned_to')->getData()));
                    // todo: check duplication

                    $rec_exits = $this->chkRcdRole($resource_name , $assigned_to);

                    $funcController->setCsrfToken('admin_resources_list');
                    $session = new Session();
                    $token = $session->get('admin_resources_list');

                    if($rec_exits == false)
                    {
                        $resource->setStatus(strip_tags(trim($form->get('status')->getData())));
                        $resource->setResourceName(strip_tags(trim($form->get('resource_name')->getData())));
                        $resource->setAssignedTo(strip_tags(trim($form->get('assigned_to')->getData())));
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($resource);
                        $em->flush();
                        if ($resource->getId())
                        {
                            $message = $this->get('translator')->trans('Record Added successfully');
                            return $this->render('admin/cms/resources.html.twig', array(
                                'form' => $form->createView(), 'message' => $message, 'token' => $token,
                                'error_cl' => 'alert-danger',
                            ));

                        }
                        else
                        {
                            $message = $this->get('translator')->trans('Unable to add record');
                            return $this->render('admin/cms/resources.html.twig', array(
                                'form' => $form->createView(), 'message' => $message,
                                'error_cl' => 'alert-danger', 'token' => $token,
                            ));
                        }
                    }
                    else
                    {
                        $funcController->setCsrfToken('admin_resources_list');
                        $session = new Session();
                        $token = $session->get('admin_resources_list');
                        $message = $this->get('translator')->trans('Record already exist');
                        return $this->render('admin/cms/resources.html.twig', array(
                            'form' => $form->createView(), 'message' => $message,
                            'error_cl' => 'alert-danger', 'token' => $token,
                        ));
                    }
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                    //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");
                }
            }
            else
            {


                $funcController->setCsrfToken('admin_resources_list');
                $session = new Session();
                $token = $session->get('admin_resources_list');
                $message = $this->get('translator')->trans('Unable to add record');
                return $this->render('admin/cms/resources.html.twig', array(
                    'form' => $form->createView(), 'message' => $message ,
                    'error_cl' => 'alert-success', 'token' => $token,
                ));
                /*
                $this->get('security.token_storage')->setToken(null);
                $response = new RedirectResponse($this->generateUrl('admin_admin'));
                return $response;
                */
                //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");
            }
        }
        else
        {
            $funcController->setCsrfToken('admin_resources_list');
            $session = new Session();
            $token = $session->get('admin_resources_list');
            //$form->get('token')->setData($token);
            //$request = $this->getRequest();

            return $this->render('admin/cms/resources.html.twig', array(
                'form' => $form->createView(), 'message' => "" ,
                'error_cl' => 'alert-success', 'token' => $token,
            ));
        }
    }


    /**
     * @Route("/admin/resourceslistall/", name="admin_resourcelistall")
     */
    public function resourceListAllAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);
        if(!$funcController->isValidRule('MANAGE_ROLES_VIEW'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        //our data to be encoded
        $em = $this->getDoctrine()->getManager();
        /*$resources = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findAll();*/
        $resources = $this->getDoctrine()
            ->getRepository('AppBundle:Resources')
            ->findAll();
        $data = array();
        $i = 0;
        foreach($resources as $resource)
        {
            // $password_encrypted = $this->Eencrypt($cmspage->getId(), $key);
            $data[$i]['id']            =    $resource->getId();
            $data[$i]['resource_name'] =  $resource->getResourceName();
            $data[$i]['assigned_to']   =  $resource->getAssignedTo();
            $data[$i]['Status']        =  $resource->getStatus();
            $i++;
        }

        if (!$resources)
        {
            return $this->render('admin/cms/resourceslistall.html.twig', array(
                'data' => $data , 'message' => 'No record found' ));
        }
        else
        {
            return $this->render('admin/cms/resourceslistall.html.twig', array(
                'data' => $data ,  'message' => ''   ));
        }
    }



    /**
     * @Route("/admin/resourceslistdelete", name="admin_resourceslistdelete")
     *
     */

    public function resourcesListDeleteAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirectToRoute('admin_admin');
        }
        else
        {
            //return $this->redirectToRoute('admin_admin');
        }
        // delete the record
        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        if(!$funcController->isValidRule('MANAGE_CMS_DELETE')) {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }

        $em = $this->getDoctrine()->getManager();
        echo $page = $request->get('param');


        $res = $em->getRepository('AppBundle:Resources')->find($page);
        $em->remove($res);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('admin_resourcelistall');
    }

    /**
     * @Route("/admin/resourceslistupdate", name="admin_resourceslistupdate")
     *
     */

    public function resourcesListUpdateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_admin');
        }

        $em = $this->getDoctrine()->getManager();

        $funcController = new FunctionsController();
        $funcController->setContainer($this->container);

        $page = $request->get('param');
        if(!$funcController->isValidRule('MANAGE_CMS_EDIT'))
        {
            return $response = new RedirectResponse($this->generateUrl('admin_accessdenied'));
        }


        $resources = $em->getRepository('AppBundle:Resources')
            ->findOneBy(array(  'id' => $page));
        $form = $this->createForm(ResourcesType::class, $resources);
        if(empty($resources))
        {
            $funcController->setCsrfToken('admin_resrce_list_upd');
            $session = new Session();
            $token = $session->get('admin_resrce_list_upd');
            return $this->render('admin/cms/resourcesedit.html.twig', array(
                'form' => $form->createView(),'message' => 'No data found',
                'error_cl' => 'alert-danger', 'token' => $token
            ));
            exit;
        }
        $status = $resources->getStatus();

        /*
         * this set the value field in the form by passing the values retrieved from the data
        $status = $form->get('atitle')->setData("testing1234");
        */
        if($status == 1)
        {
            $status = $form->get('status')->setData(true);
        }
        else
        {
            $status = $form->get('status')->setData(false);
        }

        $form->handleRequest($request);



        // cms_pages__token
        // exit;


        if ($form->isSubmitted())
        {
            $form->get('token')->getData();
            $this->checkCsrfToken($form->get('token')->getData(),'admin_resrce_list_upd');
            if($form->isValid()) {
                if($funcController->checkCsrfToken($form->get('token')->getData(),'admin_resrce_list_upd'))
                {
                    $resource_name = $resources->setResourceName(strip_tags(trim($form->get('resource_name')->getData())));

                    $resources->setStatus(strip_tags(trim($form->get('status')->getData())));

                    $assigned_to = $resources->setAssignedTo(strip_tags(trim($form->get('assigned_to')->getData())));
                    
                    $rec_exits = $this->chkRcdRole($resource_name , $assigned_to , $page);

                    if($rec_exits == false)
                    {
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    $funcController->setCsrfToken('admin_resrce_list_upd');
                    $session = new Session();
                    $token   = $session->get('admin_resrce_list_upd');

                    if ($resources->getId()) {
                        return $this->render('admin/cms/resourcesedit.html.twig', array(
                            'form' => $form->createView(),
                            'message' => $this->get('translator')->trans('Record is updated'),
                            'error_cl' => 'alert-success', 'token' => $token,
                        ));
                    }
                    else
                    {
                        return $this->render('admin/cms/resourcesedit.html.twig', array(
                            'form' => $form->createView(),
                            'message' => $this->get('translator')->trans('Nothing to update'),
                            'error_cl' => 'alert-danger', 'token' => $token,

                        ));
                    }
                }
                    else
                    {
                        return $this->render('admin/cms/resourcesedit.html.twig', array(
                            'form' => $form->createView(),
                            'message' => $this->get('translator')->trans('Nothing to update'),
                            'error_cl' => 'alert-danger', 'token' => $token,

                        ));
                    }
                    
                    
                    
                }
                else
                {
                    $this->get('security.token_storage')->setToken(null);
                    $response = new RedirectResponse($this->generateUrl('admin_admin'));
                    return $response;
                    //return $this->redirect(AppConstant::BASE_URL."/ar/sa/");
                }
            }
            else
            {

                $funcController->setCsrfToken('admin_resrce_list_upd');
                $session = new Session();
                $token = $session->get('admin_resrce_list_upd');
                $this->get('translator')->trans('Nothing to update');

                $message = $this->get('translator')->trans('Unable to update record');
                return $this->render('admin/cms/resourcesedit.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                    'error_cl' => 'alert-danger', 'token' => $token,
                ));
            }
        }
        else
        {
            // $csrf = $this->get('security.csrf.token_manager'); //Symfony\Component\Security\Csrf\CsrfTokenManagerInterface
            // echo $token = $csrf->refreshToken('asdfasdfasd');
            $funcController->setCsrfToken('admin_resrce_list_upd');
            $session = new Session();
            $token = $session->get('admin_resrce_list_upd');

            // $form->get('token')->setData($token);
            //$request = $this->getRequest();
            
            return $this->render('admin/cms/resourcesedit.html.twig', array(
                'form' => $form->createView(),'message' => "",
                'error_cl' => 'alert-success', 'token' => $token ,
            ));
        }

    }










}

?>