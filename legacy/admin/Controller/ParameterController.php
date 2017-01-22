<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Parameter;

class ParameterController extends Controller
{
    public function indexAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$parameters = $em->getRepository("ModelBundle:Parameter")->findAll();
        return $this->render("AdminBundle:Parameters:index.html.twig", ["parameters" => $parameters]);
    }
    public function addParameterAction(Request $request)
    {
    	if ($request->isMethod("post"))
    	{
    		$name = $request->request->get("name", null);
    		$value = $request->request->get("value", null);
    		if ($name!== null)
    		{
    			$em = $this->get('doctrine')->getManager();
    			$parameter = (new Parameter())
    				->setName($name)
    				->setValue($value);
    			$em->persist($parameter);
    			$em->flush();
    			return $this->redirectToRoute("admin/parameters");
    		}
    	}
        return $this->render("AdminBundle:Parameters:add.html.twig");
    }
    public function editParameterAction(Request $request)
    {
        if ($request->isMethod("post"))
        {
            $id = $request->attributes->get("id", null);
            if ($id !== null)
            {
                $em = $this->get('doctrine')->getManager();
                $parameter = $em->getRepository("ModelBundle:Parameter")->findOneById($id);
                $name = $request->request->get("name", null);
                $value = $request->request->get("value", null);
                if ($name !== null) $parameter->setName($name);
                if ($value !== null) $parameter->setValue($value);
                $em->flush();
            }
        }
        return $this->redirectToRoute("admin/parameters");
    }
}
