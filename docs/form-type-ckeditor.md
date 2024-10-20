# Sonata Extra Bundle:  Media Library for Ckeditor



## Introduction

Proper configuration of CKEditor is essential for its optimal functionality. This guide provides the steps to install a valid version of CKEditor and configure it to use the Media library in a Sonata project.

## Installation

### Addressing Invalid LTS License Key Error

If you encounter the following error:

```shell

[CKEDITOR] For more information about this error go to https://ckeditor.com/docs/ckeditor4/latest/guide/dev_errors.html#invalid-lts-license-key

```

Install the specified version of CKEditor:


```shell

bin/console ckeditor:install --tag=4.19.0
bin/console asset:install

```

### Configuration for Media Library
To integrate the Media library with CKEditor, you need to specify `filebrowserBrowseRoute` and `filebrowserImageBrowseRoute`. 
Use the `sonata_extra_ckeditor_media_browser` route for this purpose.

Here is a sample configuration:


```shell

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