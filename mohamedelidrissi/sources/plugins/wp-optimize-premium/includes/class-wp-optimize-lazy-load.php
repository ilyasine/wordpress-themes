<?php

if (!defined('WPO_VERSION')) die('No direct access allowed');

class WP_Optimize_Lazy_Load {

	/**
	 * Lazy load options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * WP_Optimize_Lazy_Load constructor.
	 */
	public function __construct() {
		add_action('add_meta_boxes', array($this, 'add_lazyload_control_metabox'));

		if (is_admin()) return;

		$default_options = array(
			'images' => false,
			'iframes' => false,
			'backgrounds' => false,
			'skip_classes' => '',
		);

		$this->options = wp_parse_args(WP_Optimize()->get_options()->get_option('lazyload'), $default_options);

		$skip_classes = array_map('trim', explode(',', $this->options['skip_classes']));
		$skip_classes[] = 'no-lazy';

		$this->options['skip_classes'] = $skip_classes;
		
		$hook_these = apply_filters('wp_optimize_lazy_load_hook_these', array('get_avatar', 'the_content', 'widget_text', 'get_image_tag', 'post_thumbnail_html', 'woocommerce_product_get_image', 'woocommerce_single_product_image_thumbnail_html'));
		
		$hook_priority = apply_filters('wp_optimize_lazy_load_hook_priority', PHP_INT_MAX);
		
		foreach ($hook_these as $hook) {
			add_filter($hook, array($this, 'process_content'), $hook_priority);
		}

	}

	/**
	 * Add lazy-load metabox to admin.
	 */
	public function add_lazyload_control_metabox() {
		add_meta_box('wpo-lazyload-metabox', '<span title="'.__('by WP-Optimize', 'wp-optimize').'">'.__('Lazy-load', 'wp-optimize').'</span>', array($this, 'render_lazyload_control_metabox'), get_post_types(array('public' => true)), 'side');
	}

	/**
	 * Render lazy-load metabox.
	 */
	public function render_lazyload_control_metabox($post) {
		$post_id = $post->ID;
		$meta_key = '_wpo_disable_lazyload';
		$disable_lazyload = get_post_meta($post_id, $meta_key, true);

		$post_type_obj = get_post_type_object(get_post_type($post_id));

		$extract = array(
			'disable_lazyload' => $disable_lazyload,
			'post_id' => $post_id,
			'post_type' => strtolower($post_type_obj->labels->singular_name),
		);

		WP_Optimize()->include_template('images/admin-metabox-lazyload-control.php', false, $extract);
	}

	/**
	 * Returns true if Lazy loading enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$post_id = get_queried_object_id();

		// if disabled on the post editr page then return false.
		if ($post_id && get_post_meta($post_id, '_wpo_disable_lazyload', true)) {
			return false;
		}

		return $this->options['images'] || $this->options['iframes'] || $this->options['backgrounds'];
	}

	/**
	 * Filter the content and replace corresponding tags for lazy loading.
	 *
	 * @param string $content
	 * @return string
	 */
	public function process_content($content) {

		if (!$this->is_enabled()) return $content;

		if ($this->options['images']) {
			$content = preg_replace_callback('/\<picture.+\/picture\>/Uis', array($this, 'update_picture_callback'), $content);
			$content = preg_replace_callback('/\<img(.+)\>/Uis', array($this, 'update_images_callback'), $content);
		}

		if ($this->options['backgrounds']) {
			$regexp = '/\<([a-z]+)\s+([^\>]*style=[\'|\"][^\>]*(background-image|background):[^\>;]*url\(([^\>]+)\)[^\>;]*[^\>\'\"]*[\'|\"][^\>]*)\>/i';
			$content = preg_replace_callback($regexp, array($this, 'update_background_callback'), $content);
		}

		if ($this->options['iframes']) {
			$content = preg_replace_callback('/\<iframe(.+)\>/Uis', array($this, 'update_iframes_callback'), $content);
		}

		return $content;
	}

