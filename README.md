# twsms-sender
Twsms  簡訊發送 

https://www.twsms.com/

Installation
------------
composer require platolin/twsms-sender

The recommended way to install SmsSender is through composer.

Just create a `composer.json` file for your project:

```json
{
    "require": {
        "platolin/twsms-sender": "~1.0"
    }
}
```


Usage
-----

First, you need an `adapter` to query an API:

``` php
<?php

        $TwsmsSender = new TwSmsSender('username','password');
        $result = $TwsmsSender->send('0975000000', 'test sms message', '201612312359' );
```
