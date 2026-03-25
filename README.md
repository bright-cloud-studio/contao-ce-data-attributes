# contao-ce-data-attributes

Allows adding custom `data-*` attributes to content element wrappers in Contao 5.

## Requirements

- PHP 8.1+
- Contao 5.3+

## What it does

Adds a **Data attributes** legend with a key/value wizard field to common content
element palettes (`text`, `html`, `image`, `list`, `table`, `accordionStart`).

Values entered by the editor are automatically rendered as `data-*` attributes on
the outer wrapper `<div>` of the content element. Keys are auto-prefixed with
`data-` if not already present.

## Installation

```bash
composer require bright-cloud-studio/contao-ce-data-attributes
```

Then run the Contao install tool to apply the database migration for the new
`customDataAttributes` column on `tl_content`.
