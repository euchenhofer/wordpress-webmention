<?php
/**
 * Takes MF2 JSON and processes it.
 */
class Webmention_MF2_Handler {

	/**
	 * Is string a URL.
	 *
	 * @param array $string
	 *
	 * @return bool
	*/
	public static function is_url( $string ) {
		if ( ! is_string( $string ) ) {
			return false;
		}
		// If debugging is on just validate that URL is validly formatted
		if ( WP_DEBUG ) {
			return filter_var( $string, FILTER_VALIDATE_URL ) !== false;
		}
		// If debugging is off limit based on WordPress parameters
		return wp_http_validate_url( $string );
	}

	/**
	 * Is this what type?
	 *
	 * @param array  $mf   Parsed Microformats Array
	 * @param string $type Type
	 *
	 * @return bool
	*/
	public static function is_type( $mf, $type ) {
		return is_array( $mf ) && ! empty( $mf['type'] ) && is_array( $mf['type'] ) && in_array( $type, $mf['type'], true );
	}

	/**
	 * Verifies if $mf is an array without numeric keys, and has a 'properties' key.
	 *
	 * @param $mf
	 *
	 * @return bool
	 */
	public static function is_microformat( $mf ) {
		return ( is_array( $mf ) && ! wp_is_numeric_array( $mf ) && ! empty( $mf['type'] ) && isset( $mf['properties'] ) );
	}

	/**
	 * Verifies if $mf has an 'items' key which is also an array, returns true.
	 *
	 * @param $mf
	 *
	 * @return bool
	 */
	public static function is_microformat_collection( $mf ) {
		return ( is_array( $mf ) && isset( $mf['items'] ) && is_array( $mf['items'] ) );
	}

	/**
	 * Verifies if property named $propname is in array $mf.
	 *
	 * @param array  $mf
	 * @param string $propname
	 *
	 * @return bool
	 */
	public static function has_property( array $mf, $propname ) {
		return ! empty( $mf['properties'][ $propname ] ) && is_array( $mf['properties'][ $propname ] );
	}


	/**
	 * Verifies if $p is an array without numeric keys and has key 'value' and 'html' set.
	 *
	 * @param $p
	 *
	 * @return bool
	 */
	public static function is_embedded_html( $p ) {
		return is_array( $p ) && ! wp_is_numeric_array( $p ) && isset( $p['value'] ) && isset( $p['html'] );
	}

	/**
	 * Verifies if rel named $relname is in array $mf.
	 *
	 * @param array  $mf
	 * @param string $relname
	 *
	 * @return bool
	 */
	public static function has_rel( array $mf, $relname ) {
		return ! empty( $mf['rels'][ $relname ] ) && is_array( $mf['rels'][ $relname ] );
	}

	/**
	 * If $v is a microformat or embedded html, return $v['value']. Else return v.
	 *
	 * @param $v
	 *
	 * @return mixed
	 */
	public static function to_plaintext( $v ) {
		if ( self::is_microformat( $v ) || self::is_embedded_html( $v ) ) {
			return $v['value'];
		} elseif ( is_array( $v ) && isset( $v['text'] ) ) {
			return $v['text'];
		}
		return $v;
	}

	/**
	 * Returns plaintext of $propname with optional $fallback.
	 *
	 * @param array       $mf Microformats Array.
	 * @param $propname Property to be retrieved.
	 * @param null|string $fallback Fallback if not available.
	 * @return mixed|null Return value.
	 */
	public static function get_plaintext( array $mf, $propname, $fallback = null ) {
		if ( ! empty( $mf['properties'][ $propname ] ) && is_array( $mf['properties'][ $propname ] ) ) {
			return self::to_plaintext( current( $mf['properties'][ $propname ] ) );
		}
		return $fallback;
	}

