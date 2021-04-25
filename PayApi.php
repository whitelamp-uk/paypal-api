<?php

namespace Blotto\Paypal

class PayApi {

    private  $connection;
    public   $constants = [
                 'PAYPAL_CODE',
                 'PAYPAL_ADMIN_EMAIL',
                 'PAYPAL_ADMIN_PHONE',
                 'PAYPAL_TERMS' 
                 'PAYPAL_PRIVACY' 
                 'PAYPAL_EMAIL',
                 'PAYPAL_ERROR_LOG',
                 'PAYPAL_CNFM_EM',
                 'PAYPAL_CNFM_PH',
                 'PAYPAL_CMPLN_EM',
                 'PAYPAL_CMPLN_PH',
                 'PAYPAL_VOODOOSMS',
                 'PAYPAL_CAMPAIGN_MONITOR'
             ];
    public   $database;
    public   $diagnostic;
    public   $error;
    public   $errorCode = 0;
    private  $from;
    public   $tickets = [];

    public function __construct ($connection) {
        $this->connection = $connection;
        $this->setup ();
    }

    public function __destruct ( ) {
    }

    public function button ( ) {
        if ($this->txn_ref) {
            require __DIR__.'/button.php';
        }
    }

    public function callback ( ) {
        try {
            $error = null;
            $step = null;
            // Do Paypal stuff
            $this->complete ($txn_ref);
            $this->supporter_add ($txn_ref);
            // Say OK back to Paypal
            // Send confirmation email
            if (PAYPAL_CMPLN_EM) {
                $step = 'Confirmation email';
                $this->campaign_monitor ($supporter_nr,$tickets,$first_draw_close,$draws);
            }
            // Send confirmation SMS
            if (PAYPAL_CMPLN_PH) {
                $step = 'Confirmation SMS';
                $sms        = new \SMS ();
                $details    = sms_message ();
                $sms->send ($_POST['mobile'],$details['message'],$details['from']);
            }
            return true;
        }
        catch (\Exception $e) {
            $error = "Error for txn=$txn_ref: {$e->getMessage()}";
            // Now saySay FOOEY back to Paypal
        }
        error_log ($error);
        mail (
            PAYPAL_EMAIL_ERROR,
            'Paypal sign-up callback error',
            $error
        );
        return false;
    }

    private function campaign_monitor ($ref,$tickets,$first_draw_close,$draws) {
        $cm         = new \CS_REST_Transactional_SmartEmail (
            CAMPAIGN_MONITOR_SMART_EMAIL_ID,
            array ('api_key' => CAMPAIGN_MONITOR_KEY)
        );
        $first      = new \DateTime ($first_draw_close);
        $first->add ('P1D');
        $first      = $first->format ('l jS F Y');
        $name       = str_replace (':','',$_POST['first_name']);
        $name      .= ' ';
        $name      .= str_replace (':','',$_POST['last_name']);
        $message    = array (
            "To"    => $name.' <'.$_POST['email'].'>',
            "Data"  => array (
                'First_Name'    => $_POST['first_name'],
                'Reference'     => $ref,
                'Tickets'       => $tickets,
                'First'         => $first,
                'Draws'         => $draws,
            )
        );
        $result     = $cm->send (
            $message,
            'unchanged'
        );
        // error_log ('Campaign Monitor result: '.print_r($result,true));
    }

    private function complete ($txn_ref) {
        // Update paypal_payment - `Paid`=NOW() where txn_ref=...
    }

    private function error_log ($code,$message) {
        $this->errorCode    = $code;
        $this->error        = $message;
        if (!defined('PAYPAL_ERROR_LOG') || !PAYPAL_ERROR_LOG) {
            return;
        }
        error_log ($code.' '.$message);
    }

