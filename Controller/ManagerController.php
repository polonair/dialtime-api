<?php

namespace Polonairs\Dialtime\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Session;  

class ManagerController extends Controller
{
    private $session = null;

    public function pageAction(Request $request)
    {
        return $this->render('ApiBundle::manager.html.twig', []);
    }
    public function apiAction(Request $rq)
    {
        $auth_key = $rq->headers->get("x-tc-authkey");

        $em = $this->get('doctrine')->getManager();
        $this->session = $em->getRepository("ModelBundle:Session")->loadSession($auth_key, 'MANAGER');
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
            case "check": $result = $this->check($request, $rq); break;
            case "newclient.get": $result = $this->newclient_get($request, $rq); break;
            case "myclient.get": $result = $this->myclient_get($request, $rq); break;
            case "bindme": $result = $this->bindme($request, $rq); break;
            case "ticket.get": $result = $this->ticket_get($request, $rq); break;
            case "demanding.get": $result = $this->demanding_get($request, $rq); break;
            case "dongle.get": $result = $this->dongle_get($request, $rq); break;
            case "demanding.resolve": $result = $this->demanding_resolve($request, $rq); break;

            /*
            case "category.get": $result = $this->category_get($request, $rq); break;
            case "location.get": $result = $this->location_get($request, $rq); break;
            case "offer.create": $result = $this->offer_create($request, $rq); break;
            case "offer.get": $result = $this->offer_get($request, $rq); break;
            case "offer.set.ask": $result = $this->offer_set_ask($request, $rq); break;
            case "task.get": $result = $this->task_get($request, $rq); break;
            case "schedule.get": $result = $this->schedule_get($request, $rq); break;
            case "transaction.get": $result = $this->transaction_get($request, $rq); break;
            case "route.get": $result = $this->route_get($request, $rq); break;
            case "call.get": $result = $this->call_get($request, $rq); break;
            case "schedule.set.intervals": $result = $this->schedule_set_intervals($request, $rq); break;
            case "offer.set.state": $result = $this->offer_set_state($request, $rq);  break;
            case "offer.remove": $result = $this->offer_remove($request, $rq); break;
            case "account.get": $result = $this->account_get($request, $rq); break;
            case "fillup.get.link": $result = $this->fillup_get_link($request, $rq); break;*/
            default: break;
        }
        return $result;
    }
    private function myclient_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $entry = $em->getRepository("ModelBundle:User")->loadMyClient($manager, $request["data"]);
            $result = [
                "id" => $entry["user"]->getId(),
                "username" => $entry["user"]->getUsername(),
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function demanding_resolve($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            /*$entry = $em->getRepository("ModelBundle:User")->loadFreeClient($manager, $request["data"]);
            if ($entry["master"] !== null) $entry["master"]->setManager($manager);
            if ($entry["partner"] !== null) $entry["partner"]->setManager($manager);
            $em->flush();*/
            
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
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $entry = $em->getRepository("ModelBundle:Dongle")->loadOneForManager($manager, $request["data"]);
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
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $entry = $em->getRepository("ModelBundle:DongleDemanding")->loadOneForManager($manager, $request["data"]);
            if ($entry === null) $result = null;
            else $result = [
                "state" => $entry->getState(),
                "dongle" => ($entry->getDongle() == null)?(null):($entry->getDongle()->getId()),
                "suggested" => $em->getRepository("ModelBundle:Dongle")->loadAllSuggestedIds($entry)
            ];
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
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $entry = $em->getRepository("ModelBundle:Ticket")->loadOneForManager($manager, $request["data"]);
            $result = [
                "client" => $entry->getClient()->getId(),
                "theme" => $entry->getTheme(),
                "state" => $entry->getState(),
                "created" => $entry->getCreatedAt()->getTimestamp(),
                "demanding" => $em->getRepository("ModelBundle:DongleDemanding")->loadOneForTicket($entry),
            ];
            if ($result["demanding"] !== null) $result["demanding"] = $result["demanding"]->getId();
            return [ "result" => "ok", "data" => $result ];
        }
        return [];   
    }
    private function bindme($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $entry = $em->getRepository("ModelBundle:User")->loadFreeClient($manager, $request["data"]);
            if ($entry["master"] !== null) $entry["master"]->setManager($manager);
            if ($entry["partner"] !== null) $entry["partner"]->setManager($manager);
            $em->flush();
            
            return [ "result" => "ok" ];
        }
        return [];
    }
    private function newclient_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $entry = $em->getRepository("ModelBundle:User")->loadFreeClient($manager, $request["data"]);
            $result = [
                "id" => $entry["user"]->getId(),
                "username" => $entry["user"]->getUsername(),
                "master" => ($entry["master"] !== null)?$entry["master"]->getId():null,
                "partner" => ($entry["partner"] !== null)?$entry["partner"]->getId():null,
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];        
    }
    private function check($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $manager = $em->getRepository("ModelBundle:Manager")->loadManagerByUser($user);

            $result = [
                "counter" => time() - 3600, // '- 3600' - only for local system!
                "entities" => [
                    "newclient" => $em->getRepository("ModelBundle:User")->loadNewClients(),
                    "myclient" => $em->getRepository("ModelBundle:User")->loadAllMyClientsIdsForManager($manager, $request["data"]),
                    "ticket" => $em->getRepository("ModelBundle:Ticket")->loadAllIdsForManager($manager, $request["data"]),
                ]
            ];

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function login($request, $rq)
    {
        $em = $this->get('doctrine')->getManager();

        $manager = $em->getRepository("ModelBundle:Manager")
            ->loadUser($request["data"]["username"], $request["data"]["password"]);

        if ($manager != null)
        {
            $this->session = new Session();
            $this->session
                ->setRealm('MANAGER')
                ->setOwner($manager->getUser());
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
}
