<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Polonairs\Dialtime\ModelBundle\Entity\Offer;

class OfferController extends Controller
{
    public function changeOfferAction(Request $request)
    {
        $oid = $request->attributes->get('id', null);
        $state = $request->query->get('state', null);
        $ask = $request->query->get('ask', null);
        if ($oid !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $offer = $em->getRepository("ModelBundle:Offer")->find($oid);
            if ($state !== null) $offer->setState($state);
            if ($ask !== null) $offer->setAsk($ask);
            $em->flush();
        }
        return $this->redirectToRoute("master/dashboard");
    }
    public function addOfferAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$master = $this->getUser();

    	if($request->isMethod("POST"))
    	{
            $em->getConnection()->beginTransaction();
            
    		$category = $em->getRepository("ModelBundle:Category")->find($request->get("category"));
    		$phone = $em->getRepository("ModelBundle:Phone")->find($request->get("phone"));
    		$location = $em->getRepository("ModelBundle:Location")->find($request->get("location"));
            $schedule = $em->getRepository("ModelBundle:Schedule")->find($request->get("schedule"));
    		$state = $request->get("state");
    		$ask = $request->get("ask");

            $offer = (new Offer())
            	->setOwner($master)
            	->setPhone($phone)
            	->setCategory($category)
            	->setLocation($location)
                ->setSchedule( $schedule)
            	->setState(Offer::STATE_ON)
            	->setAsk($ask);
            $em->persist($offer);
            $em->flush();

            $em->getConnection()->commit();

            return $this->redirectToRoute("master/dashboard");
            return $this->redirectToRoute("master/offers");
    	}
	    else
	    {

            $em = $this->get('doctrine')->getManager();
            $master = $this->getUser();

            $offers = $em->getRepository("ModelBundle:Offer")->loadAllForMaster($this->getUser());
            $categories = $em->getRepository("ModelBundle:Category")->findAll();
            $locations = $em->getRepository("ModelBundle:Location")->findAll();
            $phones = $em->getRepository("ModelBundle:Phone")->loadByOwner($master->getUser());
            $schedules = $em->getRepository("ModelBundle:Schedule")->loadByOwner($this->getUser()->getUser());

            return $this->render("MasterBundle:Offer:add.html.twig", 
                ["categories"=>$categories, 
                "locations"=>$locations,
                "phones"=>$phones,
                "schedules" => $schedules,
                "offers" => $offers]);
            return $this->redirectToRoute("master/offers");
	    }
    }
    public function offersAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $master = $this->getUser();

        $offers = $em->getRepository("ModelBundle:Offer")->loadAllForMaster($this->getUser());
        $categories = $em->getRepository("ModelBundle:Category")->findAll();
        $locations = $em->getRepository("ModelBundle:Location")->findAll();
        $phones = $em->getRepository("ModelBundle:Phone")->loadByOwner($master->getUser());
        $schedules = $em->getRepository("ModelBundle:Schedule")->loadByOwner($this->getUser()->getUser());

        return $this->render("MasterBundle:Offer:index.html.twig", 
            ["categories"=>$categories, 
            "locations"=>$locations,
            "phones"=>$phones,
            "schedules" => $schedules,
            "offers" => $offers]);
    }
}
