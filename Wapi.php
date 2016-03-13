<?php
/**
 * Wykop API PHP CLient
 * You can use this library to connect to Wykop.pl API
 *
 * @author: maciejkiner
 * @version: 1.0
 */

class libs_Wapi
{
    protected $userAgent = 'WykopAPI';
    protected $apiDomain = 'http://a.wykop.pl/';
    protected $key = null;
    protected $secret = null;
    protected $userKey = null;
    protected $format = 'json';
    protected $outputFormat = 'clear';
    protected $isValid;
    protected $error;

    public static $API_VERSION_1 = "v1";
    public static $API_VERSION_2 = "v2";

    /**
     * Constructor
     *
     * @param string $key - appkey
     * @param string $secret - appsecret
     * @param string $outputFormat - output format
     *
     */
    public function __construct($key, $secret, $outputFormat = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        if ($output !== null) {
            $this->outputFormat = $output;
        }
    }

    /**
     * Make an API request
     *
     * @param string $action - resource, ex. "links/upcoming"
     * @param array $postData - post data
     * @param array $filesData - files, ex. array('embed' => "@plik.jpg;type=image/jpeg")
     *
     * @return array - API response
     */

    public function doRequest($action, $postData = null, $filesData = null)
    {
        $url = $this->apiDomain . $action .= (strpos($action, ',') ? ',' : '/') . $this->getKey() . $this->getFormat() . $this->getOutputFormat() . $this->getUserKey();
        if ($postData === null) {
            $result = $this->curl($url);
        } else {
            if ($filesData !== null) {
                $postData = $filesData + $postData;
            }
            $result = $this->curl($url, $postData);
        }
        $this->checkIsValid($result);
        return $this->isValid ? json_decode($result['content'], true) : array();
    }


    /**
     * Is request valid?
     *
     * @return bool - is valid
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Last request error
     *
     * @return string - error message
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set user key - all future request will be made with this user auth data
     *
     * @param string $userKey - user key
     */
    public function setUserKey($userKey)
    {
        $this->userKey = $userKey;
    }

    /**
     * Generate Wykop Connect link
     *
     * @param string $redirectUrl - (optional) redirect link after valid login
     * @return string - link to Wykop Connect
     */
    public function getConnectUrl($redirectUrl = null)
    {
        $url = $this->apiDomain . 'user/connect/' . $this->getKey();
        if ($redirectUrl !== null) {
            $url .= 'redirect/' . urlencode(base64_encode($redirectUrl)) . '/';
            $url .= 'secure/' . md5($this->secret . $redirectUrl);
        }
        return $url;
    }

    /**
     * Decode Wykop Connect data
     *
     * @return array - array with connect data (appkey, login, token) - use it to login user
     */
    public function handleConnectData()
    {
        if (!empty($_GET['connectData'])) {
            $data = base64_decode($_GET['connectData']);
            return json_decode($data, true);
        }
    }

    protected function checkIsValid(&$result)
    {
        $this->error = null;
        if (empty($result['content'])) {
            $this->isValid = false;
        } else {
            $json = json_decode($result['content'], true);
            if (!empty($json['error'])) {
                $this->isValid = false;
                $this->error = $json['error']['message'];
            } else {
                $this->isValid = true;
            }
        }
    }

    protected function getUserKey()
    {
        if (!empty($this->userKey)) {
            return 'userkey/' . $this->userKey . '/';
        }
    }


    protected function getFormat()
    {
        if (!empty($this->format)) {
            return 'format/' . $this->format . '/';
        }
    }

    protected function getOutputFormat()
    {
        if (!empty($this->outputFormat)) {
            return 'output/' . $this->outputFormat . '/';
        }
    }

    protected function getKey()
    {
        if (!empty($this->key)) {
            return 'appkey/' . $this->key . '/';
        }
    }

    protected function sign($url, $post = null)
    {
        if ($post !== null) {
            ksort($post);
        }
        return md5($this->secret . $url . ($post === null ? '' : implode(',', $post)));
    }


    protected function curl($url, $post = null)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => array('apisign:' . $this->sign($url, $post))
        );

        if ($post !== null) {
            $postValue = is_array($post) ? http_build_query($post, 'f_', '&') : '';
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $postValue;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $result = curl_getinfo($ch);
        curl_close($ch);

        $result['errno'] = $err;
        $result['errmsg'] = $errmsg;
        $result['content'] = $content;
        return $result;
    }
}