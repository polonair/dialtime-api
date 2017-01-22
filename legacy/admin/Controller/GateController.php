<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Gate;

class GateController extends Controller
{
    public function gatesAction(Request $request)
    {
        $gates = $this->getDoctrine()->getManager()->getRepository("ModelBundle:Gate")->findAll();
        return $this->render("AdminBundle:Gate:gates.html.twig", ["gates" => $gates]);
    }
    public function addGateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod("POST"))
        {
            $em->getConnection()->beginTransaction();

            $gate = (new Gate())
                ->setHost($request->get("host"))
                ->setDbUser($request->get("dbuser"))
                ->setDbPassword($request->get("dbpassword"))
                ->setDbName($request->get("dbname"))
                ->setDbPort($request->get("dbport"))
                ->setSshUser($request->get("sshuser"))
                ->setSshPassword($request->get("sshpassword"));
            $em->persist($gate);
            $em->flush();

            $em->getConnection()->commit();

            return $this->redirectToRoute("admin/gates");
        }
        else
        {
            return $this->render("AdminBundle:Gate:add.gate.html.twig");
        }
    }
}
