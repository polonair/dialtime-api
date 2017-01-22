<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Category;
use Polonairs\Dialtime\ModelBundle\Entity\Location;

class TaxonomyController extends Controller
{
    public function categoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("ModelBundle:Category")->findAll();
        return $this->render("AdminBundle:Taxonomy:categories.html.twig", ["categories" => $categories]);
    }
    public function addCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod("POST"))
        {
            $em->getConnection()->beginTransaction();

            $parent = null;
            if ($request->get("parent") !== "0") 
                $parent = $em->getRepository("ModelBundle:Category")->find($request->get("parent"));
            $category = (new Category())
                ->setName($request->get("name"))
                ->setDescription($request->get("description"))
                ->setParent($parent);
            $em->persist($category);
            $em->flush();

            $em->getConnection()->commit();

            return $this->redirectToRoute("admin/categories");
        }
        else
        {
            $categories = $em->getRepository("ModelBundle:Category")->findAll();
            return $this->render("AdminBundle:Taxonomy:add.category.html.twig", ["categories" => $categories]);
        }
    }
    public function locationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $locations = $em->getRepository("ModelBundle:Location")->findAll();
        return $this->render("AdminBundle:Taxonomy:locations.html.twig", ["locations" => $locations]);
    }
    public function addLocationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod("POST"))
        {
            $em->getConnection()->beginTransaction();

            $parent = null;
            if ($request->get("parent") !== "0") 
                $parent = $em->getRepository("ModelBundle:Location")->find($request->get("parent"));
            $location = (new Location())
                ->setName($request->get("name"))
                ->setLocative($request->get("locative"))
                ->setDescription($request->get("description"))
                ->setParent($parent);
            $em->persist($location);
            $em->flush();

            $em->getConnection()->commit();

            return $this->redirectToRoute("admin/locations");
        }
        else
        {
            $locations = $em->getRepository("ModelBundle:Location")->findAll();
            return $this->render("AdminBundle:Taxonomy:add.location.html.twig", ["locations" => $locations]);
        }
    }
}
