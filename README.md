# mwExtUpgrader
**A tool for batch upgrading MediaWiki extensions**

## Project Purpose

Many MediaWiki administrators update extensions one by one using the [ExtensionDistributor](https://www.mediawiki.org/wiki/Special:ExtensionDistributor), often without using Git. This can be time-consuming and cumbersome.

**`mwExtUpgrader` is designed for operators who prefer not to use Git for managing extensions, but want a simpler, more efficient way to batch upgrade their MediaWiki extensions.**

## Features
* Batch upgrades of MediaWiki extensions.
* Replaces old extension files with new versions in one streamlined process.

## How to Use

`mwExtUpgrader` is an interactive script.

### Using the Release Version

If you have downloaded the pre-built `.phar` release, simply run the following command:

```bash
php mwExtUpgrader.phar
```

### Using the Source Code

If you've downloaded the source code, you will need to have [Composer](https://getcomposer.org/) installed to manage the dependencies. Once you have Composer, run the following commands:

```bash
composer install
php run.php
```

## Preparing a Release

To create a new release, use the `build.php` script. This script packages the project and its dependencies into a [PHAR archive](https://en.wikipedia.org/wiki/PHAR_\(file_format\)), a single executable PHP file.

Once the `.phar` file is built, it can be executed like any standard PHP file.

```bash
php build.php
```

## Support

If you encounter any issues while using the script or have suggestions for improvements, please submit them via [GitHub Issues](https://github.com/RazeSoldier/mwExtUpgrader/issues).
