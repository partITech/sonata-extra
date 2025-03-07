# Activity Log

Monitor and log all activities within your Sonata Admin interface using the **Activity Log** feature of the Sonata Extra Bundle. This provides a detailed audit of changes, enhancing transparency, accountability, and the ability to undo certain modifications.

---

## Overview

The **Activity Log** captures:

- **Action Type** (create, update, delete, etc.)
- **Affected Resource** (which entity or record was changed)
- **Activity Description** (details of the modification)
- **User Identification** (who performed the action)
- **Detailed View** (in-depth look at each change)
- **Undo Functionality** (reverse certain modifications when supported)

---

## Features

1. **Action Type Display**  
   Clearly shows what type of action was performed (create, update, delete).

2. **Resource Tracking**  
   Identifies the specific entity affected by the action.

3. **Action Description**  
   Provides a concise explanation or summary of the operation.

4. **User Identification**  
   Records who initiated or performed the action.

5. **Detailed View**  
   Offers a granular view of the changes, highlighting modified fields or properties.

6. **Undo Functionality**  
   Allows reverting certain modifications where possible (e.g., rolling back a create or update).

---

## List View  
  ![Activity_log_index.png](./doc-sonata-extra-images/Activity_log_index.png)

## Detail View
  ![Activity_log_detail.png](./doc-sonata-extra-images/Activity_log_detail.png)

---

## Enabling the Activity Log

To activate the Activity Log, register the **DoctrineActivityListener** service in your `services.yaml`:

```yaml
sonata-extra.doctrine_activity_listener:
    class: Partitech\SonataExtra\EventListener\DoctrineActivityListener
    tags:
        - { name: doctrine.event_listener, event: prePersist }
        - { name: doctrine.event_listener, event: preUpdate }
        - { name: doctrine.event_listener, event: preRemove }
        - { name: doctrine.event_listener, event: onFlush }
        - { name: kernel.event_listener, event: sonata.admin.event.batch_action.pre_batch_action, method: onPreBatchAction }
```

> [!NOTE]
> This listener hooks into **Doctrine** and **Sonata** events, allowing the bundle to log entity lifecycle actions and batch operations in Sonata Admin.

---

## Implementation Details

- **DoctrineActivityListener**  
  Listens for `prePersist`, `preUpdate`, `preRemove`, and `onFlush` events to capture entity changes.
- **onPreBatchAction**  
  Logs batch actions triggered from the Sonata Admin interface (e.g., batch deletes or updates).

---

## Usage

Once the listener is enabled:

1. **Automatic Logging**  
   All relevant actions (create, update, remove, batch operations) are automatically captured.

2. **Admin Interface**  
   View or search these logs in your Sonata Admin panel. Each entry details what changed, when, and by whom.

3. **Undo**  
   If the bundle supports undo for a particular action type, you can reverse changes directly from the admin interface.

---

## Conclusion

The **Activity Log** feature is a robust auditing tool for your Sonata-powered application. By providing clear, detailed records of every significant admin action, it fosters both transparency and accountability. The optional **undo functionality** further enhances control, allowing quick reversals of unintended changes.
