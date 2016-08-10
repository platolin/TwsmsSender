<?php

namespace TwsmsSender;

class TwsmsSender 
{	
 	/**
     * @var string
     */
    const SEND_SMS_URL = 'https://api.twsms.com/smsSend.php';

    /**
     * @var string
     */
    const SMS_STATUS_URL = 'https://api.twsms.com/smsQuery.php';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

	public function __construct($username, $password)
    {    
        if (null === $username || null === $password) {
            throw new Exception\InvalidCredentialsException('No API credentials provided');
        }

        $this->username = $username;
        $this->password = $password;
    }
    public function querypoint()
    {

        $params = $this->getParameters(
            array(
                'checkpoint' => 'Y',
                )
        );        
        $res = $this->getContent(self::SMS_STATUS_URL, 'POST', $headers = array(), $params);     
        return $this->parseQueryResults($res);
    }
    public function query($recipient , $msgid )
    {       
        $params = $this->getParameters(
            array(
                'mobile' => $recipient,
                'msgid' => $msgid,
                )
        );        
        $res = $this->getContent(self::SMS_STATUS_URL, 'POST', $headers = array(), $params);             
        return $this->parseQueryResults($res);
    }
    public function deltime($recipient , $msgid )
    {
        $params = $this->getParameters(
            array(
                'mobile' => $recipient,
                'msgid' => $msgid,
                'deltime' => 'Y',
                )
        );        
        $res = $this->getContent(self::SMS_STATUS_URL, 'POST', $headers = array(), $params);                     
        return $this->parseQueryResults($res);
    }

	public function send( $recipient , $smsmessage , $dlvtime )
	{		
        $params = $this->getParameters(
            array(
                'mobile' => $recipient,
                'sendtime' => $dlvtime,
                'message' => $this->getMessage($smsmessage),
            )
        );

        $extra_result_data = array(
            'recipient' => $recipient,
            'body' => $smsmessage,
        );

        $res = $this->getContent(self::SEND_SMS_URL, 'POST', $headers = array(), $params);
        var_dump($res);
        return $this->parseSendResults($res, $extra_result_data);
	}
     /**
     * Parses the data returned by the API for a "query" request.
     *
     * @param string $result The raw result string.
     * @param array $extra_result_data
     *
     * @return array
     */
    protected function parseQueryResults($result, array $extra_result_data = array())
    {
        $xml = simplexml_load_string($result);  
        // The message was successfully sent!
        $sms_data['point'] = $xml->point;
        $sms_data['code'] = $xml->code;
        $sms_data['text'] = $xml->text; 
        
        return array_merge($extra_result_data , $sms_data);        
    }
    /**
     * Parses the data returned by the API for a "send" request.
     *
     * @param string $result The raw result string.
     * @param array $extra_result_data
     *
     * @return array
     */
    protected function parseSendResults($result, array $extra_result_data = array())
    {

        $xml = simplexml_load_string($result);        

        $sms_data['id'] = $xml->msgid;
        $sms_data['code'] = $xml->code;
        $sms_data['text'] = $xml->text;
        $sms_data['status'] = $xml->statustext;

        return array_merge($extra_result_data , $sms_data);        
    }    

    protected function isNotNull($var)
    {
        return !is_null($var);
    }

    /**
     * Builds the parameters list to send to the API.
     *
     * @return array
     */
    protected function getParameters(array $additionnal_parameters = array())
    {
        return array_filter(
            array_merge(
                array(
                    /*
                     * Account username (case sensitive)
                     */
                    'username' => $this->username,
                    /*
                     * Account password (case sensitive)
                     */
                    'password' => $this->password,
                    /*
                     * -- sendtime
                     * 格式：YYYYMMDDHHII （請使用 24 小時制）
                     * 預約時間，例如 201504121830
                     */
                    'sendtime' => null,
                    /*
                     * -- expirytime
                     *簡訊有效期限，單位為秒，範圍：300~86400 秒
                     *例如: 86400 為 24 小時
                     */
                    'expirytime' => null,
                    /*
                     * -- message
                     * The SMS text for plain messages or
                     * UCS2 hex for Unicode. For binary,
                     * hex encoded 8-bit data.
                     */
                    'message' => null,

                ),
                $additionnal_parameters
            ),
            array($this, 'isNotNull')
        );
    }
    /**
     * @param  string $message
     * @param  int $data_coding_scheme
     * @return string
     */
    protected function getMessage($message, $data_coding_scheme = null)
    {
        return urlencode($message);
    }

    /**
     * Generate URL-encoded query string from an array.
     *
     * @param array $data The data to send.
     *
     * @return string
     */
    protected function encodePostData(array $data = array())
    {
        return http_build_query($data);
    }
    /**
     * {@inheritDoc}
     */
    public function getContent($url, $method = 'GET', array $headers = array(), $data = array())
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('cURL has to be enabled.');
        }

        $c = curl_init();

        // build the request...
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1); // allow redirects.
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, strtoupper($method)); // define the HTTP method

        // join the data
        if (!empty($data) && 'POST' === strtoupper($method)) {
            if (is_array($data)) {
                $data = $this->encodePostData($data);
            }
            curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        }

        // and add the headers
        if (!empty($headers)) {
            curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        }

        // execute the request
        $content = curl_exec($c);

        curl_close($c);

        if (false === $content) {
            $content = null;
        }

        return $content;
    }
}