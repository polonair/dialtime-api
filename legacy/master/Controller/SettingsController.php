<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends Controller
{
    public function settingsAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $master = $this->getUser();
        $user = $master->getUser();
        $schedules = $em->getRepository("ModelBundle:Schedule")->loadByOwner($user);
        $phones = $em->getRepository("ModelBundle:Phone")->loadByOwner($user);

        return $this->render("MasterBundle:Settings:index.html.twig", [
        	"user" => $user, 
        	"schedules" => $schedules,
        	"phones" => $phones]);
    }
}
