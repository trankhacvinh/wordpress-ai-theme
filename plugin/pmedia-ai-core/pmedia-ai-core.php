<?php
/**
 * Plugin Name: PMEDIA AI Core
 * Plugin URI: https://pmedia.vn/
 * Description: Core plugin for PMEDIA AI Blank Theme. Provides section builder, no-key prompt workflow, sitemap site generator, custom post types, SEO fields and rendering helpers.
 * Version: 1.1.0
 * Author: PMEDIA
 * Author URI: https://pmedia.vn/
 * Text Domain: pmedia-ai-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PMEDIA_AI_CORE_VERSION', '1.1.0');
define('PMEDIA_AI_CORE_FILE', __FILE__);
define('PMEDIA_AI_CORE_DIR', plugin_dir_path(__FILE__));
define('PMEDIA_AI_CORE_URL', plugin_dir_url(__FILE__));

require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-section-schema.php';
require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-cpt.php';
require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-meta-boxes.php';
require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-renderer.php';
require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-site-generator.php';
require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-prompt-workflow.php';
require_once PMEDIA_AI_CORE_DIR . 'includes/class-pmedia-ai-plugin.php';

function pmedia_ai_core(): PMEDIA_AI_Plugin
{
    return PMEDIA_AI_Plugin::instance();
}

add_action('plugins_loaded', 'pmedia_ai_core');
