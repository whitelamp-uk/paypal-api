<?php

// Organisation
define ( 'BLOTTO_PAY_API_CLASS_PAYPAL', '/path/to/paypal-api/PayApi.php'   );
define ( 'PAYPAL_EMAIL',            'paypal.account@my.domain'             );
define ( 'PAYPAL_ERROR_LOG',        false                                  );
define ( 'PAYPAL_CNFM_EM',          true                ); // User must confirm email address
define ( 'PAYPAL_CNFM_PH',          false               ); // User must confirm phone number
define ( 'PAYPAL_CMPLN_EM',         true                ); // Send completion message by email
define ( 'PAYPAL_CMPLN_PH',         false               ); // Send completion message by SMS
define ( 'PAYPAL_VOODOOSMS',        '/home/blotto/voodoosms/SMS.php'    );
define ( 'PAYPAL_CAMPAIGN_MONITOR', '/path/to/createsend-php/csrest_transactional_smartemail.php' );

define ( 'CAMPAIGN_MONITOR_KEY',    '' );
define ( 'CAMPAIGN_MONITOR_SMART_EMAIL_ID', '' );



// Global

define ( 'PAYPAL_PROVIDER',             'PXXX'          ); // Provider code for mandates
define ( 'PAYPAL_CCC',                  'CXXX'          ); // CCC to use in lottery data
define ( 'PAYPAL_TABLE_MANDATE',        'blotto_build_mandate'      );
define ( 'PAYPAL_TABLE_COLLECTION',     'blotto_build_collection'   );

define ( 'PAYPAL_D8_USERNAME',          'development@burdenandburden.co.uk' );
define ( 'PAYPAL_D8_PASSWORD',          ''              );
define ( 'PAYPAL_D8_EML_VERIFY_LEVEL',  'MX'            );

