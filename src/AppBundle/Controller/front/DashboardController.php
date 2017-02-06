<?php
namespace AppBundle\Controller\Front;

use AppBundle\AppBundle;
use AppBundle\AppConstant;
use AppBundle\Entity\Category;
use AppBundle\Entity\CategoryTranslation;
use AppBundle\Entity\ContentPages;
use Doctrine\ORM\Mapping\Entity;
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

use AppBundle\Entity\CmsPages;



class DashboardController extends Controller
{
    /**
     * @Route("/{_country}/{_locale}/khan")
     * @param Request $request
     */
    public function khanAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $food = new Category();
        $food->setTitle('Food');
        $food->addTranslation(new CategoryTranslation('lt', 'title', 'Maistas'));

        $fruits = new Category();
        $fruits->setParent($food);
        $fruits->setTitle('Fruits');
        $fruits->addTranslation(new CategoryTranslation('lt', 'title', 'Vaisiai'));
        $fruits->addTranslation(new CategoryTranslation('ru', 'title', 'rus trans'));

        $em->persist($food);
        $em->persist($fruits);
        $em->flush();
        die("item saved");
    }
    /**
     * @Route("/dashboard/admin")
     */
    public function adminAction()
    {
        // url = /admin/index

        return new Response('<html><body>Admin page!</body></html>');
    }

    /**
     * @Route("/dashboard/cmslist", name="cmslist")
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
        ->add('save', SubmitType::class, array('label' => 'Create Post'))
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
     * @Route("/dashboard/cmslistall", name="cmslistall")
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
        }
        else
        {
            return $this->render('admin/cms/cmslistall.html.twig', array(
                'data' => $data));
        }
    }




    /**
     * @Route("/dashboard/cmslistupdate/{page}", name="cmslistupdate")
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
     * @Route("/dashboard/cmslistdelete/{page}", name="cmslistdelete")
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
     * @Route("/dashboard/cmslistview/{page}", name="cmslistview")
     *
     */
    public function cmsListviewAction(Request $request,$page)
    {
        $em = $this->getDoctrine()->getManager();
        $cmsPage = $em->getRepository('AppBundle:CmsPages')->find($page);
        $status = $cmsPage->getStatus();

        return $this->render('admin/cms/cmsview.html.twig', array(
            'form' => $form->createView(),'message' => '',
        ));
    }




}

?>