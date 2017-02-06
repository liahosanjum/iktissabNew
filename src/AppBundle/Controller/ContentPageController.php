<?php

namespace AppBundle\Controller;


use AppBundle\Entity\ContentPage;

use AppBundle\Entity\ContentPageTranslation;
use AppBundle\Form\ContentPageType;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentPageController extends Controller
{
    /**
     * @Route("/content/add", name="admin_content_add")
     * @param Request $request
     * @return Response
     */
    public function addContentAction(Request $request)
    {
        $contentPage = new ContentPage();
        $contentPage->getPages()->add(new ContentPageTranslation());
        $form = $this->createForm(ContentPageType::class, $contentPage);



        return $this->render(':admin/content:add-content.html.twig', array('form'=>$form->createView()));


    }

    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }
}
