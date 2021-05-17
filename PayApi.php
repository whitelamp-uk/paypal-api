<?php

namespace Blotto\Paypal;

class PayApi {

    private  $connection;
    public   $constants = [
                 'PAYPAL_EMAIL',
                 'PAYPAL_CODE',
                 'PAYPAL_ERROR_LOG',
                 'PAYPAL_REFNO_OFFSET',
                 'PAYPAL_DEV_MODE',
                 'PAYPAL_TABLE_MANDATE',
                 'PAYPAL_TABLE_COLLECTION',
                 'PAYPAL_CALLBACK_TO'
             ];
    public   $database;
    public   $diagnostic;
    public   $error;
    public   $errorCode = 0;
    private  $from;
    private  $org;
    public   $supporter = [];

    public function __construct ($connection,$org=null) {
        $this->connection   = $connection;
        $this->org          = $org;
        $this->setup ();
    }

    public function __destruct ( ) {
    }

    public function callback (&$responded) {
        $responded          = false;
        $error              = null;
        $txn_ref            = null;
        try {
            $step           = 0;
            // Echo the callback back to Paypal
            // Throw an exception of anything goes wrong at this point
            // Then interpret the request to get the reference:
            $txn_ref        = blah;
            $step = 1;
            $this->complete ($txn_ref);
            // The payment is now recorded at this end
            http_response_code (200);
            $responded      = true;
            echo "Transaction completed\n";
            // Paypal only does a callback on success
            // Is this true?
            $step           = 2;
            $this->supporter = $this->supporter_add ($txn_ref);
            if ($this->org['signup_paid_email']) {
                $step       = 3;
                $result = campaign_monitor (
                    $this->org['signup_cm_key'],
                    $this->org['signup_cm_id'],
                    $this->supporter['To'],
                    $this->supporter
                );
                $ok = $result->http_status_code == 200;
                if (!$ok) {
                    throw new \Exception (print_r($result,true));
                }
            }
            if ($this->org['signup_paid_sms']) {
                $step       = 4;
                $sms_msg    = $this->org['signup_sms_message'];
                foreach ($this->supporter as $k=>$v) {
                    $sms_msg = str_replace ("{{".$k."}}",$v,$sms_msg);
                }
                sms ($this->supporter['Mobile'],$sms_msg,$this->org['signup_sms_from']);
            }
            return true;
        }
        catch (\Exception $e) {
            error_log ($e->getMessage());
            throw new \Exception ("txn=$txn_ref, step=$step: {$e->getMessage()}");
            return false;
        }
    }

