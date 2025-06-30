<?php

/**
 * Applies the sync-pattern content overrides to the inner-blocks based on matching keys.
 *
 * @param array $content_override Content override data.
 * @param array $blocks Blocks data.
 *
 * @return array
 */
function merge_override_content_to_inner_blocks($content_override, $blocks)
{
  $result = [];

  foreach ($blocks as $index => $block) {
    // Process block content overrides
    foreach ($content_override as $key => $value) {
      if (!isset($value['content']) && !isset($value['text']) && !isset($value['url'])) {
        continue;
      }

      // Get the full quality image URL
      $url = isset($value['id']) ? wp_get_attachment_url($value['id']) : '';

      if (isset($block['attrs']['metadata']['name'])) {
        if ($block['attrs']['metadata']['name'] === $key) {
          if (isset($value['content'])) {
            $block['attrs']['content'] = $value['content'];
          }
          if (isset($value['text'])) {
            $block['attrs']['text'] = $value['text'];
          }
          if (isset($value['linkTarget'])) {
            $block['attrs']['linkTarget'] = $value['linkTarget'];
          }
          if (isset($value['rel'])) {
            $block['attrs']['rel'] = $value['rel'];
          }
          if (isset($value['url'])) {
            $block['attrs']['url'] = $url;
          }
          if (isset($value['id'])) {
            $block['attrs']['id'] = $value['id'];
          }
          if (isset($value['alt'])) {
            $block['attrs']['alt'] = $value['alt'];
          }
          if (isset($value['title'])) {
            $block['attrs']['title'] = $value['title'];
          }
        }
      }

      // Handle background image override
      if (isset($block['attrs']['className'])) {
        if (str_contains($block['attrs']['className'], 'background-image-override-target')) {
          if ($key === 'Background Image Override') {
            if (isset($block['attrs']['style']['background']['backgroundImage']['url'])) {
              $block['attrs']['style']['background']['backgroundImage']['url'] = $url;
              $block['attrs']['style']['background']['backgroundImage']['id'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Tablet') {
            if (isset($block['attrs']['imageOnTablet'])) {
              $block['attrs']['imageOnTablet'] = $url;
              $block['attrs']['imageOnTabletId'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Laptop') {
            if (isset($block['attrs']['imageOnLaptop'])) {
              $block['attrs']['imageOnLaptop'] = $url;
              $block['attrs']['imageOnLaptopId'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Desktop') {
            if (isset($block['attrs']['imageOnDesktop'])) {
              $block['attrs']['imageOnDesktop'] = $url;
              $block['attrs']['imageOnDesktopId'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Mobile RTL') {
            if (isset($block['attrs']['rtlImageOnMobile'])) {
              $block['attrs']['rtlImageOnMobile'] = $url;
              $block['attrs']['rtlImageOnMobileId'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Tablet RTL') {
            if (isset($block['attrs']['rtlImageOnTablet'])) {
              $block['attrs']['rtlImageOnTablet'] = $url;
              $block['attrs']['rtlImageOnTabletId'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Laptop RTL') {
            if (isset($block['attrs']['rtlImageOnLaptop'])) {
              $block['attrs']['rtlImageOnLaptop'] = $url;
              $block['attrs']['rtlImageOnLaptopId'] = $value['id'];
            }
          } else if ($key === 'Background Image Override Desktop RTL') {
            if (isset($block['attrs']['rtlImageOnDesktop'])) {
              $block['attrs']['rtlImageOnDesktop'] = $url;
              $block['attrs']['rtlImageOnDesktopId'] = $value['id'];
            }
          }
        }
      }
    }

    // Process inner blocks if they exist
    if (!empty($block['innerBlocks'])) {
      $block['innerBlocks'] = merge_override_content_to_inner_blocks($content_override, $block['innerBlocks']);
    }

    // Add processed block to result
    $result[] = $block;
  }

  return $result;
}
