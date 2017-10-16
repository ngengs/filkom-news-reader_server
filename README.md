# Filkom News Reader Server
[![GitHub release](https://img.shields.io/github/release/ngengs/filkom-news-reader_server.svg)](https://github.com/ngengs/filkom-news-reader_server/releases/latest)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/6f7b0069007c4586826f5a8e49d2805b)](https://www.codacy.com/app/ngengs/filkom-news-reader_server?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ngengs/filkom-news-reader_server&amp;utm_campaign=Badge_Grade)

Filkom News Reader Server is backend service for filkom news reader app. It build based on Codeigniter.
The server will perform crawling data and give API for access the data.

See Online [Here](http://filkom-news-reader.ngengs.com/)

### Requirement
- PHP >= 7.1
- Codeigniter >= 3.1.6

### Installation
- Clone this [Repo](https://github.com/ngengs/filkom-news-reader_server.git)
- Download [Codeigniter file](https://github.com/bcit-ci/CodeIgniter/archive/3.1.6.zip)
- Unzip/Extract the Codeigniter file but **don't replace the `composer.json`**
- Change crawler token in web config `application/config/web_config.php` 
  ```
  $config['crawler_token'] = 'YOUR_TOKEN'
  ```
- Change some config `application/config/config.php` from Codeigniter to this
  ```
  $config['index_page'] = '';
  ...
  $config['subclass_prefix'] = 'FNR_';
  ...
  $config['composer_autoload'] = TRUE;
  ```
- Change routers config `application/config/routes.php` from Codeigniter to this
  ```
  $route['default_controller'] = 'Docs';
  ```
- Add some constant in `application/config/constants.php`
  ```
  /*
   * JSON Object Key
   */
  defined('KEY_STATUS') OR define('KEY_STATUS', 'status');
  defined('KEY_STATUS_CODE') OR define('KEY_STATUS_CODE', 'code');
  defined('KEY_STATUS_MESSAGE') OR define('KEY_STATUS_MESSAGE', 'message');
  defined('KEY_DATA') OR define('KEY_DATA', 'data');
  
  /*
   * JSON Default Value
   */
  defined('VALUE_STATUS_OK') OR define('VALUE_STATUS_OK', 'OK');
  defined('VALUE_STATUS_CODE_OK') OR define('VALUE_STATUS_CODE_OK', 200);
  defined('VALUE_STATUS_MESSAGE_DEFAULT') OR define('VALUE_STATUS_MESSAGE_DEFAULT', null);
  defined('VALUE_STATUS_ERROR') OR define('VALUE_STATUS_ERROR', 'ERROR');
  defined('VALUE_STATUS_CODE_ERROR') OR define('VALUE_STATUS_CODE_ERROR', 404);
  defined('VALUE_STATUS_MESSAGE_ERROR') OR define('VALUE_STATUS_MESSAGE_ERROR', 'Something wrong!');
  defined('VALUE_DATA_ERROR') OR define('VALUE_DATA_ERROR', null);
  ```
- Import the `db_structure.sql` to your database
- Change config for database in `application/config/database.php`
- Install composer dependencies 
  ```sh
  $ php composer.phar install
  ```
- Change `CI_ENV` in bottom of `.htaccess` to **production** or **developmet**

### Contributing
Please look at [Contributing](CONTRIBUTING.md)

### License

    Copyright 2017 Rizky Kharisma (@ngengs).

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
