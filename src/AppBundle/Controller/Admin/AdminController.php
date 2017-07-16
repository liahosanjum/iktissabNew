<?php
namespace AppBundle\Controller\Admin;

use AppBundle\AppConstant;
use AppBundle\Entity\FormSettings;
use AppBundle\Entity\Gallery;
use AppBundle\Entity\Settings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

use AppBundle\Form\SettingsType;
use AppBundle\Form\FormSettingsType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Entity\CmsPages;




class AdminController extends Controller
{
    /**
     * @Route("/admin/admin" , name= "admin_admin")
     */
    public function adminAction()
    {
        // url = /admin/index
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('homepage', array('_country' => 'sa', '_locale' => 'en' ));

        }


        $cmsPage = new CmsPages();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
        // print_r($form);
        $form->handleRequest($request);
        $request->request->get('adesc');
        $cmsData = $form->getData();
        if ($form->isValid() && $form->isSubmitted())
        {
            $cmsPage->setAtitle($form->get('atitle')->getData());
            $cmsPage->setEtitle($form->get('etitle')->getData());
            $cmsPage->setAdesc($form->get('adesc')->getData());
            $cmsPage->setEdesc($form->get('edesc')->getData());
            $cmsPage->setStatus($form->get('status')->getData());
            $cmsPage->setType('cms');
            $em = $this->getDoctrine()->getManager();
            $em->persist($cmsPage);
            $em->flush();
            if($cmsPage->getId()) {
                $message = $this->get('translator')->trans('Record Added successfully');
                return $this->render('admin/cms/cms.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                ));
            }
            //return new Response('Content page added');
        }
        return $this->render('admin/cms/cms.html.twig', array(
            'form' => $form->createView(),'message' => '',
        ));
    }

