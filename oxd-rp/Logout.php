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
	 * Client Logout class
	 *
	 * Class is connecting to oxd-server via socket, and doing logout from gluu-server.
	 *
	 * @package		  Gluu-oxd-library
	 * @subpackage	Libraries
	 * @category	  Relying Party (RP)
	 * @see	        Client_OXD_RP
	 */
	require_once 'ClientOXDRP.php';
	
	class Logout extends ClientOXDRP
{
    /**start parameter for request!**/
    private $request_oxd_id = null;
    private $request_id_token = null;
    private $request_post_logout_redirect_uri = null;
    private $request_session_state = null;
    private $request_state = null;
    /**end request parameter**/

    /**start parameter for response!**/
    private $response_html;
    
    /**
    * @var string $request_access_token     access token for each request
    */
    private $request_access_token;
    /**end response parameter**/

    function getRequest_access_token() {
        return $this->request_access_token;
    }

    function setRequest_access_token($request_access_token) {
        $this->request_access_token = $request_access_token;
    }

    public function __construct()
    {
        parent::__construct(); // TODO: Change the autogenerated stub
    }

    /**
     * @return null
     */
    public function getRequestState()
    {
        return $this->request_state;
    }

    /**
     * @param null $request_state
     */
    public function setRequestState($request_state)
    {
        $this->request_state = $request_state;
    }

    /**
     * @return null
     */
    public function getRequestSessionState()
    {
        return $this->request_session_state;
    }

    /**
     * @param null $request_session_state
     */
    public function setRequestSessionState($request_session_state)
    {
        $this->request_session_state = $request_session_state;
    }


    /**
     * @param null $request_post_logout_redirect_uri
     */
    public function setRequestPostLogoutRedirectUri($request_post_logout_redirect_uri)
    {
        $this->request_post_logout_redirect_uri = $request_post_logout_redirect_uri;
    }

    /**
     * @return mixed
     */
    public function getResponseHtml()
    {
        return $this->response_html;
    }

    /**
     * @return null
     */
    public function getRequestIdToken()
    {
        return $this->request_id_token;
    }

    /**
     * @return null
     */
    public function getRequestPostLogoutRedirectUri()
    {
        return $this->request_post_logout_redirect_uri;
    }

    /**
     * @param null $request_id_token
     */
    public function setRequestIdToken($request_id_token)
    {
        $this->request_id_token = $request_id_token;
    }

    /**
     * @return mixed
     */
    public function getRequestOxdId()
    {
        return $this->request_oxd_id;
    }

    /**
     * @param mixed $request_oxd_id
     */
    public function setRequestOxdId($request_oxd_id)
    {
        $this->request_oxd_id = $request_oxd_id;
    }



    public function setCommand()
    {
        $this->command = 'get_logout_uri';
    }

    public function setParams()
    {
        $this->params = array(
            "oxd_id" => $this->getRequestOxdId(),
            "id_token_hint" => $this->getRequestIdToken(),
            "post_logout_redirect_uri" => $this->getRequestPostLogoutRedirectUri(),
            "state" => $this->getRequestState(),
            "session_state" => $this->getRequestSessionState(),
            "protection_access_token" => $this->getRequest_access_token()
        );
    }

}
