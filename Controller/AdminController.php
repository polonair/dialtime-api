<?php

namespace Polonairs\Dialtime\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    private $session = null;

    public function pageAction(Request $request)
    {
        return $this->render('ApiBundle::partner.html.twig', []);
    }
    public function apiAction(Request $rq)
    {
        $auth_key = $rq->headers->get("x-tc-authkey");

        $em = $this->get('doctrine')->getManager();
        $this->session = $em->getRepository("ModelBundle:Session")->loadSession($auth_key);
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
            /*case "login": $result = $this->login($request, $rq); break;
            case "is.logged.in": $result = $this->isLoggedIn($request, $rq); break;
            case "logout": $result = $this->logout($request, $rq); break;
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
            case "check": $result = $this->check($request, $rq); break;
            case "schedule.set.intervals": $result = $this->schedule_set_intervals($request, $rq); break;
            case "offer.set.state": $result = $this->offer_set_state($request, $rq);  break;
            case "offer.remove": $result = $this->offer_remove($request, $rq); break;
            case "account.get": $result = $this->account_get($request, $rq); break;
            case "fillup.get.link": $result = $this->fillup_get_link($request, $rq); break;*/
            default: break;
        }
        return $result;
    }
}
