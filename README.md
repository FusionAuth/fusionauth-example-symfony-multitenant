# A multi-tenant Symfony application with OAuth

This github repo shows a complete symfony 5 application which integrates with an OAuth server for authentication and registration and allows users in one application to create tenants in FusionAuth that can be used by other users.

The application which lets users create tenants is called the control plane application. 

The control plane application url, where people sign up, might be `https://app.example.com`. A user can sign up and create their own application, which might live at `dantest.example.com` or `foobar.example.com`. 

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
* Set up a FusionAuth application in the admin UI. This will be the control plane application, where users can create their own tenants.
  * On the OAuth tab
    * Add the following to the Authorized redirect URLs list: `http://localhost:8000/connect/fusionauth/check`
    * Add the following to the Logout URL: `http://localhost:8000`
  * On the Registration tab
    * Turn on self service registration.
    * set the type to 'Basic' and the login type to 'Email'
* Note and save the client secret and client id from the FusionAuth application
* Register a user for the FusionAuth application
* Create a blueprint tenant with all the settings (theme, password rules, etc) that you like. Note the id of that tenant.
* Create a keymanager API key.
* Set up your .env.local file with the following info
  * Database connection info: `DATABASE_URL`
  * A root domain for your saas application: `SAAS_ROOT_DOMAIN`. Make sure you prefix it with a `.`: `.example.com`. Add a port if you aren't running a proxy: `.example.com:8000`.
  * The client id from your just created FusionAuth application: `CONTROL_PLANE_CLIENT_ID`
  * The client secret from your just created FusionAuth application: `CONTROL_PLANE_CLIENT_ID`
  * The hostname of the control plane web application. If the control plane, where people sign up, is app.example.com, then `CONTROL_PLANE_HOSTNAME` would be `app`. 
  * The FusionAuth location: `FUSIONAUTH_BASE`, like `https://local.fusionauth.io`
  * The key manager API key: `FUSIONAUTH_KEYMANAGER_API_KEY`
  * The blueprint tenant id: `FUSIONAUTH_BLUEPRINT_TENANT_ID`
* Set up your domain names. I used /etc/hosts and added a few hosts which all responded to localhost: `ppvc.fusionauth.io ppvctest1.fusionauth.io ppvctest2.fusionauth.io`
* Run the migration to create the objects: `symfony console doctrine:migrations:migrate`
* Start the server: `symfony server:start`
* Visit `http://ppvc.fusionauth.io:8000` and you should be able to login or register.
* Create a tenant with a hostname you have set up with /etc/hosts. 
* You should be able to login and register for the other application at the new url, http://ppvctest1.fusionauth.io:8000, which has different functionality and is entirely separate.
