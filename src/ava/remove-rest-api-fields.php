<?php
function remove_rest_api_fields(&$block, &$attr)
{
  // remove $block['innerContent'] and $block['innerHTML'] fields
  unset($block['innerContent']);
  unset($block['innerHTML']);

  // loop over $attr and remove empty fields
  foreach ($attr as $key => $value) {
    if (empty($value) && $value !== '0' && $value !== 0) {
      unset($attr[$key]);
    }
  }
}


  // $block['blockName'] $attr['text']
