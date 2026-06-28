# JoomSpy
![PHP](https://img.shields.io/badge/PHP-8.0+-blue?style=for-the-badge&logo=php)
![Version](https://img.shields.io/badge/Version-2.0-red?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Active-success?style=for-the-badge)
![CMS](https://img.shields.io/badge/Target-Joomla-orange?style=for-the-badge&logo=joomla)
![Security](https://img.shields.io/badge/Focus-Passive%20Recon-black?style=for-the-badge)
![License](https://img.shields.io/badge/License-Educational-purple?style=for-the-badge)
![Website](https://img.shields.io/badge/Official-6ickZone.site-darkred?style=for-the-badge&logo=firefox)
**JoomSpy** is a lightweight, web-based reconnaissance tool built for **Passive Attack Surface Mapping** on Joomla CMS targets. It helps security researchers identify exposed endpoints, backup files, leaked configurations, and installed third-party extensions by analyzing HTTP response codes.

This allows faster visibility into a Joomla asset’s external exposure before moving into deeper security analysis.

---

## Key Features

- **Asynchronous Processing**  
  Built using the native `fetch` API with `async/await` for smooth, fast, and non-blocking scan execution.

- **Targeted Audit Database**  
  Includes Joomla-specific audit vectors covering:
  - Core API paths
  - Critical data leak locations
  - Backup files (`.env`, `.bak`, `.old`)
  - Popular components (`com_jce`, `com_k2`, `com_media`, etc.)

- **Dynamic Response Categorization**  
  Automatically classifies:
  - `200` → Accessible
  - `403` → Restricted
  - `301/302` → Redirected
  - `404` → Not Found

- **Live Interactive Filtering**  
  Real-time search filtering for quick result analysis without page reload.

- **Direct Verification Links**  
  Clickable status codes for instant manual validation in a new browser tab.

---

## Repository Structure

```bash
JoomSpy/
├── index.php    # Main frontend interface
└── scan.php     # Backend proxy scanner (PHP cURL)
```

### File Overview

| File | Description |
|------|-------------|
| `index.php` | Frontend UI built with HTML, CSS, and vanilla JavaScript |
| `scan.php` | Handles backend HTTP requests using PHP cURL |

---

## Requirements

Make sure your environment supports:

- **PHP 8.0+**
- **cURL Extension** enabled

Verify cURL support:

```bash
php -m | grep curl
```

---

## Installation

Clone this repository:

```bash
git clone https://github.com/username/JoomSpy.git
cd JoomSpy
```

Run using PHP built-in server:

```bash
php -S localhost:8000
```

Open in browser:

```text
http://localhost:8000
```

---

## Usage

1. Enter your target Joomla URL

Example:

```text
https://target-joomla.com
```

2. Click **START**

3. JoomSpy will:
   - Probe predefined Joomla vectors
   - Analyze HTTP headers
   - Categorize responses

4. Use **Filter rows...** to search results instantly.

Example filters:

```text
200
Data Leak
com_jce
403
```

5. Click **STOP** anytime to halt the scan.

---

## Audit Coverage

### Core Paths

- `/administrator/`
- `/api/index.php`
- `/templates/`

### Backup Files

- `.env`
- `configuration.php.bak`
- `configuration.php.old`

### Common Extensions

- `com_jce`
- `com_k2`
- `com_media`
- `com_content`
- `com_users`

---

## Important Notes

- This tool performs **passive enumeration only**
- No exploitation functionality included
- Results depend on target server behavior
- Some WAFs may alter or block responses

---

## Disclaimer

For educational and authorized security research purposes only.

---

## 6ickZone

https://6ickzone.site
---

## Maintained For

Built for the security research community, bug hunters, and defensive analysts.

**Maintained with coffee and curiosity.**
