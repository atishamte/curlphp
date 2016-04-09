<?php

    /**
     * PHP Curl Class
     *
     * Work with remote servers via cURL much easier than using the native PHP bindings.
     *
     * @package        	phpCurl
     * @subpackage    	Libraries
     * @category    	Libraries
     * @author        	Atish Amte
     */

    namespace curlphp;

    class curlphp
    {
        // Curl object
        private $_curl;

        // Curl setters
        private $_Url;
        private $_method;
        private $_Data;
        private $_options = array();
        private $_headers = array();
        private $_requestTimeout = 30;

        // Curl response
        private $_response = '';
        private $_last_response = '';

        // Curl error info
        private $_error_code;
        private $_error_string;
        private $_info;

        // Constructor

        function __construct()
        {
            try {
                if ($this->is_enabled()) {
                    $this->_curl = curl_init();
                } else {
                    throw new \Exception("PHP cURL is not enabled on server.");
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Setter Methods

        function setOption($code, $value, $prefix = 'opt')
        {
            try {
                if (is_string($code) && !is_numeric($code)) {
                    $code = constant('CURL' . strtoupper($prefix) . '_' . strtoupper($code));
                }
                if(is_numeric($code)) {
                    $this->_options[$code] = $value;

                    return $this;
                } else {
                    throw new \Exception("Invalid use of constant code.");
                }

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        function setMethod($method)
        {
            try {
                if(in_array(strtoupper($method),['POST','PUT','GET','PATCH','DELETE'])) {
                    $this->_method = $method;
                    $this->_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);

                    return $this;
                } else {
                    throw new \Exception("Invalid HTTP request method used.");
                }

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        function setUrl($Url)
        {
            try {
                if (filter_var($Url, FILTER_VALIDATE_URL) !== false) {
                    $this->_Url = $Url;
                } else {
                    throw new \Exception("Invalid URL Passed.");
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        function setSSL($verify_peer = true, $verify_host = 2, $path_to_cert = null)
        {
            if ($verify_peer) {
                $this->setOption(CURLOPT_SSL_VERIFYPEER, true);
                $this->setOption(CURLOPT_SSL_VERIFYHOST, $verify_host);
                if (isset($path_to_cert)) {
                    $path_to_cert = realpath($path_to_cert);
                    $this->setOption(CURLOPT_CAINFO, $path_to_cert);
                }
            } else {
                $this->setOption(CURLOPT_SSL_VERIFYPEER, false);
                $this->setOption(CURLOPT_SSL_VERIFYHOST, $verify_host);
            }

            return $this;
        }

        function setData($Data)
        {
            try {
                if (!empty($Data)) {
                    $this->_Data = $Data;
                } else {
                    throw new \Exception("No data found for request.");
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        function setRequestTimeout($requestTimeout)
        {
            try {
                if(is_numeric($requestTimeout) || $requestTimeout <= 300){
                    $this->_requestTimeout = (int)$requestTimeout;
                } else {
                    throw new \Exception("Invalid request timeout passed.");
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        function set_cookies($params = array())
        {
            try {
                if(!empty($params)){
                    if (is_array($params)) {
                        $params = http_build_query($params, null, '&');
                    }
                    $this->setOption(CURLOPT_COOKIE, $params);

                    return $this;
                } else {
                    throw new \Exception("Invalid cookie parameters passed.");
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        function http_header($header, $content = null)
        {
            $this->_headers[] = $content ? $header . ': ' . $content : $header;

            return $this;
        }

        function http_login($username = '', $password = '', $type = 'any')
        {
            $this->setOption(CURLOPT_HTTPAUTH, constant('CURLAUTH_' . strtoupper($type)));
            $this->setOption(CURLOPT_USERPWD, $username . ':' . $password);

            return $this;
        }

        function proxy($url = '', $port = 80)
        {
            $this->setOption(CURLOPT_HTTPPROXYTUNNEL, true);
            $this->setOption(CURLOPT_PROXY, $url . ':' . $port);

            return $this;
        }

        function proxy_login($username = '', $password = '')
        {
            $this->setOption(CURLOPT_PROXYUSERPWD, $username . ':' . $password);

            return $this;
        }

        function options($options = array())
        {
            foreach ($options as $option_code => $option_value) {
                $this->setOption($option_code, $option_value);
            }

            curl_setopt_array($this->_curl, $this->_options);

            return $this;
        }

        // HTTP Request Methods

        function get()
        {
            $this->_Url = $this->_Url . ($this->_Data ? '?' . http_build_query($this->_Data, null, '&') : '');
        }

        function post()
        {
            if (is_array($this->_Data)) {
                $this->_Data = http_build_query($this->_Data, null, '&');
            }

            $this->setMethod('post');
            $this->setOption(CURLOPT_POST, true);
            $this->setOption(CURLOPT_POSTFIELDS, $this->_Data);
        }

        function put()
        {
            if (is_array($this->_Data)) {
                $this->_Data = http_build_query($this->_Data, null, '&');
            }

            $this->setMethod('put');
            $this->setOption(CURLOPT_POSTFIELDS, $this->_Data);
            $this->setOption(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
        }

        function patch()
        {
            if (is_array($this->_Data)) {
                $this->_Data = http_build_query($this->_Data, null, '&');
            }

            $this->setMethod('patch');
            $this->setOption(CURLOPT_POSTFIELDS, $this->_Data);
            $this->setOption(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PATCH'));
        }

        function delete()
        {
            if (is_array($this->_Data)) {
                $this->_Data = http_build_query($this->_Data, null, '&');
            }

            $this->setMethod('delete');
            $this->setOption(CURLOPT_POSTFIELDS, $this->_Data);
        }

        // Curl Methods

        function executeCurl()
        {
            curl_setopt($this->_curl, CURLOPT_URL, $this->_Url);

            $this->_options[CURLOPT_TIMEOUT] = $this->_requestTimeout;
            $this->_options[CURLOPT_CONNECTTIMEOUT] = $this->_requestTimeout;

            if (!isset($this->_options[CURLOPT_RETURNTRANSFER])) {
                $this->_options[CURLOPT_RETURNTRANSFER] = true;
            }
            if (!isset($this->_options[CURLOPT_FAILONERROR])) {
                $this->_options[CURLOPT_FAILONERROR] = true;
            }

            if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
                if (!isset($this->_options[CURLOPT_FOLLOWLOCATION])) {
                    $this->_options[CURLOPT_FOLLOWLOCATION] = true;
                }
            }

            $this->{$this->_method}();

            if (!empty($this->_headers)) {
                $this->setOption(CURLOPT_HTTPHEADER, $this->_headers);
            }
            $this->options();

            $this->_response = curl_exec($this->_curl);

            $this->_info = curl_getinfo($this->_curl);

            if ($this->_response === false) {
                $errno = curl_errno($this->_curl);
                $error = curl_error($this->_curl);
                curl_close($this->_curl);
                $this->setDefaults();
                $this->_error_code = $errno;
                $this->_error_string = $error;

                return false;
            } else {
                curl_close($this->_curl);
                $this->_last_response = $this->_response;
                $this->setDefaults();

                return $this->_last_response;
            }
        }

        // Other Methods
        function is_enabled()
        {
            return function_exists('curl_init');
        }

        function debug()
        {
            echo "=============================================<br/>\n";
            echo "<h2>CURL Test</h2>\n";
            echo "=============================================<br/>\n";
            echo "<h3>Response</h3>\n";
            echo "<code>" . nl2br(htmlentities($this->_last_response)) . "</code><br/>\n\n";
            if ($this->_error_string) {
                echo "=============================================<br/>\n";
                echo "<h3>Errors</h3>";
                echo "<strong>Code:</strong> " . $this->_error_code . "<br/>\n";
                echo "<strong>Message:</strong> " . $this->_error_string . "<br/>\n";
            }
            echo "=============================================<br/>\n";
            echo "<h3>Info</h3>";
            echo "<pre>";
            print_r($this->_info);
            echo "</pre>";
        }

        function setDefaults()
        {
            $this->_response = '';
            $this->_headers = array();
            $this->_options = array();
            $this->_error_code = null;
            $this->_error_string = '';
            $this->_curl = null;
        }
    }