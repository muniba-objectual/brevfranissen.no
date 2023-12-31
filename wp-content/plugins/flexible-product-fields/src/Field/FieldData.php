<?php

namespace WPDesk\FPF\Free\Field;

use WPDesk\FPF\Free\Settings\Option\FieldNameOption;
use WPDesk\FPF\Free\Settings\Option\FieldTypeOption;
use WPDesk\FPF\Free\Settings\Option\OptionInterface;

/**
 * Generates field data based on options for field type.
 */
class FieldData {

	/**
	 * Returns parsed data for field.
	 *
	 * @param array $field_settings Settings of field.
	 * @param bool  $is_decode      Is it decoding (used saved settings) data instead of encoding (for settings save).
	 *
	 * @return array Data of field.
	 */
	public static function get_field_data( array $field_settings, bool $is_decode = true ): array {
		$field_data     = [];
		$option_objects = self::get_field_options( $field_settings );

		if ( ! $option_objects ) {
			return $field_data;
		}

		$field_data[ FieldNameOption::FIELD_NAME ] = $field_settings[ FieldNameOption::FIELD_NAME ];
		foreach ( $option_objects as $field_option ) {
			$field_data = $field_option[ ( $is_decode ) ? 'update_field_callback' : 'save_field_callback' ](
				$field_data,
				$field_settings
			);
		}
		return $field_data;
	}

	/**
	 * Returns list of option objects.
	 *
	 * @param array $field_settings Settings of field.
	 *
	 * @return OptionInterface[] List of options.
	 */
	public static function get_field_options( array $field_settings ): array {
		$field_types = apply_filters( 'flexible_product_fields_field_types', [] );

		foreach ( $field_types as $field_type ) {
			if ( isset( $field_settings['type'] )
				&& ( $field_settings['type'] === $field_type[ FieldTypeOption::FIELD_NAME ] ) ) {
				return $field_type['options'];
			}
		}
		return [];
	}
}
