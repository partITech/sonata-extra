# Sonata Extra Bundle: Enabling multisite management with multilang on SonataPageBundle.

## Overview
The Sonata Extra Bundle enhances the SonataPageBundle by offering comprehensive multisite and multilanguage management capabilities. This feature allows tracking and switching between page translations in the front-end and supports a flexible multisite strategy.

## Screens
- List view :

![Activity_log_index.png](./doc-sonata-extra-images/page_multilangue_list.png)

- Detail view :

![Activity_log_detail.png](./doc-sonata-extra-images/page_multilangue_view.png)


## Setting up Multisite with Multiple Languages

### Creating Multilanguage Sites
Each site can support different locales. Use the following command to create the necessary sites:
```shell
bin/console sonata:page:create-site

```

### Quick Setup for Testing

For testing purposes, you can set up multiple sites quickly using these commands:
```sql
TRUNCATE `page__page`;
TRUNCATE `page__site`;
TRUNCATE `page__snapshot`;

bin/console  sonata:page:create-site --enabled --name="Sonata Extra FR" --locale=fr_FR --host=fr.sonata-extra.localhost --relativePath=/fr --enabledFrom=now --enabledTo=- --default
bin/console  sonata:page:create-site --enabled --name="Sonata Extra EN" --locale=en --host=en.sonata-extra.localhost --relativePath=/en --enabledFrom=now --enabledTo=-
bin/console  sonata:page:create-site --enabled --name="Sonata Extra ES" --locale=es --host=es.sonata-extra.localhost --relativePath=/es --enabledFrom=now --enabledTo=-
bin/console  sonata:page:create-site --enabled --name="Sonata Extra IT" --locale=it --host=it.sonata-extra.localhost --relativePath=/it --enabledFrom=now --enabledTo=-
bin/console  sonata:page:create-site --enabled --name="Sonata Extra VN" --locale=vn --host=vn.sonata-extra.localhost --relativePath=/vn --enabledFrom=now --enabledTo=-
```
```


### Apache Virtual Host Configuration
Configure your Apache virtual host as follows:

```apacheconf
<VirtualHost *:80>
ServerName sonata-extra.localhost
ServerAlias fr.sonata-extra.localhost
ServerAlias en.sonata-extra.localhost
ServerAlias es.sonata-extra.localhost
ServerAlias it.sonata-extra.localhost
ServerAlias vn.sonata-extra.localhost
DocumentRoot /var/www/sonata-extra/public

    <Directory /var/www/sonata-extra/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order Allow,Deny
        Allow from All
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

```

## Accessing the Sites

- **Admin Panel**: Access the main site's admin panel at `http://sonata-extra.localhost/admin/login`.
- **Localized Sites**: View content at http://`[fr,en,es,it,vn]`.sonata-extra.localhost/`[fr,en,es,it,vn]`/. Admin login for each site is available at respective URLs.

## Setting up Routes and Snapshots
Run the following commands to create routes, fix them for Sonata Extra, and create snapshots:
```
bin/console sonata:page:update-core-routes
bin/console sonata:extra:page-fix-route
bin/console sonata:page:create-snapshots

```

## Setting up Routes and Snapshots

To enable the multilanguage support of Sonata Extra, modify the configurations as follows:

### `public/index.php` Changes

```php
<?php
use App\Kernel;
use Sonata\PageBundle\Request\RequestFactory;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel =  new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    $request = RequestFactory::createFromGlobals('host_with_path');
    return $kernel->handle($request);
};
```

### `sonata_page.yaml` Changes

```
# Configuration in sonata_page.yaml for multisite management
sonata_page:
multisite: host_with_path

```

## Conclusion
The multisite and multilanguage management features in the Sonata Extra Bundle provide a powerful and flexible approach for managing content across different locales and sites, aligning with the robust capabilities of the SonataPageBundle.