    /**
     * @Route("/admin/newslist" , name = "admin_news")
     */
    public function newsListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('homepage', array('_country' => 'sa', '_locale' => 'en' ));
        }


        $cmsPage = new CmsPages();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
        // print_r($form);
        $form->handleRequest($request);
        $request->request->get('adesc');
        $cmsData = $form->getData();
        if ($form->isValid() && $form->isSubmitted())
        {
            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $cmsPage->getBrochure();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            // Move the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('images_directory'),
                $fileName
            );

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $cmsPage->setBrochure($fileName);

            $cmsPage->setAtitle($form->get('atitle')->getData());
            $cmsPage->setEtitle($form->get('etitle')->getData());
            $cmsPage->setAdesc($form->get('adesc')->getData());
            $cmsPage->setEdesc($form->get('edesc')->getData());
            $cmsPage->setStatus($form->get('status')->getData());
            $cmsPage->setType('news');
            $em = $this->getDoctrine()->getManager();
            $em->persist($cmsPage);
            $em->flush();
            if($cmsPage->getId()) {
                $message = $this->get('translator')->trans('Record Added successfully');
                return $this->render('admin/news/news.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                ));
            }
            //return new Response('Content page added');
        }
        return $this->render('admin/news/news.html.twig', array(
            'form' => $form->createView(),'message' => '',
        ));
    }




    /**
     * @Route("/admin/cmslistall/", name="cmslistall")
     */
    public function cmsListAllAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
          return $this->redirectToRoute('homepage', array('_country' => 'sa', '_locale' => 'en' ));

        }

        $em = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findBy(array( 'type' => 'cms'));
            //->findAll();
        $data = array();
        $i = 0;
        foreach($cmspages as $cmspage)
        {
            $data[$i]['id']     =  $cmspage->getId();
            $data[$i]['Atitle'] =  $cmspage->getAtitle();
            $data[$i]['Etitle'] =  $cmspage->getEtitle();
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
            return $this->redirectToRoute('homepage', array('_country' => 'sa', '_locale' => 'en' ));
        }

        $em = $this->getDoctrine()->getManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findBy(array( 'type' => 'news'));
        $data = array();
        $i = 0;
        foreach($cmspages as $cmspage)
        {
            $cmspage;
            $data[$i]['id']     =  $cmspages[$i]->getId();
            $data[$i]['Atitle'] =  $cmspages[$i]->getAtitle();
            $data[$i]['Etitle'] =  $cmspages[$i]->getEtitle();
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
     * @Route("/admin/cmslistupdate/{page}", name="cmslistupdate")
     *
     */

    public function cmsListUpdateAction(Request $request,$page)
    {
        $em = $this->getDoctrine()->getManager();
        //$cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        $cmsPage = $em->getRepository('AppBundle:CmsPages')
        ->findOneBy(array( 'type' => 'cms' , 'id' => $page));
        $status = $cmsPage->getStatus();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
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
        $request->get('adesc');
        $cmsData = $form->getData();
        $adesc = $form->get('status')->getData();


        if ($form->isValid() && $form->isSubmitted())
        {
            $cmsPage->setAtitle($form->get('atitle')->getData());
            $cmsPage->setEtitle($form->get('etitle')->getData());
            $cmsPage->setAdesc($form->get('adesc')->getData());
            $cmsPage->setEdesc($form->get('edesc')->getData());
            $cmsPage->setStatus($form->get('status')->getData());
            $em = $this->getDoctrine()->getManager();
            //$em->persist($cmsPage);
            $em->flush();
            if($cmsPage->getId())
            {
                return $this->render('admin/cms/cmsedit.html.twig', array(
                    'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                ));
            }
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            return $this->render('admin/cms/cmsedit.html.twig', array(
                'form' => $form->createView(),'message' => '',
            ));
        }
    }

    /**
     * @Route("/admin/newslistupdate/{page}", name="newslistupdate")
     *
     */

    public function newsListUpdateAction(Request $request,$page)
    {
        $em = $this->getDoctrine()->getManager();
        //$cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);

        $cmsPage = $em->getRepository('AppBundle:CmsPages')
        ->findOneBy(array( 'type' => 'news' , 'id' => $page));
        $status = $cmsPage->getStatus();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
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
        $request->get('adesc');
        $cmsData = $form->getData();
        $adesc = $form->get('status')->getData();


        if ($form->isValid() && $form->isSubmitted())
        {
            $Atitle  = $form->get('atitle')->getData();
            $Etitle  = $form->get('etitle')->getData();
            $Adesc   = $form->get('adesc')->getData();
            $Edesc   = $form->get('edesc')->getData();
            $status  = $form->get('status')->getData();
            $country = $form->get('country')->getData();
            $type    = 'news';

            $em = $this->getDoctrine()->getManager();
            //$em->persist($cmsPage);
            if($form->get('brochure')->getData() ) {
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
                $data_values = array($Adesc , $Edesc ,
                    $Atitle , $Etitle  , $status , $country , $type , $fileName  , $page  );

                $stm = $conn->executeUpdate('UPDATE cms_pages SET  adesc= ?  , edesc= ? ,
                atitle = ? , etitle= ?  , status = ?  , country = ? , type = ?  , brochure = ? 
                 
                WHERE id = ?   ', $data_values);

                $stm;
                if($stm == 1){
                    return $this->render('admin/news/newsedit.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                    ));
                }else
                {
                    return $this->render('admin/news/newsedit.html.twig', array(
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
                $data_values = array($Adesc , $Edesc ,
                    $Atitle , $Etitle  , $status , $country , $type , $page  );

                $stm = $conn->executeUpdate('UPDATE cms_pages SET  adesc= ?  , edesc= ? ,
                atitle = ? , etitle= ?  , status = ?  , country = ? , type = ?  
                 
                WHERE id = ?   ', $data_values);

                $stm;
                if($stm == 1){
                    return $this->render('admin/news/newsedit.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                    ));
                }else
                {
                    return $this->render('admin/news/newsedit.html.twig', array(
                        'form' => $form->createView(),'message' => $this->get('translator')->trans('Nothing to update'),
                    ));
                }



            }





            $em->flush();
            if($cmsPage->getId())
            {
                return $this->render('admin/news/newsedit.html.twig', array(
                    'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                ));
            }
        }
        else
        {



            return $this->render('admin/news/newsedit.html.twig', array(
                'form' => $form->createView(),'message' => '',
            ));
        }
    }


    /**
     * @Route("/admin/gallerylistupdate/{page}", name="gallerylistupdate")
     *
     */

    public function galleryListUpdateAction(Request $request,$page)
    {
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
        // delete the record
        $em = $this->getDoctrine()->getManager();
        $galleryImage = $em->getRepository('AppBundle:Gallery')->find($page);
        $em->remove($galleryImage);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('imageslistall');
    }



    /**
     * @Route("/admin/cmslistdelete/{page}", name="cmslistdelete")
     *
     */

    public function cmsListDeleteAction(Request $request,$page)
    {
        // delete the record
        $em = $this->getDoctrine()->getManager();
        $cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        $em->remove($cmsPage);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('cmslistall');

    }

    /**
     * @Route("/admin/newslistdelete/{page}", name="newslistdelete")
     *
     */

    public function newsListDeleteAction(Request $request,$page)
    {
        // delete the record
        $em = $this->getDoctrine()->getManager();
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
        $gallery = new Gallery();

        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
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
        $settings = new Settings();
        // echo '====='.$cmsPage->getAdesc();
        $form = $this->createForm(SettingsType::class, $settings);
        // print_r($form);
        $form->handleRequest($request);
        // $request->request->get('adesc');
        $cmsData = $form->getData();
        // echo $adesc = $form->getAdesc('adesc')->getData();
        if ($form->isValid() && $form->isSubmitted())
        {
            $settings->setEmail($form->get('email')->getData());
            $settings->setType($form->get('type')->getData());
            $settings->setCountry($form->get('country')->getData());
            $settings->setOther($form->get('technical')->getData());
            $settings->setOther($form->get('other')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($settings);
            $em->flush();
            if($settings->getId())
            {
                $message = $this->get('translator')->trans('Record Added successfully');
                return $this->render('admin/settings/settings.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                ));
            }
        }
        return $this->render('admin/settings/settings.html.twig',
        array(
            'form' => $form->createView(),'message' => '',
        ));
    }

    /**
     * @Route("/admin/settingsupdate/{id}", name="settingsupdate")
     *
     */

    public function settingsUpdateAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('AppBundle:Settings')->find($id);
        $type = $settings->getType();
        $form = $this->createForm(SettingsType::class, $settings);
        $form->handleRequest($request);
        $settingsData = $form->getData();
        $type = $form->get('type')->getData();
        if($form->isValid() && $form->isSubmitted())
        {
            $settingsData->setEmail($form->get('email')->getData());
            $settingsData->setType($form->get('type')->getData());
            $settingsData->setTechnical($form->get('technical')->getData());
            $settingsData->setOther($form->get('other')->getData());
            $settingsData->setCountry($form->get('country')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if($settingsData->getId())
            {
                return $this->render('admin/settings/settingsedit.html.twig', array(
                    'form' => $form->createView(),'message' => $this->get('translator')->trans('Record is updated'),
                ));
            }
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            return $this->render('admin/settings/settingsedit.html.twig', array(
                'form' => $form->createView(),'message' => '',
            ));
        }
    }

    /**
     * @Route("/admin/settingslist/", name="settingslist")
     */
    public function settingsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $settingsList = $this->getDoctrine()
            ->getRepository('AppBundle:Settings')
            ->findAll();
        $data = array();
        $i = 0;
        foreach($settingsList as $list)
        {
            $data[$i]['Id']     =  $settingsList[$i]->getId();
            $data[$i]['Email'] =  $settingsList[$i]->getEmail();
            $data[$i]['Type'] =  $settingsList[$i]->getType();
            $data[$i]['Technical'] =  $settingsList[$i]->getTechnical();
            $data[$i]['Other'] =  $settingsList[$i]->getOther();
            // country id is sa and eg
            if($settingsList[$i]->getCountry() == 'sa')
            {
                $data[$i]['Country'] =  "Saudi Arabia";
            }
            else
            {
                $data[$i]['Country'] =  "Egypt";
            }

            $i++;
        }
        return $this->render('admin/settings/settingslistall.html.twig', array(
        'data' => $data));

    }


    /**
     * @Route("/admin/settingsdelete/{id}", name="settingsdelete")
     *
     */

    public function settingsDeleteAction(Request $request,$id)
    {
        // delete the record
        $em = $this->getDoctrine()->getManager();
        $settingDel = $em->getRepository('AppBundle:Settings')->find($id);
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
        $displayList = $this->getDoctrine()
            ->getRepository('AppBundle:FormSettings')
            ->findAll();
        $data = array();
        $i = 0;
        //exit;
        foreach($displayList as $list)
        {
            $data[$i]['Id']          =  $displayList[$i]->getId();
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
        $displaysettings = new FormSettings();
        $form = $this->createForm(FormSettingsType::class, $displaysettings);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted())
        {
            $displaysettings->setStatus($form->get('status')->getData());
            $displaysettings->setFormType($form->get('formtype')->getData());
            $displaysettings->setCountry($form->get('country')->getData());
            $displaysettings->setSubmissions($form->get('submissions')->getData());
            $displaysettings->setLimitto($form->get('limitto')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($displaysettings);
            $em->flush();
            if($displaysettings->getId())
            {
                $message = 'Record Added successfully';
                return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
                    'form' => $form->createView(),'message' =>  $message ,
                ));
            }
        }
        return $this->render('admin/settings/addformdisplaysettings.html.twig', array(
            'form' => $form->createView(),'message' => '',
        ));
    }



    /**
     * @Route("/admin/formdisplaysettingsupdate/{id}", name="admin_formdisplaysettingsupdate")
     *
     */

    public function formDisplaySettingsUpdateAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('AppBundle:FormSettings')->find($id);
        $form = $this->createForm(FormSettingsType::class, $settings);
        $form->handleRequest($request);
        $settingsData = $form->getData();
        $type = $form->get('formtype')->getData();
        if($form->isValid() && $form->isSubmitted())
        {
            $settingsData->setStatus($form->get('status')->getData());
            $settingsData->setFormType($form->get('formtype')->getData());
            $settingsData->setCountry($form->get('country')->getData());
            $settingsData->setSubmissions($form->get('submissions')->getData());
            $settingsData->setLimitto($form->get('limitto')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            if($settingsData->getId())
            {
                $message = $this->get('translator')->trans('Record is updated');
                return $this->redirectToRoute('admin_formdisplaysettings' , array('message'=> $message ) );
            }
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            return $this->render('admin/settings/updateformdisplaysettings.html.twig', array(
                'form' => $form->createView(),'message' => '',
            ));
        }
    }

    /**
     * @Route("/admin/formdisplaysettingsdelete/{id}", name="admin_formdisplaysettingsdelete")
     *
     */

    public function formDisplaySettingsDeleteAction(Request $request,$id)
    {
        // delete the record
        $em = $this->getDoctrine()->getManager();
        $settingDel = $em->getRepository('AppBundle:FormSettings')->find($id);
        $em->remove($settingDel);
        $em->flush();
        // route to listing
        return $this->redirectToRoute('admin_formdisplaysettings');

    }



}

?>