<?php
function process_shortcode($block, &$attr)
{
  $is_supports_shortcode_for_blocks = $block['blockName'] === 'core/paragraph' || $block['blockName'] === 'core/heading';

  if ($is_supports_shortcode_for_blocks) {
    if (isset($attr['content'])) {
      $attr['content'] = do_shortcode($attr['content']);
    }
    if (isset($attr['text'])) {
      $attr['text'] = do_shortcode($attr['text']);
    }
  }
}
