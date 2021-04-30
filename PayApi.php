<?php

namespace Blotto\Paypal;

class PayApi {

    private  $connection;
    public   $constants = [
                 'PAYPAL_CODE',
                 'PAYPAL_ADMIN_EMAIL',
                 'PAYPAL_ADMIN_PHONE',
                 'PAYPAL_TERMS',
                 'PAYPAL_PRIVACY',
                 'PAYPAL_EMAIL',
                 'PAYPAL_CMPLN_EML_CM_ID',
                 'PAYPAL_CMPLN_EML',
                 'PAYPAL_CMPLN_MOB',
                 'PAYPAL_ERROR_LOG',
                 'PAYPAL_REFNO_OFFSET'
             ];
    public   $database;
    public   $diagnostic;
    public   $error;
    public   $errorCode = 0;
    private  $from;
    public   $supporter = [];

    public function __construct ($connection) {
        $this->connection = $connection;
        $this->setup ();
    }

    public function __destruct ( ) {
    }

    public function callback ( ) {
        try {
            $error = null;
            $step = 1;
            $this->complete ($txn_ref);
            $step = 2;
            $this->supporter = $this->supporter_add ($txn_ref);
            if (PAYPAL_CMPLN_EML) {
                $step = 3;
                campaign_monitor (PAYPAL_CMPLN_EML_CM_ID,$this->supporter);
            }
            if (PAYPAL_CMPLN_MOB) {
                $step = 4;
                // TODO: we need to build a proper message
                $message    = print_r ($this->supporter,true);
                sms ($this->supporter['Mobile'],$message,PAYPAL_SMS_FROM);
            }
            return true;
        }
        catch (\Exception $e) {
            $error = "Error for txn=$txn_ref, step=$step: {$e->getMessage()}";
        }
        error_log ($error);
        mail (
            PAYPAL_EMAIL_ERROR,
            'Paypal sign-up callback error',
            $error
        );
        return false;
    }

    private function complete ($txn_ref) {
        try {
            $this->connection->query (
                "UPDATE `paypal_payment` SET `paid`=NOW() WHERE `txn_ref`='$txn_ref' LIMIT 1"
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

    private function refno ($id) {
        return PAYPAL_REFNO_OFFSET + $id;
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
        // Insert into paypal_payment leaving especially `Paid` and `Created` as null
        // $this->txn_ref = something unique to go in button
    }

    private function supporter_add ($txn_ref) {
        try {
            $s = $this->connection->query (
              "SELECT * FROM `paypal_payment` WHERE `txn_ref`='$txn_ref' LIMIT 0,1"
            );
            $s = $s->fetch_assoc ();
            if (!$s) {
                throw new \Exception ("Transaction reference '$txn_ref' was not identified");
            }
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (122,'SQL select failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
        try {
            // Insert a supporter, a player and a contact
            $cref = $this->cref ($s['id']);
            signup ($s,PAYPAL_CODE,$cref);
            // Add tickets here so that they can be emailed/texted
            $tickets = tickets (PAYPAL_CODE,$this->refno($s['id']),$cref,$s['chances']);
        }
        catch (\mysqli_sql_exception $e) {
            $this->error_log (121,'SQL insert failed: '.$e->getMessage());
            throw new \Exception ('SQL error');
            return false;
        }
        return [
            'Email'         => $s['email'],
            'Mobile'        => $s['first_name'],
            'First_Name'    => $s['first_name'],
            'Last_Name'     => $s['last_name'],
            'Reference'     => $cref,
            'Chances'       => $s['quantity'],
            'Tickets'       => explode (',',$tickets),
            'Draws'         => $s['draws'],
            'First_Draw'    => draw_first ($s['created'],PAYPAL_CODE)
        ];
    }

}

