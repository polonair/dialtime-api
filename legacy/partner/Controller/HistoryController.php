<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Polonairs\Dialtime\ModelBundle\Entity\RouteRejection;
use Polonairs\Dialtime\ModelBundle\Entity\Route as CallRoute;
use Polonairs\Dialtime\ModelBundle\Entity\Call;
use Polonairs\Dialtime\ModelBundle\Entity\Ticket;

class HistoryController extends Controller
{
    public function historyAction(Request $request)
    {
        return $this->render("PartnerBundle:History:main.html.twig");
    }
    public function callsAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$routes = $em->getRepository("ModelBundle:Route")->loadByPartner($this->getUser());
        return $this->render("PartnerBundle:History:calls.html.twig", ["routes" => $routes]);
    }
    public function rejectsAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $rejections = $em->getRepository("ModelBundle:RouteRejection")->loadAllByPartner($this->getUser());
        return $this->render("PartnerBundle:History:rejects.html.twig", ["rejections" => $rejections]);
    }
    public function rejectAcceptAction(Request $request)
    {
        $id = $request->attributes->get("id", null);
        if ($id !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $rejection = $em->getRepository("ModelBundle:RouteRejection")->findOneById($id);
            $rejection->setState(RouteRejection::STATE_APPROVED_BY_PARTNER);
            $route = $rejection->getRoute();
            $initialCall = $em->getRepository("ModelBundle:Call")->findOneBy([
                "direction" => Call::DIRECTION_RG,
                "route" => $route->getId()]);
            $transaction = $initialCall->getTransaction();
            if ($transaction !== null)
            {
                $em->getRepository("ModelBundle:Transaction")->doCancel($transaction);
            }
            switch($rejection->getReason())
            {
                case "spam":
                    $route->setState(CallRoute::STATE_SPAM);
                    // update spam list
                    break;
                case "noresult":
                    $route->setState(CallRoute::STATE_REMOVED);
                    break;
            }
            $em->flush();
        }
        return $this->redirectToRoute("partner/history/rejects");
    }
    public function rejectDeclineAction(Request $request)
    {
        $id = $request->attributes->get("id", null);
        if ($id !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $rejection = $em->getRepository("ModelBundle:RouteRejection")->findOneById($id);
            $rejection->setState(RouteRejection::STATE_DECLINED_BY_PARTNER);
            $partner_ticket = (new Ticket)
                ->setClient($this->getUser()->getUser())
                ->setTheme("Отказ от контакта #".$rejection->getRoute()->getId());
            $master_ticket = (new Ticket)
                ->setClient($rejection->getRoute()->getMasterPhone()->getOwner())
                ->setTheme("Отказ от контакта #".$rejection->getRoute()->getId());
            $em->persist($partner_ticket);
            $em->persist($master_ticket);
            $em->flush();
        }
        return $this->redirectToRoute("partner/history/rejects");
    }
}
