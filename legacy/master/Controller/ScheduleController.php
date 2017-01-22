<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Polonairs\Dialtime\ModelBundle\Entity\Schedule;
use Polonairs\Dialtime\ModelBundle\Entity\Interval;

class ScheduleController extends Controller
{
    public function schedulesAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $schedules = $em->getRepository("ModelBundle:Schedule")->loadByOwner($this->getUser()->getUser());
        return $this->render("MasterBundle:Settings:schedules.html.twig", ["schedules" => $schedules]);
    }
    public function scheduleAddAction(Request $request)
    {
    	if ($request->isMethod("post"))
    	{
    		$from_dow = $request->request->get('from_dow', null);
    		$to_dow = $request->request->get('to_dow', null);
    		$from_tod = $request->request->get('from_tod', null);
            $to_tod = $request->request->get('to_tod', null);
            $tz = $request->request->get('tz', null);
    		if($from_dow !== null && $to_dow !== null && $from_tod !== null && $to_tod !== null)
    		{
    			$em = $this->get('doctrine')->getManager();
    			$schedule = (new Schedule())->setOwner($this->getUser()->getUser())->setTimezone($tz);
    			$em->persist($schedule);
    			for ($i = $from_dow; $i <= $to_dow; $i++)
    			{
    				$int = (new Interval())
    					->setSchedule($schedule)
    					->setFrom($from_tod*60+1440*$i)
    					->setTo(($to_tod+1)*60+1440*$i-1);
    				$em->persist($int);
    			}
    			$em->flush();
    		}
    	}
    	return $this->redirectToRoute("master/schedules");
    }
}