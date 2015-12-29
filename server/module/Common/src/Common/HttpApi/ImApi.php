<?php
namespace Common\HttpApi;

use User\Entity\UserEntity;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\AdapterInterface;
use Zend\Config\Reader\Xml;

class ImApi {
    protected $adapter;
    protected $client;
    protected $reader;
    
    //online
    protected $secret;
    protected $uri;
    
    public function __construct(AdapterInterface $adapter, $host, $port, $secret) {
        $this->secret = $secret;
        $this->uri = "http://{$host}:{$port}/plugins/userService/userservice";
        $this->adapter = $adapter;    
        $this->client = new Client();
        $this->client->setAdapter($adapter);
        $this->reader = new Xml();
    }
    
    public function create(UserEntity $userEntity) {
        $params = array();
        $params["type"] = "add";
        $params["secret"] = $this->secret;
        $params["username"] = 'eck' .'_'.$userEntity->username .'_'. $userEntity->id;
        //默认6个1
        $params["password"] = "111111";
        $params["name"] = $userEntity->username;
        $this->client->setParameterGet($params);
        $this->client->setUri($this->uri);
        $response = $this->client->send();
        $data = $this->reader->fromString($response->getBody());
        
        $result = false;
        if ($data == "ok" || $data == "UserAlreadyExistsException") {
        	   $result = true;
        }
        return $result;
    }
}

?>