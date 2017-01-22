<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HelpController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->render("PartnerBundle:Help:index.html.twig");
    }
}
