<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function tasksAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$tasks = $em->getRepository("ModelBundle:Task")->loadActive();
        return $this->render("AdminBundle:Task:index.html.twig", ["tasks" => $tasks]);
    }
}
