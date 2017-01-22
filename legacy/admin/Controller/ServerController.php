<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Server;
use Polonairs\Dialtime\ModelBundle\Entity\ServerJob;

class ServerController extends Controller
{
    public function indexAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$servers = $em->getRepository("ModelBundle:Server")->findAll();
        return $this->render("AdminBundle:Server:index.html.twig", ["servers" => $servers]);
    }
    public function addAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $s = new Server();
        $em->persist($s);
        $em->flush();
        return $this->redirectToRoute("admin/servers");
    }
    public function viewAction(Request $request)
    {
        $id = $request->attributes->get("id", null);
        if ($id !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $server = $em->getRepository("ModelBundle:Server")->findOneById($id);
            $jobs = $em->getRepository("ModelBundle:ServerJob")->loadJobsForServerId($id);
            return $this->render("AdminBundle:Server:view.html.twig", ["server" => $server, "jobs" => $jobs]);
        }
        return $this->redirectToRoute("admin/servers");
    }
    public function doAction(Request $request)
    {
        $id = $request->attributes->get("id", null);
        if ($id !== null) $this->get('dialtime.server.updater')->updateAll($id, 1);
        return new Response("<html><head></head><body>done</body></html>");
        return $this->redirectToRoute("admin/servers");
    }
    public function addJobAction(Request $request)
    {
        $id = $request->attributes->get("id", null);
        if ($id !== null)
        {
            $em = $this->get('doctrine')->getManager();
            $server = $em->getRepository("ModelBundle:Server")->findOneById($id);
            if ($request->isMethod("post"))
            {
                $name = $request->request->get("job", null);
                $order = $request->request->get("order", null);
                if ($name!==null && $order !== null)
                {
                    $job = (new ServerJob())->setName($name)->setOrder($order)->setServer($server);
                    $em->persist($job);
                    $em->flush();
                    return $this->redirectToRoute("admin/server/view", ["id" => $id]);
                }
                return $this->render("AdminBundle:Server:job.add.html.twig", ["server" => $server]);
            }
            return $this->render("AdminBundle:Server:job.add.html.twig", ["server" => $server]);
        }
        return $this->redirectToRoute("admin/servers");
    }

}
