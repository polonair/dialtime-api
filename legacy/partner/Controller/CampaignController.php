<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Campaign;
use Polonairs\Dialtime\ModelBundle\Entity\Ticket;
use Polonairs\Dialtime\ModelBundle\Entity\DongleDemanding;

class CampaignController extends Controller
{
    public function demandCampaignAction(Request $request)
    {
        $oid = $request->attributes->get('id', null);
        if ($oid !== null)
        {
            $em = $this->get('doctrine')->getManager();

            $campaign = $em->getRepository("ModelBundle:Campaign")->loadOneById($oid, $this->getUser());

            if($campaign->getState() !== Campaign::STATE_WAIT)
            {
                $ticket = (new Ticket())
                    ->setTheme("Запрос номера")
                    ->setClient($this->getUser()->getUser());
                $demanding = (new DongleDemanding())
                    ->setTicket($ticket)
                    ->setCampaign($campaign);
                $em->persist($ticket);
                $em->persist($demanding);
            }

            if($campaign->getState() === Campaign::STATE_DRAFT) 
                $campaign->setState(Campaign::STATE_WAIT);

            $em->flush();
            //return new Response("");
        }
        return $this->redirectToRoute("partner/dashboard");
    }
    public function viewCampaignAction(Request $request)
    {
        $oid = $request->attributes->get('id', null);
        $location = $request->query->get('location', null);
        $category = $request->query->get('category', null);
        $state = $request->query->get('state', null);
        $bid = $request->query->get('bid', null);
        if ($oid !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $campaign = $em->getRepository("ModelBundle:Campaign")->find($oid);
            $dongles = $em->getRepository("ModelBundle:Dongle")->loadAllForCampaign($campaign);
            $demandings = $em->getRepository("ModelBundle:DongleDemanding")->loadAllForCampaign($campaign);
            return $this->render("PartnerBundle:Campaign:view.campaign.html.twig", [
                "campaign" => $campaign, 
                "dongles" => $dongles, 
                "demandings" => $demandings]);
        }
        return $this->redirectToRoute("partner/campaigns");
    }
    public function changeCampaignAction(Request $request)
    {
        $oid = $request->attributes->get('id', null);
        $location = $request->query->get('location', null);
        $category = $request->query->get('category', null);
        $state = $request->query->get('state', null);
        $bid = $request->query->get('bid', null);
        if ($oid !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $campaign = $em->getRepository("ModelBundle:Campaign")->find($oid);
            if ($location !== null) 
            { 
                $location = $em->getRepository("ModelBundle:Location")->find($location); 
                $campaign->setLocation($location); 
            }
            if ($category !== null) 
            { 
                $category = $em->getRepository("ModelBundle:Category")->find($category); 
                $campaign->setCategory($category); 
            }
            if ($state !== null) $campaign->setState($state);
            if ($bid !== null) $campaign->setBid($bid);
            $em->flush();
        }
        return $this->redirectToRoute("partner/campaigns");
    }
    public function addCampaignAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$partner = $this->getUser();

    	if($request->isMethod("POST"))
    	{
            $em->getConnection()->beginTransaction();
            
    		$category = $em->getRepository("ModelBundle:Category")->find($request->get("category"));
    		$location = $em->getRepository("ModelBundle:Location")->find($request->get("location"));
    		$state = $request->get("state");
    		$bid = $request->get("bid");

            $campaign = (new Campaign())
            	->setOwner($partner)
            	->setCategory($category)
            	->setLocation($location)
            	->setState("edit")
            	->setBid($bid);
            $em->persist($campaign);
            $em->flush();

            $em->getConnection()->commit();

    		return $this->redirectToRoute("partner/dashboard");
    	}
	    else
	    {
	    	$categories = $em->getRepository("ModelBundle:Category")->findAll();
	    	$locations = $em->getRepository("ModelBundle:Location")->findAll();

        	return $this->render("PartnerBundle:Campaign:add.campaign.html.twig", ["categories"=>$categories, "locations"=>$locations]);
	    }
    }
    public function dashboardAction(Request $request)
    {
        $campaigns = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository("ModelBundle:Campaign")
            ->loadActiveForPartner($this->getUser());
        return $this->render(
            "PartnerBundle:Campaign:index.html.twig", 
            ["campaigns" => $campaigns]);
    }
}
