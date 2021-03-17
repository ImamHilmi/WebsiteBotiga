<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Buttons
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'mod_title_button' => '',
    'buttons_size' => '',
    'buttons_shape' => 'circle',
    'buttons_style' => 'solid',
    'fullwidth_button' => '',
    'nofollow_link' => '',
    'download_link' => '',
    'display' => 'buttons-horizontal',
    'alignment' => '',
    'content_button' => array(),
    'animation_effect' => '',
    'css_button' => ''
);
/* for old button style args */
if (isset($args['mod_settings']['buttons_style'])) {
    if (in_array($args['mod_settings']['buttons_style'], array('circle', 'rounded', 'squared'), true)) {
		$args['mod_settings']['buttons_shape'] = $args['mod_settings']['buttons_style'];
    }
	elseif ($args['mod_settings']['buttons_style'] === 'outline') {
		Themify_Builder_Model::load_module_self_style($args['mod_name'], $args['mod_settings']['buttons_style']);
    }
}
/* End of old button style args */
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name = $args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];


$container_class = apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $fields_args['display'],
    $fields_args['buttons_style'],
    $fields_args['css_button']
	), $mod_name, $element_id, $fields_args);
if ($fields_args['buttons_size']!=='normal') {
	$container_class[] = $fields_args['buttons_size'];
}
if ($fields_args['buttons_shape']!=='normal') {
	$container_class[] = $fields_args['buttons_shape'];
	if ($fields_args['buttons_shape'] === 'rounded') {
		Themify_Builder_Model::load_appearance_css($fields_args['buttons_shape']);
	}
}
if (!empty($fields_args['fullwidth_button'])) {
    $fields_args['alignment'] = $fields_args['display'] = '';
    $container_class[] = $fields_args['fullwidth_button'];
    Themify_Builder_Model::load_module_self_style($mod_name, 'fullwidth');
}
elseif ($fields_args['alignment'] !== '' || $fields_args['display'] === 'buttons-vertical') {
    if($fields_args['alignment']!==''){
	$container_class[] = 'tf_text' . $fields_args['alignment'][0];
    }
    if ($fields_args['display'] === 'buttons-vertical') {
	Themify_Builder_Model::load_module_self_style($mod_name, 'vertical');
    }
}
if (!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active === false) {
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	    'class' => implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);
if (Themify_Builder::$frontedit_active === false) {
    $container_props['data-lazy'] = 1;
}
?>
<!-- module buttons -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
<?php
$container_props = $container_class =$args= null;
do_action('themify_builder_background_styling', $builder_id, array('styling' => $fields_args, 'mod_name' => $mod_name), $element_id, 'module');
?>
    <?php if ($fields_args['mod_title_button'] !== ''): ?>
	<?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_button'], $fields_args) . $fields_args['after_title']; ?>
    <?php endif; ?>
    <?php
    $content_button = array_filter($fields_args['content_button']);
    foreach ($content_button as $i => $content):
	$content = wp_parse_args($content, array(
	    'label' => '',
	    'link' => '',
	    'icon' => '',
	    'icon_alignment' => 'left',
	    'link_options' => false,
	    'lightbox_width' => '',
	    'lightbox_height' => '',
	    'lightbox_width_unit' => 'px',
	    'lightbox_height_unit' => 'px',
	    'button_color_bg' => 'tb_default_color',
	    'title' => ''
	));
	if ($content['button_color_bg'] === 'default') {
	    $content['button_color_bg'] = 'tb_default_color';
	}
	$link_css_clsss = array('ui builder_button');
	$link_attr = array();

	if ($content['link_options'] === 'lightbox') {
	    $link_css_clsss[] = 'themify_lightbox';

	    if ($content['lightbox_width'] !== '' || $content['lightbox_height'] !== '') {
		$lightbox_settings = array();
		if ($content['lightbox_width'] !== '') {
		    $lightbox_settings[] = $content['lightbox_width'] . $content['lightbox_width_unit'];
		}
		if ($content['lightbox_height'] !== '') {
		    $lightbox_settings[] = $content['lightbox_height'] . $content['lightbox_height_unit'];
		}
		$link_attr[] = sprintf('data-zoom-config="%s"', implode('|', $lightbox_settings));
	    }
	} 
	elseif ($content['link_options'] === 'newtab') {
	    $nofollow = $fields_args['nofollow_link'] === 'yes' ? 'nofollow ' : '';
	    $link_attr[] = 'target="_blank" rel="' . $nofollow . 'noopener"';
	}
	$link_css_clsss[] = $content['button_color_bg'];
	Themify_Builder_Model::load_color_css($content['button_color_bg']);
	if ($fields_args['nofollow_link'] === 'yes' && $content['link_options'] !== 'newtab') {
	    $link_attr[] = 'rel="nofollow"';
	}

	if ($fields_args['download_link'] === 'yes') {
	    $link_attr[] = 'download';
	}
	$icon = $content['icon'] ? sprintf('<i class="tf_inline_b tf_vmiddle">%s</i>', themify_get_icon($content['icon'])) : '';
	?>
    	<div class="module-buttons-item tf_inline_b">
	    <?php if ($content['link']): ?>
		    <a href="<?php echo esc_url($content['link']) ?>" class="<?php echo implode(' ', $link_css_clsss) ?>" <?php echo implode(' ', $link_attr) ?><?php echo !empty($content['title'])?' title="'.esc_attr($content['title']).'"':''; ?>>
		<?php endif; ?>
		    <?php if ($content['icon_alignment'] !== 'right' && $icon !== ''): ?>
			<?php echo $icon ?>
		    <?php endif; ?>
    		<span class="tf_inline_b tf_vmiddle"<?php if (Themify_Builder::$frontedit_active === true): ?> contenteditable="false" data-name="label" data-repeat="content_button" data-index="'.$i.'"<?php endif; ?>><?php echo $content['label'] ?></span>
		    <?php if ($content['icon_alignment'] === 'right' && $icon !== ''): ?>
			<?php echo $icon ?>
		    <?php endif; ?>
		    <?php if ($content['link']): ?>
		    </a>
		    <?php endif; ?>
    	</div>
	    <?php endforeach; ?>
</div>
<!-- /module buttons -->
