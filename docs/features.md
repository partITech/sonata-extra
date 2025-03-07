# Features

**The Partitech Sonata Extra Bundle** extends your Sonata application with a broad range of advanced features. This document covers the key functionalities provided by the bundle, including configuration attributes, activity logging, approval workflows, asset management, blogging, security enhancements, multilingual support, AI services, WordPress importing, block management, form types, and more.

> [!TIP]
> Refer to each feature’s dedicated documentation file for details on usage, configuration, and best practices.

---

## AsAdmin() PHP Attribute Configuration

Add a new configuration type directly in the Admin class for simpler, more concise administration.

```php
use Partitech\SonataExtra\Attribute\AsAdmin;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'My Entity Admin',
    model_class: \Partitech\SonataExtra\Entity\Article::class,
    controller: \Partitech\SonataExtra\Controller\Admin\ArticleController::class,
    calls: [
        ['addChild', \Partitech\SonataExtra\Admin\ArticleRevisionAdmin::class, 'article'],
    ]
)]
class ArticleAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;
    protected $baseRoutePattern = 'article';

```

---

## Activity Log in Admin

Monitor and log all activities within the admin site. This feature offers a complete overview of actions taken, the resources affected, action descriptions, and the user who performed them.

- **Action Type Display**: Shows the type of action performed (e.g., create, update, delete).
- **Resource Tracking**: Identifies which resource was affected.
- **Detailed View**: Offers an in-depth look at the changes made.
- **Undo Functionality**: Allows reversing modifications when possible.

**List view**:  
![Activity_log_index.png](./doc-sonata-extra-images/Activity_log_index.png)

**Detail view**:  
![Activity_log_detail.png](./doc-sonata-extra-images/Activity_log_detail.png)

---

## Activity Approval

Require a user with `ROLE_APPROVE` to validate certain actions before they take effect. This workflow logs every action, offering a comprehensive overview but applies changes only once they’re approved.

- **Role-Based Approval**: Pending actions need a user with `ROLE_APPROVE` for validation.
- **Visual Notifications**: A red notification badge alerts admins of pending modifications.
- **Detailed Action View**: Showcases the modified fields and values.
- **Purge Functionality**: Allows purging of pending modifications.

**Editor action**:  
![approval_editor_action.png](./doc-sonata-extra-images/approval_editor_action.png)

**Admin notification**:  
![approval_admin_notification.png](./doc-sonata-extra-images/approval_admin_notification.png)

**List view**:  
![approval_admin_list.png](./doc-sonata-extra-images/approval_admin_list.png)

**Detail view**:  
![approval_admin_detail.png](doc-sonata-extra-images/approval_admin_detail.png)

---

## Assets Management

Manage CSS and JavaScript assets in Sonata blocks with ease. Include external files and inline scripts or styles efficiently.

```twig
{{ sonata_extra_get_blocks_css('default')|raw }}
{{ sonata_extra_get_blocks_css_inline('default', true)|raw }}
{{ sonata_extra_get_blocks_js('default')|raw }}
{{ sonata_extra_get_blocks_js_inline('default', true)|raw }}
```

---

## Blog

Enhance your Sonata project with an integrated blog system. It supports multilingual URLs, custom routes, and more.

**Multilanguage custom URLs**:  
![Blog_CustomUrl.png](./doc-sonata-extra-images/Blog_CustomUrl.png)

**Blog service type**:  
![Blog_ServiceType.png](./doc-sonata-extra-images/Blog_ServiceType.png)

---

## Content Security Policy (CSP)

Implement and manage CSP to protect against common security threats like XSS or data injection. This ensures safer content handling across your application.

**Code Preview**:  
```yaml
content_security_policy:
    object-src:
      - 'none'
    script-src:
      - "'self'"
      - "'unsafe-inline'"
      - "'unsafe-eval'"
      - 'https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.11/beautify-html.js'
      - 'https://unpkg.com/@popperjs/core@2'
      - 'https://unpkg.com/tippy.js@6'
    style-src:
      - "'self'"
      - "'unsafe-inline'"
      - 'fonts.googleapis.com'
      - 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toaster/5.1.0/css/bootstrap-toaster.min.css'
      - 'https://cdn.jsdelivr.net/npm/github-markdown-css/github-markdown.css'

```

**Rendered header**:  
![result_security_policy.png](./doc-sonata-extra-images/result_security_policy.png)

---

## Header Redirect Manager

Manage all header redirections in a single interface, complete with list and detail views for easy oversight.

**List view**:  
![Activity_log_index.png](./doc-sonata-extra-images/header-redirect-list.png)

**Detail view**:  
![Activity_log_detail.png](./doc-sonata-extra-images/header-redirect-detail.png)

---

## Language-Switcher

