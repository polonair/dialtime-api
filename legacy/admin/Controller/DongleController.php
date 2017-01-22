<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Dongle;

class DongleController extends Controller
{
    public function donglesAction(Request $request)
    {
        $dongles = $this->getDoctrine()->getManager()->getRepository("ModelBundle:Dongle")->findAll();
        return $this->render("AdminBundle:Dongle:dongles.html.twig", ["dongles" => $dongles]);
    }
    public function addDongleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod("POST"))
        {
            $em->getConnection()->beginTransaction();

            $gate = $em->getRepository("ModelBundle:Gate")->find($request->get("gate"));

            $dongle = (new Dongle())
                ->setNumber($request->get("number"))
                ->setPassText($request->get("pass_text"))
                ->setPassVoice($request->get("pass_voice"))
                ->setGate($gate);
            $em->persist($dongle);
            $em->flush();

            $em->getConnection()->commit();

            return $this->redirectToRoute("admin/dongles");
        }
        else
        {
            $gates = $em->getRepository("ModelBundle:Gate")->findAll();
            return $this->render("AdminBundle:Dongle:add.dongle.html.twig", ["gates" => $gates]);
        }
    }
}
