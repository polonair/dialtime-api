<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        return $this->render("AdminBundle:Security:login.html.twig");
    }
    public function loginCheckAction() { }
}
