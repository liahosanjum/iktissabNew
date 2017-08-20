<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 8/20/17
 * Time: 11:56 AM
 */

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\Response;

class FOpenWrapper
{


    private $uri;

    public function __construct()
    {
        if(!function_exists('fopen') || ini_get('allow_url_fopen') != 1){
            $this->exception();
        }
    }

    /**
     * @throws \Exception
     */
    private function exception(){
        throw new \Exception('fopen is disabled please enable it');
    }

    /**
     * @param string $method
     * @param array $headers
     * @param string $content
     * @return resource
     */
    private function getStreamContext(array $headers=array(), $method='GET', $content=''){
        $opt = [ 'http'=>['method'=>$method]];
        $h = '';
        if(!empty($headers)){
            foreach ($headers as $header=>$value){
                $h .= "$header: $value\r\n";
            }

            $opt['http']['header'] = $h;
        }

        if($content != ''){
            $opt['http']['header'] .= "Content-Length: " . strlen($content);
            $opt['http']['content'] = $content;
        }

        return stream_context_create($opt);
    }

    /**
     * @param $uri
     * @param array $headers
     * @return Response
     */
    public function get($uri, array $headers = array()){
        $this->uri = $uri;
        return $this->send($this->getStreamContext($headers));
    }

    /**
     * @param $uri
     * @param $payload
     * @param $headers
     * @return Response
     */
    public function post($uri, $payload, $headers){
        $this->uri = $uri;
        return $this->send($this->getStreamContext($headers, 'POST', $payload));
    }


    /**
     * @param resource $context
     * @return Response
     *
     * @throws \Exception
     */
    private function send($context){
        $fp = fopen($this->uri, 'r', false, $context);
        $metadata = stream_get_meta_data($fp);


        $status = explode(' ', $metadata['wrapper_data'][0]);
        $headers = $this->parse( $metadata['wrapper_data']);

        $content = stream_get_contents($fp);


        return new Response($content, $status[1] , $headers );
    }

    /**
     * Parse Http Headers in array
     *
     * @param  string $fields
     * @return array
     */
    private function parse($fields) {
        //$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers));

        if(empty($fields)) return [];

        return array_reduce($fields, function($carry, $field) {
            $match = [];
            if (!preg_match('/([^:]+): (.+)/m', $field, $match)) return $carry;

            $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./',function($matches) {
                return strtoupper($matches[0]);
            }, strtolower(trim($match[1])));

            $carry[$match[1]] = isset($carry[$match[1]]) ? [$carry[$match[1]], $match[2]] : trim($match[2]);

            return $carry;
        }, []);
    }
}