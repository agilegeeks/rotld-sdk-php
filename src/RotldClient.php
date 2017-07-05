<?php
namespace AgileGeeks\Rotld;

class RotldClient
{
    protected $result;
    protected $result_message;
    protected $result_code;

    protected $client;

    public function __construct($regid, $password, $apiurl, $lang='en', $format='json')
    {
        $this->client = new ApiClient(
            array(
            	'regid'=>$regid,
            	'password'=>$password,
                'apiurl'=>$apiurl,
            	'lang'=>$lang,
            	'format'=>$format
            )
        );
    }

    public function getClient()
    {
        return $this->client;
    }

    private function commit(){
        $this->result = null;
        $this->result_code = null;
        $this->result_message = null;
        $this->result_iserror = null;

        try{
            $result = json_decode($this->client->commit());
            $this->result = $result;
            $this->result_code = $result->result_code;
            $this->result_message = $result->result_message;
            $this->result_iserror = boolval($result->error);
            if ($result->error==1){
                return False;
            }
            return True;
        }catch(\Exception $e){
            throw new RotldApiException($e->getMessage());
        }
    }


    private function set_params($params=array()){
        foreach($params as $parameter=>$value){
            $this->client->set_param($parameter,$value);
    	}
    }

    public function getResult(){
        return $this->result;
    }

    public function getResultMessage(){
        return $this->result_message;
    }

    public function getResultCode(){
        return $this->result_code;
    }


    public function create_contact($contact_data){
        $params = array();
        $params['command'] = 'contact-create';
        $this->set_params(array_merge($params,$contact_data));
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data->cid;
        }else{
            return False;
        }
    }

    private function _register_domain($params=array()){
        $params['command'] = 'domain-register';
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }

    public function register_domain($domain_name, $domain_period, $registrant_cid, $domain_password){
        $params = array();
        $params['domain'] = $domain_name;
        $params['domain_period'] = $domain_period;
        $params['domain_password'] = $domain_password;
        $params['reservation'] = 0;
        $params['c_registrant'] = $registrant_cid;
        return $this->_register_domain($params);
    }

    public function reserve_domain($domain_name, $domain_period, $registrant_cid, $domain_password){
        $params = array();
        $params['command'] = 'domain-register';
        $params['domain'] = $domain_name;
        $params['domain_period'] = $domain_period;
        $params['domain_password'] = $domain_password;
        $params['reservation'] = 1;
        $params['c_registrant'] = $registrant_cid;
        return $this->_register_domain($params);
    }

    public function check_availability($domain_name){
        $params = array();
        $params['command'] = 'check-availability';
        $params['domain'] = $domain_name;
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data->status;
        }else{
            return False;
        }
    }

    public function reset_nameservers($domain_name, $nameservers=array()){
        $params = array();
        $params['command'] = 'domain-reset-ns';
        $params['domain'] = $domain_name;
        $params['nameservers'] = implode(",", $nameservers);
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }

    public function info_contact($cid){
        $params = array();
        $params['command'] = 'contact-info';
        $params['cid'] = $cid;
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }


}
