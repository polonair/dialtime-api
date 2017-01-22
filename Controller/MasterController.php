<?php

namespace Polonairs\Dialtime\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Polonairs\Dialtime\ModelBundle\Entity\Schedule;
use Polonairs\Dialtime\ModelBundle\Entity\Interval;	
use Polonairs\Dialtime\ModelBundle\Entity\Offer;    
use Polonairs\Dialtime\ModelBundle\Entity\Session;    
use Polonairs\Dialtime\ModelBundle\Entity\Transaction;   
use Polonairs\Dialtime\ModelBundle\Entity\TransactionEntry;  
use Polonairs\Dialtime\ModelBundle\Entity\Account;    	

class MasterController extends Controller
{
    private $session = null;

    public function pageAction(Request $request)
    {
        return $this->render('ApiBundle::master.html.twig', []);
    }
    public function payApiAction(Request $rq)
    {
        $provider = $request->attributes->get('provider', null);
        if ($provider == "robokassa")
        {
            if ($request->isMethod("post"))
            {
                $amount = $request->request->get("OutSum");
                $trid = $request->request->get("InvId");
                $signature = $request->request->get("SignatureValue");
                if ($signature === md5("$amount:$trid:d2p9Nq4nKj6o"))
                {
                    $em = $this->get('doctrine')->getManager();
                    $transaction = $em->getRepository("ModelBundle:Transaction")->find($trid);
                    $em->getRepository("ModelBundle:Transaction")->doApply($transaction);
                    return new Response("OK$trid");
                }
            }
        }
        return new Response("");
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
            case "login": $result = $this->login($request, $rq); break;
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
            case "fillup.get.link": $result = $this->fillup_get_link($request, $rq); break;
            default: break;
        }
        return $result;
    }
    private function fillup_get_link($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $amount = $request["data"]["amount"];
            $provider = $request["data"]["provider"];
            if ($provider == "robokassa")
            {
                $provider_account = $em->getRepository("ModelBundle:Account")->findOneByName("PROVIDER_ROBOKASSA");
                $user_account = $user->getMainAccount();

                $transaction = (new Transaction())->setEvent(Transaction::EVENT_FILLUP);
                $entry = (new TransactionEntry())
                    ->setTransaction($transaction)
                    ->setFrom($provider_account)
                    ->setTo($user_account)
                    ->setAmount($amount)
                    ->setCurrency(Account::CURRENCY_RUR);
                $em->persist($transaction);
                $em->persist($entry);

                $em->flush();

                $em->getRepository("ModelBundle:Transaction")->doHold($transaction);

                $trid = $transaction->getId();
                $signature = md5("target-call:$amount:$trid:5XlUNz9dTEp0");
                //$link = "http://test.robokassa.ru/Index.aspx?".
                $link = "https://auth.robokassa.ru/Merchant/Index.aspx?".
                    "MrchLogin=target-call".
                    "&OutSum=" . urlencode($amount).
                    "&InvId=" . urlencode($trid).
                    "&Desc=" . urlencode("Пополнение счета в сервисе Target-Call").
                    "&SignatureValue=".$signature;

                return [ "result" => "ok", "data" => [ "link" => $link ] ];
            }
        }
        return [];
    }
    private function account_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $entry = $em->getRepository("ModelBundle:Account")->loadOneForMaster($master, $request["data"]);
            $result = [
                "id" => $entry->getId(),
                "balance" => $entry->getBalance(),
                "holdin" => $entry->getIncomeHold(),
                "holdout" => $entry->getOutcomeHold(),
                "credit" => $entry->getCredit(),
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];        
    }
    private function schedule_set_intervals($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $schedule = $em->getRepository("ModelBundle:Schedule")->loadOneForMaster($master, $request["data"]["schedule"]);

            foreach ($schedule->getIntervals() as $i) $i->remove();

            $tz = $request["data"]["tz"];
            $fd = $request["data"]["fd"];
            $td = $request["data"]["td"];
            if ($td < $fd) $td += 7;
            $ft = $request["data"]["ft"];
            $tt = $request["data"]["tt"];
            if ($tt < $ft)
            {
                $tt = $request["data"]["ft"];
                $ft = $request["data"]["tt"];
            }

            for ($i = $fd; $i <=  $td; $i++)
            {
                if ($i > 6) $d = $i - 7;
                else $d = $i;
                $int = (new Interval())
                    ->setSchedule($schedule)
                    ->setFrom($ft*60 + 1440*$d)
                    ->setTo($tt*60 + 1440*$d - 1);
                $em->persist($int);
            }
            $schedule->setTimezone($tz);

            $em->flush();
            return [ "result" => "ok" ];
        }
        return [];
    }
    private function offer_set_state($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $offer = $em->getRepository("ModelBundle:Offer")->loadOneForMaster($master, $request["data"]["offer"]);
            
            $state =  $request["data"]["state"];
            $offer->setState($state);

            $em->flush();

            return [ "result" => "ok" ];
        }
        return [];
    }
    private function offer_remove($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $offer = $em->getRepository("ModelBundle:Offer")->loadOneForMaster($master, $request["data"]["offer"]);
            $offer->remove();

            $em->flush();

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
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $entry = $em->getRepository("ModelBundle:Call")->loadOneForMaster($master, $request["data"]);
            dump($entry);
            $result = [
                "id" => $entry->getId(),
                "createdAt" => $entry->getCreatedAt()->getTimestamp(),
                "route" => $entry->getRoute()->getId(),
                "length" => $entry->getAnswerLength(),
            ];
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
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $entry = $em->getRepository("ModelBundle:Route")->loadOneForMaster($master, $request["data"]);
            dump($entry);
            $result = [
                "id" => $entry->getId(),
                "number" => $entry->getTerminator()->getNumber(),
                "createdAt" => $entry->getCreatedAt()->getTimestamp(),
                "offer" => $entry->getTask()->getOffer()->getId(),
                "direct" => null,
                "cost" => 240.0, //$entry->getAttachment()["cost"],
            ];
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
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $entry = $em->getRepository("ModelBundle:Transaction")->loadOneForMaster($master, $request["data"]);
            $result = [
                "id" => $entry->getId(),
                "trid" => $entry->getTransaction()->getId(),
                "amount" => $entry->getAmount(), 
                "role" => $entry->getRole(),
                //"event" => $entry->getTransaction()->getEvent(),
                "open" => $entry->getTransaction()->getOpenAt()->getTimestamp(),
                //"hold" => $entry->getTransaction()->getHoldAt(),
                //"cancel" => $entry->getTransaction()->getCancelAt(),
                //"close" => $entry->getTransaction()->getCloseAt(),
                "status"=> $entry->getTransaction()->getStatus()
            ];
            return [ "result" => "ok", "data" => $result ];
        }
        return [];        
    }
    private function offer_set_ask($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $offer = $em->getRepository("ModelBundle:Offer")->loadOneForMaster($master, $request["data"]["offer"]);
            
            $ask =  $request["data"]["value"];
            $offer->setAsk($ask);

            $em->flush();

            return [ "result" => "ok" ];
        }
        return [];
    }
    private function schedule_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $task = $em->getRepository("ModelBundle:Schedule")->loadOneForMaster($master, $request["data"]);
            $result = [
                "id" => $task->getId(),
                "intervals" => [],
                "tz" => $task->getTimezone(),
            ];
            $ints = $task->getIntervals();
            foreach ($ints as $i) $result["intervals"][] = [ "from" => $i->getFrom(), "to" => $i->getTo() ];

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }    
    private function task_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $task = $em->getRepository("ModelBundle:Task")->loadOneForMaster($master, $request["data"]);
            $result = [
                "id"    => $task->getId(),
                "rate" => $task->getRate(),
            ];

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }    
    private function offer_get($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $offer = $em->getRepository("ModelBundle:Offer")->loadOneForMaster($master, $request["data"]);
            $result = [
                "id"       => $offer->getId(),
                "state"    => $offer->getState(),
                "category" => $offer->getCategory()->getId(),
                "location" => $offer->getLocation()->getId(),
                "ask"      => $offer->getAsk(),
                "schedule" => $offer->getSchedule()->getId(),
                "task"     => ($offer->getTask() !== null)?$offer->getTask()->getId():0,
                "removed"  => $offer->isRemoved(),
            ];

            return  [ "result" => "ok", "data" => $result ];
        }
        return [];
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
    private function check($request, $rq)
    {
        if ($this->session !== null) 
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $result = [
                "counter" => time() - 3600, // '- 3600' - only for local system!
                "entities" => [
                    "offer" =>$em->getRepository("ModelBundle:Offer")->loadAllIdsForMaster($master, $request["data"]),
                    "task" =>$em->getRepository("ModelBundle:Task")->loadAllIdsForMaster($master, $request["data"]),
                    "schedule" =>$em->getRepository("ModelBundle:Schedule")->loadAllIdsForMaster($master, $request["data"]),
                    "call" =>$em->getRepository("ModelBundle:Call")->loadAllIdsForMaster($master, $request["data"]),
                    "route" =>$em->getRepository("ModelBundle:Route")->loadAllIdsForMaster($master, $request["data"]),
                    "transaction" =>$em->getRepository("ModelBundle:Transaction")->loadAllIdsForMaster($master, $request["data"]),
                    "account" =>$em->getRepository("ModelBundle:Account")->loadAllIdsForMaster($master, $request["data"]),
                ]
            ];

            return [ "result" => "ok", "data" => $result ];
        }
        return [];
    }
    private function login($request, $rq)
    {
        $em = $this->get('doctrine')->getManager();

        $master = $em->getRepository("ModelBundle:Master")
            ->loadUser($request["data"]["username"], $request["data"]["password"]);

        if ($master != null)
        {
            $this->session = new Session();
            $this->session
                ->setRealm('MASTER')
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
    private function offer_create($request, $rq)
    {
        if ($this->session !== null)
        {
            $em = $this->get('doctrine')->getManager();

            $user = $this->session->getOwner();
            $master = $em->getRepository("ModelBundle:Master")->loadMasterByUser($user);

            $phones = $em->getRepository("ModelBundle:Phone")->loadByOwner($user);            
            $category = $em->getRepository("ModelBundle:Category")->find($request["data"]["category"]);
            $location = $em->getRepository("ModelBundle:Location")->find($request["data"]["location"]);

            $schedule = (new Schedule())->setOwner($user)->setTimezone($request["data"]["schedule"]["timezone"]);
            $intervals = $request["data"]["schedule"]["intervals"];
            foreach ($intervals as $v) 
            {
                $int = (new Interval())
                    ->setSchedule($schedule)
                    ->setFrom($v["from"])
                    ->setTo($v["to"]);
                $em->persist($int);
            }
            $em->persist($schedule);
            $ask = $request["data"]["ask"];

            $offer = (new Offer())
                ->setOwner($master)
                ->setPhone($phones[0])
                ->setCategory($category)
                ->setLocation($location)
                ->setSchedule($schedule)
                ->setState(Offer::STATE_ON)
                ->setAsk($ask);
            $em->persist($offer);
            $em->flush();

            $em->getConnection()->commit();/**/

            return [ "result" => "ok" ];
        }
        return [];
    }
}
