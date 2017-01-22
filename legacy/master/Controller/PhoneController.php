<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Polonairs\Dialtime\ModelBundle\Entity\Phone;

class PhoneController extends Controller
{
    public function phonesAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $phones = $em->getRepository("ModelBundle:Phone")->loadByOwner($this->getUser()->getUser());
        return $this->render("MasterBundle:Settings:phones.html.twig", ["phones" => $phones]);
    }
    public function phoneAddAction(Request $request)
    {
    	$phone = $request->request->get('phone', null);
    	if ($phone !== null)
    	{
        	$em = $this->get('doctrine')->getManager();
        	$p = (new Phone())
        		->setNumber($phone)
        		->setOwner($this->getUser()->getUser());
        	$em->persist($p);
        	$em->flush();
    	}
    	return $this->redirectToRoute("master/phones");
    }
}