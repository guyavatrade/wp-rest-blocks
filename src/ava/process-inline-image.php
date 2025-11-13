<?php

/**
 * Process inline images to extract only the filename from src attribute
 *
 * @param string $content The content to process
 * @return string The processed content
 */
function process_inline_image_content($content)
{
  if (strpos($content, '<img') === false || strpos($content, 'wp-image-') === false) {
    return $content;
  }

  // Pattern to match img tags with wp-image-{number} class and src attribute
  $pattern = '/<img\s+([^>]*class="[^"]*wp-image-\d+[^"]*"[^>]*)>/i';

  $content = preg_replace_callback($pattern, function ($matches) {
    $img_tag = $matches[0];

    // Extract the src attribute value
    if (preg_match('/src="([^"]+)"/', $img_tag, $src_matches)) {
      $src_url = $src_matches[1];
      $filename = basename($src_url);
      $new_src = '/wp-inline-images/' . $filename;

      // Replace the src value in the img tag
      $img_tag = preg_replace('/src="[^"]+"/', 'src="' . $new_src . '"', $img_tag);
    }

    // Add display: inline-block to the style attribute
    if (preg_match('/style="([^"]*)"/', $img_tag)) {
      // Style attribute exists, append display: inline-block
      $img_tag = preg_replace('/style="([^"]*)"/', 'style="$1; display: inline-block"', $img_tag);
    } else {
      // No style attribute, add one
      $img_tag = preg_replace('/<img\s+/', '<img style="display: inline-block" ', $img_tag);
    }

    return $img_tag;
  }, $content);

  return $content;
}

/**
 * Process table section cells for inline images
 *
 * @param array $section The table section to process
 */
function process_table_section_inline_images(&$section)
{
  if (!is_array($section)) {
    return;
  }

  foreach ($section as &$row) {
    if (isset($row['cells']) && is_array($row['cells'])) {
      foreach ($row['cells'] as &$cell) {
        if (isset($cell['content'])) {
          $cell['content'] = process_inline_image_content($cell['content']);
        }
      }
    }
  }
}

/**
 * Process inline images in blocks
 *
 * This function processes blocks of type 'core/paragraph', 'core/heading', and 'core/table'
 * to replace full image URLs with just the filename.
 *
 * @param array $block The block data
 * @param array $attr The block attributes to manipulate
 */
function process_inline_image(&$block, &$attr)
{
  $supported_blocks = ['core/paragraph', 'core/heading', 'core/table'];

  if (!in_array($block['blockName'], $supported_blocks, true)) {
    return;
  }

  // Process paragraph and heading content
  if ($block['blockName'] === 'core/paragraph' || $block['blockName'] === 'core/heading') {
    if (isset($attr['content'])) {
      $attr['content'] = process_inline_image_content($attr['content']);
    }
    if (isset($attr['text'])) {
      $attr['text'] = process_inline_image_content($attr['text']);
    }
  }

  // Process table cells
  if ($block['blockName'] === 'core/table') {
    $table_sections = ['head', 'body', 'foot'];

    foreach ($table_sections as $section) {
      if (isset($attr[$section])) {
        process_table_section_inline_images($attr[$section]);
      }
    }
  }
}
