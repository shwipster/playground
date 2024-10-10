<?php

include_once ($_SERVER["DOCUMENT_ROOT"]."atinclude/common.php"); //need config()
include_once ($_SERVER["DOCUMENT_ROOT"]."atinclude/db_functions.php"); //need config()

$request = request();

//Temp fix. This file should be one level down so that v1/ is already in request
//Legacy code has some other api logic on level down
$q = "v1/" . $request->query->get('q');
$request->query->set('q', $q);

$api = new \A24\Api\Api();
$api->handle($request);