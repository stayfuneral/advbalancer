<?php


include '../../../inc/includes.php';

$request = json_decode(file_get_contents('php://input'));
$response = [];

$requestType = $request->type;

switch ($requestType) {
    case 'get_limit':

        $limit = PluginAdvbalancerConfigs::getInstance()->getUserTicketsLimit();
        $response['limit'] = $limit;

        break;
    case 'set_limit':
        $limit = $request->limit;

        if(PluginAdvbalancerConfigs::getInstance()->setUserTicketsLimit($limit)) {
            $response = [
                'result' => 'success',
                'limit' => $limit
            ];
        }
}

echo json_encode($response);