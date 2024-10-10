<?php

namespace A24\Api\v1\Partners;


use A24\Api\ApiException;
use A24\Api\EndpointInterface;
use A24\Security\Access;
use A24\VehicleQueries\DataMapper;
use A24\VehicleQueries\DataMapperException;
use A24\VehicleQueries\DataModel;
use A24\VehicleQueries\Service as VehicleQueryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegistrationInfoEndpoint implements EndpointInterface
{
    private VehicleQueryService $vehicleQueryService;
    private DataMapper $dataMapper;

    public function __construct(
        VehicleQueryService $vehicleQueryService = null,
        DataMapper $dataMapper = null
    )
    {
        if (!$vehicleQueryService) {
            $this->vehicleQueryService = new VehicleQueryService();
        }

        if (!$dataMapper) {
            $this->dataMapper = new DataMapper();
        }
    }

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
     * api/v1/partners/registrationInfo
     *
     * @throws ApiException
     */
    public function proccess(Request $request): Response
    {
        $method = $request->getMethod();
        switch ($method) {
            case Request::METHOD_POST:
                return $this->POST($request);
        }
        throw new ApiException("", Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Our partner send car info to this endpoint
     * @throws ApiException
     * @throws \Exception
     */
    private function POST(Request $request): Response
    {
        $rawData = $request->toArray();

        //Some basic field checks
        if (!$rawData) {
            throw new ApiException("Missing body", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!isset($rawData['data'])) {
            throw new ApiException("Missing data block", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!isset($rawData['data']['technical_data'])) {
            throw new ApiException("Missing 'data.technical_data' block", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!isset($rawData['data']['licence_plate'])) {
            throw new ApiException("Missing 'data.licence_plate' field", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!isset($rawData['data']['technical_data']['vin'])) {
            throw new ApiException("Missing 'data.technical_data.vin' field", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $rawData["data"];
        $technicalData = $data['technical_data'];
        $regNr = $data['licence_plate'];
        $vin = $technicalData['vin'];

        $dataModel = new DataModel();

        //Add raw data as single attribute. Saved to DB as serialized string
        $dataModel->addData(['rawData' => $rawData]);

        try {

            //Only check mappings
            $dataModelTemp = new DataModel();
            $this->dataMapper->map($rawData, $dataModelTemp);

        } catch (DataMapperException $e) {

            //Raw data structure is changed
            throw new ApiException($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } finally {
            //Always save to db even if mapping fails. We still have raw data
            $this->vehicleQueryService->saveRegData($regNr, $vin, $dataModel);
        }

        return new JsonResponse(['msg'=>'ok']);
    }
}