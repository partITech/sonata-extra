## Integration with PrestaSitemapBundle

Our application automatically integrates with the "PrestaSitemapBundle" to generate a `sitemap.xml` file. This feature encompasses all image assets, article modules, and content pages within the application. The process is entirely automated and includes support for multilingual links.

### Extending Sitemap Functionality

Users looking to further customize the sitemap, especially by adding additional resources from Symfony controllers, are encouraged to consult the official documentation of the "PrestaSitemapBundle". This documentation offers detailed instructions and examples on how to effectively leverage the bundle for extending sitemap capabilities within Symfony-based applications.

The official documentation can be an invaluable resource for:

- Understanding the core functionalities of the "PrestaSitemapBundle".
- Learning how to add custom URLs or resources to the sitemap.
- Implementing advanced features and configurations for more complex website structures.


### basic setup

Let's say you have 4 languages in your setup on the https://my-website.dev/{en,fr,es,it}
so you have 4 version of your main website https://my-website.dev/en


**mall website content** you can use the "on the fly" process from PrestaSitemapBundle.
use this type of config. Of course, adjust params to fit to your needs

```yaml
# config/packages/presta_sitemap.yaml
presta_sitemap:
  defaults:
    priority: 1
    changefreq: daily
    lastmod: now
```

```yaml
# config/routes.yaml
presta_sitemap:
  resource: "@PrestaSitemapBundle/config/routing.yml"
```

```txt
## public/robot.txt
User-agent: *
Disallow: /admin/
Sitemap: https://my-website.dev/en/sitemap.xml
Sitemap: https://my-website.dev/fr/sitemap.xml
Sitemap: https://my-website.dev/es/sitemap.xml
Sitemap: https://my-website.dev/it/sitemap.xml
```
That's all.

**Now let's say that you have a very large amount of articles. on th fly generation is not suitable.**
Use a directory to put generated files accessible from the web : public/sitemaps/
Each lang will have his respective directory so :
```yaml
public/sitemaps/en
public/sitemaps/fr
public/sitemaps/es
public/sitemaps/it
```


```yaml
# config/packages/presta_sitemap.yaml
presta_sitemap:
  defaults:
    priority: 1
    changefreq: daily
    lastmod: now
```
Remove routes from your configuration if you do not want the on the fly generation to be executed
```yaml
# config/routes.yaml
#presta_sitemap:
#  resource: "@PrestaSitemapBundle/config/routing.yml"
```

log into your sheel and execute the generation commands. As prestiteBundle have it s own mechanism and we do not want to change it, 
we will customize our shell commands to suits our needs : 

`SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/en" symfony console presta:sitemaps:dump public/sitemaps/en/  --base-url https://my-website.dev/en
`

Ok, this is not as simple as we wanted, but it's just a shell command that you can automate easily.
```shell
#!/bin/sh

SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/en" symfony console presta:sitemaps:dump public/sitemaps/en/  --base-url https://my-website.dev/sitemaps/en
SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/fr" symfony console presta:sitemaps:dump public/sitemaps/fr/  --base-url https://my-website.dev/sitemaps/fr
SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/es" symfony console presta:sitemaps:dump public/sitemaps/es/  --base-url https://my-website.dev/sitemaps/es
SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/it" symfony console presta:sitemaps:dump public/sitemaps/it/  --base-url https://my-website.dev/sitemaps/it
```
Make your own shell script and execute it with cron or whatever you use. 
 here is the result : 


```shell
❯ tree public/sitemaps/ -l
public/sitemaps/
├── en
│   ├── sitemap.Articles.xml
│   ├── sitemap.Medias.xml
│   ├── sitemap.Pages.xml
│   └── sitemap.xml
├── es
│   ├── sitemap.Medias.xml
│   ├── sitemap.Pages.xml
│   └── sitemap.xml
├── fr
│   ├── sitemap.Articles.xml
│   ├── sitemap.Medias.xml
│   ├── sitemap.Pages.xml
│   └── sitemap.xml
└── it
    ├── sitemap.Medias.xml
    ├── sitemap.Pages.xml
    └── sitemap.xml

```
Last thing is to create your robots.txt to point to your sitemaps. 

```txt
## public/robot.txt
User-agent: *
Disallow: /admin/
Sitemap: https://my-website.dev/sitemaps/en/sitemap.xml
Sitemap: https://my-website.dev/sitemaps/fr/sitemap.xml
Sitemap: https://my-website.dev/sitemaps/es/sitemap.xml
Sitemap: https://my-website.dev/sitemaps/it/sitemap.xml
```

