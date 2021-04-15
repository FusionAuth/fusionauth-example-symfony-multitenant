# Securing a Symfony application with OAuth

This github repo shows a complete, if simple, symfony 5 application which integrates with an OAuth server for authentication and registration.

## Tutorial link

TBD

## Prerequisites

* Symfony 5 installed, including the `symfony` cli tool.
* Composer
* A modern PHP (tested with php 7.3.24)
* FusionAuth installed. See https://fusionauth.io/docs/v1/tech/5-minute-setup-guide/ for install instructions.
* A database such as MySQL installed.

## To set up

* Clone this repository
* run 
* Set up a FusionAuth application in the admin UI.
  * On the OAuth tab
    * Add the following to the Authorized redirect URLs list: `http://localhost:8000/connect/fusionauth/check`
    * Add the following to the Logout URL: `http://localhost:8000`
  * On the Registration tab
    * Turn on self service registration.
    * set the type to 'Basic' and the login type to 'Email'
* Note and save the client secret and client id from the FusionAuth application
* Register a user for the FusionAuth application
* Set up your .env.local file with the following info
  * Database connection info: `DATABASE_URL`
  * FusionAuth client id: `CLIENT_ID`
  * FusionAuth client secret: `CLIENT_SECRET`
  * FusionAuth location: `FUSIONAUTH_BASE`, like `https://local.fusionauth.io`
* Run the migration to create the objects: `symfony console doctrine:migrations:migrate`
* Start the server: `symfony server:start
* Visit `http://localhost:8000` and you should be able to login or register.
