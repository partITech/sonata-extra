# Integration with PrestaSitemapBundle

Automatically integrates with **PrestaSitemapBundle** to generate a `sitemap.xml` file. This integration covers image assets, article modules, and content pages, and supports multilingual links out of the box.

> [!TIP]
> For more advanced or custom setups, consult the official [PrestaSitemapBundle documentation](https://github.com/prestaconcept/PrestaSitemapBundle). It details how to add resources from Symfony controllers and implement more complex configurations.

---

## Basic Setup

If your website is accessible at `https://my-website.dev/{en,fr,es,it}`, each language variation (e.g., `https://my-website.dev/en`) can generate its own sitemap using PrestaSitemapBundle’s default “on-the-fly” mode.

### Configuration

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

### robots.txt

Add sitemap entries for each locale:

```txt
## public/robot.txt
User-agent: *
Disallow: /admin/
Sitemap: https://my-website.dev/en/sitemap.xml
Sitemap: https://my-website.dev/fr/sitemap.xml
Sitemap: https://my-website.dev/es/sitemap.xml
Sitemap: https://my-website.dev/it/sitemap.xml
```

> [!NOTE]
> This configuration is sufficient for small websites that can handle real-time sitemap generation.

---

## Handling Large Websites

For large content sets (e.g., numerous articles), the **on-the-fly** generation may be too resource-intensive. Instead, you can generate sitemap files in advance and serve them from a dedicated directory like `public/sitemaps/`.

### Directory Structure

Create separate subfolders for each language:

```text
public/sitemaps/en 
public/sitemaps/fr 
public/sitemaps/es 
public/sitemaps/it
```



### Configuration

```yaml
# config/packages/presta_sitemap.yaml
presta_sitemap:
defaults:
priority: 1
changefreq: daily
lastmod: now
```

Remove or comment out the **on-the-fly** route if you prefer fully static sitemaps:

```yaml
# config/routes.yaml
# presta_sitemap:
#   resource: "@PrestaSitemapBundle/config/routing.yml"
```

### Generating the Sitemaps

Use shell commands to generate static sitemaps for each locale. In the following example, the `SYMFONY_HTTP_CONTEXT_URL` environment variable sets the base URL context for each language:

```shell
SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/en" \
symfony console presta:sitemaps:dump public/sitemaps/en/ --base-url https://my-website.dev/sitemaps/en

SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/fr" \
symfony console presta:sitemaps:dump public/sitemaps/fr/ --base-url https://my-website.dev/sitemaps/fr

SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/es" \
symfony console presta:sitemaps:dump public/sitemaps/es/ --base-url https://my-website.dev/sitemaps/es

SYMFONY_HTTP_CONTEXT_URL="https://my-website.dev/it" \
symfony console presta:sitemaps:dump public/sitemaps/it/ --base-url https://my-website.dev/sitemaps/it
```

> [!TIP]
> Automate these commands via a custom shell script or a CRON job. This way, your sitemaps stay up-to-date without manual intervention.

### Sample Directory Tree

```shell
❯ tree public/sitemaps/ -l
public/sitemaps/
├── en
│   ├── sitemap.Articles.xml
│   ├── sitemap.Medias.xml
│   ├── sitemap.Pages.xml
│   └── sitemap.xml
├── es
│   ├── sitemap.Medias.xml
│   ├── sitemap.Pages.xml
│   └── sitemap.xml
├── fr
│   ├── sitemap.Articles.xml
│   ├── sitemap.Medias.xml
│   ├── sitemap.Pages.xml
│   └── sitemap.xml
└── it
├── sitemap.Medias.xml
├── sitemap.Pages.xml
└── sitemap.xml
```

### Updated robots.txt

Finally, update your `robots.txt` to point to your static sitemap files:

```txt
## public/robot.txt
User-agent: *
Disallow: /admin/
Sitemap: https://my-website.dev/sitemaps/en/sitemap.xml
Sitemap: https://my-website.dev/sitemaps/fr/sitemap.xml
Sitemap: https://my-website.dev/sitemaps/es/sitemap.xml
Sitemap: https://my-website.dev/sitemaps/it/sitemap.xml
```

---

## Conclusion

By leveraging PrestaSitemapBundle’s features—either **on-the-fly** or via **static generation**—your site can serve robust multilingual sitemaps to search engines. Adjust your configuration based on the size of your content and your performance needs. For advanced customization, refer to the official PrestaSitemapBundle documentation.
