<?php

namespace Polonairs\Dialtime\MasterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Polonairs\Dialtime\ModelBundle\Entity\Account;
use Polonairs\Dialtime\ModelBundle\Entity\Transaction;
use Polonairs\Dialtime\ModelBundle\Entity\TransactionEntry;

class FinanceController extends Controller
{
    public function fillupResultAction(Request $request)
    {
        return $this->render("MasterBundle:Finance:result.html.twig");
    }
    public function financeFillupAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        if ($request->isMethod("post"))
        {
            $amount = $request->request->get('amount', null);
            $provider = $request->request->get('provider', null);
            if ($provider === "tcpay")
            {
                $provider_account = $em->getRepository("ModelBundle:Account")->findOneByName("PROVIDER_TCPAY");
                $user_account = $this->getUser()->getUser()->getMainAccount();

                $transaction = (new Transaction())->setEvent(Transaction::EVENT_FILLUP);
                $entry = (new TransactionEntry())
                    ->setTransaction($transaction)
                    ->setFrom($provider_account)
                    ->setTo($user_account)
                    ->setAmount($amount)
                    ->setCurrency(Account::CURRENCY_RUR);
                $em->persist($transaction);
                $em->persist($entry);

                $em->flush();

                $em->getRepository("ModelBundle:Transaction")->doHold($transaction);
                return $this->redirectToRoute("master/finance/tcpay", ["amount" => $amount, "trid" => $transaction->getId()]);
            }
        }
        return $this->render("MasterBundle:Finance:fillup.html.twig");
    }
    public function financeAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $entries = $em->getRepository("ModelBundle:Transaction")->loadAllForUser($this->getUser()->getUser());
        return $this->render("MasterBundle:Finance:index.html.twig", ["entries" => $entries]);
    }
    public function testProviderAction(Request $request)
    {
        if ($request->isMethod("post"))
        {
            $amount = $request->request->get('amount', null);
            $trid = $request->request->get('trid', null);
            if ($amount !== null)
            {
                $url = $this->generateUrl('master/finance/payapi', ['provider' => 'tcpay'], UrlGeneratorInterface::ABSOLUTE_URL);
                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["amount" => $amount, "trid" => $trid]));

                $result = curl_exec($ch);
                curl_close($ch);

                return $this->redirectToRoute("master/finance/fillup/result", ["result" => $result]);
            }
        }
        else
        {
            $amount = $request->query->get('amount', null);
            $trid = $request->query->get('trid', null);
            return $this->render("MasterBundle:Finance:tcpay.html.twig", ["amount" => $amount, "trid" => $trid]);
        }
    }
    public function payApiAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $provider = $request->attributes->get('provider', null);
        if ($provider === "tcpay")
        {
            $data = $request->getContent();
            $data = json_decode($data, true);
            $amount = $data["amount"];
            $trid = $data["trid"];
            $transaction = $em->getRepository("ModelBundle:Transaction")->find($trid);
            $em->getRepository("ModelBundle:Transaction")->doApply($transaction);
            return new Response("ok");
        }
        return new Response("fail");
    }
}