    private function execute ($sql_file) {
        echo file_get_contents ($sql_file);
        exec (
            'mariadb '.escapeshellarg($this->database).' < '.escapeshellarg($sql_file),
            $output,
            $status
        );
        if ($status>0) {
            $this->error_log (127,$sql_file.' '.implode(' ',$output));
            throw new \Exception ("SQL file '$sql_file' execution error");
            return false;
        }
        return $output;
    }

    private function form_vars ( ) {
        $vars = array (
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'dob' => '',
            'postcode' => '',
            'address_1' => '',
            'address_2' => '',
            'address_3' => '',
            'town' => '',
            'county' => '',
            'quantity' => '',
            'draws' => '',
            'pref_1' => '',
            'pref_2' => '',
            'pref_3' => '',
            'pref_4' => '',
            'telephone' => '',
            'mobile' => '',
            'email' => '',
            'gdpr' => '',
            'terms' => '',
            'age' => '',
            'signed' => ''
        );
        foreach ($_POST as $k=>$v) {
            $vars[$k] = $v;
        }
        return $vars;
    }

    function get_address ( ) {
        if (ctype_digit($_POST['address_1'][0])) {
            $firstline          = explode(' ', $_POST['address_1'], 2);
            $house_number       = $firstline[0];
            $address_1          = $firstline[1];
            $address_2          = $_POST['address_2'];
            if ($_POST['address_3']) {
                $address_2     .= ', '.$_POST['address_3'];
            }
            $house_name         = '';
        }
        else {
            $house_name         = $_POST['address_1'];
            $address_1          = $_POST['address_2'];
            $address_2          = $_POST['address_3'];
            $house_number       = '';
        }
        $address_obj            = new \stdClass ();
        $address_obj->city          = $_POST['town'];
        $address_obj->county        = $_POST['county'];
        $address_obj->country       = 'GB';
        $address_obj->postcode      = $_POST['postcode'];
        $address_obj->address_1     = $address_1;
        $address_obj->address_2     = $address_2;
        $address_obj->house_name    = $house_name;
        $address_obj->house_number  = $house_number;
        $address = json_encode ($address_obj);
        return $address;
    }

    public function import ($from) {
        $from               = new \DateTime ($from);
        $this->from         = $from->format ('Y-m-d');
        $this->execute (__DIR__.'/create_payment.sql');
        $this->output_mandates ();
        $this->output_collections ();
    }

