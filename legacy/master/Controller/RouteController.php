<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Route as CallRoute;
use Polonairs\Dialtime\ModelBundle\Entity\RouteRejection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RouteController extends Controller
{
    public function routesAction(Request $request)
    {
        $routes = $this->get('doctrine')->getManager()->getRepository("ModelBundle:Route")->loadByMaster($this->getUser());
        return $this->render("MasterBundle:Route:index.html.twig", ["routes" => $routes]);
    }
    public function callsAction(Request $request)
    {
        $calls = $this->get('doctrine')->getManager()->getRepository("ModelBundle:Call")->loadByMaster($this->getUser());
        return $this->render("MasterBundle:Route:calls.html.twig", ["calls" => $calls]);
    }
    public function recordAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $id = $request->attributes->get("id", null);
        if ($id !== null)
        {
            $call = $this->get('doctrine')->getManager()->getRepository("ModelBundle:Call")->find($id); 
            dump($call);
            $response = new Response(stream_get_contents($call->getRecord()), 200, array('Content-Type' => 'audio/mp3')); 
            //$response = new BinaryFileResponse($call->getRecord());   
            return $response;
        }
        return $this->redirectToRoute("master/calls");
    }
    public function routeRejectAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $id = $request->attributes->get('id', null);
        $reason = $request->query->get('reason', null);

        if ($id !== null)
        {
            $route = $em->getRepository("ModelBundle:Route")->findOneById($id);
            if ($reason !== null)
            {
                $route->setState(CallRoute::STATE_REJECTED);
                $rrj = (new RouteRejection())
                    ->setRoute($route)
                    ->setReason($reason)
                    ->setState(RouteRejection::STATE_REJECTED_BY_MASTER)
                    ->setPartnerTicket(null)
                    ->setMasterTicket(null)
                    ->setTransaction(null);
                $em->persist($rrj);
                $em->flush();                
            }
            else
            {
                return $this->render("MasterBundle:Route:rejection.html.twig", ["route" => $route]);
            }
        }
        return $this->redirectToRoute("master/routes");
    }
    public function routeViewAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $id = $request->attributes->get('id', null);

        $route = $em->getRepository("ModelBundle:Route")->findOneById($id);
        $calls = $em->getRepository("ModelBundle:Call")->loadAllForRoute($route);
        $rejections = $em->getRepository("ModelBundle:RouteRejection")->loadAllForRoute($route);
        return $this->render("MasterBundle:Route:view.html.twig", ["route" => $route, "calls" => $calls, "rejections" => $rejections]);
    }
    public function cancelRejectionAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $id = $request->attributes->get("id", null);

        if ($id !== null)
        {
            $rejection = $em->getRepository("ModelBundle:RouteRejection")->findOneById($id);
            $rejection->setState(RouteRejection::STATE_CANCELED_BY_MASTER);
            $route = $rejection->getRoute();
            $route->setState(CallRoute::STATE_ACTIVE);
            $em->flush();

        }
        return $this->redirectToRoute("master/routes");
    }
}
