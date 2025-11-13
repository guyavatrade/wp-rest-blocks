<?php
function process_shortcode($block, &$attr)
{
  $is_supports_shortcode_for_blocks = $block['blockName'] === 'core/paragraph' || $block['blockName'] === 'core/heading' || $block['blockName'] === 'core/table';

  if ($is_supports_shortcode_for_blocks) {
    // Process paragraph and heading content
    if (isset($attr['content'])) {
      $attr['content'] = do_shortcode($attr['content']);
    }
    if (isset($attr['text'])) {
      $attr['text'] = do_shortcode($attr['text']);
    }

    // Process table cells
    if ($block['blockName'] === 'core/table') {
      $table_sections = ['head', 'body', 'foot'];

      foreach ($table_sections as $section) {
        if (isset($attr[$section]) && is_array($attr[$section])) {
          foreach ($attr[$section] as &$row) {
            if (isset($row['cells']) && is_array($row['cells'])) {
              foreach ($row['cells'] as &$cell) {
                if (isset($cell['content'])) {
                  $cell['content'] = do_shortcode($cell['content']);
                }
              }
            }
          }
        }
      }
    }
  }
}
