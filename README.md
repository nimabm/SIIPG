# SPIPG
Simple Persian Internet Payment Gateway (IPG) in PHP


Bank Mellat Example :

```php
$data = ['terminal' => (int) ,
        'username' => (str),
        'password' => (str) ,
        'amount' => (int) ,
        'callback' => (str) ,
        'order' => (int)];


#Mellat Payment Start: (payment.php)
    $ipg = new Mellat();
    $ipg->set_data($data);
    $return = $ipg->start();
   
    
#Mellat Payment Verification: (callback.php) 
    $ipg = new Mellat();
    $ipg->set_data($data);
    $return = $ipg->payment_check($_REQUEST);
```


Zarinpal Example :

```php
$data = ['merchantid' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'(str),
         'amount' => (int) ,
         'callback' => (str) ,
         'order' => (int),
         'additionalData' => (str)];


#Zarinpal Payment Start: (payment.php)
    $ipg = new Zarinpal();
    $ipg->set_data($data);
    $return = $ipg->start();

    
#Zarinpal Payment Verification: (callback.php)  
    $ipg = new Zarinpal();
    $ipg->set_data($data);
    $return = $ipg->payment_check($_REQUEST);

```



