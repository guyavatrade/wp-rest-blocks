<?php
require_once __DIR__ . '/../merge-override-content-to-inner-blocks/merge-override-content-to-inner-blocks.php';

function sync_pattern_to_inner_blocks(&$block)
{
  if ($block['blockName'] === 'core/block' && isset($block['attrs']['ref']) && !empty($block['attrs']['ref'])) {
    $sync_pattern = get_post($block['attrs']['ref']);

    $content_override = [];
    if (isset($block['attrs']['content'])) {
      $content_override = $block['attrs']['content'];
    }

    if ($sync_pattern && 'wp_block' === $sync_pattern->post_type) {
      // parse the inner blocks
      $block['innerBlocks'] = parse_blocks($sync_pattern->post_content);
      // remove the empty blocks
      $sync_inner_blocks = [];
      foreach ($block['innerBlocks'] as $_block) {
        if ($_block['blockName']) {
          $sync_inner_blocks[] = $_block;
        }
      }
      $block['innerBlocks'] = $sync_inner_blocks;
      // merge the content override to the inner blocks
      $block['innerBlocks'] = merge_override_content_to_inner_blocks($content_override, $block['innerBlocks']);
    }
  }
}
