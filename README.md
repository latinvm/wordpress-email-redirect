# Email Redirect WordPress Plugin

## Description
This WordPress plugin redirects users to URLs based on their email domain. Users enter their email address in a form, and the plugin automatically opens the appropriate URL based on domain mappings you configure.

## Features
- Widget-based email input form
- Shortcode support for inline placement in posts and pages
- Admin settings page for easy domain-to-URL configuration
- Subdomain matching with priority over main domain matches
- Popup window with fallback link if popups are blocked
- AJAX-based submission for smooth user experience
- Input validation for email addresses

## Installation

1. Create a folder named `email-redirect` in your WordPress `wp-content/plugins/` directory
2. Copy all plugin files into this folder:
   - email-redirect.php (main plugin file)
   - widget.php
   - script.js
   - style.css
   - admin-script.js
   - admin-style.css
   - languages/ (folder with translation files)

3. Activate the plugin through the WordPress admin panel (Plugins > Installed Plugins)

### Compiling Translations (optional)

If you modify the `.po` translation files, regenerate the compiled `.mo` files:

```bash
cd wp-content/plugins/email-redirect/languages
msgfmt -o email-redirect-nl_NL.mo email-redirect-nl_NL.po
```

## Configuration

### Setting up Domain Mappings

1. Go to Settings > Email Redirect in your WordPress admin
2. Add domain-to-URL mappings:
   - Enter the domain (without protocol), e.g., `company.com` or `mail.company.com`
   - Enter the complete redirect URL, e.g., `https://example.com/page`
3. Click "Add Mapping" to add more rows
4. Click "Remove" to delete unwanted mappings
5. Click "Save Changes" when done

### Domain Matching Priority

The plugin uses the following matching logic:
1. First attempts exact domain match (including subdomain if present)
2. If no exact match, extracts and checks the main domain + TLD
3. Subdomain configurations always take precedence

Example:
- If you configure both `mail.company.com` and `company.com`
- Email `user@mail.company.com` will match `mail.company.com` first
- Email `user@company.com` will match `company.com`
- Email `user@support.company.com` will match `company.com` (main domain fallback)

## Usage

### Adding the Widget

1. Go to Appearance > Widgets in WordPress admin
2. Find "Email Redirect Form" widget
3. Drag it to your desired widget area (sidebar, footer, etc.)
4. Optionally set a custom title
5. Save the widget

### Using the Shortcode

You can also embed the form directly in any post, page, or shortcode-enabled area:

```
[email_redirect_form]
```

With a custom title:

```
[email_redirect_form title="Enter Your Email"]
```

### User Experience

When a user submits their email:
1. The plugin validates the email format
2. Extracts the domain from the email
3. Finds the matching redirect URL from your configuration
4. Opens the URL in a new browser window
5. Shows a confirmation message with a clickable link
6. If the domain is not found, shows an error message

## File Structure

```
email-redirect/
├── email-redirect.php  # Main plugin file
├── widget.php          # Widget class
├── script.js           # Frontend JavaScript
├── style.css           # Widget styling
├── admin-script.js     # Admin JavaScript
├── admin-style.css     # Admin styling
└── languages/          # Translation files
    ├── email-redirect.pot        # Translation template
    ├── email-redirect-nl_NL.po   # Dutch translation source
    └── email-redirect-nl_NL.mo   # Dutch translation (compiled)
```

## Translations

The plugin includes Dutch (nl_NL) translations and is fully translatable.

### Required Tools

To compile translations, you need `gettext` which provides the `msgfmt` command:

**Ubuntu/Debian:**

```bash
sudo apt install gettext
```

**macOS (Homebrew):**

```bash
brew install gettext
```

**Windows:**
Download from [GNU gettext](https://mlocati.github.io/articles/gettext-iconv-windows.html) or use [Poedit](https://poedit.net/).

### Adding a New Translation

1. Copy the template file to create a new translation:

   ```bash
   cp email-redirect.pot email-redirect-de_DE.po
   ```

2. Edit the `.po` file with a text editor or Poedit, translating each `msgstr` entry

3. Compile the `.po` file to `.mo`:

   ```bash
   msgfmt -o email-redirect-de_DE.mo email-redirect-de_DE.po
   ```

### Updating Existing Translations

1. Edit the `.po` file (e.g., `email-redirect-nl_NL.po`)
2. Recompile to `.mo`:

   ```bash
   msgfmt -o email-redirect-nl_NL.mo email-redirect-nl_NL.po
   ```

### Using Poedit (GUI Alternative)

[Poedit](https://poedit.net/) provides a graphical interface for editing translations and automatically generates the `.mo` file when you save.

## Technical Details

- Uses WordPress AJAX for form processing
- Nonce verification for security
- Sanitization and validation of all inputs
- Responsive form design
- Compatible with standard WordPress themes

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- jQuery (included with WordPress)

## Support

For issues or questions, contact your plugin administrator.

## License

This plugin is licensed under the [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html) license.
