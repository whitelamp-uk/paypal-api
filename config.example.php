<?php

// Organisation - Paypal
define ( 'BLOTTO_PAY_API_PAYPAL',           '/path/to/paypal-api/PayApi.php' );
define ( 'BLOTTO_PAY_API_PAYPAL_CLASS',     '\Blotto\Paypal\PayApi'         );
define ( 'BLOTTO_PAY_API_PAYPAL_BUY',       true        ); // Provide integration
define ( 'PAYPAL_EMAIL',            'paypal.account@my.domain'              );
define ( 'PAYPAL_CODE',             'PYPL'      ); // CCC and Provider
define ( 'PAYPAL_ERROR_LOG',        false       );
define ( 'PAYPAL_REFNO_OFFSET',     200000000   );
define ( 'PAYPAL_DEV_MODE',         true        );


// Organisation - all payment providers
define ( 'BLOTTO_DEV_MODE',         true        );
efine ( 'CAMPAIGN_MONITOR',        '/path/to/createsend-php/csrest_transactional_smartemail.php' );
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