	/**
	 * Converts $propname in $mf into array_map plaintext, or $fallback if not valid.
	 *
	 * @param array       $mf
	 * @param string      $propname
	 * @param null|string $fallback
	 *
	 * @return null
	 */
	public static function get_plaintext_array( array $mf, $propname, $fallback = null ) {
		if ( ! empty( $mf['properties'][ $propname ] ) && is_array( $mf['properties'][ $propname ] ) ) {
			return array_map( array( get_called_class(), 'to_plaintext' ), $mf['properties'][ $propname ] );
		}

		return $fallback;
	}

	/**
	 *  Return an array of properties, and may contain plaintext content.
	 *
	 * @param array       $mf Microformats Array.
	 * @param array       $properties An array of the properties to be retrieved.
	 * @return null|array The returned properties.
	 */
	public static function get_property_array( array $mf, $properties, $args = null ) {
		if ( ! self::is_microformat( $mf ) ) {
			return array();
		}
		$data = array();
		foreach ( $properties as $p ) {
			if ( array_key_exists( $p, $mf['properties'] ) ) {
				foreach ( $mf['properties'][ $p ] as $v ) {
					if ( isset( $data[ $p ] ) ) {
						$data[ $p ][] = $v;
					} else {
						$data[ $p ] = array( $v );
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Returns ['html'] element of $v, or ['value'] or just $v, in order of availablility.
	 *
	 * @param $v Microformats Content.
	 * @return mixed HTML Element if present.
	 */
	public static function to_html( $v ) {
		if ( self::is_embedded_html( $v ) ) {
			return $v['html'];
		} elseif ( self::is_microformat( $v ) ) {
			return htmlspecialchars( $v['value'] );
		}
		return htmlspecialchars( $v );
	}

	/**
	 * Gets HTML of $propname or if not, $fallback.
	 *
	 * @param array       $mf Microformats JSON array.
	 * @param $propname Property Name.
	 * @param null|string $fallback Fallback if property not found.
	 * @return mixed|null Value of proerty.
	 */
	public static function get_html( array $mf, $propname, $fallback = null ) {
		if ( ! empty( $mf['properties'][ $propname ] ) && is_array( $mf['properties'][ $propname ] ) ) {
			return self::to_html( current( $mf['properties'][ $propname ] ) );
		}
		return $fallback;
	}


	/**
	 * Gets the DateTime properties including published or updated, depending on params.
	 *
	 * @param $name string updated or published.
	 * @param array                            $mf Microformats JSON array.
	 * @param null|DateTimeImmutable           $fallback What to return if not a DateTime property.
	 * @return mixed|null
	 */
	public static function get_datetime_property( $name, array $mf, $fallback = null ) {
		if ( self::has_property( $mf, $name ) ) {
			$return = self::get_plaintext( $mf, $name );
		} else {
			return $fallback;
		}
		try {
			return new DateTimeImmutable( $return );
		} catch ( Exception $e ) {
			return $fallback;
		}
	}

	/**
	 * get all top-level items.
	 *
	 * @param array $mf_array the microformats array.
	 * @param array an array of top level elements array.
	 *
	 * @return array Return the top level items in an array.
	 */
	public static function get_items( $mf_array ) {
		if ( ! self::is_microformat_collection( $mf_array ) ) {
			return array();
		}

		// Get first item.
		$first_item = $mf_array['items'][0];

		// Check if it is an h-feed.
		if ( self::is_type( $first_item, 'h-feed' ) && array_key_exists( 'children', $first_item ) ) {
			$mf_array['items'] = $first_item['children'];
		}
		// Return entries.
		return $mf_array['items'];
	}

	/**
	 * Helper to find the correct h- node.
	 *
	 * @param array $mf The parsed microformats json.
	 * @param string $url the retrieved url.
	 *
	 * @return array the h- node or false/
	 */
	public static function find_representative_item( $mf, $url ) {
		$items = self::get_items( $mf );
		if ( ! is_array( $items ) || empty( $items ) ) {
			return false;
		}
		if ( 1 === count( $items ) ) {
			return $items[0];
		}
		// Iterate array
		foreach ( $items as $item ) {
			if ( self::compare_urls( $url, $item['properties']['url'] ) ) {
				return $item;
			}
		}
		return false;
	}


	/**
	 * Takes the mf2 json array passed through and returns a cleaned up representative item.
	 *
	 * @param $mf The entire mf array.
	 * @param $url The source URL.
	 *
	 * @return array Return the representative item on the page.
	 */
	public static function get_representative_item( $mf, $url ) {
		$item = self::find_representative_item( $mf, $url );

		if ( empty( $item ) || ! is_array( $item ) ) {
			return array();
		}

		// If entry does not have an author try to find one elsewhere.
		if ( ! self::has_property( $item, 'author' ) ) {
			$item['properties']['author'] = self::get_representative_author( $mf, $url );
		}

		// If u-syndication is not set use rel syndication.
		if ( array_key_exists( 'syndication', $mf['rels'] ) && ! self::has_property( $item, 'syndication' ) ) {
			$item['properties']['syndication'] = $mf['rels']['syndication'];
		}

		return $item;

	}


	/**
	 * Helper to find the correct author node.
	 *
	 * @param array $item Item to find an author on.
	 * @param array $mf The parsed microformats array.
	 * @param string $source The source url.
	 * @see http://indieweb.org/authorship
	 *
	 * @return array|null the h-card node or null.
	 */
	public static function get_representative_author( $item, $mf ) {
		$authorpage = false;

		if ( self::has_property( $item, 'author' ) ) {
			// Check if any of the values of the author property are an h-card.
			foreach ( $item['properties']['author'] as $a ) {
				if ( self::is_type( $a, 'h-card' ) ) {
					// 5.1 "if it has an h-card, use it, exit."
					return $a;
				} elseif ( is_string( $a ) ) {
					if ( wp_http_validate_url( $a ) ) {
						// 5.2 "otherwise if author property is an http(s) URL, let the author-page have that URL"
						$authorpage = $a;
					} else {
						// 5.3 "otherwise use the author property as the author name, exit"
						// We can only set the name, no h-card or URL was found.
						$author = self::get_plaintext( $item, 'author' );
					}
				} else {
					// This case is only hit when the author property is an mf2 object that is not an h-card.
					$author = self::get_plaintext( $item, 'author' );
				}
				if ( ! $authorpage ) {
					return array(
						'type'       => array( 'h-card' ),
						'properties' => array(
							'name' => array( $author ),
						),
					);
				}
			}
		}
		// 6. "if no author page was found" ... check for rel-author link.
		if ( ! $authorpage ) {
			if ( isset( $mf2['rels'] ) && isset( $mf2['rels']['author'] ) ) {
				$authorpage = $mf2['rels']['author'][0];
			}
		}
		// 7. "if there is an author-page URL" .
		if ( $authorpage ) {
			if ( ! self::urls_match( $authorpage, self::get_plaintext( $mf2, 'url' ) ) ) {
				return array(
					'type'       => array( 'h-card' ),
					'properties' => array(
						'url' => array( $authorpage ),
					),
				);
			}
		}
	}

	/**
	 * Compare an url with a list of urls.
	 *
	 * @param string  $needle      The target url.
	 * @param array   $haystack    A list of urls.
	 * @param boolean $schemelesse Define if the target url should be checked with http:// and https:// .
	 *
	 * @return boolean
	 */
	public static function compare_urls( $needle, $haystack, $schemeless = true ) {
		if ( ! self::is_url( $needle ) ) {
			return false;
		}
		if ( is_array( reset( $haystack ) ) ) {
			return false;
		}
		if ( true === $schemeless ) {
			// Remove url-scheme.
			$schemeless_target = preg_replace( '/^https?:\/\//i', '', $needle );

			// Add both urls to the needle.
			$needle = array( 'http://' . $schemeless_target, 'https://' . $schemeless_target );
		} else {
			// Make $needle an array.
			$needle = array( $needle );
		}

		// Compare both arrays.
		return array_intersect( $needle, $haystack );
	}
}