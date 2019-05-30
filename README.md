# SIIPG
Simple Iran Internet Payment Gateway (IPG) in PHP  
This library includes MellatBank and ZarrinPal classes and new IPGs will be added soon.

### Bank Mellat Example :
------------
##### Bank Mellat Config
```php
$config = ['terminal' => (int) ,
        'username' => (str),
        'password' => (str) ,
        'amount' => (int) ,
        'callback' => (str) ,
        'order' => (int)];
```
##### Bank Mellat Payment Start: (payment.php)
```php
#Mellat Payment Start: (payment.php)
    $ipg = new Mellat();
    $ipg->set_config($config);
    $return = $ipg->start();
```   
##### Bank Mellat Payment Verification: (callback.php) 
```php    
#Mellat Payment Verification: (callback.php) 
    $ipg = new Mellat();
    $ipg->set_config($config);
    $return = $ipg->payment_check($_REQUEST);
```


### Zarinpal Example :
------------
##### Zarinpal Config
```php
$config = ['merchantid' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'(str),
         'amount' => (int) ,
         'callback' => (str) ,
         'order' => (int),
         'additionalData' => (str)];
```
##### Zarinpal Payment Start: (payment.php)
```php
    $ipg = new Zarinpal();
    $ipg->set_config($config);
    $return = $ipg->start();
```
##### Zarinpal Payment Verification: (callback.php) 
```php     
    $ipg = new Zarinpal();
    $ipg->set_config($config);
    $return = $ipg->payment_check($_REQUEST);
```




