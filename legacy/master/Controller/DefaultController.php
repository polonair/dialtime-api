<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function pageAction(Request $request)
    {
        return $this->render('MasterBundle:Default:page.html.twig', []);
    }
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('MasterBundle:Default:landingPage.html.twig', []);
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'domain' => $this->container->getParameter('master_host'),
        ]);
    }
    public function dashboardAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $master = $this->getUser();
        $user = $master->getUser();

        $offers = $em->getRepository("ModelBundle:Offer")->loadAllForMaster($master);
        $categories = $em->getRepository("ModelBundle:Category")->findAll();
        $locations = $em->getRepository("ModelBundle:Location")->findAll();
        $phones = $em->getRepository("ModelBundle:Phone")->loadByOwner($user);
        $schedules = $em->getRepository("ModelBundle:Schedule")->loadByOwner($user);
        $routes = $em->getRepository("ModelBundle:Route")->loadByMaster($master);

        return $this->render("MasterBundle:Default:dashboard.html.twig", 
            ["categories"=>$categories, 
            "locations"=>$locations,
            "phones"=>$phones,
            "schedules" => $schedules,
            "offers" => $offers,
            "routes" => $routes]);
    }
    public function aboutAction(Request $request)
    {
        return $this->render("MasterBundle:Default:about.html.twig");
    }
}
