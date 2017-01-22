<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute("admin/dashboard");
    }
    public function dashboardAction(Request $request)
    {
        return $this->render("AdminBundle:Default:dashboard.html.twig");
    }
}
