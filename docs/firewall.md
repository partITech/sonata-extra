# Firewall

The **Sonata Extra Bundle** provides advanced capabilities for managing firewall rules within your Sonata Admin environment. This system lets you define and apply filtering rules based on **stop words**, **IP addresses**, and **User Agents**, enabling a more secure and controlled experience for your application.

> [!NOTE]
> Once activated, the firewall rules run on every incoming request, providing dynamic and flexible filtering.

---

## Features

- **Firewall Rule Management**  
  Create, update, and remove firewall rules directly within Sonata Admin.

- **Multiple Criteria Support**  
  Filter requests by **stop words**, **IP addresses**, or **User Agents**.

- **Dynamic Rule Application**  
  Inspect different parts of the request (GET, POST, Headers) to apply the right rules.

- **Cache Integration**  
  Rules are cached for performance. The cache resets automatically upon rule updates.

- **Recursive Data Analysis**  
  Complex request structures (e.g., multi-level arrays) are traversed and validated against firewall criteria.

---

## Activation

### 1. Register the Firewall Listener

Add the firewall listener to your `services.yaml`:

```yaml
sonata-extra.Firewall_Listener:
class: Partitech\SonataExtra\EventListener\FirewallListener
tags:
- { name: kernel.event_listener, event: kernel.request, method: onKernelRequest }
```

### 2. Add Admin Interfaces

Enable the firewall admin UIs in your `sonata_admin.yaml`:

```yaml
firewall:
    icon: fa fa-cogs
    label: Firewall
    items:
        - Partitech\SonataExtra\Admin\SecFirewallRuleAdmin
        - Partitech\SonataExtra\Admin\SecStopWordAdmin
        - Partitech\SonataExtra\Admin\SecIpRuleAdmin
```

---

## Creating Firewall Rules

From within Sonata Admin, you can create and manage the following rule types:

- **Stop Word**  
  Block requests containing specific keywords (e.g., spam terms, suspicious strings).

- **IP**  
  Deny traffic from one or more IP addresses (individual addresses or subnet ranges).

- **User Agent**  
  Prevent requests originating from known malicious or unwanted user agents.

---

## Configuring Rules

Each rule includes:

- **type**  
  The rule’s classification (e.g., `stop_word`, `ip`, `user_agent`).

- **source**  
  Specifies which part of the request to inspect (e.g., `GET`, `POST`, `HEADER`).

- **parameters**  
  One or more values to check against (e.g., a list of banned words, IPs, or user agents).

- **matchMode**  
  Determines whether you look for an **exact** match (`equal`) or a **partial** match (`contain`).

> [!NOTE]
> Rules can be combined to form a more thorough filtering strategy, addressing multiple sources and match modes.

---

## Cache Management

Firewall rules are **cached** to boost performance. The cache automatically **resets** whenever you:

- Create a new rule
- Update an existing rule
- Delete an existing rule

This ensures changes are reflected immediately in the filtering process, keeping your site secure and responsive.

---

## Conclusion

By leveraging the **Firewall** features in the Sonata Extra Bundle, you can swiftly block unwanted traffic and malicious requests. Its easy-to-use admin interface, combined with dynamic rule application and caching, makes it a robust solution for improving the security po