A user-friendly language switcher for hybrid pages. Easily toggle between locales directly in the interface.

**Front view**:  
![Activity_log_index.png](./doc-sonata-extra-images/language_switcher.png)

---

## Multilanguage on SonataPageBundle

Comprehensive multisite and multilingual management for SonataPageBundle. Quickly switch between translations on the front-end and handle multiple site setups seamlessly.

**List view**:  
![Activity_log_index.png](./doc-sonata-extra-images/page_multilangue_list.png)

**Detail view**:  
![Activity_log_detail.png](./doc-sonata-extra-images/page_multilangue_view.png)

---

## Multilanguage Support for User Admins

Enable multilingual functionality in the admin interface. Includes traits for admin classes and entities, plus locale icons from SonataPage to link records and manage languages.

**List view**  
![Multilanguage_edit.png](./doc-sonata-extra-images/Multilanguage_edit.png)

**Edit view**  
![Multilanguage_list.png](./doc-sonata-extra-images/Multilanguage_list.png)

**Create translation**  
![Multilanguage_create_translation.png](./doc-sonata-extra-images/Multilanguage_create_translation.png)

---

## Firewall

Configure and manage firewall rules from the Sonata Admin environment. Create rules to filter incoming requests based on IP addresses, user agents, or specific stop words.

---

## Integration with PrestaSitemapBundle

Automatically generate a `sitemap.xml` file encompassing images, articles, and pages. This feature also supports multilingual links for seamless SEO integration.

---

## Smart Services (AI-Powered Services)

### Translation

Use built-in AI-powered translation to translate text between languages efficiently.

### Translation CMD

Automate the translation of a selected language for any admin object using a command-line tool.

### Translation Template

Generate custom translation templates effortlessly.

---

## WordPress-Import

Easily import images, categories, posts, and tags from a WordPress site. This tool is key for integrating WordPress content into your Sonata application.

**Import command**:  
![wordpress_import.png](./doc-sonata-extra-images/wordpress_import.png)

---

## Blocks

### Cookie Consent Block (GDPR)

Manage user consent for cookies with a flexible, GDPR-compliant block.

![cookie-consent-block.png](./doc-sonata-extra-images/cookie-consent-block.png)  
![cookie-consent-block_btn.png](./doc-sonata-extra-images/cookie-consent-block_btn.png)

---

## Form Types

### Gutenberg Editor

Leverage the WordPress Gutenberg editor for an advanced, block-based editing experience within Sonata.

![Gutenberg_FormType_1.png](./doc-sonata-extra-images/Gutenberg_FormType_1.png)

### Slider Manager with Sonata Page Block

Add fully customizable sliders to your Sonata Pages.

![Slider Manager Preview](./doc-sonata-extra-images/slider-manager-preview.png)

### FAQ Manager with Sonata Page Block

Integrate FAQs into your Sonata Pages easily.

![faq_list.png](./doc-sonata-extra-images/faq_list.png)  
![faq_questions.png](./doc-sonata-extra-images/faq_questions.png)  
![faq_block.png](./doc-sonata-extra-images/faq_block.png)  
![faq_page.png](./doc-sonata-extra-images/faq_page.png)

### Sonata Page Block Manager

Hide unwanted blocks and manage visible blocks within Sonata Page.

![Block Manager Preview](./doc-sonata-extra-images/block-manager-preview.png)

### Article Manager with Gutenberg Editor

Write articles using a block-based interface. Choose between Gutenberg and other editors.

![article_editor_gutemberg.png](./doc-sonata-extra-images/article_editor_gutemberg.png)  
![article_editor_selector.png](./doc-sonata-extra-images/article_editor_selector.png)

### Sonata Admin FormTypes

Additional FormTypes for Sonata Admin, including Markdown and Gutenberg editors.

![Markdown_editor.png](./doc-sonata-extra-images/Markdown_editor.png)

### Gutenberg Editor with Advanced Features

Add file uploads, custom patterns, and SonataMedia galleries to Gutenberg for a richer editing experience.

![Gutenberg_FormType_5.png](./doc-sonata-extra-images/Gutenberg_FormType_5.png)  
![Gutenberg_FormType_3.png](./doc-sonata-extra-images/Gutenberg_FormType_3.png)

### CKEditor Gallery View

Enable improved gallery viewing directly within CKEditor.

![CkEditor_2.png](./doc-sonata-extra-images/CkEditor_2.png)  
![CkEditor_1.png](./doc-sonata-extra-images/CkEditor_1.png)

### Automatic WordPress Importer

A dedicated command to import WordPress data into your Sonata application.

![Wordpress_import.png](./doc-sonata-extra-images/wordpress_import.png)

---

> [!CAUTION]
> Review each feature’s documentation for comprehensive setup steps, configuration details, and usage notes.
