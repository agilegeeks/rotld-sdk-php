<?php
namespace AgileGeeks\Rotld;

use Mockery\Exception;


class RotldClient {
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

    private function commit()
    {
        $this->result = null;
        $this->result_code = null;
        $this->result_message = null;
        $this->result_iserror = null;

        try{
            $result = json_decode($this->client->commit());
            $this->result = $result;
            $this->result_code = $result->result_code;
            $this->result_message = $result->result_message;
            $this->result_iserror = (bool) $result->error;
            if ($result->error==1){
                return False;
            }
            return True;
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    private function set_params($params=array())
    {
        foreach($params as $parameter=>$value){
            $this->client->set_param($parameter,$value);
    	}
    }

    private function _register_domain($params=array())
    {
        $params['command'] = 'domain-register';
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getResultMessage()
    {
        return $this->result_message;
    }

    public function getResultCode()
    {
        return $this->result_code;
    }

    public function create_contact($contact_data)
    {
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

    public function contact_update($contact_data)
    {
        $params = array();
        $params['command'] = 'contact-update';
        $this->set_params(array_merge($params, $contact_data));

        if ($this->commit()) {
            return True;
        } else {
            return False;
        }
    }

    public function register_domain($domain_name, $domain_period, $registrant_cid, $domain_password)
    {
        $params = array();
        $params['domain'] = $domain_name;
        $params['domain_period'] = $domain_period;
        $params['domain_password'] = $domain_password;
        $params['reservation'] = 0;
        $params['c_registrant'] = $registrant_cid;
        return $this->_register_domain($params);
    }

    public function reserve_domain($domain_name, $domain_period, $registrant_cid, $domain_password)
    {
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

    public function reset_nameservers($domain_name, $nameservers=array())
    {
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

    public function create_nameserver($nameserver, $ips)
    {
        $params = array();
        $params['command'] = 'nameserver-create';
        $params['nameserver'] = $nameserver;
        $params['ips'] = $ips;
        
        $this->set_params($params);
        
        if ($this->commit()){
            return $this->result->data;
        }

        return False;
    }

    public function update_nameserver($nameserver, $ips)
    {
        $params = array();
        $params['command'] = 'nameserver-update';
        $params['nameserver'] = $nameserver;
        $params['ips'] = $ips;
        
        $this->set_params($params);
        
        if ($this->commit()){
            return $this->result->data;
        }

        return False;
    }

    public function delete_nameserver($nameserver)
    {
        $params = array();
        $params['command'] = 'nameserver-delete';
        $params['nameserver'] = $nameserver;
        
        $this->set_params($params);
        
        if ($this->commit()){
            return $this->result->data;
        }

        return False;
    }

    public function info_nameserver($nameserver)
    {
        $params = array();
        $params['command'] = 'nameserver-info';
        $params['nameserver'] = $nameserver;

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        }

        return false;
    }

    public function info_contact($cid)
    {
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

    public function info_domain($domain_name)
    {
        $params = array();
        $params['command'] = 'domain-info';
        $params['domain'] = $domain_name;
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }

    public function renew_domain($domain_name, $period)
    {
        $params = array();
        $params['command'] = 'domain-renew';
        $params['domain'] = $domain_name;
        $params['domain_period'] = $period;
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }

    public function activate_domain($domain_name)
    {
        $params = array();
        $params['command'] = 'domain-activate';
        $params['domain'] = $domain_name;
        $this->set_params($params);
        if ($this->commit()){
            return $this->result->data;
        }else{
            return False;
        }
    }

    public function transfer_domain($domain_name, $authorization_key)
    {
        $params = array();
        $params['command'] = 'domain-transfer';
        $params['domain'] = $domain_name;
        $params['authorization_key'] = $authorization_key;

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }

    public function trade_domain($domain_name, $authorization_key, $cid, $period)
    {
        $params = array();
        $params['command'] = 'domain-trade';
        $params['domain'] = $domain_name;
        $params['authorization_key'] = $authorization_key;
        $params['c_registrant'] = $cid;
        $params['domain_period'] = $period;

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }

    public function trade_info($tid)
    {
        $params = array();
        $params['command'] = 'trade-info';
        $params['tid'] = $tid;

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }

    public function trade_confirm($tid)
    {
        $params = array();
        $params['command'] = 'trade-confirm';
        $params['tid'] = $tid;

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }

    public function check_balance()
    {
        $params = array();
        $params['command'] = 'check-balance';

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }

    public function add_dnssec_data($domain, $ds_data)
    {
        $params = array();
        $params['command'] = 'domain-dsdata-add';
        $params['domain'] = $domain;
        $params['keytag'] = $ds_data['keytag'];
        $params['alg'] = $ds_data['alg'];
        $params['digest_type'] = $ds_data['digest_type'];
        $params['digest'] = $ds_data['digest'];

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }

    public function remove_dnssec_data($domain, $ds_data=null)
    {
        $params = array();
        $params['command'] = 'domain-dsdata-remove';
        $params['domain'] = $domain;

        if ($ds_data) {
            $params['keytag'] = $ds_data['keytag'];
            $params['alg'] = $ds_data['alg'];
            $params['digest_type'] = $ds_data['digest_type'];
            $params['digest'] = $ds_data['digest'];
        }

        $this->set_params($params);

        if ($this->commit()) {
            return $this->result->data;
        } else {
            return false;
        }
    }
}
