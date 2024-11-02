<?php /*

**************************************************************************

Plugin Name:  Chili Code Highlighter
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/chili-code-highlighter/
Description:  Allows for easy posting of code and syntax highlights it via a jQuery plugin called <a href="http://noteslog.com/chili/">Chili</a> by <a href="http://noteslog.com/">Andrea Ercolino</a>.
Version:      1.0.0 Beta 2008.05.30 02:05
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Copyright (C) 2008 Viper007Bond

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class ChiliCodeHighlighter {
	var $folder = '/wp-content/plugins/chili-code-highlighter'; // You shouldn't need to change this, even if you use WPMU (just move the main plugin file to "mu-plugins")
	var $encodemode = 'normal';


	// Plugin initialization
	function ChiliCodeHighlighter() {
		// This version only supports WP 2.5+ (learn to upgrade please!)
		if ( !function_exists('add_shortcode') ) return;

		// Register Chili script and the additional JS and CSS
		if ( !is_admin() ) wp_enqueue_script( 'jquery-chili', $this->folder . '/chili/jquery.chili-2.0.js', array('jquery'), '2.0' );
		add_action( 'wp_head', array(&$this, 'ExtraJSAndCSS') );

		// Register this plugin's shortcodes now incase other plugins need to look for them or something...
		// They're registered for real inside ChiliCodeHighlighter::EarlyShortcode()
		add_shortcode( 'code' , array(&$this, 'ShortcodeHandler_code') );
		add_shortcode( 'source' , array(&$this, 'ShortcodeHandler_source') );
		add_shortcode( 'sourcecode' , array(&$this, 'ShortcodeHandler_sourcecode') );

		// Register the filter functions that makes the shortcode run early
		add_filter( 'the_content', array(&$this, 'DoEarlyShortcode'), 5 );
		add_filter( 'widget_text', array(&$this, 'DoEarlyShortcode'), 5 ); // Text widgets

		// Account for TinyMCE
		add_filter( 'pre_post_content', array(&$this, 'TinyMCEDecode') );
		add_filter( 'the_editor_content', array(&$this, 'TinyMCEEncode') );
	}


	// Output additional needed Javascript and CSS
	function ExtraJSAndCSS() { ?>
	<!-- Chili Code Highlighter v1.0.0 | http://www.viper007bond.com/wordpress-plugins/chili-code-highlighter/ -->
	<script type="text/javascript">
		ChiliBook.recipeFolder = '<?php echo apply_filters( 'chili_recipefolder', get_bloginfo('wpurl') . $this->folder . '/chili/' ); ?>';
	</script>
	<style type="text/css">
		pre.chili {
			padding: 5px;
			overflow-x: auto;
		}
	</style>
	<!--[if IE]><style type="text/css">pre.chili { padding-bottom: 20px; }</style><![endif]-->
<?php
	}


	// Wrapper functions so we know what tag we're on
	function ShortcodeHandler_code( $atts = array(), $content = NULL ) { return $this->ShortcodeHandler( $atts, $content, 'code' ); }
	function ShortcodeHandler_source( $atts = array(), $content = NULL ) { return $this->ShortcodeHandler( $atts, $content, 'source' ); }
	function ShortcodeHandler_sourcecode( $atts = array(), $content = NULL ) { return $this->ShortcodeHandler( $atts, $content, 'sourcecode' ); }


	// Run do_shortcode() but only with this plugin's shortcodes as this function gets called early
	function DoEarlyShortcode( $content ) {
		global $shortcode_tags;

		// Backup and clear out the shortcodes list
		$shortcode_backup = $shortcode_tags;
		$shortcode_tags = array();

		// Re-register this plugin's shortcodes
		add_shortcode( 'code' , array(&$this, 'ShortcodeHandler_code') );
		add_shortcode( 'source' , array(&$this, 'ShortcodeHandler_source') );
		add_shortcode( 'sourcecode' , array(&$this, 'ShortcodeHandler_sourcecode') );

		// Run the shortcodes function on the content now that it's just this plugin's shortcodes
		$content = do_shortcode( $content );

		// Put the shortcodes back to normal
		$shortcode_tags = $shortcode_backup;

		return $content;
	}


	// Process the shortcode tags
	function ShortcodeHandler( $atts = array(), $content = NULL, $tag ) {
		// Set any missing $atts items to the defaults
		$atts = shortcode_atts(array(
			0          => FALSE,
			'lang'     => FALSE,
			'language' => FALSE,
		), $atts);

		$atts = $this->attributefix( $atts );

		// This is for TinyMCE
		if ( 'decode' == $this->encodemode || 'encode' == $this->encodemode ) {
			$output = '[' . $tag;
			if ( FALSE !== $atts[0] )          $output .= '="' . $atts[0] . '"';
			if ( FALSE !== $atts['lang'] )     $output .= ' lang="' . $atts['lang'] . '"';
			if ( FALSE !== $atts['language'] ) $output .= ' language="' . $atts['language'] . '"';
			$output .= ']';

			$output .= ( 'decode' == $this->encodemode ) ? htmlspecialchars_decode( $content, ENT_QUOTES ) : htmlspecialchars( $content, ENT_QUOTES );

			$output .= '[/' . $tag . ']';
		}

		// Normal in-post handling
		else {
			// Figure out the language (lowest priority attributes on top)
			$lang = FALSE;
			if ( FALSE !== $atts[0] )          $lang = strip_tags( $atts[0] ); // [tag="lang"]...[/tag]
			if ( FALSE !== $atts['lang'] )     $lang = strip_tags( $atts['lang'] );
			if ( FALSE !== $atts['language'] ) $lang = strip_tags( $atts['language'] );

			// Generate the output
			$output = '<pre class="chili"><code';
			if ( !empty($lang) ) $output .= ' class="' . $lang . '"';
			$output .= '>' . htmlspecialchars( $content, ENT_QUOTES ) . '</code></pre>';
		}

		return $output;
	}


	// No-name attribute fixing
	function attributefix( $atts = array() ) {
		if ( empty($atts[0]) ) return $atts;

		$atts[0] = str_replace( array( '="', "='" ), '', $atts[0] );

		return $atts;
	}


	// Reverse changes TinyMCE made to the entered code
	function TinyMCEDecode( $content ) {
		if ( !user_can_richedit() ) return $content;

		$this->encodemode = 'decode';
		$content = $this->DoEarlyShortcode( $content );
		$this->encodemode = 'normal';

		return $content;
	}


	// (Re)Encode the code so TinyMCE will display it correctly
	function TinyMCEEncode( $content ) {
		if ( !user_can_richedit()  ) return $content;

		$this->encodemode = 'encode';
		$content = $this->DoEarlyShortcode( $content );
		$this->encodemode = 'normal';

		return $content;
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $ChiliCodeHighlighter; $ChiliCodeHighlighter = new ChiliCodeHighlighter();' ) );


// For those poor souls stuck on PHP4
if ( !function_exists( 'htmlspecialchars_decode' ) ) {
	function htmlspecialchars_decode( $string, $quote_style = ENT_COMPAT ) {
		return strtr( $string, array_flip( get_html_translation_table( HTML_SPECIALCHARS, $quote_style) ) );
	}
}

?>