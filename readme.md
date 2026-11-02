# app-t3-appointment

TYPO3 extension **bt_appointment** — embeds bookingtime's online appointment booking into your TYPO3 website, with an included setup process.

<img src="https://github.com/bookingtime/app-t3-appointment/blob/master/aws/logo_typo3.svg" alt="logo typo3" width="200" />

## Requirements
- TYPO3 11.5, 12.4, 13.4 or 14 (Composer mode or classic mode), PHP 8.1 or newer
- System requirements: https://docs.typo3.org/m/typo3/tutorial-getting-started/main/en-us/SystemRequirements/Index.html

## How To Install
1. Download, install and activate the bookingtime `bt_appointment` extension via the extension manager.
2. Include the static template "Appointment (bt_appointment)".
3. Register and configure the settings in the backend module "bookingtime appointments".
4. Insert the appointment form into a page with the content element "appointment".

Alternatively install via Composer:
```bash
composer require bookingtime/app-t3-appointment
```
see: https://packagist.org/packages/bookingtime/app-t3-appointment

## Help and docs
- Administrator documentation with screenshots: [Documentation](/Documentation/Readme.md)
- Technical notes for contributors: [CLAUDE.md](/CLAUDE.md)
- Support for developers: https://developer.bookingtime.com
- API documentation: https://service.bookingtime.com/apidoc/module

## Security
If you discover a security vulnerability within this package, please send an email to support@bookingtime.com or create a [ticket](https://developer.bookingtime.com/hc/en-us/requests/new?ticket_form_id=9359661193628). All security vulnerabilities will be promptly addressed.

## License
This extension is distributed under the MIT License, see LICENSE file for more information.

---
Copyright 2014 bookingtime GmbH. All Rights Reserved.

Made with :blue_heart: by © bookingtime

<img src="https://github.com/bookingtime/app-t3-appointment/blob/master/aws/logo_bookingtime.png" alt="logo" width="30" height="44" />
