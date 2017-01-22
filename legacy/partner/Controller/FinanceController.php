<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FinanceController extends Controller
{
    public function financeAction(Request $request)
    {
        return $this->render("PartnerBundle:Finance:index.html.twig");
    }
}