    private function complete ($txn_ref) {
        // paypal_payment does not have a `failure_code` or `failure_message`
        // because Paypal only does a callback on success
        // is this true?
        try {
            $this->connection->query (
              "
                UPDATE `paypal_payment`
                SET
                  `callback_at`=NOW()
                 ,`refno`={$this->refno()}
                 ,`cref`='{$this->cref()}'
                WHERE `txn_ref`='$txn_ref'
                LIMIT 1
              "
            );
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (122,'SQL select failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
    }

    private function cref ($id) {
        return PAYPAL_CODE.'_'.$this->refno($id);
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

    public function import ($from) {
        $from               = new \DateTime ($from);
        $this->from         = $from->format ('Y-m-d');
        $this->execute (__DIR__.'/create_payment.sql');  // this creates the table? Yes (if not exists)
        $this->output_mandates ();
        $this->output_collections ();
    }

    private function output_collections ( ) {
        $sql                = "INSERT INTO `".PAYPAL_TABLE_COLLECTION."`\n";
        $sql               .= file_get_contents (__DIR__.'/select_collection.sql');
        $sql                = $this->sql_instantiate ($sql);
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

    private function output_mandates ( ) {
        $sql                = "INSERT INTO `".PAYPAL_TABLE_MANDATE."`\n";
        $sql               .= file_get_contents (__DIR__.'/select_mandate.sql');
        $sql                = $this->sql_instantiate ($sql);
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

    private function refno ($id) {
        return PAYPAL_REFNO_OFFSET + $id;
    }

    private function setup ( ) {
        foreach ($this->constants as $c) {
            if (!defined($c)) {
                $this->error_log (124,"$c not defined");
                throw new \Exception ('Configuration error $c not defined');
                return false;
            }
        }
    }

    private function sql_instantiate ($sql) {
        $sql                = str_replace ('{{PAYPAL_FROM}}',$this->from,$sql);
        $sql                = str_replace ('{{PAYPAL_CODE}}',PAYPAL_CODE,$sql);
        return $sql;
    }

    public function start (&$e) {
        $v = www_signup_vars ();
        $v['txn_ref'] = bin2hex (random_bytes(16));
        foreach ($v as $key => $val) {
            $v[$key] = $this->connection->real_escape_string($val);
        }
        $amount = intval($v['quantity']) * intval($v['draws']) * BLOTTO_TICKET_PRICE;
        $pounds_amount = $amount / 100;
        // Insert into paypal_payment leaving especially `paid` as null
        $sql = "
          INSERT INTO `paypal_payment`
          SET
            `txn_ref`='{$v['txn_ref']}'
           ,`quantity`='{$v['quantity']}'
           ,`draws`='{$v['draws']}'
           ,`amount`='{$pounds_amount}'
           ,`title`='{$v['title']}'
           ,`name_first`='{$v['first_name']}'
           ,`name_last`='{$v['last_name']}'
           ,`dob`='{$v['dob']}'
           ,`email`='{$v['email']}'
           ,`mobile`='{$v['mobile']}'
           ,`telephone`='{$v['telephone']}'
           ,`postcode`='{$v['postcode']}'
           ,`address_1`='{$v['address_1']}'
           ,`address_2`='{$v['address_2']}'
           ,`address_3`='{$v['address_3']}'
           ,`town`='{$v['town']}'
           ,`county`='{$v['county']}'
           ,`gdpr`='{$v['gdpr']}'
           ,`terms`='{$v['terms']}'
           ,`pref_1`='{$v['pref_1']}'
           ,`pref_2`='{$v['pref_2']}'
           ,`pref_3`='{$v['pref_3']}'
           ,`pref_4`='{$v['pref_4']}
          ;
        ";
        try {
            $this->connection->query ($sql);
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (122,'SQL insert failed: '.$e->getMessage());
            $e[] = 'Sorry something went wrong - please try later';
            return;
        }
        require __DIR__.'/button.php';
    }

    private function supporter_add ($txn_ref) {
        try {
            $s = $this->connection->query (
              "
                SELECT
                  `p`.*
                 ,`p`.`created` AS `first_draw_close`
                 ,drawOnOrAfter(`p`.`created`) AS `draw_first`
                FROM `paypal_payment` AS `p`
                WHERE `p`.`txn_ref`='$txn_ref'
                LIMIT 0,1
              "
            );
            $s = $s->fetch_assoc ();
            if (!$s) {
                throw new \Exception ("paypal_payment.txn_ref='$txn_ref' was not found");
            }
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (122,'SQL select failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
        // Insert a supporter, a player and a contact
        signup ($s,PAYPAL_CODE,$s['cref'],$s['first_draw_close']);
        // Add tickets here so that they can be emailed/texted
        $tickets            = tickets (PAYPAL_CODE,$s['refno'],$cref,$s['quantity']);
        $draw_first         = new \DateTime ($s['draw_first']);
        return [
            'To'            => $s['name_first'].' '.$s['name_last'].' <'.$s['email'].'>',
            'Title'         => $s['title'],
            'Name'          => $s['name_first'].' '.$s['name_last'],
            'Email'         => $s['email'],
            'Mobile'        => $s['mobile'],
            'First_Name'    => $s['name_first'],
            'Last_Name'     => $s['name_last'],
            'Reference'     => $s['cref'],
            'Chances'       => $s['quantity'],
            'Tickets'       => implode (',',$tickets),
            'Draws'         => $s['draws'],
            'First_Draw'    => $draw_first->format ('l jS F Y')
        ];
    }

}

