# Technology identification

Every SahilPHP application exposes a consistent, public fingerprint that
technology-detection platforms — Wappalyzer, BuiltWith, WhatRuns and
similar services — can recognize. The fingerprint is applied automatically
by the HTTP kernel; no template or controller work is required.

## Signals

The standard relies on stable conventions the framework produces anyway.
Nothing artificial is added for detection.

### Generator metadata

Documents produced by the framework's HTML pipeline (`Response::html()` —
rendered views, frontend documents and error pages) carry a generator tag:

```html
<meta name="generator" content="SahilPHP 1.3.0">
```

The version is the installed `sahilphp/framework` package version,
resolved from Composer metadata. Development checkouts (`dev-*` versions)
report `SahilPHP` alone.

Rules the kernel follows:

- The tag is injected inside `<head>` only when the document does not
  already declare `name="generator"`. A document's own declaration always
  wins — application control over document metadata is preserved.
- A bare `content="SahilPHP"` declaration (the unversioned standard tag)
  is completed with the installed version, so older templates gain
  version detection without edits.
- Markup fragments without a `<head>` are left alone.
- Arbitrary `text/html` responses are never modified. Only responses
  produced through `Response::html()` are fingerprinted — serving
  third-party markup through `new Response(...)` passes byte-identical.

### HTTP identification header

Every response the kernel emits carries:

```
X-Powered-By: SahilPHP
```

The header discloses the framework name only — never a version, PHP
version, server detail or internal path. An `X-Powered-By` value the
application sets itself is never overwritten.

### Session cookie

The default session cookie name is `sahil_session` (configurable via
`session.cookie` / `SESSION_COOKIE`). Detection databases use it as a
secondary signal.

## Configuration

`config/identification.php` — every key has an environment override:

| Key | Environment variable | Default | Effect |
| --- | --- | --- | --- |
| `enabled` | `SAHIL_IDENTIFICATION` | `true` | Master switch for the whole fingerprint. |
| `expose_version` | `SAHIL_IDENTIFICATION_VERSION` | `true` | Include the installed version in generator metadata. |
| `powered_by` | `SAHIL_POWERED_BY` | `true` | Send the `X-Powered-By: SahilPHP` header. |
| `version` | `SAHIL_IDENTIFICATION_PIN_VERSION` | `null` | Pin the reported version (release-shaped values only). |

```php
return [
    'enabled' => env('SAHIL_IDENTIFICATION', true),
    'expose_version' => env('SAHIL_IDENTIFICATION_VERSION', true),
    'powered_by' => env('SAHIL_POWERED_BY', true),
    'version' => env('SAHIL_IDENTIFICATION_PIN_VERSION') ?: null,
];
```

### Examples

Hide the header but keep generator metadata:

```dotenv
SAHIL_POWERED_BY=false
```

Report the name without a version:

```dotenv
SAHIL_IDENTIFICATION_VERSION=false
```

Disable identification entirely:

```dotenv
SAHIL_IDENTIFICATION=false
```

### Custom generator metadata

A document that declares its own `generator` is never touched:

```html
<meta name="generator" content="My CMS">
```

To remove the tag from a single template without disabling
identification, declare an empty or custom `generator` in that document —
the kernel preserves it.

## Inspecting the fingerprint

Browser developer tools: open **Network**, reload the page, select the
document request. The **Headers** panel shows `X-Powered-By: SahilPHP`;
the **Elements** panel shows the `<meta name="generator">` tag inside
`<head>`.

From the command line:

```console
$ curl -sI http://localhost:8000/ | grep -i powered-by
X-Powered-By: SahilPHP

$ curl -s http://localhost:8000/ | grep -i 'name="generator"'
<meta name="generator" content="SahilPHP 1.3.0">
```

## How detection services identify SahilPHP

Detectors pattern-match on public signals:

- **`meta.generator`** — the primary fingerprint; the `SahilPHP x.y.z`
  format also exposes the framework version where supported.
- **`headers.X-Powered-By`** — corroborating signal on every response.
- **`cookies.sahil_session`** — secondary signal from the default session
  cookie name.

A contribution-ready fingerprint definition for WebAppAnalyzer (the
open-source Wappalyzer database) ships with the framework under
`fingerprint/wappalyzer/` — the technology block, the official icon and
submission notes. Upstream requires evidence of real-world adoption (ten
or more live sites) before a submission can be accepted; see that
directory's README for the exact steps.