	/**
	 * Update PICTURE tag.
	 *
	 * @param array $picture matched element from preg_replace_callback.
	 * @return string
	 */
	public function update_picture_callback($picture) {
		$picture = $picture[0];

		preg_match('/<picture(.*)\>/Uis', $picture, $picture_tag);
		$picture_tag = $picture_tag[0];

		$attributes = $this->parse_attributes($picture_tag);

		// don't use lazy load for images with no-lazy class.
		if (array_key_exists('class', $attributes) && $this->has_class($attributes['class'], $this->options['skip_classes'])) return $picture;

		$attributes['class'] = array_key_exists('class', $attributes) ? $attributes['class'] . ($this->has_class($attributes['class'], 'lazyload') ? '' : ' lazyload') : 'lazyload';

		// update inner img, source tags.
		$picture = preg_replace_callback('/\<(img|source)(.+)\>/Uis', array($this, 'update_picture_item_callback'), $picture);

		$picture = preg_replace('/<picture(.*)\>/Uis', '<picture '.$this->build_attributes($attributes).'>', $picture);

		return $picture;
	}

	/**
	 * Update PICTURE inner tags.
	 *
	 * @param array $picture_item matched element from preg_replace_callback.
	 * @return string
	 */
	public function update_picture_item_callback($picture_item) {
		$attributes = $this->parse_attributes($picture_item[2]);

		return $this->build_lazy_load_tag($picture_item[1], $attributes);
	}

	/**
	 * Update image tag to use lazy load.
	 *
	 * @param array $image
	 * @return string
	 */
	public function update_images_callback($image) {
		$image_tag = $image[1];
		$attributes = $this->parse_attributes($image_tag);

		// don't use lazy load for images with no-lazy class.
		if (array_key_exists('class', $attributes) && $this->has_class($attributes['class'], $this->options['skip_classes'])) return $image[0];

		// don't change anything if data-src already set.
		if (array_key_exists('data-src', $attributes)) return $image[0];

		$attributes['class'] = array_key_exists('class', $attributes) ? $attributes['class'] . ($this->has_class($attributes['class'], 'lazyload') ? '' : ' lazyload') : 'lazyload';

		return $this->build_lazy_load_tag('img', $attributes);
	}

	/**
	 * Update background inline styles callback.
	 *
	 * @param array $match [1] - tag name, [2] - tag attribiutes
	 * @return string
	 */
	public function update_background_callback($match) {
		$original = $match[0];
		$tag = $match[1];

		$tag_attributes = $match[2];

		$attributes = $this->parse_attributes($tag_attributes);
		// don't use lazy load for images with no-lazy class.

		if (array_key_exists('class', $attributes) && $this->has_class($attributes['class'], $this->options['skip_classes'])) return $original;

		// split style attribute.
		$style = $attributes['style'];
		$style_items = explode(';', $style);

		// check style properties for background and background-image items.
		foreach ($style_items as &$item) {
			$item = trim($item);
			$regexp = '/^([^:]+):[^;]*/i';

			if (!preg_match($regexp, $item, $match)) continue;
			$property = strtolower($match[1]);

			if (!in_array($property, array('background', 'background-image'))) continue;

			// get all urls in property.
			if (!preg_match_all('/url\((.+)\)/Ui', $item, $match)) continue;

			$original_urls = $match[1];

			foreach ($original_urls as &$url) {
				$url = trim($url, '\'"');
			}

			// add data-* attribute with original images urls.
			$attributes['data-'.$property] = join(';', $original_urls);

			// replace original urls with the blank image.
			$replace = 'url('.includes_url('/images/blank.gif').')';
			$replaced = preg_replace('/url\((.+)\)/Ui', $replace, $item);
			$item = $replaced;
		}

		// update style attribute.
		$attributes['style'] = implode(';', $style_items);

		// add lazyload class to the element.
		$attributes['class'] = array_key_exists('class', $attributes) ? $attributes['class'] . ($this->has_class($attributes['class'], 'lazyload') ? '' : ' lazyload') : 'lazyload';

		return '<'.$tag.' '.$this->build_attributes($attributes).'>';
	}

