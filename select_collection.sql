-- Must be a single select query
SELECT
  `Created`
 ,'{{PAYPAL_PROVIDER}}'
 ,null
 ,`TransactionRef`
 ,`ClientRef`
 ,`Amount`
FROM `pponce_payment`
WHERE `Created` IS NOT NULL
  AND `Created`>='{{PAYPAL_FROM}}'
ORDER BY `id`
;
