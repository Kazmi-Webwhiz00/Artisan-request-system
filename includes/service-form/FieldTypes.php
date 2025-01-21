<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Field Types Enum
 */
enum FieldTypes: string {
    case TEXT_INPUT = 'text_input';
    case NUMBER_INPUT = 'number_input';
    case RADIO = 'radio';
    case CHECKBOX_SIMPLE = 'checkbox_simple';
    case CHECKBOX_WITH_IMAGE = 'checkbox_with_image';
    case TEXTAREA = 'textarea';

    /**
     * Get All Field Types as an Array
     * @return array
     */
    public static function getAll(): array {
        return [
            self::TEXT_INPUT->value => __('Text Input', 'textdomain'),
            self::NUMBER_INPUT->value => __('Number Input', 'textdomain'),
            self::RADIO->value => __('Radio Button', 'textdomain'),
            self::CHECKBOX_SIMPLE->value => __('Simple Checkbox', 'textdomain'),
            self::CHECKBOX_WITH_IMAGE->value => __('Checkbox with Image', 'textdomain'),
            self::TEXTAREA->value => __('Text Area', 'textdomain'),
        ];
    }
}
