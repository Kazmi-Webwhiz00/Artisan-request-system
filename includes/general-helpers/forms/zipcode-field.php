<?php
function render_zipcode_field_with_place_selector($name = 'zip_code', $id = "zipcode", $placeholder = "eg. 5400", $label= "Zip Code *", $required = false) {
?>
   <label for="<?php echo $id ?> " class="form-label"><?php echo $label ?></label>
    <div class="zipcode-input-wrapper">
        <input 
            type="text" 
            id="<?php echo esc_attr($id); ?>" 
            name="<?php echo esc_attr($name); ?>" 
            class="zip-input-field" 
            maxlength="4" 
            placeholder="<?php echo esc_attr($placeholder); ?>"
        />
        <span class="zip-place-display" id="<?php echo esc_attr($id); ?>-place"></span>
        <div class="zip-suggestions" id="<?php echo esc_attr($id); ?>-suggestions"></div>
    </div>
    <div class="zip-error-box" id="<?php echo esc_attr($id); ?>-error"></div> <!-- Error box moved outside wrapper -->
<?php
}
?>
