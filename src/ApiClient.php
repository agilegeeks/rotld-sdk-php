<?php
namespace AgileGeeks\Rotld;

class CurlRequest
{
    private $ch;

    public function init($params)
    {
        $this->ch = curl_init();
        $user_agent = 'ROTLD RESTAPI CLIENT 2.0';

        $header = array("Accept-Charset: utf-8;q=0.7,*;q=0.7","Keep-Alive: 60");
        if (isset($params['host']) && $params['host'])      $header[]="Host: ".$params['host'];
        if (isset($params['header']) && $params['header']) $header[]=$params['header'];

        @curl_setopt ( $this -> ch , CURLOPT_RETURNTRANSFER , 1 );
        @curl_setopt ( $this -> ch , CURLOPT_VERBOSE , 0 );
        @curl_setopt ( $this -> ch , CURLOPT_HEADER , 1 );

        @curl_setopt ( $this -> ch, CURLOPT_FOLLOWLOCATION, 1);
        @curl_setopt ( $this -> ch , CURLOPT_HTTPHEADER, $header );
		@curl_setopt( $this -> ch, CURLOPT_POST, true );
        @curl_setopt( $this -> ch, CURLOPT_POSTFIELDS, $params['post_fields'] );

        @curl_setopt( $this -> ch, CURLOPT_URL, $params['url']);
        @curl_setopt ( $this -> ch , CURLOPT_SSL_VERIFYPEER, 0 );
        @curl_setopt ( $this -> ch , CURLOPT_SSL_VERIFYHOST, 0 );
		@curl_setopt ($this -> ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		@curl_setopt($this -> ch , CURLOPT_USERPWD,$params['login'].':'.$params['password']);
		@curl_setopt ( $this -> ch , CURLOPT_TIMEOUT, 30);
	}

    public function exec()
    {
        $response = curl_exec($this->ch);
        $error = curl_error($this->ch);
        $result = array( 'header' => '',
                         'body' => '',
                         'curl_error' => '',
                         'http_code' => '',
                         'last_url' => '');
        if ( $error != "" )
        {
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size = curl_getinfo($this->ch,CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr( $response, $header_size );
        $result['http_code'] = curl_getinfo($this -> ch,CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($this -> ch,CURLINFO_EFFECTIVE_URL);
        return $result;
    }
}


class ApiClient
{
	private $params = array();
	private $fields = array();

	public function __construct($config_params){
        $this->params['post_fields']='';
		$this->params['host']='undefined';
		$this->fields['lang']='en';
		$this->fields['format']='json';

		if(isset($config_params['lang']) && $config_params['lang']!='') $this->fields['lang'] = $config_params['lang'];
		if(isset($config_params['format']) && $config_params['format']!='') $this->fields['format'] = $config_params['format'];

		if(!isset($config_params['apiurl'])) throw new \Exception('Invalid apiurl');
		$this->params['url'] = $config_params['apiurl'];

		if(isset($config_params['registrar_domain'])) $this->params['host'] = $config_params['registrar_domain'];

		if(!isset($config_params['regid'])) throw new \Exception('Invalid regid');
		$this->params['login'] = trim($config_params['regid']);

		if(!isset($config_params['password'])) throw new \Exception('Invalid password');
		$this->params['password'] = trim($config_params['password']);
	}

	public function set_param($param,$value){
		$this->fields[$param] = trim($value);
	}

	public function commit(){
		$poststr = '';
		$fields = $this->fields;
		if(is_array($fields) && sizeof($fields)){
			foreach($fields as $key=>$val){
				$val = urlencode($val);
				$poststr.=$key."=".$val."&";
			}
		}
		$this->params['post_fields']=$poststr;

		$ch = new CurlRequest();
		$ch->init($this->params);
		$result = $ch->exec();
		if ($result['http_code']!='200') {
			switch ($result['http_code']){
				case '401':
					throw new \Exception("Authentication Failure. Invalid credentials.");
				case '500':
					throw new \Exception("Service not available.");
				default:
					throw new \Exception("Service not available.");
			}
		}
		if (!$result['body'])	throw new \Exception("Invalid response from server");

		return $result['body'];
	}
	public function reset(){
		$this->fields = array();
	}
}

?>
