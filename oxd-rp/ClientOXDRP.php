<?php
	
	/**
	 * Gluu-oxd-library
	 *
	 * An open source application library for PHP
	 *
	 *
	 * @copyright Copyright (c) 2017, Gluu Inc. (https://gluu.org/)
	 * @license	  MIT   License            : <http://opensource.org/licenses/MIT>
	 *
	 * @package	  Oxd Library by Gluu
	 * @category  Library, Api
	 * @version   3.1.2
	 *
	 * @author    Gluu Inc.          : <https://gluu.org>
	 * @link      Oxd site           : <https://oxd.gluu.org>
	 * @link      Documentation      : <https://gluu.org/docs/oxd/3.0.1/libraries/php/>
	 * @director  Mike Schwartz      : <mike@gluu.org>
	 * @support   Support email      : <support@gluu.org>
	 * @developer Volodya Karapetyan : <https://github.com/karapetyan88> <mr.karapetyan88@gmail.com>
	 *
	 
	 *
	 * This content is released under the MIT License (MIT)
	 *
	 * Copyright (c) 2017, Gluu inc, USA, Austin
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 *
	 */
	
	/**
	 * Client OXD RP class
	 *
	 * Class is basic, which is connecting to oxd-server via socket
	 *
	 * @package		  Gluu-oxd-library
	 * @subpackage	Libraries
	 * @category	  Base class for all protocols
	 */

	abstract class ClientOXDRP{

    protected $data = array();
    protected $command;
    protected $params = array();
    protected $response_json;
    protected $response_object;
    protected $response_data = array();
    protected static $socket = null;


    /**
     * abstract Client_oxd constructor.
     */
    public function __construct()
    {
        $oxd_config = get_option('gluu_oxd_config');

        $this->setCommand();

    }
    /**
     * request to oxd socket
     **/
    public function oxd_socket_request($data, $char_count = 8192){
        $oxd_config = get_option('gluu_oxd_config');
        self::$socket = stream_socket_client('127.0.0.1:' . $oxd_config['oxd_host_port'], $errno, $errstr, STREAM_CLIENT_PERSISTENT);
        if (!self::$socket) {
            return 'socket_error';
        }else{
            fwrite(self::$socket, $data);
            $result = fread(self::$socket, $char_count);
            fclose(self::$socket);
            return $result;
        }

    }
    /**
     * request to oxd http
     **/
    public function oxd_http_request($url,$data){
        $headers = ["Content-type: application/json"];
        $data = json_decode($data,true);
        if(array_key_exists('protection_access_token',$data)){
            $headers[] = "Authorization: Bearer ".$data['protection_access_token'];
            unset($data['protection_access_token']);
        }
        $data = json_encode($data,true);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        //Remove these lines while using real https instead of self signed
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //remove above 2 lines
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                        $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ( $status != 201 && $status != 200) {
            die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        }


        curl_close($curl);
        $result = $json_response;
        return $result;

    }
    /**
     * send function sends the command to the oxD server.
     *
     * Args:
     * command (dict) - Dict representation of the JSON command string
     **/
    public function request($url=null)
    {
        $this->setParams();

        $jsondata = json_encode($this->getData(), JSON_UNESCAPED_SLASHES);

        $lenght = strlen($jsondata);
        if($lenght<=0){
            return array('status'=> false, 'message'=> 'Sorry .Problem with oxd.');
        }else{
            $lenght = $lenght <= 999 ? "0" . $lenght : $lenght;
        }
        if($url)
        {
            $jsonHttpData = json_encode($this->getData()["params"]);
            $this->response_json = $this->oxd_http_request($url,$jsonHttpData);
        }
        else
        {
            $this->response_json =  $this->oxd_socket_request(utf8_encode($lenght . $jsondata));
            $this->response_json = str_replace(substr($this->response_json, 0, 4), "", $this->response_json);
        }
        if($this->response_json !='socket_error'){
//            $this->response_json = str_replace(substr($this->response_json, 0, 4), "", $this->response_json);
            if ($this->response_json) {
                $object = json_decode($this->response_json);
                if ($object->status == 'error') {
                    if($object->data->error == "invalid_op_host"){
                        return array('status'=> false, 'message'=> $object->data->error);
                    }elseif($object->data->error == "internal_error"){
                        return array('status'=> false, 'message'=> $object->data->error , 'error_message'=>$object->data->error_description);
                    }else{
                        return array('status'=> false, 'message'=> $object->data->error . ' : ' . $object->data->error_description);
                    }
                } elseif ($object->status == 'ok') {
                    $this->response_object = json_decode($this->response_json);
                    return array('status'=> true);
                }
            }
        }else{
            return array('status'=> false, 'message'=> 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
        }

    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        if (!$this->getResponseObject()) {
            $this->response_data = 'Data is empty';
            return;
        } else {
            $this->response_data = $this->getResponseObject()->data;
        }
        return $this->response_data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $this->data = array('command' => $this->getCommand(), 'params' => $this->getParams());
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    abstract function setCommand();

    /**
     * getResult function geting result from oxD server.
     * Return: response_object - The JSON response parsing to object
     **/
    public function getResponseObject()
    {
        return $this->response_object;
    }

    /**
     * function getting result from oxD server.
     * return: response_json - The JSON response from the oxD Server
     **/
    public function getResponseJSON()
    {
        return $this->response_json;
    }

    /**
     * @param array $params
     */
    abstract function setParams();

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

}
