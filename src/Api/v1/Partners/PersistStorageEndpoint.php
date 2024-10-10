<?php

namespace A24\Api\v1\Partners;


use A24\Api\ApiException;
use A24\Api\EndpointInterface;
use A24\Security\Access;
use Storage\Layers;
use Storage\S3;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class PersistStorageEndpoint implements EndpointInterface
{
    /**
     * @throws ApiException
     */
    public function authenticate(Request $request): void
    {
        if (\Config::Ini()->env_dev) {
            return;
        }

        $authorization = $request->headers->get('Authorization');
        if (isset($authorization)) {

            $token = str_replace('Bearer ', '', $authorization);
            if (Access::isApiClientAllowed($token)) {
                return;
            }
        }

        throw new ApiException("Not Allowed", Response::HTTP_UNAUTHORIZED);
    }

    /**
     * api/v1/partners/persistStorage
     *
     * @throws ApiException
     */
    public function proccess(Request $request): Response
    {
        $method = $request->getMethod();
        switch ($method) {
            case Request::METHOD_GET:
                return $this->GET($request);
            case Request::METHOD_POST:
                return $this->POST($request);
        }
        throw new ApiException("", Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * TESTING Simulate responses from partner
     * NB! This saves to dev S3 but we are actually using live S3
     * api/v1/partners/persistStorage/123abc
     *
     * @throws ApiException
     */
    private function GET(Request $request): Response
    {
        $uriParams = $request->attributes->get('uriParams');
        if (\Config::Ini()->env_dev) {
            $json = file_get_contents(__DIR__."/$uriParams[0].json");
            $request = Request::create('', Request::METHOD_POST, [], [], [], [], $json );
            return $this->POST($request);
        }
        throw new ApiException("", Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * This endpoint is used for testing in sandbox env.
     * This emdpoint in only executed in live env
     * 1) We start data request from sandbox
     * 2) Vininfo sends response to this endpoint if request was started from sandbox
     * 3) Response is saved to S3 (live)
     * 4) We read contents from S3 public url and parse response.
     * 5) If response good then save to db and show result to user
     */
    private function POST(Request $request): Response
    {
        $post = $request->toArray();
        if (count($post)) {
            $fname = "response.json";
            $layer = new Layers\VininfoResponse($fname);
            $storage = new S3($layer);
            $storage->putContent(json_encode($post));

            return new JsonResponse(['msg'=>'ok']);
        }
        return new JsonResponse(['msg'=>'Missing body'], Response::HTTP_BAD_REQUEST);
    }
}