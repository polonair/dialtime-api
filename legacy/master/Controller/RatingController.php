<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RatingController extends Controller
{
    public function ratingAction(Request $request)
    {
        return $this->render("MasterBundle:Rating:index.html.twig");
    }
}
