<?php
function remove_rest_api_fields(&$block, &$attr)
{
  // remove $block['innerContent'] and $block['innerHTML'] fields
  if ($block["blockName"] !== "core/html") {
    unset($block['innerHTML']);
  }
  unset($block['innerContent']);

  // loop over $attr and remove empty fields
  foreach ($attr as $key => $value) {
    if (empty($value) && $value !== '0' && $value !== 0) {
      unset($attr[$key]);
    }
  }
}


  // $block['blockName'] $attr['text']
