<?php
function render_zipcode_field_with_place_selector(
    $name = 'zip_code',
    $id = 'zipcode',
    $placeholder = 'eg. 5400',
    $label = 'Zip Code *',
    $required = false
) {
?>
    <label for="<?php echo $id; ?>" class="form-label"><?php echo $label; ?></label>
    <div class="zipcode-input-wrapper">
        <input 
            type="text" 
            id="<?php echo esc_attr($id); ?>" 
            name="<?php echo esc_attr($name); ?>" 
            class="zip-input-field" 
            maxlength="6" 
            placeholder="<?php echo esc_attr($placeholder); ?>"
        />
        
        <!-- Place display and suggestions -->
        <span class="zip-place-display" id="<?php echo esc_attr($id); ?>-place"></span>
        <div class="zip-suggestions" id="<?php echo esc_attr($id); ?>-suggestions"></div>
        
        <!-- Hidden fields for lat/lng -->
        <input type="hidden" id="<?php echo esc_attr($id); ?>-lat" name="<?php echo esc_attr($name); ?>_lat" value="" />
        <input type="hidden" id="<?php echo esc_attr($id); ?>-lng" name="<?php echo esc_attr($name); ?>_lng" value="" />
        
    </div>
    
    <div class="zip-error-box" id="<?php echo esc_attr($id); ?>-error"></div>
<?php
}
?>
