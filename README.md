# Open List

Version 2.x.1.0

SOAP service handling user list elements in Danish libraries.

Each library may implement an open_list client on their website and thereby share certain user data between all library websites using the Open List service.

## Server Requirements
  * PHP 5.3.3+
  * Mysql 5.5+
  * Zend Engine 2.3+ (For WDSL auto discover)

## Install

    Create MySql database for the service
    Run sql/openlist.sql into the db
    Copy settings_default.php to settings.php
    Create a virtual host with document root www/ and index.php

## Testing
The service can be tested at this location
    
    http://example.com/tools/client/


## Available clients
  * **ting\_open\_list** Drupal client
