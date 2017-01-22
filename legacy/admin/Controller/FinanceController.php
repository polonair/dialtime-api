<?php

namespace Polonairs\Dialtime\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Polonairs\Dialtime\ModelBundle\Entity\Account;

class FinanceController extends Controller
{
    public function dashboardAction(Request $request)
    {
        return $this->render("AdminBundle:Finance:index.html.twig");
    }
    public function accountsAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$accounts = $em->getRepository("ModelBundle:Account")->findAll();
        return $this->render("AdminBundle:Finance:accounts.html.twig", ["accounts" => $accounts]);
    }
    public function addAcccountAction(Request $request)
    {
    	$em = $this->get('doctrine')->getManager();
    	$owners = $em->getRepository("ModelBundle:User")->findAll();
    	$currencies = [ Account::CURRENCY_RUR ];
    	$states = [ Account::STATE_ACTIVE ];
    	if ($request->isMethod("post"))
    	{
			$name = $request->request->get("name", null);
			$owner = $request->request->get("owner", null);
			$currency = $request->request->get("currency", null);
			$state = $request->request->get("state", null);
			$credit = $request->request->get("credit", null);

			if (count($name) === 0) $name = null;
			if ($owner == 0) $owner = null;
			else $owner = $em->getRepository("ModelBundle:User")->findOneById($owner);

			$account = (new Account())
				->setName($name)
				->setOwner($owner)
				->setCurrency($currency)
				->setState($state)
				->setCredit($credit);
			$em->persist($account);
			$em->flush();
			return $this->redirectToRoute("admin/finance/accounts");
    	}
    	return $this->render("AdminBundle:Finance:add.account.html.twig", ["owners" => $owners, "currencies" => $currencies, "states" => $states]);
    }
    public function transactionsAction(Request $request)
    {
        return $this->render("AdminBundle:Finance:transactions.html.twig");
    }
}
