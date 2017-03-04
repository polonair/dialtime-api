<?php

namespace Polonairs\Dialtime\ApiBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiProcessor
{
	protected $requestStack;
	protected $request;
	protected $logger;
	protected $doctrine;
	protected $session = null;
	protected $realm;

	public function __construct(RequestStack $requestStack, $logger, $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }
    public function getResponse()
    {
    	$em = $this->doctrine->getManager();
        $this->request = $this->requestStack->getCurrentRequest();
        $this->realm = $realm;
        $this->authKey = $this->request->headers->get("x-tc-authkey", null);
        if ($this->authKey !== null)
        {
        	$this->logger->info("AUTHKEY = ".$this->authKey);
        	$this->session = $em->getRepository("ModelBundle:Session")->loadSession($auth_key, $this->realm);
        }
        else
        {
        	$this->logger->info("AUTHKEY is NULL");
        	$this->session = null;
        }

        $result = [ "result" => "fail", "code": "1000" ];
        $json = json_decode($this->request->getContent(), TRUE);
        if ($json !== null)
        {
            $this->logger->info("Json data found");
            if (is_array($json))
            {
                if (array_key_exists("action", $json))
                {
                    $this->logger->info("Found single request");
                    $requests = $this->parseRequest($json);
                }
                else
                {
                    $this->logger->info("Found packet request");
                    foreach($json as $subjson)
                    {
                        $requests = $this->parseRequest($subjson);
                    }
                }
            }
            else
            {
                $this->logger->error("Data is not array");
                $result = [ "result" => "fail", "code": "1001" ];
            }
        }
        else
        {
            $this->logger->error("Request content is not json data");
            $result = [ "result" => "fail", "code": "1002" ];
        }
        return new JsonResponse($result);
    }
    private function parseRequest($data)
    {
        $result = [];
        if (array_key_exists("action", $data))
        {

        }
        elseif (array_key_exists("rqid", $data) && array_key_exists("request", $data))
        {

        }
    }
}
