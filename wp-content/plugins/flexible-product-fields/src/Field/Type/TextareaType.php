<?php

namespace WPDesk\FPF\Free\Field\Type;

use WPDesk\FPF\Free\Field\Types;
use WPDesk\FPF\Free\Settings\Option\CssOption;
use WPDesk\FPF\Free\Settings\Option\DefaultOption;
use WPDesk\FPF\Free\Settings\Option\FieldLabelOption;
use WPDesk\FPF\Free\Settings\Option\FieldNameOption;
use WPDesk\FPF\Free\Settings\Option\FieldPriorityOption;
use WPDesk\FPF\Free\Settings\Option\FieldTypeOption;
use WPDesk\FPF\Free\Settings\Option\LengthMaxOption;
use WPDesk\FPF\Free\Settings\Option\LogicAdvOption;
use WPDesk\FPF\Free\Settings\Option\PlaceholderOption;
use WPDesk\FPF\Free\Settings\Option\PricingAdvOption;
use WPDesk\FPF\Free\Settings\Option\RequiredOption;
use WPDesk\FPF\Free\Settings\Option\TooltipOption;
use WPDesk\FPF\Free\Settings\Tab\AdvancedTab;
use WPDesk\FPF\Free\Settings\Tab\GeneralTab;
use WPDesk\FPF\Free\Settings\Tab\LogicTab;
use WPDesk\FPF\Free\Settings\Tab\PricingTab;

/**
 * Supports "Textarea" field type.
 */
class TextareaType extends TypeAbstract {

	const FIELD_TYPE = 'textarea';

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type(): string {
		return self::FIELD_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_value( string $field_id, bool $is_request = false ) {
		$form_data = ( $is_request ) ? $_REQUEST : $_POST; // phpcs:ignore
		if ( ! isset( $form_data[ $field_id ] ) ) {
			return null;
		}

		$posted_value = wp_unslash( $form_data[ $field_id ] );
		return sanitize_textarea_field( $posted_value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type_label(): string {
		return __( 'Textarea', 'flexible-product-fields' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_group(): string {
		return Types::FIELD_GROUP_TEXT;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type_icon(): string {
		return 'icon-align-left';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options_objects(): array {
		return [
			GeneralTab::TAB_NAME  => [
				FieldPriorityOption::FIELD_NAME => new FieldPriorityOption(),
				FieldTypeOption::FIELD_NAME     => new FieldTypeOption(),
				FieldLabelOption::FIELD_NAME    => new FieldLabelOption(),
				RequiredOption::FIELD_NAME      => new RequiredOption(),
				PlaceholderOption::FIELD_NAME   => new PlaceholderOption(),
				CssOption::FIELD_NAME           => new CssOption(),
				TooltipOption::FIELD_NAME       => new TooltipOption(),
				DefaultOption::FIELD_NAME       => new DefaultOption(),
				FieldNameOption::FIELD_NAME     => new FieldNameOption(),
			],
			AdvancedTab::TAB_NAME => [
				LengthMaxOption::FIELD_NAME => new LengthMaxOption(),
			],
			PricingTab::TAB_NAME  => [
				PricingAdvOption::FIELD_NAME => new PricingAdvOption(),
			],
			LogicTab::TAB_NAME    => [
				LogicAdvOption::FIELD_NAME => new LogicAdvOption(),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_required(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_max_length(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_placeholder(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_css_class(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_tooltip(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_price_info(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_logic_info(): bool {
		return true;
	}
}
