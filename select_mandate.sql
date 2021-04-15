-- Must be a single select query
SELECT
  '{{PAYPAL_PROVIDER}}'
 ,null
 ,`TransactionRef`
 ,`ClientRef`
 ,`Created`
 ,`Updated`
 ,`FirstDrawClose`
 ,'LIVE'
 ,'Monthly'
 ,`Amount`
 ,`Chances`
 ,`Name`
 ,'{{PAYPAL_PROVIDER}}'
 ,''
 ,''
 ,`id`
 ,1
 ,`Created`
 ,`Created`
FROM `pponce_payment`
WHERE `Created` IS NOT NULL
  AND `Created`>='{{PAYPAL_FROM}}'
ORDER BY `id`
;

