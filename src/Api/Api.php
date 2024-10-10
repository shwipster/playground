<?php

namespace A24\Api;

use A24\Log\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Api
{
    private Logger $logger;
    private EndpointFactory $endpointFactory;

    public function __construct(
        Logger $logger = null,
        EndpointFactory $endpointFactory = null
    )
    {
        if (!$logger) {
            $this->logger = new Logger("API");
        }

        if (!$endpointFactory) {
            $this->endpointFactory = new EndpointFactory();
        }
    }

    public function handle(Request $request)
    {
        try {
            $endpoint = $this->endpointFactory::build($request);
            $endpoint->authenticate($request);
            $response = $endpoint->proccess($request);

        } catch (ApiException $e) {

            $eCode = $e->getCode();

            //Send client side error to client
            $response = new JsonResponse(['msg' => $e->getMessage()], $eCode);

            if (\Config::Ini()->env_dev) {
                $response = $this->createDebugResponse($e, $eCode);
            } else {
                $this->logger->error($e, $this->createLogContext($response));
            }

            //Show users 404 page instead of json output. If they open url directly in browser
            if ($eCode===Response::HTTP_NOT_FOUND) {
                $response = new Response(null, Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {

            //Send server side error to client
            $response = new JsonResponse(['msg' => 'Error handling request'], Response::HTTP_INTERNAL_SERVER_ERROR);

            if (\Config::Ini()->env_dev) {
                $response = $this->createDebugResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                $this->logger->error($e, $this->createLogContext($response));
            }
        } finally {

            //$response->headers->set('Access-Control-Allow-Orgin', '*');
            //$response->headers->set('Access-Control-Allow-Methods', '*');
            $response->send();
        }
    }

    private function createDebugResponse(\Exception $e, int $code): Response
    {
        $response = new Response(null, $code);

        $content = sprintf('<pre>%s<br>Callstack: %s<br>',
            print_r($e->getMessage(),true),
            print_r($e->getTrace(),true)
        );

        $response->setContent($content);

        return $response;
    }

    private function createLogContext(Response $response): array
    {
        $allowedErrorLogKeys = [
            'REQUEST_URI',
            'HTTP_AUTHORIZATION'
        ];

        $context = array_intersect_key($_SERVER,array_flip($allowedErrorLogKeys));

        $context['POST'] = $_POST;
        $context['RESPONSE'] = $response;

        return $context;
    }
}