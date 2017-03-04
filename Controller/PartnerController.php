<?php

namespace Polonairs\Dialtime\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Session;  
use Polonairs\Dialtime\ModelBundle\Entity\Campaign;
use Polonairs\Dialtime\ModelBundle\Entity\Ticket;
use Polonairs\Dialtime\ModelBundle\Entity\DongleDemanding;

class PartnerController extends Controller
{
    private $session = null;

    public function pageAction(Request $request)
    {
        return $this->render('ApiBundle::partner.html.twig', []);
    }
    public function apiAction(Request $rq)
    {
        /*return $this->get('dialtime.api.processor')->init('PARTNER')->getResponse();
        return $this->get('dialtime.api.processor')->getResponse('PARTNER');
        $processor;
        $requests = $processor->getApiRequests();
        foreach($requests as $request)
        {
            $processor->pushResponse($this->getResult2());

        }*/

        $auth_key = $rq->headers->get("x-tc-authkey");

        $em = $this->get('doctrine')->getManager();
        $this->session = $em->getRepository("ModelBundle:Session")->loadSession($auth_key, 'PARTNER');
        $result = [];
        $request = json_decode($rq->getContent(), true);
        if (array_key_exists("action", $request)) $result = $this->getResult($request, $rq);
        elseif (array_key_exists("rqid", $request[0]))
        {
            foreach ($request as $r) 
            {
                $result[] = ["rqid" => $r["rqid"], "response" => $this->getResult($r["request"], $rq)];
            }
        }

        return new JsonResponse($result);
    }
    private function getResult($request, $rq)
    {
        $result = [];
        switch($request["action"])
        {
            case "login": $result = $this->login($request, $rq); break;
            case "is.logged.in": $result = $this->isLoggedIn($request, $rq); break;
            case "logout": $result = $this->logout($request, $rq); break;
            case "campaign.get": $result = $this->campaign_get($request, $rq); break;
            case "check": $result = $this->check($request, $rq); break;
            case "call.get": $result = $this->call_get($request, $rq); break;
            case "location.get": $result = $this->location_get($request, $rq); break;
            case "category.get": $result = $this->category_get($request, $rq); break;
            case "campaign.create": $result = $this->campaign_create($request, $rq); break;
            case "dongle.get": $result = $this->dongle_get($request, $rq); break;
            case "demanding.get": $result = $this->demanding_get($request, $rq); break;
            case "demand": $result = $this->demand($request, $rq); break;
            case "message.get": $result = $this->message_get($request, $rq); break;
            case "partner.get": $result = $this->partner_get($request, $rq); break;
            case "route.get": $result = $this->route_get($request, $rq); break;
            case "ticket.get": $result = $this->ticket_get($request, $rq); break;
            case "transaction.get": $result = $this->transaction_get($request, $rq); break;
            default: break;
        }
        return $result;
    }
    private function message_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            //$entry = $em->getRepository("ModelBundle:Dongle")->loadOneForPartner($partner, $request["data"]);
            $result = [ ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];  
    }
    private function partner_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            //$entry = $em->getRepository("ModelBundle:Dongle")->loadOneForPartner($partner, $request["data"]);
            $result = [ ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];  
    }
    private function route_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            //$entry = $em->getRepository("ModelBundle:Dongle")->loadOneForPartner($partner, $request["data"]);
            $result = [ ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];  
    }
    private function ticket_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            //$entry = $em->getRepository("ModelBundle:Dongle")->loadOneForPartner($partner, $request["data"]);
            $result = [ ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];  
    }
    private function transaction_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            //$entry = $em->getRepository("ModelBundle:Transaction")->loadOneForPartner($partner, $request["data"]);
            $result = [ ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];  
    }
    private function demand($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            $campaign = $em->getRepository("ModelBundle:Campaign")->loadOneForPartner($partner, $request["data"]);

            $ticket = (new Ticket())
                ->setTheme("Запрос номера")
                ->setClient($user);
            $demanding = (new DongleDemanding())
                ->setTicket($ticket)
                ->setCampaign($campaign);
            $em->persist($ticket);
            $em->persist($demanding);
            $em->flush();

            return [ "result" => "ok" ];
        }
        return [];    
    }
    private function dongle_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            $entry = $em->getRepository("ModelBundle:Dongle")->loadOneForPartner($partner, $request["data"]);
            dump($entry);
            $result = [
                "id" => $entry->getId(),
                "number" => $entry->getNumber()
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];        
    }
    private function demanding_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            $entry = $em->getRepository("ModelBundle:DongleDemanding")->loadOneForPartner($partner, $request["data"]);
            dump($entry);
            $result = [
                "id" => $entry->getId(),
                "state" => $entry->getState()
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function campaign_create($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);


            $em->getConnection()->beginTransaction();
            
            $category = $em->getRepository("ModelBundle:Category")->find($request["data"]["category"]);
            $location = $em->getRepository("ModelBundle:Location")->find($request["data"]["location"]);
            $bid = $request["data"]["ask"];

            $campaign = (new Campaign())
                ->setOwner($partner)
                ->setCategory($category)
                ->setLocation($location)
                ->setState("edit")
                ->setBid($bid);
            $em->persist($campaign);
            $em->flush();

            $em->getConnection()->commit();    
            return [ "result" => "ok" ];
        }
        return [];
    }
    private function call_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            $entry = $em->getRepository("ModelBundle:Call")->loadOneForPartner($partner, $request["data"]);
            dump($entry);
            $result = [
                "id" => $entry->getId(),
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function campaign_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            $campaign = $em->getRepository("ModelBundle:Campaign")->loadOneForPartner($partner, $request["data"]);
            $result = [
                "id" => $campaign->getId(),
                "category" => $campaign->getCategory()->getId(),
                "location" => $campaign->getLocation()->getId(),
                "dongles" => $em->getRepository("ModelBundle:Dongle")->loadAllIdsForCampaign($campaign),
                "active_demandings" => $em->getRepository("ModelBundle:DongleDemanding")->loadAllIdsForCampaign($campaign),
                "bid" => $campaign->getBid(),
            ];


            return  [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function check($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $partner = $em->getRepository("ModelBundle:Partner")->loadPartnerByUser($user);

            $result = [
                "counter" => time() - 3600, // '- 3600' - only for local system!
                "entities" => [
                    "campaign" => $em->getRepository("ModelBundle:Campaign")->loadAllIdsForPartner($partner, $request["data"]),
                    //"partner" => $em->getRepository("ModelBundle:Partner")->loadAllIdsForPartner($partner, $request["data"]),
                    "route" => $em->getRepository("ModelBundle:Route")->loadAllIdsForPartner($partner, $request["data"]),
                    "ticket" => $em->getRepository("ModelBundle:Ticket")->loadAllIdsForPartner($partner, $request["data"]),
                    "transaction" => $em->getRepository("ModelBundle:Transaction")->loadAllIdsForPartner($partner, $request["data"]),
                ]
            ];

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function login($request, $rq)
    {
        $em = $this->get('doctrine')->getManager();

        $master = $em->getRepository("ModelBundle:Partner")
            ->loadUser($request["data"]["username"], $request["data"]["password"]);

        if ($master != null)
        {
            $this->session = new Session();
            $this->session
                ->setRealm('PARTNER')
                ->setOwner($master->getUser());
            $em->persist($this->session);
            $em->flush();
            return [ "result" => "ok", "data" => $this->session->getId() ];
        }
        return [ "result" => "fail" ];
    }
    private function isLoggedIn($request, $rq)
    {
        if ($this->session !== null) return [ "result" => "yes" ];
        return [ "result" => "no" ];
    }
    private function logout($request, $rq)
    {
        $auth_key = $rq->headers->get("x-tc-authkey");

        $em = $this->get('doctrine')->getManager();
        $session = $em->getRepository("ModelBundle:Session")->loadSession($auth_key);
        if ($session)
        {
            $session->close();
            $em->flush();
        }

        return [ "result" => "ok" ];
    }
    private function location_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $location = $em->getRepository("ModelBundle:Location")->loadOne($request["data"]);
            $children = $em->getRepository("ModelBundle:Location")->loadChildrenFor($location);
            $result = [
                "id"    => $location->getId(),
                "name" => $location->getName(),
                "locative" => $location->getLocative(),
                "children" => [],
            ];
            foreach ($children as $child) $result["children"][] = $child->getId();

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function category_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $category = $em->getRepository("ModelBundle:Category")->loadOne($request["data"]);
            $children = $em->getRepository("ModelBundle:Category")->loadChildrenFor($category);
            $result = [
                "id"    => $category->getId(),
                "name" => $category->getName(),
                "children" => [],
            ];
            foreach ($children as $child) $result["children"][] = $child->getId();

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }    
}
