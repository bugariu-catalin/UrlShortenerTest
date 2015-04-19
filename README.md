# UrlShortenerTest
Test: PHP script for url shortener


### Version
1.0.0

### Installation

Create database:

```
CREATE TABLE IF NOT EXISTS `url` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url_hash` char(40) NOT NULL,
  `url` text NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_hash` (`url_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

```

Setup int/config.inc.php:
```php
define('MAIN_URL', '');
define('DB_HOST','');
define('DB_USER','');
define('DB_PASS','');
define('DB_DATABASE','');

```
License
----

GPL v3
