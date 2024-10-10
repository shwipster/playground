<?php

namespace A24\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EndpointFactory
{
    /**
     * @throws \Exception
     */
    public static function build(Request $request): EndpointInterface
    {
        //Uri part from urlrewrite. Ex q='v1/vehicles/tax/123abc'
        $uri = $request->query->get('q');

        $args = explode('/', rtrim($uri, '/')); // Ex. ['v1', 'vehicles', 'tax', '123abc']

        $uriParams = [];
        while (count($args)) {

                $endpointName = self::generateEndpointName($args); // Ex. v1\Vehicles\Tax
                $className = self::generateClassName($endpointName); // Ex. A24\v1\vehicles\TaxEndpoint
                if (class_exists($className)) {

                    //Store rest of uri as potential parameters
                    $request->attributes->set('uriParams', array_reverse( $uriParams)); // Ex. ['123abc']
                    return new $className();
               }

               //Remove last element
               $uriParams[] = array_pop($args);
        }

        throw new ApiException("", Response::HTTP_NOT_FOUND);
    }

    private static function generateEndpointName(array $args): string
    {
        foreach ($args as &$arg) {

            //Version may be whatever folder name inside src/Api. Keep v1, v2 etc lowercase but everything to upper first
            if (!preg_match('/^v(\d+)$/', $arg)) {
                $arg = ucfirst($arg);
            }
        }

        return implode('\\',$args);
    }

    private static function generateClassName(string $endpointName): string
    {
        $class = sprintf(
            '%s\\%sEndpoint',
            __NAMESPACE__,
            $endpointName
        );

        return $class;
    }
}