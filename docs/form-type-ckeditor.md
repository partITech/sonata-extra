# Media Library for CKEditor

Enhance CKEditor with a **media library** directly within your Sonata project. This guide outlines the steps to install a compatible CKEditor version and configure it to browse media from Sonata’s media library.

> [!NOTE]
> Make sure you use the correct CKEditor version to avoid license key warnings or incompatibilities.

---

## Introduction

To ensure **CKEditor** functions properly, you must install a valid version and configure it to interact with the Sonata media library. By linking CKEditor’s file browser routes to the **Sonata Extra** media library routes, you can seamlessly insert and manage media within your content.

---

## Installation

### Addressing the "Invalid LTS License Key" Error

If you see an error like:

```shell
[CKEDITOR] For more information about this error go to https://ckeditor.com/docs/ckeditor4/latest/guide/dev_errors.html#invalid-lts-license-key
```

Install CKEditor **4.19.0**:

```shell
bin/console ckeditor:install --tag=4.19.0
bin/console asset:install
```

---

## Configuration for Media Library

To integrate the media library into **CKEditor**, configure the `filebrowserBrowseRoute` and `filebrowserImageBrowseRoute` to use the **`sonata_extra_ckeditor_media_browser`** route.

Below is an example `fos_ck_editor` configuration:

```yaml
fos_ck_editor:
  default_config: default
  base_path: "build/ckeditor"
  js_path:   "build/ckeditor/ckeditor.js"
  configs:
    default:
      language: fr
      # default toolbar plus Format button
      toolbar:
        - [Bold, Italic, Underline, -, Cut, Copy, Paste,
           PasteText, PasteFromWord, -, Undo, Redo, -,
           NumberedList, BulletedList, -, Outdent, Indent, -,
           Blockquote, -, Image, Link, Unlink, Table]
        - [Format, Maximize, Source]

      enterMode: CKEDITOR.ENTER_BR
      filebrowserBrowseRoute: sonata_extra_ckeditor_media_browser
      filebrowserImageBrowseRoute: sonata_extra_ckeditor_media_browser
```

> [!NOTE]
> Adjust the **language**, **toolbar**, or **enterMode** settings as needed. Make sure your `base_path` and `js_path` match the directory where CKEditor is installed.

---

## Conclusion

By installing the correct CKEditor version and pointing the file browser routes to **`sonata_extra_ckeditor_media_browser`**, you can easily access Sonata’s media library from CKEditor. This configuration streamlines content creation, letting users insert and manage images, documents, and other media directly in the editor.
