<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\DongleDemanding;

class SupportController extends Controller
{
    public function rejectionViewAction(Request $request)
    {
        $id = $request->attributes->get("id", null);
        if ($id!== null)
        {
            $rejection = $this->get('doctrine')->getManager()->getRepository("ModelBundle:RouteRejection")->findOneById($id);        
            return $this->render("AdminBundle:Support:view.rejection.html.twig", ["rejection" => $rejection]);
        }
        return $this->redirectToRoute("admin/support/rejections");
    }
    public function rejectionsAction(Request $request)
    {
        $rejections = $this->get('doctrine')->getManager()->getRepository("ModelBundle:RouteRejection")->findAll();
        return $this->render("AdminBundle:Support:rejections.html.twig", ["rejections" => $rejections]);
    }
    public function supportAction(Request $request)
    {
    	$tickets = $this
    		->get('doctrine')
    		->getManager()
    		->getRepository("ModelBundle:Ticket")
    		->findAll();
        return $this->render("AdminBundle:Support:support.html.twig", ["tickets" => $tickets]);
    }
    public function viewTicketAction(Request $request)
    {
    	$id = $request->attributes->get('id', null);
    	if ($id !== null)
    	{
    		$ticket = $this
	    		->get('doctrine')
	    		->getManager()
	    		->getRepository("ModelBundle:Ticket")
	    		->find($id);
	    	$demanding = $this
	    		->get('doctrine')
	    		->getManager()
	    		->getRepository("ModelBundle:DongleDemanding")
	    		->loadForTicket($ticket);
        	return $this->render("AdminBundle:Support:view.ticket.html.twig", ["ticket" => $ticket, "demanding" => $demanding]);
    	}
    	return $this->redirectToRoute("admin/support");
    }
    public function resolveDemandingAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $demanding = $request->attributes->get('id', null);
        $dongle = $request->query->get('dongle', null);
        if ($demanding !== null)
        {
            $demanding = $em->getRepository("ModelBundle:DongleDemanding")->find($demanding);
            if ($demanding !== null && $demanding->getState() === DongleDemanding::STATE_WAIT)
            {
                if ($dongle !== null)
                {
                    $dongle = $em->getRepository("ModelBundle:Dongle")->find($dongle);
                    if ($dongle !== null && $dongle->getCampaign() === null)
                    {
                        $demanding
                            ->setDongle($dongle)
                            ->setState(DongleDemanding::STATE_ACCEPTED);
                        $dongle->setCampaign($demanding->getCampaign());
                        $em->flush();
                    }                    
                }
                else
                {
                    $demanding
                        ->setDongle(null)
                        ->setState(DongleDemanding::STATE_DECLINED);
                    $em->flush();
                }
                return $this->redirectToRoute("admin/support/ticket/view", ["id" => $demanding->getTicket()->getId()]);
            }
        }
        return $this->redirectToRoute("admin/support");
    }
    public function demandingsAction(Request $request)
    {
        $demandings = $this->getDoctrine()->getManager()->getRepository("ModelBundle:DongleDemanding")->findAll();
        return $this->render("AdminBundle:Support:demandings.html.twig", ["demandings" => $demandings]);
    }
    public function demandingViewAction(Request $request)
    {
        $id = $request->attributes->get('id');
        $dongles = $this->getDoctrine()->getManager()->getRepository("ModelBundle:Dongle")->loadAllFree();
        $demanding = $this->getDoctrine()->getManager()->getRepository("ModelBundle:DongleDemanding")->findOneById($id);
        return $this->render("AdminBundle:Support:view.demanding.html.twig", ["demanding" => $demanding, "dongles" => $dongles]);
    }
}