    private function output_collections ( ) {
        $sql                = "INSERT INTO `".PAYPAL_TABLE_COLLECTION."`\n";
        $sql               .= file_get_contents (__DIR__.'/select_collection.sql');
        $sql                = str_replace ('{{PAYPAL_FROM}}',$this->from,$sql);
        echo $sql;
        try {
            $this->connection->query ($sql);
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (126,'SQL insert failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
    }

    public function output_signup_form ( ) {
        $v = $this->form_vars ();
        $titles = explode (',',BLOTTO_TITLES_WEB);
        require __DIR__.'/form.php';
    }

    private function output_mandates ( ) {
        $sql                = "INSERT INTO `".PAYPAL_TABLE_MANDATE."`\n";
        $sql               .= file_get_contents (__DIR__.'/select_mandate.sql');
        echo $sql;
        try {
            $this->connection->query ($sql);
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (125,'SQL insert failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
    }

    private function setup ( ) {
        foreach ($this->constants as $c) {
            if (!defined($c)) {
                $this->error_log (124,"$c not defined");
                throw new \Exception ('Configuration error');
                return false;
            }
        }
        $sql                = "SELECT DATABASE() AS `db`";
        try {
            $db             = $this->connection->query ($sql);
            $db             = $db->fetch_assoc ();
            $this->database = $db['db'];
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (123,'SQL select failed: '.$e->getMessage());
            throw new \Exception ('SQL database error');
            return false;
        }
    }

    public function start ( ) {
         verify_general ();
        if ($_POST['telephone'] && !verify_phone($_POST['telephone'])) {
            $error = 'Telephone number (landline) is not valid';
        }
        elseif ($_POST['mobile'] && !verify_phone($_POST['mobile'],'m')) {
            $error = 'Telephone number (mobile) is not valid';
        }
        elseif ($_POST['email'] && !verify_email($_POST['email'])) {
            $error = 'Email address is not valid';
        }
        else {
            // Insert into paypal_payment leaving especially `Paid` and `Created` as null
            // $this->txn_ref = something unique to go in button
        }
        if ($error) {
            throw new \Exception ($error);
            return false;
        }
    }

    private function sms_message ( ) {
        return [
            'from' => PAYPAL_SMS_FROM,
            'message' => PAYPAL_SMS_MESSAGE
        ];
    }

    private function supporter_add ($txn_ref) {
        try {
            $supporter = $this->connection->query (
              "SELECT * FROM `stripe_payment` WHERE `txn_ref`='$txn_ref' LIMIT 0,1"
            );
            $supporter = $supporter->fetch_assoc ();
            if (!$supporter) {
                throw new \Exception ("Transaction reference '$txn_ref' was not identified");
            }
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (122,'SQL select failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
        $ccc        = STRIPE_CODE;
        $provider   = STRIPE_CODE;
        $refno      = STRIPE_REFNO_OFFSET + $supporter['id'];
        $cref       = STRIPE_CODE.'_'.$refno;
        // Insert a supporter, a player and a contact
        try {
            $this->connection->query (
              "
                INSERT INTO `blotto_supporter` SET
                  `created`=DATE('{$['created']}')
                 ,`signed`=DATE('{$['created']}')
                 ,`approved`=DATE('{$['created']}')
                 ,`canvas_code`='$ccc'
                 ,`canvas_agent_ref`='$ccc'
                 ,`canvas_ref`='{$['id']}'
                 ,`client_ref`='$cref'
              "
            );
            $sid = $this->connection->lastInsertId ();
            $this->connection->query (
              "
                INSERT INTO `blotto_player` SET
                 ,`started`=DATE('{$['created']}')
                 ,`supporter_id`=$sid
                 ,`client_ref`='$cref'
                 ,`chances`={$['quantity']}
              "
            );
            $this->connection->query (
              "
                INSERT INTO `blotto_contact` SET
                  `supporter_id`=$sid
                 ,`title`='{$['title']}'
                 ,`name_first`='{$['first_name']}'
                 ,`name_last`='{$['last_name']}'
                 ,`email`='{$['email']}'
                 ,`mobile`='{$['mobile']}'
                 ,`telephone`='{$['telephone']}'
                 ,`address_1`='{$['address_1']}'
                 ,`address_2`='{$['address_2']}'
                 ,`address_3`='{$['address_3']}'
                 ,`town`='{$['town']}'
                 ,`county`='{$['county']}'
                 ,`postcode`='{$['postcode']}'
                 ,`dob`='{$['dob']}'
                 ,`p0`='{$['pref_1']}'
                 ,`p1`='{$['pref_2']}'
                 ,`p2`='{$['pref_3']}'
                 ,`p3`='{$['pref_4']}'
              "
            );
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (121,'SQL insert failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
    }

    private function verify_email ($email) {
        $params = array(
            "username" => PAYPAL_D8_USERNAME,
            "password" => PAYPAL_D8_PASSWORD,
            "email" => $email,
            "level" => PAYPAL_EMAIL_VERIFY_LEVEL,
        );
        $client = new \SoapClient ("https://webservices.data-8.co.uk/EmailValidation.asmx?WSDL");
        $result = $client->IsValid ($params);
        if ($result->IsValidResult->Status->Success == false) {
            throw new \Exception ("Error trying to validate email: ".$result->Status->ErrorMessage);
            return false;
        }
        if ($result->IsValidResult->Result=='Invalid') {
            define ( 'PAYPAL_GO', 'contact');
            throw new \Exception ("$email is an invalid address");
            return false;
        }
        return true;
    }

    public function verify_general ( ) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = trim($value);
        }
        if (!$_POST['title']) {
            define ( 'PAYPAL_GO', 'about');
            throw new \Exception ('Title is required');
            return false;
        }
        if (!$_POST['first_name']) {
            define ( 'PAYPAL_GO', 'about');
            throw new \Exception ('First name is required');
            return false;
        }
        if (!$_POST['last_name']) {
            define ( 'PAYPAL_GO', 'about');
            throw new \Exception ('Last name is required');
            return false;
        }
        if (!$_POST['dob']) {
            define ( 'PAYPAL_GO', 'about');
            throw new \Exception ('Date of birth is required');
            return false;
        }
        $dt             = new \DateTime ($_POST['dob']);
        if (!$dt) {
            define ( 'PAYPAL_GO', 'about');
            throw new \Exception ('Date of birth is not valid');
            return false;
        }
        $now        = new \DateTime ();
        $years      = $dt->diff($now)->format ('%r%y');
        if ($years<18) {
            throw new \Exception ('You must be 18 or over to sign up');
            return false;
        }
        if (!$_POST['postcode']) {
            define ( 'PAYPAL_GO', 'address');
            throw new \Exception ('Postcode is required');
            return false;
        }
        if (!$_POST['address_1']) {
            define ( 'PAYPAL_GO', 'address');
            throw new \Exception ('First line of address is required');
            return false;
        }
        if (!$_POST['town']) {
            define ( 'PAYPAL_GO', 'address');
            throw new \Exception ('Town/city is required');
            return false;
        }
        if (!$_POST['sort_code']) {
            define ( 'PAYPAL_GO', 'bank');
            throw new \Exception ('Bank sort code is required');
            return false;
        }
        if (!$_POST['account_number']) {
            define ( 'PAYPAL_GO', 'bank');
            throw new \Exception ('Bank account number is required');
            return false;
        }
        if (!array_key_exists('gdpr',$_POST) || !$_POST['gdpr']) {
            define ( 'PAYPAL_GO', 'gdpr');
            throw new \Exception ('You must confirm that you have read the GDPR statement');
            return false;
        }
        if (!array_key_exists('signed',$_POST) || !$_POST['signed']) {
            define ( 'PAYPAL_GO', 'sign');
            throw new \Exception ('You must confirm your direct debit instruction');
            return false;
        }
        if (!array_key_exists('terms',$_POST) || !$_POST['terms']) {
            define ( 'PAYPAL_GO', 'sign');
            throw new \Exception ('You must agree to terms & conditions and the privacy policy');
            return false;
        }
        if (!array_key_exists('age',$_POST) || !$_POST['age']) {
            define ( 'PAYPAL_GO', 'sign');
            throw new \Exception ('You must be aged 18 or over to signup');
            return false;
        }
        return true;
    }

    function verify_phone ($number, $type='l') {
        $params = array(
            "username" => D8_USERNAME,
            "password" => D8_PASSWORD,
            "telephoneNumber" => $number,
            "defaultCountry" => 'GB',
        );
        $params['options']['Option'][] =  array("Name" => "UseMobileValidation", "Value" => false);
        $params['options']['Option'][] =  array("Name" => "UseLineValidation", "Value" => false);
        $client = new \SoapClient ("https://webservices.data-8.co.uk/InternationalTelephoneValidation.asmx?WSDL");
        $result = $client->IsValid($params);
        if ($result->IsValidResult->Status->Success == false) {
            throw new \Exception ("Error trying to validate phone number: ".$result->Status->ErrorMessage);
            return false;
        }
        if ($result->IsValidResult->Result->ValidationResult=='Invalid') {
            define ( 'PAYPAL_GO', 'contact');
            throw new \Exception ("$number is not a valid phone number");
            return false;
        }
        elseif ($type == 'm' && $result->IsValidResult->Result->NumberType!='Mobile') {
            define ( 'PAYPAL_GO', 'contact');
            throw new \Exception ("$number is not a valid mobile phone number");
            return false;
        }
        return true;
    }

}

