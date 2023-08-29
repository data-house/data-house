[![CI](https://github.com/data-house/data-house/actions/workflows/ci.yml/badge.svg)](https://github.com/data-house/data-house/actions/workflows/ci.yml) [![Build Docker Image](https://github.com/data-house/data-house/actions/workflows/docker.yml/badge.svg)](https://github.com/data-house/data-house/actions/workflows/docker.yml)


## About Data House

Data House is a web application designed for studying knowledge management requirements and sustaining digital transformation in complex organisations and project networks.

It can be used and customised for proof of concept (PoC), prototype or minimum viable product (MVP) development.

The following features are currently available in the Data House:

- Authorisation and user permissions
- Group users into teams and manage their access levels
- Upload documents (PDF, Word document, PowerPoint presentation, images)
- Import files from external sources via WebDAV (e.g. Nextcloud)
- Full-text search of uploaded documents
- AI-powered interactions including summarisation, question answering across single and multiple documents (_coming soon_)
- Extensible architecture

> **note**
> The Data House is under active development. Some documentation may be missing or incomplete. If you have any further questions, please send a message to [info@oneofftech.xyz](mailto:info@oneofftech.xyz).

## Installation

> Requires [Docker](https://www.docker.com/), [Docker Compose](https://docs.docker.com/compose/) and a [MariaDB 10.8](https://mariadb.org/) database.

_to be documented_

## Usage

_to be documented_


## Development

### Getting started

Data House is built using the [Laravel framework](https://laravel.com/) and 
[Jetstream](https://jetstream.laravel.com/2.x/introduction.html). 
[Livewire](https://laravel-livewire.com/) is used to deliver dynamic
components, while [TailwindCSS](https://tailwindcss.com/) powers
the UI styling.

Given the selected stack the development requires:

- [PHP 8.1](https://www.php.net/) or above
- [Composer](https://getcomposer.org/)
- [NodeJS](https://nodejs.org/en/) version 14 or above with [Yarn](https://yarnpkg.com/getting-started/install) package manager (tested with v1.x)
- [MariaDB](https://mariadb.org/) version 10.8 or above
- [Docker](https://www.docker.com/)

A [Docker Compose file](./docker-compose.yml) (generated using [Laravel Sail](https://laravel.com/docs/10.x/sail)) is provided to quick start the required services.

### Testing

The application is covered by unit and feature tests (powered by PHPUnit).
Tests runs at each push to GitHub.

You can run all the test suite by executing:

```bash
php artisan test
# or ./vendor/bin/phpunit
```

Tests execution requires a running instance of MySQL. If you use the given [Docker Compose](./docker-compose.yml) file a test database is already generated for you.


## Contributing

Thank you for considering contributing to the Data House! The contribution guide can be found in the [CONTRIBUTING.md](./.github/CONTRIBUTING.md) file.


## Supporters

The project is supported by [OneOff-Tech (UG)](https://oneofftech.de).

<p align="left"><a href="https://oneofftech.de" target="_blank"><img src="https://raw.githubusercontent.com/OneOffTech/.github/main/art/oneofftech-logo.svg" width="200"></a></p>

## Security Vulnerabilities

If you discover a security vulnerability within Data House, please send an e-mail to OneOff-Tech team via [security@oneofftech.xyz](mailto:security@oneofftech.xyz). All security vulnerabilities will be promptly addressed.

## License

The Data House code is currently open, but use requires to get a license from OneOff-Tech. Contact us at info@oneofftech.xyz.

Copyright (c) 2023-Present OneOff-tech UG, Germany [www.oneofftech.xyz](https://oneofftech.xyz)

