# Aibolit for BrainyCP

This module is based on the work of the [Ai-Bolit](https://revisium.com/ai/) scanner and provides a convenient interface for checking sites.

## Installation

1) Download zip archive and unpack to /etc/brainy 
2) Open your panel https://your_ip:8000/index.php?do=aibolit

For ease of navigation, you can add a link to your panel template /etc/brainy/tpl/basic/index.tpl

```html
<a href="?do=aibolit">Aibolit</a>
```

## Usage

For the module to work, the installed version of php 7.0 is required - at the first start, a compatibility check will be performed.

Everything is controlled by the module through the web interface.

## Removing
To remove a module, go to your panel in the CRON settings and delete the entry

```bash
/etc/brainy/src/compiled/php5/bin/php /etc/brainy/modules/aibolit/console.php
```

## History

Version 1.1 (04.12.2020)

Add:
- Added switch sites for auto scanning.
- Added show module history in tab 'Info'

Fix:
- Fixed error save setting notify to email
- Fixed error send report auto scanning to email

Version 1.0 (09.11.2020)...

## License
[MIT](https://choosealicense.com/licenses/mit/)
