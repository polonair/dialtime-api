<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpreadController extends Controller
{
    public function spreadsAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$spreads = $em->getRepository("ModelBundle:Spread")->loadAll();
        return $this->render("AdminBundle:Spread:index.html.twig", ["spreads" => $spreads]);
    }
    public function spreadDetailAction(Request $request)
    {
    	$id = $request->attributes->get("id", null);
    	if ($id !== null)
    	{
	    	$em = $this->get('doctrine')->getManager();
	    	$spread = $em->getRepository("ModelBundle:Spread")->loadOneById($id);
	        return $this->render("AdminBundle:Spread:detail.html.twig", ["spread" => $spread]);    	
	    }
	    else
	    {
	    	return $this->redirectToRoute("admin/spreads");
	    }
    }
}