	/**
	 * Update IFRAME tag.
	 *
	 * @param array $iframe matched element from preg_replace_callback.
	 * @return string
	 */
	public function update_iframes_callback($iframe) {
		$iframe_tag = $iframe[1];

		// don't use lazy load for Gravity Form ajax iframe.
		if (strpos($iframe[0], 'gform_ajax_frame')) return $iframe[0];

		$attributes = $this->parse_attributes($iframe_tag);

		// don't use lazy load for iframes with no-lazy class.
		if (array_key_exists('class', $attributes) && $this->has_class($attributes['class'], $this->options['skip_classes'])) return $iframe[0];

		$attributes['class'] = array_key_exists('class', $attributes) ? $attributes['class'] . ($this->has_class($attributes['class'], 'lazyload') ? '' : ' lazyload') : 'lazyload';

		return $this->build_lazy_load_tag('iframe', $attributes);
	}

	/**
	 * Build tag for lazy loading.
	 *
	 * @param string $tag
	 * @param array  $attributes
	 * @return string
	 */
	public function build_lazy_load_tag($tag, $attributes) {
		if (array_key_exists('src', $attributes)) {
			$attributes['data-src'] = $attributes['src'];
			if ('iframe' == $tag) {
				$attributes['src'] = 'about:blank';
			} else {
				$attributes['src'] = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
			}
		}

		if (array_key_exists('srcset', $attributes)) {
			$attributes['data-srcset'] = $attributes['srcset'];
			unset($attributes['srcset']);
		}

		return '<'.$tag.' '.$this->build_attributes($attributes).'>';
	}

	/**
	 * Returns true if we can use lazy load for $source.
	 *
	 * @param string $source
	 * @return bool
	 */
	public function can_lazy_load($source) {

		// Remove Lazy load for AMP version of a post with the AMP for WordPress plugin from Auttomatic.
		if (defined('AMP_QUERY_VAR') && function_exists('is_amp_endpoint') && is_amp_endpoint()) {
			return false;
		}

		$lazy_load_filters = array(
			'/wpcf7_captcha/',
			'/timthumb\.php\?src/',
		);

		$can_lazy_load = true;

		foreach ($lazy_load_filters as $filter) {
			if (preg_match($filter, $source)) return $can_lazy_load = false;
		}

		return apply_filters('wpo_can_lazy_load', $source, $can_lazy_load);
	}

	/**
	 * Parse tag attributes and return array with them.
	 *
	 * @param string $tag
	 * @return array
	 */
	public function parse_attributes($tag) {
		$attributes = array();

		$_attributes = wp_kses_hair($tag, wp_allowed_protocols());

		if (empty($_attributes)) return $attributes;

		foreach ($_attributes as $key => $value) {
			$attributes[$key] = $value['value'];
		}

		return $attributes;
	}

	/**
	 * Get associative array with tag attributes and their values and build tag attribute string.
	 *
	 * @param array $attributes
	 * @return string
	 */
	public function build_attributes($attributes) {
		$_attributes = array();

		if (!empty($attributes)) {
			foreach ($attributes as $key => $value) {
				$_attributes[] = $key . '="' . esc_attr($value) . '"';
			}
		}

		return join(' ', $_attributes);
	}

	/**
	 * Check if class or one of classes exists in class attribute value.
	 *
	 * @param string       $class_attr
	 * @param string|array $class_name
	 * @return bool
	 */
	private function has_class($class_attr, $class_name) {
		if (is_array($class_name)) {
			foreach ($class_name as $_class_name) {
				$_class_name = str_replace('*', '.*', $_class_name);
				if (preg_match('/(^|\s)'.$_class_name.'(\s|$)/', $class_attr)) return true;
			}
		} else {
			$class_name = str_replace('*', '.*', $class_name);
			if (preg_match('/(^|\s)'.$class_name.'(\s|$)/', $class_attr)) {
				return true;
			}
		}

		return false;
	}
}
