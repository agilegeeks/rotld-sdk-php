<?php
use PHPUnit\Framework\TestCase;
use AgileGeeks\Rotld\RotldClient;
use AgileGeeks\Rotld\RotldApiException;

class RotldTestCase extends PHPUnit\Framework\TestCase {

    protected function setUp($configfile = null) {
        include('config.php');
        $this->client = new RotldClient(
            $config['regid'],
            $config['password'],
            $config['apiurl'],
            $config['lang'],
            $config['format']
        );

    }

    protected function tearDown() {

    }

    protected function randomstring($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function randomnumber($length) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function create_dummy_registrant() {
        $registrant_data = array();
        $registrant_data['name'] = 'John  Doe';
        $registrant_data['cnp_fiscal_code'] = '1283777778234';
        $registrant_data['registration_number'] = '';
        $registrant_data['email'] = 'johndoe@gmail.com';
        $registrant_data['phone'] = '+49.27366643444';
        $registrant_data['fax'] = '';
        $registrant_data['address1'] = 'address 1';
        $registrant_data['address2'] = 'address 2';
        $registrant_data['address3'] = '';
        $registrant_data['city'] = 'Bucuresti';
        $registrant_data['state_province'] = 'Bucuresti';
        $registrant_data['postal_code'] = '';
        $registrant_data['country_code'] = 'RO';
        $registrant_data['person_type'] = 'p';
        return $this->client->create_contact($registrant_data);
    }

    protected function create_dummy_domain(){
        $cid = $this->create_dummy_registrant();
        $domain_name = 'test-'.$this->randomstring(50).'.ro';
        $result = $this->client->register_domain(
            $domain_name = $domain_name,
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = 'G0odPasswd21#'
        );
        return $domain_name;
    }

    public function test_create_contact() {
        $registrant_data = array();
        $registrant_data['name'] = '';
        $registrant_data['cnp_fiscal_code'] = '';
        $registrant_data['registration_number'] = '';
        $registrant_data['email'] = 'radub@gmail.com';
        $registrant_data['phone'] = '';
        $registrant_data['fax'] = '';
        $registrant_data['address1'] = 'address 1';
        $registrant_data['address2'] = '';
        $registrant_data['address3'] = '';
        $registrant_data['city'] = 'Bucuresti';
        $registrant_data['state_province'] = '';
        $registrant_data['postal_code'] = '';
        $registrant_data['country_code'] = '';
        $registrant_data['person_type'] = '';

        $result = $this->client->create_contact($registrant_data);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50102');

        $registrant_data['name'] = 'John Doe';
        $result = $this->client->create_contact($registrant_data);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50106');

        $registrant_data['phone'] = '+40.76516755554';
        $result = $this->client->create_contact($registrant_data);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50125');

        $registrant_data['country_code'] = 'RO';
        $result = $this->client->create_contact($registrant_data);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50122');


        $registrant_data['state_province'] = 'București';
        $result = $this->client->create_contact($registrant_data);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50129');

        $registrant_data['cnp_fiscal_code'] = '128817687628374678236';
        $result = $this->client->create_contact($registrant_data);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50137');


        $registrant_data['person_type'] = 'p';
        $result = $this->client->create_contact($registrant_data);
        $this->assertEquals($result[0], 'C');

    }

    public function test_register_domain() {
        $cid = $this->create_dummy_registrant();

        $result = $this->client->register_domain(
            $domain_name = 'invaliddomainname',
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = ''
        );
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50002');

        $result = $this->client->register_domain(
            $domain_name = 'rotld.ro',
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = 'invalidpassword'
        );
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50014');

        $result = $this->client->register_domain(
            $domain_name = 'test-'.$this->randomstring(50).'.ro',
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = 'G0odPasswd21#'
        );
        $this->assertEquals($result->registrant_id, $cid);
        $this->assertEquals($this->client->getResultCode(),'00200');

    }

    public function test_reserve_domain() {
        $cid = $this->create_dummy_registrant();

        $result = $this->client->reserve_domain(
            $domain_name = 'test-'.$this->randomstring(50).'.ro',
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = 'G0odPasswd21#'
        );
        $this->assertEquals($result->registrant_id, $cid);
        $this->assertEquals($this->client->getResultCode(),'00200');

    }

    public function test_activate_domain() {
        $cid = $this->create_dummy_registrant();
        $domain_name = 'test-'.$this->randomstring(50).'.ro';

        $result = $this->client->reserve_domain(
            $domain_name = $domain_name,
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = 'G0odPasswd21#'
        );
        $this->assertEquals($result->registrant_id, $cid);
        $this->assertEquals($this->client->getResultCode(),'00200');

        $result = $this->client->activate_domain($domain_name);
        $this->assertEquals($this->client->getResultCode(),'00200');

    }
    public function test_check_availability() {
        $result = $this->client->check_availability('adomainthatcertainlyshouldnotberegistedhaha.ro');
        $this->assertEquals($this->client->getResultCode(),'00200');
        $this->assertEquals($result,'Available');

        $domain_name = $this->create_dummy_domain();
        $result = $this->client->check_availability($domain_name);
        $this->assertEquals($this->client->getResultCode(),'00200');
        $this->assertEquals($result,'Not Available');
    }

    public function test_reset_nameservers() {
        $domain_name = $this->create_dummy_domain();

        $nameservers = array('ns.x.com');
        $result = $this->client->reset_nameservers($domain_name,$nameservers);
        $this->assertEquals($this->client->getResultCode(),'00200');

        $nameservers = array('ns.'.$domain_name);
        $result = $this->client->reset_nameservers($domain_name,$nameservers);
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'10502');


    }

    public function test_info_contact() {
        $result = $this->client->info_contact('1000000000000000');
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'50006');

        $cid = $this->create_dummy_registrant();
        $result = $this->client->info_contact($cid);
        $this->assertEquals($this->client->getResultCode(),'00200');
        $this->assertEquals($result->registrant_id,$cid);
    }

    public function test_info_domain() {
        $result = $this->client->info_domain('adomainthatcertainlyshouldnotberegistedhaha.ro');
        $this->assertFalse($result);
        $this->assertEquals($this->client->getResultCode(),'10001');

        $domain_name = $this->create_dummy_domain();
        $result = $this->client->info_domain($domain_name);
        $this->assertEquals($this->client->getResultCode(),'00200');
        $this->assertEquals($result->domain, $domain_name);

        // echo $this->client->getResultCode()."  ".$this->client->getResultMessage();
        // var_dump($this->client->getResult());

    }

    public function test_contact_update() {
        $cid = $this->create_dummy_registrant();
        $registrant_data['address1'] = 'Test Street nr.12';
        $registrant_data['cid'] = $cid;
        $domain_name = 'test-'.$this->randomstring(50).'.ro';
        $result = $this->client->register_domain(
            $domain_name = $domain_name,
            $domain_period = 1,
            $registrant_cid = $cid,
            $domain_password = 'G0odPasswd21#'
        );

        $result = $this->client->contact_update($registrant_data);
        $this->assertEquals($this->client->getResultCode(),'00200');

        $result = $this->client->info_contact($cid);
        $this->assertEquals($result->address1, $registrant_data['address1']);
    }

}
