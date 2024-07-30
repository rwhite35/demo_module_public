# Example ETL Project

ETL automation example using PHP, Laminas Framework Components and Server system resources for asynchronous data processing.

## Project Overview

This project is an example of a capability often requested for enterprise software projects.  It provides Extract Transform and Load(ETL) processing to import data and transforms it into actionable business intelligence, customer engagement insights, and back-office operation. While not a complete feature, it does lay the ground work for extending the code base to full-featured ETL module that could bolt onto another Laminas project. Think of it as an ETL `starter kit`. 

## MVC Architecture

The project is based on Model-View-Controller(MVC) architecture and implements [Laminas MVC](https://docs.laminas.dev/mvc/) framework (among others). It leverages abstraction, dependency injection, dynamic routing & autoload, and database integration to demonstrate an ETL Proof-of-Concept. MVC ensures `separation of concern` and promotes clean code resulting in stable operation and feature logevity.

## Prerequisites & Setup

The project was created for demonstration purpose and configured to serve from an instance of [Apache2](https://httpd.apache.org/) with a configured VirtualHost.  It can be deployed on a container(Docker), cloud service(AWS) or dedicated web servers.  The following outlines some basic configuration before installing and running the project locally.

### VirtualHost

This assumes Apache is already installed and accessible for local development. To that end, the following is a reference for how the projects VirtualHost was configured.  Values can be changed, but ServerName should match the root directory this project is running under.  The URL to load the project would be `http://demo.acme.com/public` if everything is correctly configured.

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

Additionally, it will be necessary to add an entry in the localhost `hosts` file. On Mac that file is `/etc/hosts`, and for Windows its usually `c:\windows\system32\drivers\etc\hosts`. Add the following line to hosts file using any text editor, this will load the above URL.

```bash
127.0.0.1 demo.acme.com
```

### .htaccess File

Laminas MVC along with its ModuleManager and ServiceManager uses Configuration Management to dynamically load views, controllers and artifacts that have been defined in the Router's `route stack`. The route stack is a merged `routes` object collated from each resources 'module.config.php` file.  There are a couple configurations that are required to support dynamic autoloading. 

1. Confirm Apache has enabled rewrite module
..* LoadModule rewrite_module modules/mod_rewrite.so

2. Confirm Apache has enabled Access File
..* AccessFileName .htaccess

3. Add the following directives to the an .htaccess file under public/ directory

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











