<?php

// Organisation
define ( 'BLOTTO_PAY_API_PAYPAL',           '/some/paypal-api/PayApi.php'   );
define ( 'BLOTTO_PAY_API_PAYPAL_CLASS',     '\Blotto\Paypal\PayApi'         );
define ( 'BLOTTO_PAY_API_PAYPAL_CODE',      'PYPL'              );
define ( 'PAYPAL_CODE',                     BLOTTO_PAY_API_PAYPAL_CODE      );
define ( 'PAYPAL_ADMIN_EMAIL',              'paypal.support@my.biz'         );
define ( 'PAYPAL_ADMIN_PHONE',              '01 234 567 890'                );
define ( 'PAYPAL_TERMS' ,                   'https://my.biz/terms'          );
define ( 'PAYPAL_PRIVACY' ,                 'https://my.biz/privacy'        );
define ( 'PAYPAL_EMAIL',                    'paypal.account@my.domain'      );
define ( 'PAYPAL_ERROR_LOG',                false               );
define ( 'PAYPAL_CNFM_EM',                  true                ); // User must confirm email address
define ( 'PAYPAL_CNFM_PH',                  false               ); // User must confirm phone number
define ( 'PAYPAL_CMPLN_EM',                 true                ); // Send completion message email
define ( 'PAYPAL_CMPLN_PH',                 false               ); // Send completion message SMS
define ( 'PAYPAL_VOODOOSMS',                '/home/blotto/voodoosms/SMS.php'    );
define ( 'PAYPAL_SMS_FROM',                 '11charLotto' );
define ( 'PAYPAL_SMS_MESSAGE',              'Shall I compare thee to a Paypal transaction?' );

define ( 'PAYPAL_CAMPAIGN_MONITOR', '/path/to/createsend-php/csrest_transactional_smartemail.php' );

define ( 'CAMPAIGN_MONITOR_KEY',            '' );
define ( 'CAMPAIGN_MONITOR_SMART_EMAIL_ID', '' );



// Global

define ( 'PAYPAL_PROVIDER',             'PXXX'          ); // Provider code for mandates
define ( 'PAYPAL_CCC',                  'CXXX'          ); // CCC to use in lottery data
define ( 'PAYPAL_TABLE_MANDATE',        'blotto_build_mandate'      );
define ( 'PAYPAL_TABLE_COLLECTION',     'blotto_build_collection'   );

define ( 'PAYPAL_D8_USERNAME',          'development@burdenandburden.co.uk' );
define ( 'PAYPAL_D8_PASSWORD',          ''              );
define ( 'PAYPAL_D8_EML_VERIFY_LEVEL',  'MX'            );

