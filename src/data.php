<?php
/**
 * Data layer to process to block data.
 *
 * @package WP_REST_Blocks.
 */

namespace WP_REST_Blocks\Data;

use WP_Block;
use pQuery;

function merge_override_content($content_override, $blocks)
{
  foreach ($content_override as $key => $value) {
    if (!isset($value['content']) && !isset($value['text']) && !isset($value['url'])) {
      continue;
    }

    foreach ($blocks as &$block) {
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
            $block['attrs']['url'] = $value['url'];
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
      if(isset($block['attrs']['className'])){
        if( str_contains($block['attrs']['className'], 'background-image-override-target') ){
          if ($key === 'Background Image Override') {
            if(isset($block['attrs']['style']['background']['backgroundImage']['url'])){
              $block['attrs']['style']['background']['backgroundImage']['url'] = $value['url'];
              $block['attrs']['style']['background']['backgroundImage']['id'] = $value['id'];
            }
          }else if($key === 'Background Image Override Tablet'){
            if(isset($block['attrs']['imageOnTablet'])){
              $block['attrs']['imageOnTablet'] = $value['url'];
              $block['attrs']['imageOnTabletId'] = $value['id'];
            }
          }else if($key === 'Background Image Override Laptop'){
            if(isset($block['attrs']['imageOnLaptop'])){
              $block['attrs']['imageOnLaptop'] = $value['url'];
              $block['attrs']['imageOnLaptopId'] = $value['id'];
            }
          }else if($key === 'Background Image Override Desktop'){
            if(isset($block['attrs']['imageOnDesktop'])){
              $block['attrs']['imageOnDesktop'] = $value['url'];
              $block['attrs']['imageOnDesktopId'] = $value['id'];
            }
          }else if($key === 'Background Image Override RTL'){
            if(isset($block['attrs']['rtlBackgroundImage'])){
              $block['attrs']['rtlBackgroundImage'] = $value['url'];
              $block['attrs']['rtlBackgroundImageId'] = $value['id'];
            }
          }else if($key === 'Background Image Override Tablet RTL'){
            if(isset($block['attrs']['rtlTabletBackgroundImage'])){
              $block['attrs']['rtlTabletBackgroundImage'] = $value['url'];
              $block['attrs']['rtlTabletBackgroundImageId'] = $value['id'];
            }
          }else if($key === 'Background Image Override Laptop RTL'){
            if(isset($block['attrs']['rtlLaptopBackgroundImage'])){
              $block['attrs']['rtlLaptopBackgroundImage'] = $value['url'];
              $block['attrs']['rtlLaptopBackgroundImageId'] = $value['id'];
            }
          }else if($key === 'Background Image Override Desktop RTL'){
            if(isset($block['attrs']['rtlDesktopBackgroundImage'])){
              $block['attrs']['rtlDesktopBackgroundImage'] = $value['url'];
              $block['attrs']['rtlDesktopBackgroundImageId'] = $value['id'];
            }
          }
        }
      }

      if (!empty($block['innerBlocks'])) {
        $block['innerBlocks'] = merge_override_content($content_override, $block['innerBlocks']);
      }
    }
  }
  return $blocks;
}

/**
 * Get blocks from html string.
 *
 * @param string $content Content to parse.
 * @param int    $post_id Post int.
 *
 * @return array
 */
function get_blocks( $content, $post_id = 0 ) {
	$output = [];
	$blocks = parse_blocks( $content );

	foreach ( $blocks as $block ) {
		$block_data = handle_do_block( $block, $post_id );
		if ( $block_data ) {
			$output[] = $block_data;
		}
	}

	return $output;
}

/**
 * Process a block, getting all extra fields.
 *
 * @param array $block Block data.
 * @param int   $post_id Post ID.
 *
 * @return array|false
 */
