<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HelpController extends Controller
{
    public function indexAction(Request $request)
    {
    	return $this->renderHelpPage('main');
    }
    public function pageAction(Request $request)
    {
    	$page_name = $request->attributes->get('page_name', null);
    	return $this->renderHelpPage($page_name);
    }
    private function renderHelpPage($file)
    {
        $content = file_get_contents( __DIR__ . '/../Resources/doc/user/' . $file . '.md' );
        return $this->render("MasterBundle:Help:page.html.twig", ["text" => $content]);
    }
}
