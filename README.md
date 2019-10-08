# MAW Simple SAML for PHP
In order to authenticate our PHP applications with Azure AD, all PHP applications must be wrapped using the PHP extension Simple SAML PHP. Follow the instructions below to setup external and internal applications for authentication with Azure AD using SAML.


**Table of Contents**

## Prerequisites Before Wrapping Application
The following tools and permissions are needed for PowerShell:
* Root Access to Azure Tentant
* PHP 7+ Installed on External Application Server
* Simple SAML for PHP setup on external server
    > Extension should be stored under the C:\inetpub folder at the same level as the application to be wrapped. (ie If application is at C:\inetpub\wwwroot_myapp, the extension should be stored at C:\inetpub\wwwroot_simple-saml.) Admin password can be secured from your local admin. 
* IIS access to exernal PHP application (cPanel Access on Linux Server)


## Wrapping External PHP Applications

![](https://azuredemo.wish.org/simple-saml/assets/poc-external-apps.png "External Application SSO Authentication")

### `Register Enterprise Application`

* Login to Azure Tenant as Admin

* Create Enterprise Application
    * **Navigate to Enterprise Applications** (if not available add it to favorites from 'All Services')
    * Click **New Application**
    * Click **Non-Gallery Application**
    * Give the registration a name, keeping our naming convention in consideration (ie webreg-ext-t3-demo-appname)
    * Click **Add**

* Setup Single Sign On (SSO) for Application
    * **Navigate to Enterprise Applications** (if not available add it to favorites from 'All Services')
    * Click on the newly created application
    * Click on **Single sign-on**
    * Setup SSO with SAML
        * Step 1: Supply Basic SAML Configration
            * Click edit icon in section 1
            * Add a unique identifier for the registration, GUIDs are not to be used
            * Supply Reply URL (format: https://domain.wish.org/simple-saml/www/module.php/saml/sp/saml2-acs.php/<Entity ID>)
            * Click Save
        * Step 2: User Attributes & Claims
            * Click edit icon in section 2
            * If there are any additional attributes that you wish to supply to your applicaition click "**Add New Claim**"
            * If there are any additional group attributes that you wish to supply to your applicaition click "**Add A Group Claim**"
        * Step 3: Download Federation Data
            * Click "**Download**" next to **Federation Metadata XML** and store this file in a safe place. You will use it in subsequent steps.
        * Step 4: Application URLs
            * If your appliation uses Logout: Copy "**Logout URL**"
            * Copy the "**Azure AD Indentifier**" as it will be used in subsequent steps.

* User Assignments: Option 1 - Applies to Small Subset of Users
    > Users are granted access one-by-one and all others denied

    * **Navigate to Enterprise Applications** (if not available add it to favorites from 'All Services')
    * Click on the newly created application
    * Click **Properties**
    * Ensure that **Yes** is checked next to "User Assignment Required**
    * Click Save
    * Assign Roles to Application Registration
        * **Navigate to App registrations** (if not available add it to favorites from 'All Services')
        * Click on the newly created application
        * Click on **Manifest**
        * Add any new roles to the "appRoles" section if required, else authenticate by User only. Only users assigned to the application wil have access.
        * Sample of Roles for "appRoles"
            ```json
            "appRoles": [
                {
                "allowedMemberTypes": [
                    "User"
                ],
                "displayName": "Writer",
                "id": "d1c2ade8-98f8-45fd-aa4a-6d06b947c66f",
                "isEnabled": true,
                "description": "Writers Have the ability to create tasks.",
                "value": "Writer"
                },
                {
                "allowedMemberTypes": [
                    "User"
                ],
                "displayName": "Observer",
                "id": "fcac0bdb-e45d-4cfc-9733-fbea156da358",
                "isEnabled": true,
                "description": "Observers only have the ability to view tasks and their statuses.",
                "value": "Observer"
                },
                {
                "allowedMemberTypes": [
                    "User"
                ],
                "displayName": "Approver",
                "id": "fc803414-3c61-4ebc-a5e5-cd1675c14bbb",
                "isEnabled": true,
                "description": "Approvers have the ability to change the status of tasks.",
                "value": "Approver"
                },
                {
                "allowedMemberTypes": [
                    "User"
                ],
                "displayName": "Admin",
                "id": "81e10148-16a8-432a-b86d-ef620c3e48ef",
                "isEnabled": true,
                "description": "Admins can manage roles and perform all task actions.",
                "value": "Admin"
                }
            ],
            ```
    * Assign Users to Application Registration
        * **Navigate to Enterprise Applications** (if not available add it to favorites from 'All Services')
        * Click on the newly created application
        * Click "**Add User**"
        * Click "**Users and Groups**"
        * Add the users that will have access to the applicaition, click "**Select**"
        * Click "**Select Role**"
        * Add the role that the user will have. If there were no additional roles added to the manifest then only "User" will be available.
        * Click "**Assign**"

* User Assignments: Option 2 - Applies to All Users in Active Directory
    > All users granted access by default and certain users can be restricted if necessary

    * **Navigate to Enterprise Applications** (if not available add it to favorites from 'All Services')
    * Click on the newly created application
    * Click **Properties**
    * If only a specific number of users will access your application:
        * Ensure that **Yes** is checked next to "User Assignment Required**
    * If all users in the active directory will have access to your application:
        * Ensure that **No** is checked next to "User Assignment Required**
    * Restrict All Users Except Particular Group - Optional
        * Click on "**New Policy**"
        * Give the name a descriptive policy name (ie "Block Users Without Web Developer Role")
        * Click on "Users and Groups"
            * On the Include tab: Click All Users
            * On the Exclude tab: 
                * Check "Users and Groups"
                * Click "Select excluded users"
                * Select "Users" and/or "Groups" that are excluded from this policy.
                * Click "Select"
            * Click "**Done**"
        * Click "**Cloud Apps or Actions**"
            * On the Include tab
                * Toggle "Select apps"
                * Click "**Select**"
                * Select all applictions where this policy will apply
                * Click "**Select**"
                * Click "**Done**"
        * Click "**Grant**"
            * Click "**Block access**"
        * Click "**On**" under "**Enable Policy**"
        * Click "**Save**"
* Ensure Enterprise Settings Flow Down to Registration
    * **Navigate to App registrations** (if not available add it to favorites from 'All Services')
        * Click on the newly created application
        * Click "**Overview**"
        * Ensure that Application ID URI is set
        * Click on "Redirect URIs"
            * Ensure that the Redirect URI is set to the same Reply URL from the [Setup SSO with SAML](#SetupSsoWithSaml) section.

### `Wrap PHP Application using SAML/PHP Extension`

#### Windows Setup
* Setup Virtual Directory in IIS
    * Navigate to the server where the application is wrapped
    * Open IIS Manager and expand "Sites"
    * Right click on site name of application and click "**Add Virtual Directory...**"
        * Set the alias to "**simple-saml**"
        * Set the physical path to the location of the SAML extension (ie C:\inetpub\wwwroot_simple-saml)
* Save Signing Certificate for Application
    * Locate the federation file downloaded from Step 3 of the [Setup SSO with SAML](#SetupSsoWithSaml) section and open it.
        * Location the element `<X509Certificate>` and note the contents. You will need this in the next step.
    * Open Windows Explorer on the remote server and navigate to the installation directory of the simple SAML extension and open the following file <install-dir>\metadata\saml20-idp-remote.php.
    * If the file contains the following entry...
        ```php
        0 => /* certificate-admin-test */
        array (
            'encryption' => false,
            'signing' => true,
            'type' => 'X509Certificate',
            'X509Certificate' => 'MIIC8DCCAdigAwIBAgIQN3...+v68Iz',
        ),    
        ```
    * You will update the file to look like the following and replace `<Federation X509Certificate>` with the content of the ``<X509Certificate>` in the federation file you downloaded previouslys:
        ```php
        0 => /* certificate-admin-test */
        array (
        'encryption' => false,
        'signing' => true,
        'type' => 'X509Certificate',
        'X509Certificate' => 'MIIC8DCCAdigAwIBAgIQN3...+v68Iz',,
        ),
        1 => /* helloworld */
        array (
        'encryption' => false,
        'signing' => true,
        'type' => 'X509Certificate',
        'X509Certificate' => '<Federation X509Certificate>',
        ),
        ```
* Add a New Authencation Source
    * Open Windows Explorer and navigate to the installation directory of the simple SAML extension and open the following file <install-dir>\config\authsources.php.
    * Recall the Entity ID from Step 1 of the [Setup SSO with SAML](#SetupSsoWithSaml) section
    * Recall the Azure AD Indentifier from Step 4 of the [Setup SSO with SAML](#SetupSsoWithSaml) section
    * Add a new authentication source key:
        ```php
        '<Entity ID>' => [
            'saml:SP',

            // The entity ID of this SP.
            'entityID' => '<Entity ID>',

            // The entity ID of the IdP this SP should contact.
            'idp' => '<Azure AD Identifier>/',
            
            // The URL to the discovery service.
            'discoURL' => null,
        ],
        ```

#### LINUX Setup
* Coming Soon

## Wrapping PHP Applications in Azure
* Coming Soon