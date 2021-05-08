-- Must be a single select query
SELECT
  `created`
 ,'{{PAYPAL_CODE}}'
 ,null
 ,`refno`
 ,`cref`
 ,`amount`
FROM `paypal_payment`
WHERE `created` IS NOT NULL
  AND `created`>='{{PAYPAL_FROM}}'
  AND `paid` IS NOT NULL
ORDER BY `id`
;
