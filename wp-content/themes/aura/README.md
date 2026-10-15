# Aura theme

Aura is an Astra child theme for Aura Spa. Astra remains the parent theme; Elementor renders page content and supplies the site header and footer through its Theme Builder locations.

The theme keeps Astra's document and content hooks but omits Astra's default header and footer markup. Its header and footer templates render Elementor Theme Builder locations when assigned. `page.php` and `front-page.php` output page content only, with no title or sidebar. Empty new pages therefore start with a blank content area.

The original standalone Aura theme files have been kept in `legacy/` and are not loaded by this child theme.

## Requirements

- Astra must remain installed as the parent theme.
- Elementor must remain active. Elementor Pro is needed for global Theme Builder header and footer templates.

Activate **Aura** from **Appearance → Themes**. Select the Home2 page under **Settings → Reading** if it is not already the site's front page.
