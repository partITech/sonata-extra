# Header Redirect Manager

The **Header Redirect Manager** within the Sonata Extra Bundle enables comprehensive management of incoming traffic redirections, allowing you to define custom redirection rules with various HTTP status codes.

> [!NOTE]
> This functionality is especially useful for SEO optimization, ensuring users and search engines are correctly routed to the most relevant URLs.

---

## Overview

With the Header Redirect Manager, you can configure and maintain redirection rules by specifying:
- The **incoming URL** that triggers a redirect,
- The **domain** from which requests originate (if applicable),
- The **target URL** to which traffic should be sent,
- And the **HTTP status code** to use.

---

## List view


![header-redirect-list.png](./doc-sonata-extra-images/header-redirect-list.png)
 
## Detail view
![header-redirect-detail.png](./doc-sonata-extra-images/header-redirect-detail.png)

---

## Supported Redirection Status Codes

- **301 (Moved Permanently)**  
  Indicates the resource has been permanently relocated to a new URL.

- **302 (Found)**  
  Suggests a temporary redirection to another URL.

- **307 (Temporary Redirect)**  
  Temporarily moves the resource without changing the HTTP method.

- **308 (Permanent Redirect)**  
  Permanently redirects the resource, preserving the HTTP method and request body.

---

## Features

- **Redirection Rule Management**  
  Create and maintain redirection rules with granular control.

- **List View**  
  View all configured rules at a glance, including their domain, incoming URL, target, and status code.

- **Flexible Configuration**  
  Specify unique incoming paths and target URLs for various domains.

- **Status Code Specification**  
  Select the appropriate HTTP status code for each rule to align with best practices.

---

## Configuring a Redirection Rule

When creating a new rule:

1. **Incoming URL**: The path or URI that users request.
2. **Domain**: The domain under which this redirect should apply (optional, if not using a multi-domain setup).
3. **Target URL**: The destination where traffic should be sent.
4. **Status Code**: Select from 301, 302, 307, or 308 to control the nature of the redirect.

> [!IMPORTANT]
> Always choose the status code that reflects whether the redirect is temporary or permanent to maintain proper SEO value.

---

## Usage

1. **Access the Header Redirect Manager** in your Sonata Admin interface.
2. **Add a New Rule** by specifying the incoming URL, domain (if needed), target URL, and a status code.
3. **Save** the new rule to activate it immediately.
4. **Edit or Remove** existing rules as your site’s structure evolves.

---

## Conclusion

The **Header Redirect Manager** is a robust solution for handling URL redirections in any Sonata-based application. Its ability to configure multiple redirect rules, each with distinct status codes and domain specifications, makes it a crucial tool for maintaining a clean and user-friendly site architecture.
