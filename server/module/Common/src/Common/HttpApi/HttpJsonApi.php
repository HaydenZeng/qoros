<?php

namespace Common\HttpApi;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Json\Exception\RuntimeException;

class HttpJsonApi {
    
    protected function post($url, $data) {
        $request = new Request();
        $request->setUri($url);
        $request->setMethod('POST');
        $request->getPost()->fromArray($data);
    
        $client = new Client();
        $client->setEncType(Client::ENC_URLENCODED);
        $response = $client->dispatch($request);
        try {
            $result = Json::decode($response->getBody(), Json::TYPE_ARRAY);
            return $result;
        }catch (RuntimeException $e) {
            return $response->getBody();
        }
    }
    
    protected function get($url, $data, $options = array())
    {
        $request = new Request();
        $request->setUri($url);
        $request->setMethod('GET');
        $request->getQuery()->fromArray($data);

        $client = new Client();
        $client->setOptions($options);
        $response = $client->dispatch($request);
        try {
            $result = Json::decode($response->getBody(), Json::TYPE_ARRAY);
            return $result;
        }catch (RuntimeException $e) {
            return $response->getBody();
        }
    }

}

?>