<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SupportController extends Controller
{
    public function supportAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$myTickets = $em->getRepository("ModelBundle:Ticket")->loadTicketsByUser($this->getUser()->getUser());
        return $this->render("PartnerBundle:Support:index.html.twig", ["tickets" => $myTickets]);
    }
    public function supportAddTicketAction(Request $request)
    {
    	if ($request->isMethod("post"))
    	{
    		$theme = $request->request->get("theme", null);
    		$message = $request->request->get("message", null);
    		if ($theme !== null && $message !== null)
    		{
    			$em = $this->get('doctrine')->getManager();
    			$ticket = (new Ticket())
    				->setTheme($theme)
    				->setClient($this->getUser()->getUser());
    			$em->persist($ticket);
    			$message = (new TicketMessage())
    				->setDirection(TicketMessage::DIRECTION_FROM_CLIENT)
    				->setTicket($ticket)
    				->setMessage($message);
    			$em->persist($message);
    			$em->flush();
    		}
    		return $this->redirectToRoute("partner/support");
    	}
        return $this->render("PartnerBundle:Support:add.ticket.html.twig");/**/
    }
}
