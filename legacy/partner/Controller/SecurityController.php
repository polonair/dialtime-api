<?php

namespace Polonairs\Dialtime\PartnerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Polonairs\Dialtime\ModelBundle\Entity\Account;
use Polonairs\Dialtime\ModelBundle\Entity\Partner;
use Polonairs\Dialtime\ModelBundle\Entity\Phone;
use Polonairs\Dialtime\ModelBundle\Entity\User;
use Polonairs\Dialtime\ModelBundle\Entity\Parameter;
use Polonairs\SmsiBundle\Smsi\SmsMessage;
use Polonairs\Dialtime\CommonBundle\Service\UserService\UserAlreadyRegisteredException;
use Polonairs\Dialtime\CommonBundle\Service\UserService\UserHaveAnotherRoleException;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'))
        {
            return $this->render("PartnerBundle:Security:login.html.twig");
        }
        return $this->redirectToRoute("partner/dashboard");
    }
    public function recoverAction(Request $request)
    {
        return $this->render("PartnerBundle:Security:login.html.twig");
    }
    public function registerAction(Request $request)
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'))
        {
            if ($request->isMethod("post"))
            {
                $us = $this->get('dialtime.common.user');
                $ss = $this->get('dialtime.common.settings');
                $sm = $this->get('polonairs.smsi');

                $passwordLength = $ss->get('user.password.length', 12);
                $passwordPattern = $ss->get('user.password.pattern', "abcdefghijklmnopqrstuvwxyz0123456789");

                $password = $us->createPassword($passwordLength, $passwordPattern);
                $username = $request->request->get("phone", null);
                $username = $us->normalizeUsername($username);

                if ($username !== null)
                {
                    try
                    {
                        $us->registerPartner($username, $password, $request->getClientIp());
                        $sms = (new SmsMessage())
                            ->setTo($username)
                            ->setText($this->renderView("PartnerBundle:Message/Sms:registration.txt.twig", 
                                ['username' => $username, "password" => $password]));
                        $sm->send($sms);
                        $this->addFlash('reg_user_name',"+$username");
                        return $this->redirectToRoute("partner/login");
                    }
                    catch(UserAlreadyRegisteredException $e)
                    {
                        return $this->redirectToRoute("partner/login");
                    }
                    catch(UserHaveAnotherRoleException $e)
                    {
                        return $this->redirectToRoute("partner/login");                        
                    }
                }
            }
            return $this->render("PartnerBundle:Security:register.html.twig");
        }
        return $this->redirectToRoute("partner/dashboard");
    }
    public function loginCheckAction() { }
}