function handle_do_block( array $block, $post_id = 0 ) {
	// Sync Patterns: Parsing and processing the pattern inner blocks.
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
			$block['innerBlocks'] = merge_override_content($content_override, $block['innerBlocks']);
		}
	}

	if ( ! $block['blockName'] ) {
		return false;
	}

	$block_object = new WP_Block( $block );
	$attr         = $block['attrs'];
	if ( $block_object && $block_object->block_type ) {
		$attributes = $block_object->block_type->attributes;
		$supports   = $block_object->block_type->supports;
		if ( $supports && isset( $supports['anchor'] ) && $supports['anchor'] ) {
				$attributes['anchor'] = [
					'type'      => 'string',
					'source'    => 'attribute',
					'attribute' => 'id',
					'selector'  => '*',
					'default'   => '',
				];
		}

		if ( $attributes ) {
			foreach ( $attributes as $key => $attribute ) {
				if ( ! isset( $attr[ $key ] ) ) {
					$attr[ $key ] = get_attribute( $attribute, $block_object->inner_html, $post_id );
				}
			}
		}
	}

  // Process shortcodes
  $is_supports_shortcode_for_blocks = $block['blockName'] === 'core/paragraph' || $block['blockName'] === 'core/heading';
  if($is_supports_shortcode_for_blocks){
    if (isset($attr['content'])) {
      $attr['content'] = do_shortcode($attr['content']);
    }
    if (isset($attr['text'])) {
      $attr['text'] = do_shortcode($attr['text']);
    }
  }

  // * Removed by Ava
	// $block['rendered'] = $block_object->render();
	// $block['rendered'] = do_shortcode( $block['rendered'] );
	$block['attrs']    = $attr;
	if ( ! empty( $block['innerBlocks'] ) ) {
		$inner_blocks         = $block['innerBlocks'];
		$block['innerBlocks'] = [];
		foreach ( $inner_blocks as $_block ) {
			$block['innerBlocks'][] = handle_do_block( $_block, $post_id );
		}
	}

	return $block;
}

/**
 * Get attribute.
 *
 * @param array  $attribute Attributes.
 * @param string $html HTML string.
 * @param int    $post_id Post Number. Deafult 0.
 *
 * @return mixed
 */
function get_attribute( $attribute, $html, $post_id = 0 ) {
	$value = null;
	$dom   = pQuery::parseStr( trim( $html ) );
	$node  = isset( $attribute['selector'] ) ? $dom->query( $attribute['selector'] ) : $dom->query();

	if ( isset( $attribute['source'] ) ) {
		switch ( $attribute['source'] ) {
			case 'attribute':
				$value = $node->attr( $attribute['attribute'] );
				break;
			case 'html':
			case 'rich-text':
				$value = $node->html();
				break;
			case 'text':
				$value = $node->text();
				break;
			case 'query':
				if ( isset( $attribute['query'] ) ) {
					$counter = 0;
					$nodes   = $node->getIterator();
					foreach ( $nodes as $v_node ) {
						foreach ( $attribute['query'] as $key => $current_attribute ) {
							$current_value = get_attribute( $current_attribute, $v_node->toString(), $post_id );
							if ( null !== $current_value ) {
								$value[ $counter ][ $key ] = $current_value;
							}
						}
						++$counter;
					}
				}
				break;
			case 'meta':
				if ( $post_id && isset( $attribute['meta'] ) ) {
					$value = get_post_meta( $post_id, $attribute['meta'], true );
				}
				break;
		}
	}

	// Assign default value if value is null and a default exists.
	if ( is_null( $value ) && isset( $attribute['default'] ) ) {
		$value = $attribute['default'];
	}

	$allowed_types = [ 'array', 'object', 'string', 'number', 'integer', 'boolean', 'null' ];
	// If attribute type is set and valid, sanitize value.
	if ( isset( $attribute['type'] ) && in_array( $attribute['type'], $allowed_types, true ) && rest_validate_value_from_schema( $value, $attribute ) ) {
		$value = rest_sanitize_value_from_schema( $value, $attribute );
	}

	return $value;
}
