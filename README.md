# onelogin-api-php

This application interacts with OneLogin's User API, it let you create, set passwords and remove users.
See reference. https://developers.onelogin.com/docs/user-elements

1) Tested on:
PHP version 5.6.3
Zend Framework version 1.12.10 - Included
JQuery version 1.11.2.min - Included

2) Create the database.
Open the file /onelogin-api-php/public/users/templates/db_install.sql and run queries to create the database and tables. 

IMPORTANT. By executing these queries will create an application admin user with password Admin_123! 

3) Settings.
Be sure to create the file /onelogin-api-php/application/configs/local.ini and add this content:
;-----------------------------------
; Server specific settings
; This file will be kept out of GIT
;------------------------------------
[main]
env_mode = development; Working environment (production, development)
use_ssl = 0; Are we using https only ? (0, 1)

[database]
host        = localhost
dbname      = "one_login_api"
username    = "<Your User Name>" ; Whatever login name with access to one_login_api database
password    = "<Your User Password>" ; Password for user
adapter     = Mysqli

[olapi] ; API KEY and Proxy configuration if needed
api_key = "<OneLogin API Key>" ; Your OneLogin API Key goes here, see https://developers.onelogin.com/docs/using-the-onelogin-api
use_proxy = 0; Set to 1 if using a proxy
proxy_host = "<Your Proxy Server>"; Proxy 
proxy_port = "80"; Port
proxy_user = ""; User
proxy_pass = ""; Password

4) Permissions.
Be sure to create /onelogin-api-php/data/cache folder and allow APACHE to write over it
Be sure to create /onelogin-api-php/data/session folder and allow APACHE to write over it
Be sure to create /onelogin-api-php/public/users/files folder and allow APACHE to write over it 