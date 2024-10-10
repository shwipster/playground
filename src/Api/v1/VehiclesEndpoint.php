<?php

namespace A24\Api\v1;

use A24\Api\ApiException;
use A24\Api\EndpointInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VehiclesEndpoint implements EndpointInterface
{
    /**
     * @throws ApiException
     */
    public function authenticate(Request $request): void
    {
        if (\Config::Ini()->env_dev) {
            return;
        }

        $ref = $request->server->get('HTTP_REFERER');
        if (isset($ref)) {

            $paramsRef = parse_url($ref);
            $paramsHost = parse_url(Config()->siteInfo->site_url);
            if ($paramsRef['host']===$paramsHost['host'])
            {
                return;
            }
        }

        throw new ApiException("Not Allowed", Response::HTTP_UNAUTHORIZED);
    }

    /**
     * api/v1/vehicles
     *
     * @throws ApiException
     */
    public function proccess(Request $request): Response
    {
        $method = $request->getMethod();
        switch ($method) {
            case Request::METHOD_GET:
                return $this->GET($request);
        }

        throw new ApiException("", Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Example
     *
     * @throws ApiException
     */
    private function GET(Request $request): Response
    {
        throw new ApiException("", Response::HTTP_METHOD_NOT_ALLOWED);
    }
}