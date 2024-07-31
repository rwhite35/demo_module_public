# Example ETL Project

An ETL automation example using PHP, [Laminas Framework](https://getlaminas.org/), and asynchronous server side data processing.

## Project Overview

This project is an example of a capability often requested for enterprise software projects.  It provides `Extract Transform and Load`(ETL) processing for import data, transforming it into actionable business intelligence, customer engagement insights, and back-office operation. While not a complete feature, it does provide the plumbing for a full-featured ETL module that could bolt onto other solutions.

## MVC Architecture

The project is based on a Model-View-Controller(MVC) architecture and implements [Laminas MVC](https://docs.laminas.dev/mvc/) framework (among others)to leverages abstraction, dependency injection, dynamic routing & autoload. MVC ensures `separation of concern` and promotes clean code resulting in stable operation and feature/code longevity.

## Prerequisites & Setup

This project was created for demonstration purposes and configured to serve from an instance of [Apache2](https://httpd.apache.org/) running localhost with a configured VirtualHost.  It can however be deployed from containers(Docker), cloud services(AWS) or dedicated web servers.  The following outlines some basic configuration in order install and run the project locally.

### VirtualHost

The following is a reference for how the projects VirtualHost was configured.  Values can change, but the ServerName should match the root directory that the project is running under.  The URL `http://demo.acme.com:8888/public` should load the project if correctly configured.

```apache
<VirtualHost *:8888>
    ServerName demo.acme.com
    DocumentRoot /path/to/demo.acme.com/public
    <Directory /path/to/demo.acme.com/public>
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
    LogLevel warn
    ErrorLog "logs/demo.acme.com-error_log"
</VirtualHost>
```

Additionally, it may be necessary to add an entry in the servers `hosts` file. On Mac that file is `/etc/hosts`, and for Windows it's usually `c:\windows\system32\drivers\etc\hosts`. Add the following line to hosts file using any text editor with read/write permissions.

```bash
127.0.0.1 demo.acme.com
```

### .htaccess File

Laminas MVC along with ModuleManager and ServiceManager use Configuration Management to dynamically load views, controllers and other artifacts that have been defined in the `route stack` object. The route stack is a merged set of individual `routes` collated from each resources `module.config.php` file. 

1. Confirm Apache (usually in httpd.conf) has enabled rewrite module
..* LoadModule rewrite_module modules/mod_rewrite.so

2. Confirm Apache has enabled Access File
..* AccessFileName .htaccess

3. Add the following directives to the .htaccess file under any public/ directory that's configured for autoloading

```bash
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [L]
# for request to an index.php file
RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
RewriteRule ^(.*) - [E=BASE:%1]
RewriteRule ^(.*)$ %{ENV:BASE}/index.php [L]
# explicitly accept all origins
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods: "GET"
```

For more details on the above access file, see [Apache How-To htaccess](https://httpd.apache.org/docs/current/howto/htaccess.html)
For more details on Autoloading, see [Laminas Autoloader](https://docs.laminas.dev/laminas-modulemanager/module-autoloader/)


## Composer Package Manager and Autoloading

The final configuration task initializes the project and installs the framework components using Composer package manager. Dependencies are defined in a `composer.json` file which is not included in this repo. However, the required dependencies are listed below. This way another package dependency solution can be cleanly installed without Composer artifacts.  To walk through installing this project using Composer, refer to [Composer Basic Usage](https://getcomposer.org/doc/01-basic-usage.md).

1. Laminas & PHP Dependencies

```bash
"php": "~7.1.20 || ~7.2.8",
"laminas/laminas-session": "2.9.x-dev",
"laminas/laminas-hydrator": "^2.4",
"laminas/laminas-modulemanager": "^2.9",
"laminas/laminas-servicemanager": "3.5.x-dev",
"laminas/laminas-mvc": "3.1.x-dev",
"laminas/laminas-http": "2.13.x-dev",
"psr/container": "^1.0"
```

The above should copy/paste into the `compose.json` files "require": {...} node.

2. Composer Autoloading
..* Add the following to `composer.json` if using Composer's autoload feature

```bash
"psr-4": { "Acme\\": "module/Acme/src/" }
```

The above should copy/paste into the `composer.json` files "autoload": {...} node.


## Application Initialized

The following view should load assuming the above configurations are used and/or modified with install specific values.

[image]: https://github.com/rwhite35/demo_module_public/raw/main/public/images/expected_view.jpeg "Module View"


## References

1. [Laminas](https://getlaminas.org/): Enterprise Software framework and components.
2. [PHP](https:php.net): General Purpose programming language for web development.
3. [Composer](https://getcomposer.org/): Package and dependency management for PHP projects.
4. [whitepatchcode.com](whitepatchcode.com): Project website and knowledgebase











