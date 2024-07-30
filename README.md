# Example ETL Project

ETL automation example using PHP, Laminas Framework Components and Server system resources for asynchronous data processing.

## Project Overview

This project is an example of a capability often requested for enterprise software projects.  It provides Extract Transform and Load(ETL) processing to import data and transforms it into actionable business intelligence, customer engagement insights, and back-office operation. While not a complete feature, it does lay the ground work for extending the code base to full-featured ETL module that could bolt onto another Laminas project. Think of it as an ETL `starter kit`. 

## MVC Architecture

The project is based on Model-View-Controller(MVC) architecture and implements [Laminas MVC](https://docs.laminas.dev/mvc/) framework (among others). It leverages abstraction, dependency injection, dynamic routing & autoload, and database integration to demonstrate an ETL Proof-of-Concept. MVC ensures `separation of concern` and promotes clean code resulting in stable operation and feature logevity.

## Prerequisites & Setup

The project was created for demonstration purpose and configured to serve from an instance of [Apache2](https://httpd.apache.org/) with a configured VHost.  It could be refactored to deploy on containers(Docker), cloud services(AWS) or a dedicated web servers.  The following outlines some basic requirements for installing and running the project locally.

### VirtualHost

This project assumes Apache is already installed and accessible for local development. To that end, use the following block as a reference for setting up a VirtualHost.






