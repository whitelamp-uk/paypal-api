-- Must be a single select query
SELECT
  `created`
 ,'{{PAYPAL_CODE}}'
 ,null
 ,`refno`
 ,`cref`
 ,`amount`
FROM `paypal_payment`
WHERE `created`>='{{PAYPAL_FROM}}'
  AND `callback_at` IS NOT NULL
ORDER BY `id`
;
