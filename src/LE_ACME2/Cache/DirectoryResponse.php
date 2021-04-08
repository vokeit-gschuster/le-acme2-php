<?php
namespace LE_ACME2\Cache;

use LE_ACME2\Connector;

use LE_ACME2\Account;
use LE_ACME2\SingletonTrait;

use LE_ACME2\Exception;
use LE_ACME2\Request;
use LE_ACME2\Response;

class DirectoryResponse {
    
    use SingletonTrait;

    private const _FILE = 'DirectoryResponse';
    
    private function __construct() {}
    
    private $_response = null;

    /**
     * @return Response\GetDirectory
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function get() : Response\GetDirectory {

        if($this->_response === NULL) {

            $cacheFile = Account::getCommonKeyDirectoryPath() . self::_FILE;

            if(file_exists($cacheFile) && filemtime($cacheFile) > strtotime('-2 days')) {

                $rawResponse = Connector\RawResponse::getFromString(file_get_contents($cacheFile));

                try {
                    return $this->_response = new Response\GetDirectory($rawResponse);

                } catch(Exception\AbstractException $e) {
                    unlink($cacheFile);
                }
            }

            $request = new Request\GetDirectory();
            $this->set($request->getResponse());
        }

        return $this->_response;
    }

    public function set(Response\GetDirectory $response) : void {

        $cacheFile = Account::getCommonKeyDirectoryPath() . self::_FILE;

        $this->_response = $response;
        file_put_contents($cacheFile, $this->_response->getRaw()->toString());
    }
}