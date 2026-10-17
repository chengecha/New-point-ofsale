<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Email layout library
 *
 * Wraps rendered view content inside the responsive master email layout
 * (views/emails/master_layout.html) that uses {{VARIABLE}} placeholders.
 *
 * Usage in a controller:
 *   $content = $this->load->view('sales/invoice_email', $data, TRUE);
 *   $html = $this->email_layout->render($content, [
 *       '{{GREETING}}'              => 'Please find your invoice below.',
 *       '{{CALL_TO_ACTION_BUTTON}}' => 'View Invoice',
 *       '{{CTA_LINK}}'              => site_url('invoices/' . $invoice_id),
 *   ]);
 */
class Email_layout
{
	private $CI;
	private $template_path;
	private $template_cache;

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->template_path = APPPATH . 'views/emails/master_layout.html';
	}

	/**
	 * Override the master layout template file path.
	 *
	 * @param string $path Absolute or APPPATH-relative path to an HTML template
	 */
	public function set_template($path)
	{
		$this->template_path = $path;
		$this->template_cache = null;
	}

	/**
	 * Load the template file (cached for the duration of the request).
	 *
	 * @return string
	 */
	private function _get_template()
	{
		if ($this->template_cache === null) {
			if (!file_exists($this->template_path)) {
				show_error('Email master layout template not found: ' . $this->template_path);
			}
			$this->template_cache = file_get_contents($this->template_path);
		}
		return $this->template_cache;
	}

	/**
	 * Strip HTML document-structure tags (DOCTYPE, html, head, body)
	 * so that a complete HTML document returned by $this->load->view()
	 * can be safely embedded inside the {{MESSAGE_BODY}} slot.
	 *
	 * @param string $html
	 * @return string
	 */
	private function _strip_document_structure($html)
	{
		$html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
		$html = preg_replace('/<html[^>]*>/i', '', $html);
		$html = preg_replace('/<\/html>/i', '', $html);
		$html = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $html);
		$html = preg_replace('/<body[^>]*>/i', '', $html);
		$html = preg_replace('/<\/body>/i', '', $html);

		return trim($html);
	}

	/**
	 * Build default replacement values from CI configuration items.
	 *
	 * @return array  Map of {{VARIABLE}} => value
	 */
	private function _get_config_defaults()
	{
		$company = $this->CI->config->item('company');
		$email   = $this->CI->config->item('email');
		$phone   = $this->CI->config->item('phone');
		$address = $this->CI->config->item('address');
		$logo    = $this->CI->config->item('company_logo');

		$logo_url = !empty($logo)
			? $this->CI->config->item('base_url') . 'uploads/' . $logo
			: 'https://via.placeholder.com/150x50?text=Your+Logo';

		return [
			'{{LOGO_URL}}'           => htmlspecialchars($logo_url, ENT_QUOTES),
			'{{COMPANY_NAME}}'       => htmlspecialchars($company  ?: 'Company Name', ENT_QUOTES),
			'{{SUPPORT_EMAIL}}'      => htmlspecialchars($email    ?: 'support@example.com', ENT_QUOTES),
			'{{SUPPORT_PHONE}}'      => htmlspecialchars($phone    ?: '', ENT_QUOTES),
			'{{COMPANY_ADDRESS}}'    => $address
				? nl2br(htmlspecialchars($address, ENT_QUOTES), false)
				: '',
			'{{YEAR}}'               => date('Y'),
			'{{SOCIAL_FACEBOOK}}'    => htmlspecialchars($this->CI->config->item('social_facebook') ?: '#', ENT_QUOTES),
			'{{SOCIAL_TWITTER}}'     => htmlspecialchars($this->CI->config->item('social_twitter') ?: '#', ENT_QUOTES),
			'{{SOCIAL_INSTAGRAM}}'   => htmlspecialchars($this->CI->config->item('social_instagram') ?: '#', ENT_QUOTES),
			'{{SOCIAL_TIKTOK}}'      => htmlspecialchars($this->CI->config->item('social_tiktok') ?: '#', ENT_QUOTES),
			'{{SOCIAL_LINKEDIN}}'    => htmlspecialchars($this->CI->config->item('social_linkedin') ?: '#', ENT_QUOTES),
			'{{UNSUBSCRIBE_LINK}}'   => '#',
			'{{PREFERENCES_LINK}}'   => '#',
		];
	}

	/**
	 * Render the master email layout.
	 *
	 * @param string $content  HTML to inject into {{MESSAGE_BODY}}
	 * @param array  $data     Additional {{VARIABLE}} => value pairs
	 * @param bool   $strip    Whether to strip HTML document tags from $content
	 * @return string          Complete HTML email ready for mailtype = html
	 */
	public function render($content = '', array $data = [], $strip = TRUE)
	{
		$template = $this->_get_template();

		if ($strip && $content !== '') {
			$content = $this->_strip_document_structure($content);
		}

		// Start with company/config defaults
		$replacements = $this->_get_config_defaults();

		// Apply caller-supplied overrides
		if (!empty($data)) {
			$replacements = array_merge($replacements, $data);
		}

		// Content slot — allow explicit override via $data
		if (!array_key_exists('{{MESSAGE_BODY}}', $replacements)) {
			$replacements['{{MESSAGE_BODY}}'] = $content;
		}

		// Sensible fallbacks for the required dynamic slots
		$defaults = [
			'{{GREETING}}'              => 'Hello,',
			'{{CALL_TO_ACTION_BUTTON}}' => 'View Details',
			'{{CTA_LINK}}'              => $this->CI->config->item('website') ?: site_url(),
			'{{PREHEADER_TEXT}}'        => '',
		];
		foreach ($defaults as $key => $value) {
			if (!array_key_exists($key, $replacements)) {
				$replacements[$key] = $value;
			}
		}

		return strtr($template, $replacements);
	}
}
