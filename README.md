# Sonata Extra Bundle

> [!TIP]
> For more detailed information and comprehensive guides, please visit our [official documentation](https://sonata-extra.partitech.com)


**An advanced extension for the Sonata Admin Bundle, empowering your Symfony projects with enhanced functionality and ease of use.**

---

The Sonata Extra Bundle enriches the Sonata Admin experience by adding powerful tools and functionalities, such as multilingual management, detailed auditing, flexible approval workflows, AI-driven smart services, seamless WordPress integration, efficient asset management, and more.

## 📌 Features

- **Activity Logging**: Comprehensive logs of admin actions for increased transparency.
- **Workflow Approval**: Role-based approval system for critical changes.
- **Multilanguage and Multisite Management**: Easily manage content across multiple languages and sites.
- **Content Security Policy (CSP)**: Enhanced security against common web vulnerabilities.
- **AI-Powered Smart Services**: Automatic translation and SEO optimization using AI.
- **WordPress Importer**: Seamless migration from WordPress to Sonata.
- **Advanced Asset Handling**: Optimized management of CSS and JavaScript resources.
- **SEO Optimization**: Automated SEO enhancements and suggestions.
- **Cookie Consent Management**: Flexible and customizable GDPR compliance tool.
- **Header Redirect Manager**: Conveniently manage HTTP redirects directly from the admin.
- **Customizable Error Pages**: Easily set up personalized error pages.
- **Integration with Gutenberg and CKEditor**: Rich content editing experiences with enhanced editors.

---

## 🚀 Installation

### Step 1: Require the Bundle

Use Composer to install the bundle in your Symfony project:

```bash
composer require partitech/sonata-extra
```

### 2. Enable the Bundle

Register the bundle in your Symfony application by updating `config/bundles.php`:

```php
return [
    //...
    Partitech\SonataExtra\PartitechSonataExtraBundle::class => ['all' => true],
];
```

### 3. Install Assets

Run the following commands to install and initialize the necessary assets:

```bash
bin/console sonata:extra:install-gutenberg
bin/console ckeditor:install --tag=4.19.0
bin/console asset:install
```

## 📖 Documentation

Detailed setup instructions, feature guides, and practical examples can be found on our official documentation site:

**[🔗 sonata-extra.partitech.com](https://sonata-extra.partitech.com)**

