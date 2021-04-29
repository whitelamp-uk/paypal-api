<?php

// Organisation
define ( 'BLOTTO_PAY_API_PAYPAL',           '/path/to/paypal-api/PayApi.php' );
define ( 'BLOTTO_PAY_API_PAYPAL_CLASS',     '\Blotto\Paypal\PayApi'         );
define ( 'BLOTTO_PAY_API_PAYPAL_BUY',       true        ); // Provide integration
define ( 'PAYPAL_CODE',                     'PYPL'      ); // CCC and Provider
define ( 'PAYPAL_ADMIN_EMAIL',      'paypal.support@my.biz'                 );
define ( 'PAYPAL_ADMIN_PHONE',      '01 234 567 890'                        );
define ( 'PAYPAL_TERMS' ,           'https://my.biz/terms'                  );
define ( 'PAYPAL_PRIVACY' ,         'https://my.biz/privacy'                );
define ( 'PAYPAL_EMAIL',            'paypal.account@my.domain'              );
define ( 'PAYPAL_CMPLN_EML',        true        ); // Send completion message by email
define ( 'PAYPAL_CMPLN_MOB',        false       ); // Send completion message by SMS
define ( 'PAYPAL_ERROR_LOG',        false                                   );
define ( 'PAYPAL_REFNO_OFFSET',     100000000           );

define ( 'CAMPAIGN_MONITOR',        '/path/to/createsend-php/csrest_transactional_smartemail.php' );
define ( 'CAMPAIGN_MONITOR_KEY',    '' );
define ( 'CAMPAIGN_MONITOR_SMART_EMAIL_ID', ''  );

define ( 'VOODOOSMS',               '/home/blotto/voodoosms/SMS.php'        );

define ( 'BLOTTO_SIGNUP_VFY_EML',   true        ); // User must confirm email address
define ( 'BLOTTO_SIGNUP_VFY_MOB',   false       ); // User must confirm phone number


// Global

define ( 'PAYPAL_PROVIDER',             'PXXX'          ); // Provider code for mandates
define ( 'PAYPAL_CCC',                  'CXXX'          ); // CCC to use in lottery data
define ( 'PAYPAL_TABLE_MANDATE',        'blotto_build_mandate'      );
define ( 'PAYPAL_TABLE_COLLECTION',     'blotto_build_collection'   );

define ( 'PAYPAL_D8_USERNAME',          'development@burdenandburden.co.uk' );
define ( 'PAYPAL_D8_PASSWORD',          ''              );
define ( 'PAYPAL_D8_EML_VERIFY_LEVEL',  'MX'            );

