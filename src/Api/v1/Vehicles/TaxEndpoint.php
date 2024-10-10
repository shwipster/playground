<?php

namespace A24\Api\v1\Vehicles;


use A24\Api\ApiException;
use A24\Api\EndpointFactory;
use A24\Api\EndpointInterface;
use A24\Log\Logger;
use A24\VehicleQueries\DataMapperException;
use A24\VehicleQueries\DataModel;
use A24\VehicleQueries\Service as VehicleQueryService;
use A24\VehicleTax\Factory as VehicleTaxFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaxEndpoint implements EndpointInterface
{
    private VehicleQueryService $vehicleQueryService;
    private VehicleTaxFactory $vehicleTaxFactory;
    private Logger $logger;
    private EndpointFactory $endpointFactory;

    public function __construct(
        VehicleQueryService $vehicleQueryService = null,
        VehicleTaxFactory $vehicleTaxFactory = null,
        Logger $logger = null,
        EndpointFactory $endpointFactory = null
    )
    {
        if (!$vehicleQueryService) {
            $this->vehicleQueryService = new VehicleQueryService();
        }

        if (!$vehicleTaxFactory) {
            $this->vehicleTaxFactory = new VehicleTaxFactory();
        }

        if (!$logger) {
            $this->logger = new Logger("API");
        }

        if (!$endpointFactory) {
            $this->endpointFactory = new EndpointFactory();
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

        $ref = $request->server->get('HTTP_REFERER');
        if (isset($ref)) {

            $paramsRef = parse_url($ref);
            $paramsHost = parse_url(Config()->siteInfo->site_url);
            if ($paramsRef['host']===$paramsHost['host']) {
                return;
            }
        }

        throw new ApiException("Not Allowed", Response::HTTP_UNAUTHORIZED);
    }

    /**
     * api/v1/vehicles/tax/{regNr}/{checkFlag}
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
     * Get car tax based on registration info stored in our DB
     * If no data in our DB then send request to parnter API
     * and poll until we get response from them
     * @throws ApiException
     * @throws \Exception
     */
    private function GET(Request $request): Response
    {
        $uriParams = $request->attributes->get('uriParams');
        $regNr = (string)$uriParams[0]; //First url parameter

        //User inserted fields manually. Create model and proceed to calculations
        if ($regNr==='manual') {

            $fields = $request->toArray();
            $dataModel = new DataModel($fields);
            $data = $this->calculatorOutput($dataModel);

        } else {
            if (!$regNr) {
                throw new ApiException("Endpoint is missing registration number. Format is: {$_SERVER['REQUEST_URI']}/{regNr}", Response::HTTP_BAD_REQUEST);
            }

            $this->readResponseFromS3(); //For dev only

            //Load data from database
            $dataModel = null;
            try {
                $dataModel = $this->vehicleQueryService->getRegData($regNr);
            } catch(DataMapperException $e) {
                //Mark data as invalid so we dont get mapping exeption on sequential requests
                $this->vehicleQueryService->markRegDataInvalid($regNr);

                //Notify us but continue with execution. Data is fetched again since $datamodel=null
                if (!\Config::Ini()->env_dev) {
                    $this->logger->error($e, ["regNr"=>$regNr]);
                }
            }

            if ($dataModel && $dataModel->validate()) { //Model in db with valid fields

                $data = $this->calculatorOutput($dataModel);

            } else if($dataModel===null) { // No model or mapping failes.

                $checkFlag = isset($uriParams[1]); //Second url parameter
                if (!$checkFlag) {
                    $this->vehicleQueryService->fetchRegData($regNr);
                }
                $data = ['check'=>true]; //JS appends check to uri. vehicles/tax/{regNr}/check

            } else { //Model in db but with invalid fields

                //Raw data is OK. We just need to notify us and make sure that fields in model are all used in datamapper and with same names
                $missingRequiredFields = $dataModel->getUninitializedFields();
                throw new \Exception('Missing fields: '.implode(',', $missingRequiredFields));
            }
        }
        return new JsonResponse($data);
    }


    /**
     * This endpoint is used when manually inserting fields
     * @throws ApiException
     */
    private function POST(Request $request): Response
    {
        return $this->GET($request);
    }

    /**
     * Hack for sandbox development
     * Response is sent to live endpoint and saved to live S3
     * Read contents from live s3 public address and save to sandbox DB
     *
     * @throws \Exception
     */
    private function readResponseFromS3(): void
    {
        if (\Config::Ini()->env_dev) {
            try {
                //$s3public = "https://a24-static-dev.img-bcg.eu/vininfo/response.json";
                $s3public = "https://a24-static.img-bcg.eu/vininfo/response.json";
                $json = file_get_contents($s3public);
                if ($json) {
                    $req = Request::create('', Request::METHOD_POST, [], [], [], [], $json );
                    $req->query->set('q','v1/partners/registrationInfo');
                    $endpoint = $this->endpointFactory::build($req);
                    $endpoint->proccess($req);
                }
            } catch(ApiException $e) {
                //Catch here so program can continue. SImlating POST from partner
            }
        }
    }

    /**
     * @throws ApiException
     */
    private function calculatorOutput(DataModel $dataModel): array
    {
        try {
            $calc = $this->vehicleTaxFactory::build($dataModel->category, $dataModel);
            $data = [
                'annual' => $calc->annualTax(),
                'registration' => $calc->registrationTax()
            ];
            $data['formated'] = $this->formatResponseFields($data, $dataModel);
            return $data;

        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Create a list of formated fields used in FE forms etc
     */
    private function formatResponseFields(array $respData, DataModel $dataModel): array
    {
        $matches = [];
        $productionYear = '';
        if( preg_match('/(\d{4})-/', $dataModel->dateRegistered, $matches) ) {
            $productionYear = $matches[1];
        }

        $type = uc_tr("form_type_$dataModel->category", 231);
        if ($dataModel->category == 'M1' || $dataModel->category == 'N1') {
            $type .= " ($dataModel->category)";
        }


        $engine = "";
        if ($dataModel->fuelType == DataModel::FUEL_TYPE_ELECTRIC) {
            $engine = uc_tr("form_engine_electric", 231);
        } else {
            if ($dataModel->fuelType == DataModel::FUEL_TYPE_PETROL) {
                $fuel = tr("form_engine_petrol", 231);
            } else {
                $fuel = tr("form_engine_diesel", 231);
            }

            if ( $dataModel->hybridType == DataModel::HYBRID_TYPE_NONE ) {
                $engine = uc_tr("form_engine_compustion", 231) . " ($fuel)";
            } else if ($dataModel->hybridType == DataModel::HYBRID_TYPE_PLUGIN) {
                $engine = uc_tr("form_engine_hybrid_plug", 231) . " ($fuel)";
            } else if ($dataModel->hybridType == DataModel::HYBRID_TYPE_HYBRID) {
                $engine = uc_tr("form_engine_hybrid", 231) . " ($fuel)";
            } else {
                //Should not happen
            }
        }

        $co2Extra = '';
        if ($dataModel->co2Method == DataModel::CO2_METHOD_WLTP) {
            $method = tr("form_co2_method_wltp", 231);
        } else if ($dataModel->co2Method == DataModel::CO2_METHOD_NEDC){
            $method = tr("form_co2_method_nedc", 231);
        } else {
            $method = '';
        }

        $co2 = $dataModel->co2 . " " . tr("form_unit_co2", 231) . " ($method)";

        $fields = [
            'annual' => number_format($respData['annual'],2) . " €",
            'registration' => number_format($respData['registration'],2) . " €",
            'makeModel' => $dataModel->make . " " . $dataModel->model,
            'productionYear' => $productionYear,
            'type' => $type,
            'engine' => $engine,
            'co2' => $co2,
            'co2_extra' => $co2Extra,
            'grossWeight' => $dataModel->grossWeight . tr('form_unit_kg',231)
        ];

        return $fields;
    }
}