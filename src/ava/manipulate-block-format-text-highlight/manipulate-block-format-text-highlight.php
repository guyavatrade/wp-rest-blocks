<?php

/**
 * Get a mapping of color slugs to hex values from theme.json
 *
 * @return array Associative array of color slug to hex code
 */
function get_theme_colors()
{
  static $colors = null;

  if ($colors === null) {
    $colors = [];
    $theme_json_path = get_template_directory() . '/theme.json';

    if (file_exists($theme_json_path)) {
      $theme_json = json_decode(file_get_contents($theme_json_path), true);

      if (isset($theme_json['settings']['color']['palette'])) {
        foreach ($theme_json['settings']['color']['palette'] as $color) {
          if (isset($color['slug']) && isset($color['color'])) {
            $colors[$color['slug']] = $color['color'];
          }
        }
      }
    }
  }

  return $colors;
}

/**
 * Manipulate block format for text highlight
 *
 * This function processes blocks of type 'core/paragraph' and 'core/heading'
 * to replace inline color classes with style attributes that was generated by the block format core/text-highlight.
 *
 * @param array $block The block data to manipulate.
 */
function manipulate_block_format_text_highlight(&$block, &$attr)
{
  if ($block['blockName'] === 'core/paragraph' || $block['blockName'] === 'core/heading') {
    if (isset($attr['content'])) {
      if (strpos($attr['content'], 'has-inline-color has-') === false) {
        return;
      }

      // Process inline color classes in content
      $colors = get_theme_colors();

      // First, handle the specific mark tags with inline color classes
      $pattern = '/<mark\s+[^>]*class="([^"]*has-inline-color\s+has-([a-z0-9-]+)-color[^"]*)"[^>]*>/i';

      $attr['content'] = preg_replace_callback($pattern, function ($matches) use ($colors) {
        $full_match = $matches[0];
        $full_class = $matches[1];
        $color_slug = $matches[2];

        if (isset($colors[$color_slug])) {
          $hex_color = $colors[$color_slug];

          // Create a new tag without the class attribute but with the color style
          $new_tag = preg_replace('/class="[^"]*"/', '', $full_match);

          // Check if there's already a style attribute
          if (strpos($new_tag, 'style=') !== false) {
            // Replace existing style attribute - remove background-color if it's transparent
            if (strpos($new_tag, 'background-color:rgba(0, 0, 0, 0)') !== false) {
              $new_tag = preg_replace('/style="[^"]*"/', 'style="color: ' . $hex_color . '"', $new_tag);
            } else {
              // Append color to existing style that's not just a transparent background
              $new_tag = preg_replace('/style="([^"]*)"/', 'style="$1; color: ' . $hex_color . '"', $new_tag);
            }
          } else {
            // No style attribute, add one
            $new_tag = str_replace('<mark ', '<span style="color: ' . $hex_color . '" ', $new_tag);
          }

          // Replace the tag name from mark to span
          $new_tag = str_replace('<mark', '<span', $new_tag);

          return $new_tag;
        }

        return $full_match;
      }, $attr['content']);


      $attr['content'] = str_replace('<mark', '<span', $attr['content']);
      $attr['content'] = str_replace('</mark>', '</span>', $attr['content']);
      $attr['content'] = str_replace('class="has-inline-color"', '', $attr['content']);
    }
  }
}
