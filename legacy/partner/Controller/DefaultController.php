<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'domain' => $this->container->getParameter('partner_host'),
        ]);
    }
    public function dashboardAction(Request $request)
    {
        $campaigns = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository("ModelBundle:Campaign")
            ->loadActiveForPartner($this->getUser());
        return $this->render(
            "PartnerBundle:Default:dashboard.html.twig", 
            ["campaigns" => $campaigns]);
    }
    public function aboutAction(Request $request)
    {
        return $this->render("PartnerBundle:Default:about.html.twig");
    }
}
