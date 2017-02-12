<?php
namespace AppBundle\Controller\Admin;

use AppBundle\AppConstant;
use AppBundle\Entity\Settings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
use AppBundle\Form\SettingsType;

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
            return $this->redirectToRoute('homepage');
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
        // url = /admin/cmslist
        // echo "get all cms listing";
        /*
            $cms  = new CmsPagesType();
            $form = $this->createForm(CmsPagesType::class, $cms, array(
                'additional'  => array(
                    'locale'  => $request->getLocale(),
                    'country' => $request->get('_country'))
            ));
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cms);
                $em->flush();
                return $this->redirect($this->generateUrl(
                    'cmslist',
                    array('id' => $cms->getId())
                ));
            }
            return $this->render('admin/cms/cms.html.twig',
                array('iktData' => "1234")
            );
        */

        // echo $this->get('translator')->trans('lang-A');

        $cmsPage = new CmsPages();
        /*
        $cmsPage->setAtitle('test page name ar');
        $cmsPage->setEtitle('test page name en');
        $cmsPage->setAdesc('test page name ar');
        $cmsPage->setEdesc('test page name en');
        $cmsPage->setStatus(1);
        */
        // $title = $this->get('translator')->trans('lang-A');

        /*
        $form = $this->createFormBuilder($cmsPage)
            ->add('atitle' , TextType::class, array('label' => 'Title Arabic'))
        ->add('etitle' , TextType::class, array('label' => 'Title English','required' => true))
        ->add('edesc'  , TextType::class, array('label' => 'Title English'))
        ->add('adesc'  , TextType::class, array('label' => 'Title English'))
        ->add('save', SubmitType::class, array('label'  => 'Create Post'))
        ->getForm();
        */

        //echo '====='.$cmsPage->getAdesc();
        $form = $this->createForm(CmsPagesType::class, $cmsPage);
        // print_r($form);
        $form->handleRequest($request);
        $request->request->get('adesc');
        $cmsData = $form->getData();
        //echo $adesc = $form->getAdesc('adesc')->getData();
        if ($form->isValid() && $form->isSubmitted())
        {
            $cmsPage->setAtitle($form->get('atitle')->getData());
            $cmsPage->setEtitle($form->get('etitle')->getData());
            $cmsPage->setAdesc($form->get('adesc')->getData());
            $cmsPage->setEdesc($form->get('edesc')->getData());
            $cmsPage->setStatus($form->get('status')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($cmsPage);
            $em->flush();
            if($cmsPage->getId())
            {
                $message = $this->get('translator')->trans('Record Added successfully');
                return $this->render('admin/cms/cms.html.twig', array(
                    'form' => $form->createView(),'message' => $message,
                ));
            }
            return new Response('Saved new product with id'.$cmsPage->getId());
        }
        return $this->render('admin/cms/cms.html.twig', array(
            'form' => $form->createView(),'message' => '',
        ));


        /* $em = $this->getDoctrine()->getManager();
        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($cmsPage);
        // actually executes the queries (i.e. the INSERT query)
        $em->flush();
        return new Response('Saved new product with id '.$cmsPage->getId());*/
    }

    /**
     * @Route("/admin/cmslistall/", name="cmslistall")
     */
    public function cmsListAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $cmspages = $this->getDoctrine()
            ->getRepository('AppBundle:CmsPages')
            ->findAll();
        $data = array();
        $i = 0;
        foreach($cmspages as $cmspage)
        {
            $data[$i]['id']     =  $cmspages[$i]->getId();
            $data[$i]['Atitle'] =  $cmspages[$i]->getAtitle();
            $data[$i]['Etitle'] =  $cmspages[$i]->getEtitle();
            $data[$i]['Status'] =  $cmspages[$i]->getStatus();
            $i++;
        }

        if (!$cmspages)
        {
            throw $this->createNotFoundException(
                'No page found '
            );
            return $this->render('admin/cms/cmslistall.html.twig', array(
                'data' => $data));
        }
        else
        {
            return $this->render('admin/cms/cmslistall.html.twig', array(
                'data' => $data));
        }
    }




    /**
     * @Route("/admin/cmslistupdate/{page}", name="cmslistupdate")
     *
     */

    public function cmsListUpdateAction(Request $request,$page)
    {
        $em = $this->getDoctrine()->getManager();
        $cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
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
                $message = $this->get('translator')->trans('Record Added successfully');
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
     * @Route("/admin/uploadfile/", name="uploadfile")
     *
     */
    public function uploadFileAction(Request $request)
    {

        return $this->render('admin/cms/upload.html.twig');
    }


    /**
     * @Route("/admin/settings/", name="admin_settings")
     *
     */
    public function settingsAction(Request $request)
    {
        // url = /admin/cmslist
        // echo "get all cms listing";
        /*
            $cms  = new CmsPagesType();
            $form = $this->createForm(CmsPagesType::class, $cms, array(
                'additional'  => array(
                    'locale'  => $request->getLocale(),
                    'country' => $request->get('_country'))
            ));
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cms);
                $em->flush();
                return $this->redirect($this->generateUrl(
                    'cmslist',
                    array('id' => $cms->getId())
                ));
            }
            return $this->render('admin/cms/cms.html.twig',
                array('iktData' => "1234")
            );
        */

        // echo $this->get('translator')->trans('lang-A');

        $settings = new Settings();
        /*
        $cmsPage->setAtitle('test page name ar');
        $cmsPage->setEtitle('test page name en');
        $cmsPage->setAdesc('test page name ar');
        $cmsPage->setEdesc('test page name en');
        $cmsPage->setStatus(1);
        */
        // $title = $this->get('translator')->trans('lang-A');

        /*
        $form = $this->createFormBuilder($cmsPage)
            ->add('atitle' , TextType::class, array('label' => 'Title Arabic'))
        ->add('etitle' , TextType::class, array('label' => 'Title English','required' => true))
        ->add('edesc'  , TextType::class, array('label' => 'Title English'))
        ->add('adesc'  , TextType::class, array('label' => 'Title English'))
        ->add('save', SubmitType::class, array('label'  => 'Create Post'))
        ->getForm();
        */

        //echo '====='.$cmsPage->getAdesc();
        $form = $this->createForm(SettingsType::class, $settings);
        // print_r($form);
        $form->handleRequest($request);
        // $request->request->get('adesc');
        $cmsData = $form->getData();
        // echo $adesc = $form->getAdesc('adesc')->getData();
        if ($form->isValid() && $form->isSubmitted())
        {
            //$file = $settings->getBrochure();
            // Generate a unique name for the file before saving it
            // $fileName = md5(uniqid()).'.'.$file->guessExtension();
            // Move the file to the directory where brochures are stored
            // $file->move(
              //  $this->getParameter('brochures_directory'),
              //  $fileName
            ///);


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
        return $this->render('admin/settings/settings.html.twig', array(
            'form' => $form->createView(),'message' => '',
        ));


        /* $em = $this->getDoctrine()->getManager();
        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($cmsPage);
        // actually executes the queries (i.e. the INSERT query)
        $em->flush();
        return new Response('Saved new product with id '.$cmsPage->getId());*/
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
                $message = $this->get('translator')->trans('Record Added successfully');
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
            if($settingsList[$i]->getCountry() == 1)
            {
                $data[$i]['Country'] =  "Saudi Arabia";
            }
            else
            {
                $data[$i]['Country'] =  "Egypt";
            }

            $i++;
        }

        if (!$list)
        {
            throw $this->createNotFoundException('No page found');
        }
        else
        {
            return $this->render('admin/settings/settingslistall.html.twig', array(
                'data' => $data));
        }
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







}

?>