<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends Controller
{
    public function settingsAction(Request $request)
    {
        return $this->render("PartnerBundle:Settings:index.html.twig");
    }
}
