<?php

function serve_full_image_quality(&$block)
{
  $image_blocks = ['core/image', 'core/media_text', 'core/cover', 'core/group'];
  if (in_array($block['blockName'], $image_blocks, true)) {
    if (isset($block['attrs']['id'])) {
      $block['attrs']['url'] = wp_get_attachment_url($block['attrs']['id']);
    }
    if (isset($block['attrs']['style']['background']['backgroundImage']['id'])) {
      $block['attrs']['style']['background']['backgroundImage']['url'] = wp_get_attachment_url($block['attrs']['style']['background']['backgroundImage']['id']);
    }
    if (isset($block['attrs']['imageOnTabletId'])) {
      $block['attrs']['imageOnTablet'] = wp_get_attachment_url($block['attrs']['imageOnTabletId']);
    }
    if (isset($block['attrs']['imageOnLaptopId'])) {
      $block['attrs']['imageOnLaptop'] = wp_get_attachment_url($block['attrs']['imageOnLaptopId']);
    }
    if (isset($block['attrs']['imageOnDesktopId'])) {
      $block['attrs']['imageOnDesktop'] = wp_get_attachment_url($block['attrs']['imageOnDesktopId']);
    }
    if (isset($block['attrs']['rtlImageOnMobileId'])) {
      $block['attrs']['rtlImageOnMobile'] = wp_get_attachment_url($block['attrs']['rtlImageOnMobileId']);
    }
    if (isset($block['attrs']['rtlImageOnTabletId'])) {
      $block['attrs']['rtlImageOnTablet'] = wp_get_attachment_url($block['attrs']['rtlImageOnTabletId']);
    }
    if (isset($block['attrs']['rtlImageOnLaptopId'])) {
      $block['attrs']['rtlImageOnLaptop'] = wp_get_attachment_url($block['attrs']['rtlImageOnLaptopId']);
    }
    if (isset($block['attrs']['rtlImageOnDesktopId'])) {
      $block['attrs']['rtlImageOnDesktop'] = wp_get_attachment_url($block['attrs']['rtlImageOnDesktopId']);
    }
  }
}
