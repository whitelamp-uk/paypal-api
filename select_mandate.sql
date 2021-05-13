-- Must be a single select query
SELECT
  '{{PAYPAL_CODE}}'
 ,null
 ,`refno`
 ,`cref`
 ,`created`
 ,`paid`
 ,DATE_ADD(DATE(`paid`),INTERVAL 1 DAY)
 ,'LIVE'
 ,'Single'
 ,`amount`
 ,`quantity`
 ,CONCAT_WS(' ',`title`,`name_first`,`name_last`)
 ,''
 ,''
 ,''
 ,`id`
 ,1
 ,`created`
 ,`created`
FROM `paypal_payment`
WHERE `created`>='{{PAYPAL_FROM}}'
  AND `callback_at` IS NOT NULL
ORDER BY `id`
;

