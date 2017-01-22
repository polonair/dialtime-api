<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Polonairs\Dialtime\ModelBundle\Entity\Template;
use ZipArchive;

class TemplateController extends Controller
{
    public function templatesImportAction(Request $request)
    {
        if ($request->isMethod("post"))
        {
            $fname = $request->files->get("userfile")->getRealPath();
            $zip = new ZipArchive();
            if ($zip->open($fname))
            {
                $tpls = [];
                for($i = 0; $i<$zip->numFiles; $i++)
                {
                    $tpls[$zip->getNameIndex($i)] = $zip->getFromIndex($i);
                }
                $em = $this->get('doctrine')->getManager();
                $templates = $em->getRepository('ModelBundle:Template')->loadAllTemplatesIndexedByName();

                foreach($tpls as $name => $source)
                {
                    if (array_key_exists($name, $templates))
                    {
                        $templates[$name]->setSource($source);
                    }
                    else
                    {
                        $t = (new Template())->setName($name)->setSource($source);
                        $em->persist($t);
                    }
                }
                $em->flush();
                $zip->close();
            }
            return $this->redirectToRoute("admin/templates");
        }
        return $this->render("AdminBundle:Template:import.html.twig");
    }
    public function templatesExportAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $templates = $em->getRepository('ModelBundle:Template')->loadAllTemplates();

        $zip = new ZipArchive();
        $zip_name = "templates-".time().".zip";
        $tmp_dir = $this->get('kernel')->getRootDir() . '/../var/tmp/';
        $zip->open($tmp_dir.$zip_name, ZIPARCHIVE::CREATE);

        foreach($templates as $template)
        {
            $zip->addFromString($template->getName(), $template->getSource());
        }
        $zip->close();
        $r = new BinaryFileResponse($tmp_dir.$zip_name);
        $r->deleteFileAfterSend(true);
        $r->headers->set('Content-Type', 'application/zip');
        $r->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zip_name);
        return $r;
    }
    public function templatesAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $templates = $em->getRepository('ModelBundle:Template')->loadAllTemplates();
        return $this->render("AdminBundle:Template:index.html.twig", ["templates" => $templates]);
    }
    public function templateAddAction(Request $request)
    {
        if ($request->isMethod("POST"))
        {
            $em = $this->get('doctrine')->getManager();
            $source = $request->request->get("source", null);
            $name = $request->request->get("name", null);
            if ($source !== null && $name !== null) 
            {
                $template = (new Template())
                    ->setName($name)
                    ->setSource($source);
                $em->persist($template);
                $em->flush();
                return $this->redirectToRoute("admin/template/edit", ["id" => $template->getId()]);
            }
        }
        return $this->render("AdminBundle:Template:add.html.twig");
    }
    public function templateEditAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $id = $request->attributes->get('id', null);
        if ($id !== null)
        {
            $template = $em->getRepository("ModelBundle:Template")->loadOneById($id);
            if ($request->isMethod("POST"))
            {
                $source = $request->request->get("source", null);
                if ($source !== null) $template->setSource($source);
                $name = $request->request->get("name", null);
                if ($name !== null) $template->setName($name);
                $em->flush();
                return $this->redirectToRoute("admin/template/edit", ["id" => $id]);
            }
            else
            {
                return $this->render("AdminBundle:Template:edit.html.twig", ["template" => $template]);            
            }
        }
        return $this->redirectToRoute("admin/templates");
    }
}
