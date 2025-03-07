# Cookie Consent Block (GDPR)

**The Cookie Consent Block** is a customizable solution within the Sonata Extra Bundle to manage user consent for cookies in compliance with GDPR regulations. It offers a flexible, user-friendly interface for consent management.

---

## Overview

This feature allows you to present a clear and concise cookie consent notice to your users. By integrating the **Orejime** library, the block can handle multiple types of scripts and services according to the user’s consent preferences.

---

## Screens

![cookie-consent-block.png](./doc-sonata-extra-images/cookie-consent-block.png)  
![cookie-consent-block_btn.png](./doc-sonata-extra-images/cookie-consent-block_btn.png)

---

## Usage

Add the **Cookie Consent Block** to your Sonata Admin dashboard or any section of your application where you need to display a cookie consent prompt. Once configured, the block automatically handles user input, storing and respecting cookie preferences.

---

## Configuration

### Settings

You can configure the Cookie Consent Block by adjusting the following options in your block configuration:

- **`config_orejim`**  
  JavaScript configuration for the Orejime consent management platform.
- **`config_tags`**  
  Custom script tags for analytics or other purposes, aligned with user consent.
- **`template`**  
  The Twig template path for rendering the cookie consent UI.
- **`style_url` (Optional)**  
  URL to additional CSS for styling.
- **`class` (Optional)**  
  A custom CSS class for further styling control.

### Example Configuration

```php
$resolver->setDefaults([
    'config_orejim' => $orejimeConfig, // Orejime JavaScript configuration
    'config_tags'   => $config_tags,   // Custom script tags
    'style_url'     => '',             // Optional additional CSS
    'class'         => null,           // Optional custom CSS class
    'template'      => '@PartitechSonataExtra/Blocks/cookie_consent/default.html.twig',
]);
```

> [!NOTE]
> Adjust the template path and any script configurations to match your project’s structure.

---

## Customization

To tailor the **Cookie Consent Block** to your needs, modify:

1. **Twig Template** (`@PartitechSonataExtra/Blocks/cookie_consent/default.html.twig`)  
   Add or remove UI elements, change text, and include additional styling.

2. **JavaScript Configuration** (`config_orejim`)  
   Adapt how cookies and services are categorized, define consent types, and localize strings.

3. **CSS Styles**  
   Apply your own stylesheet or override the default one via the `style_url` parameter or a custom class.

---

## Integration with Orejime

The Cookie Consent Block leverages the [Orejime](https://github.com/empreinte-digitale/orejime) library for managing user consent. Make sure you understand Orejime’s configuration and usage to maximize this block’s capabilities.

---

## Translation and Localization

The block supports multiple languages to ensure GDPR compliance across different locales. Update your `config_orejim` settings to include translations or localized text, matching user expectations in each region.

> [!IMPORTANT]
> Always verify that each service requiring consent is correctly categorized (e.g., analytics, tracking, social media). Misclassification can lead to privacy compliance issues.
