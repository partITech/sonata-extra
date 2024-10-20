# Content Security Policy in Sonata-Extra Bundle

## Introduction

Content Security Policy (CSP) is a crucial security feature in web development, helping to prevent various types of attacks, such as Cross-Site Scripting (XSS) and data injection attacks. The Sonata-Extra bundle for Symfony provides an efficient way to implement and manage CSP in your application.

## Understanding Content Security Policy (CSP)

CSP is a response header that allows you to control the resources your web page can load or execute. By specifying a list of trusted sources, you can mitigate the risk of malicious content injections.

### Key Concepts

- **Directives**: These are the rules that define which resources can be loaded. Each directive controls a specific type of resource, like scripts, styles, images, etc.
- **Sources**: Under each directive, you specify sources (like URLs) from which the resource can be loaded.

## Security Types in Sonata-Extra Bundle

In the Sonata-Extra bundle, `SECURITY_TYPES` is a constant array mapping CSP directives to their configuration keys. Here’s a breakdown of each directive:

### `default-src`
Default fallback for most directives. Controls default sources for various content types.

### `script-src`
Defines valid sources for JavaScript. Options include:
- `'self'`: Only load scripts from the same origin.
- `'unsafe-inline'`: Allow inline scripts, though it's less secure.
- Specific URLs: Define external scripts that are allowed.

### `style-src`
Specifies valid sources for stylesheets. Similar options to `script-src`.

### `img-src`
Controls where images can be loaded from.

### `connect-src`
Limits the origins to which you can connect (e.g., WebSockets, AJAX requests).

### `font-src`
Defines sources for font files.

### `object-src`
Controls sources for elements like `<object>`, `<embed>`, etc.

### `media-src`
Specifies sources for loading media (audio and video).

### `frame-src`
Determines valid sources for frames and iframes.

### Other Directives
- `child-src`, `form-action`, `frame-ancestors`, `manifest-src`, `base-uri`, `sandbox`, `report-uri`, `worker-src`, `navigate-to`: These further refine policies for specific use cases.

## Implementing CSP in Sonata-Extra Bundle

### Configuration

Define your CSP policies in a YAML configuration file under `partitech_sonata_extra`. For example:

```yaml
partitech_sonata_extra:
  content_security_policy:
    object-src:
      - 'none'
    script-src:
      - 'self'
      - 'unsafe-inline'
      - 'https://external.script.url'
    style-src:
      - 'self'
      - 'unsafe-inline'
      - 'https://external.stylesheet.url'
    font-src:
      - 'self'
      - 'https://cdnjs.cloudflare.com/'
      - 'https://fonts.gstatic.com/'

