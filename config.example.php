<?php

// Organisation - Paypal
define ( 'BLOTTO_PAY_API_PAYPAL',           '/path/to/paypal-api/PayApi.php' );
define ( 'BLOTTO_PAY_API_PAYPAL_CLASS',     '\Blotto\Paypal\PayApi'         );
define ( 'BLOTTO_PAY_API_PAYPAL_BUY',       true        ); // Provide integration
define ( 'PAYPAL_ADMIN_EMAIL',      'paypal.support@my.biz'                 );
define ( 'PAYPAL_ADMIN_PHONE',      '01 234 567 890'                        );
define ( 'PAYPAL_TERMS' ,           'https://my.biz/terms'                  );
define ( 'PAYPAL_PRIVACY' ,         'https://my.biz/privacy'                );
define ( 'PAYPAL_EMAIL',            'paypal.account@my.domain'              );
define ( 'PAYPAL_CODE',             'PYPL'      ); // CCC and Provider
define ( 'PAYPAL_CMPLN_EML',        true        ); // Send completion message by email
define ( 'PAYPAL_CMPLN_MOB',        false       ); // Send completion message by SMS
define ( 'PAYPAL_ERROR_LOG',        false       );
define ( 'PAYPAL_REFNO_OFFSET',     200000000   );
define ( 'PAYPAL_MAX_TICKETS',      10          );
define ( 'PAYPAL_MAX_PAYMENT',      50          );
define ( 'PAYPAL_DEV_MODE',         true        );


// Organisation - all payment providers
define ( 'BLOTTO_SIGNUP_CM_ID',     ''          ); // Verify email
define ( 'BLOTTO_SIGNUP_SMS_FROM',  ''          ); // Verify SMS
define ( 'BLOTTO_SIGNUP_VFY_EML',   true        ); // User must confirm email address
define ( 'BLOTTO_SIGNUP_VFY_MOB',   false       ); // User must confirm phone number
define ( 'CAMPAIGN_MONITOR',        '/path/to/createsend-php/csrest_transactional_smartemail.php' );
define ( 'CAMPAIGN_MONITOR_KEY',    ''          );
define ( 'CAMPAIGN_MONITOR_SMART_EMAIL_ID', ''  );
define ( 'DATA8_USERNAME',          ''          );
define ( 'DATA8_PASSWORD',          ''          );
define ( 'DATA8_COUNTRY',           'GB'        );
define ( 'VOODOOSMS',               '/home/blotto/voodoosms/SMS.php' );


// Global - Paypal
define ( 'PAYPAL_TABLE_MANDATE',    'blotto_build_mandate'      );
define ( 'PAYPAL_TABLE_COLLECTION', 'blotto_build_collection'   );
define ( 'PAYPAL_CALLBACK_TO',      30          ); // Confirmation time-out 


// Global - all payment providers
define ( 'DATA8_EMAIL_LEVEL',       'MX'        );
define ( 'VOODOOSMS_DEFAULT_COUNTRY_CODE', 44   );
define ( 'VOODOOSMS_FAIL_STRING',   'Sending SMS failed'        );
define ( 'VOODOOSMS_JSON',          __DIR__.'/voodoosms.cfg.json' );

