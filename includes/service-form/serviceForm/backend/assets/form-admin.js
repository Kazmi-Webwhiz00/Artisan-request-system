jQuery(document).ready(function ($) {
    const addFieldButton = $("#kz-add-new-field");
    const fieldsContainer = $("#kz-fields-container");

    function init() {
        enableSortableFields();
        initializeGlobalEventListeners();
        attachEventsToExistingFields();
    }

    function enableSortableFields() {
        fieldsContainer.sortable({
            handle: ".kz-drag-handle",
            placeholder: "kz-sortable-placeholder",
            stop: function() {
                updateQuestionNumbers();
            }
        });
    }

    function attachEventsToExistingFields() {
        fieldsContainer.find(".kz-field-container").each(function () {
            const fieldContainer = $(this);
            attachFieldEventListeners(fieldContainer);
    
            // Handle dynamic options for checkboxes, radio buttons, and checkbox_with_image
            const fieldType = fieldContainer.find(".kz-field-type-selector").val();
            const dynamicOptions = fieldContainer.find(".kz-dynamic-options");
            const uniqueId = fieldContainer.attr("id");
    
            // Call attachDynamicEvents for all dynamic options
            attachDynamicEvents(dynamicOptions, fieldType, uniqueId);
    
            // Handle existing checkbox_with_image items specifically
            if (fieldType === "checkbox_with_image") {
                dynamicOptions.find(".kz-checkbox-with-image-item").each(function () {
                    const item = $(this);
    
                    // Attach image upload functionality
                    item.find(".upload-image-button").on("click", function () {
                        const button = $(this);
                        const mediaUploader = wp.media({
                            title: "Select Image",
                            button: { text: "Use Image" },
                            multiple: false,
                        });
    
                        mediaUploader.on("select", function () {
                            const attachment = mediaUploader.state().get("selection").first().toJSON();
                            button.parent().html(
                                `<img src="${attachment.url}" alt="Preview" style="max-width: 100px;">`
                            );
                            item.attr("data-image-id", attachment.id);
                            updateFieldOptions(fieldContainer, uniqueId);
                        });
    
                        mediaUploader.open();
                    });
    
                    // Update options on input change for existing checkbox_with_image
                    item.find(".editable-input").on("input", function () {
                        updateFieldOptions(fieldContainer, uniqueId);
                    });
    
                    // Attach remove functionality for existing checkbox_with_image
                    item.find(".kz-remove-checkbox-with-image").on("click", function () {
                        $(this).closest(".kz-checkbox-with-image-item").remove();
                        updateFieldOptions(fieldContainer, uniqueId);
                    });
                });
            }
    
            // Handle existing checkboxes and radio buttons (update and remove)
            if (fieldType === "checkbox_simple" || fieldType === "radio") {
                // Attach events for existing checkbox/radio labels
                dynamicOptions.find(".kz-checkbox-item .editable-input, .kz-radio-item .editable-input").on("input", function () {
                    updateFieldOptions(fieldContainer, uniqueId);
                });
    
                // Attach remove event for existing checkbox/radio items
                dynamicOptions.find(".kz-remove-checkbox, .kz-remove-radio").on("click", function () {
                    $(this).closest(".kz-checkbox-item, .kz-radio-item").remove();
                    updateFieldOptions(fieldContainer, uniqueId);
                });
            }
    
            // Handle existing text_input, email, textarea, and number_input placeholders
            if (fieldType === "text_input" || fieldType === "email" || fieldType === "textarea" || fieldType === "number_input") {
                dynamicOptions.find(".kz-placeholder-input, .kz-min-value, .kz-max-value").on("input", function () {
                    updateFieldOptions(fieldContainer, uniqueId);
                });
            }
        });
    }
    
    
    
    function addNewField() {
        const uniqueId = FormMetaData.formId + "-" + new Date().getTime();
        const fieldContainer = createFieldContainer(uniqueId);
        fieldsContainer.append(fieldContainer);
        attachFieldEventListeners(fieldContainer);
        updateQuestionNumbers();
    }

    function createFieldContainer(uniqueId) {
        return $(
            `<div class="kz-field-container" id="field-${uniqueId}">
                <div class="kz-field-header">
                    <span class="kz-drag-handle">☰</span>
                    <span class="kz-toggle-collapse">Q${$(".kz-field-container").length + 1}: New Question</span>
                    <span class="kz-remove-field">✖</span>
                </div>
                <div class="kz-field-body">
                    <label>Input Field Label:</label>
                    <input type="text" 
                           placeholder="Enter field label here" 
                           class="kz-field-label-input" 
                           name="fields[${uniqueId}][field_label]">
                    
                    <label>Field Type:</label>
                    <select class="kz-field-type-selector" name="fields[${uniqueId}][field_type]">
                        <option value="text_input">Text Input</option>
                        <option value="email">Email</option>
                        <option value="number_input">Number Input</option>
                        <option value="radio">Radio Button</option>
                        <option value="checkbox_simple">Simple Checkbox</option>
                        <option value="textarea">Text Area</option>
                        <option value="checkbox_with_image">Checkbox with Image</option>
                    </select>
        
                    <label>Is Required:</label>
                    <div class="kz-radio-group">
                        <label>
                            <input type="radio" name="fields[${uniqueId}][is_required]" value="1"> Yes
                        </label>
                        <label>
                            <input type="radio" name="fields[${uniqueId}][is_required]" value="0" checked> No
                        </label>
                    </div>
        
                    <div class="kz-dynamic-options"></div>
        
                    <!-- Hidden JSON Field -->
                    <input type="hidden" class="kz-options-json" name="fields[${uniqueId}][options]" value="{}">
                    
                    <!-- Hidden Field Order -->
                    <input type="hidden" class="kz-field-order-input" name="fields[${uniqueId}][field_order]" value="">
                </div>
            </div>`
        );
    }
    

    function updateFieldOptions(container, uniqueId) {
        const options = {};
        const type = container.find(".kz-field-type-selector").val();
    
        // Handle email field type
        if (type === "email" || type === "text_input") {
            options.placeholder = container.find(".kz-dynamic-options input[placeholder]").val() || null;
        }
    
        // Number Input
        if (type === "number_input") {
            options.placeholder = container.find(".kz-dynamic-options input[placeholder]").val() || null;
            options.min = container.find(".kz-min-value").val() || null;
            options.max = container.find(".kz-max-value").val() || null;
        }
    
        // Textarea
        if (type === "textarea") {
            options.placeholder = container.find(".kz-dynamic-options textarea[placeholder]").val() || null;
            options.min = container.find(".kz-min-length").val() || null;
            options.max = container.find(".kz-max-length").val() || null;
        }
    
        // Radio or Checkbox
        if (type === "radio" || type === "checkbox_simple") {
            const optionsList = [];
            container.find(".kz-checkbox-list .kz-checkbox-item, .kz-radio-list .kz-radio-item").each(function () {
                const label = $(this).find(".editable-input").val() || "Untitled";
                optionsList.push({
                    label,
                    value: label.toLowerCase().replace(/\s+/g, "_"), // Auto-generate a value from the label
                });
            });
            options.options_list = optionsList;
        }
    
        if (type === "checkbox_with_image") {
            const optionsList = [];
            container.find(".kz-checkbox-with-image-item").each(function () {
                const item = $(this);
                optionsList.push({
                    label: item.find(".editable-input").val() || "Untitled",
                    value: (item.find(".editable-input").val() || "Untitled").toLowerCase().replace(/\s+/g, "_"),
                    imageId: item.attr("data-image-id") || null, // Get the saved image ID
                });
            });
            options.options_list = optionsList;
        }
        
        // Update the hidden JSON field
        container.find(".kz-options-json").val(JSON.stringify(options));
    }
    


    
    
    function handleFieldTypeChange(selector, container) {
        const selectedType = selector.val();
        const dynamicOptions = container.find(".kz-dynamic-options");
        const uniqueId = container.attr("id");
        dynamicOptions.empty();
    
        const htmlGenerators = {
            text_input: generateTextInputHTML,
            email: generateTextInputHTML, // Use the same generator for email and text_input
            number_input: generateNumberInputHTML,
            textarea: generateTextareaHTML,
            checkbox_simple: generateCheckboxHTML,
            radio: generateRadioHTML,
            checkbox_with_image: generateCheckboxWithImageHTML,
        };
    
        if (htmlGenerators[selectedType]) {
            dynamicOptions.append(htmlGenerators[selectedType](uniqueId));
            attachDynamicEvents(dynamicOptions, selectedType, uniqueId);
        }
    }
    

    function generateTextInputHTML() {
        return `
            <div class="kz-text-input-options">
                <label>Placeholder:</label>
                <input type="text" class="kz-placeholder-input" placeholder="Enter placeholder text">
            </div>
        `;
    }

    function generateCheckboxWithImageHTML(uniqueId) {
        return `
            <div class="kz-checkbox-with-image-options">
                <button type="button" class="kz-add-checkbox-with-image kz-add-btn">+ Add Option</button>
                <div class="kz-checkbox-with-image-list" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
            </div>
        `;
    }
    
    

    function generateNumberInputHTML() {
        return `
            <label>Placeholder:</label>
            <input type="text" placeholder="Enter placeholder text">
            <label>Min Value:</label>
            <input type="number" class="kz-min-value" placeholder="Enter minimum value">
            <label>Max Value:</label>
            <input type="number" class="kz-max-value" placeholder="Enter maximum value">
            <p class="kz-range-message"></p>
        `;
    }

    function generateTextareaHTML() {
        return `
            <label>Placeholder:</label>
            <textarea placeholder="Enter placeholder text"></textarea>
            <label>Min Length:</label>
            <input type="number" class="kz-min-length" placeholder="Enter minimum length">
            <label>Max Length:</label>
            <input type="number" class="kz-max-length" placeholder="Enter maximum length">
            <p class="kz-length-message"></p>
        `;
    }

    function generateCheckboxHTML(uniqueId) {
        return `
            <div class="kz-checkbox-options">
                <span class="kz-add-checkbox kz-add-btn">+ Add Checkbox</span>
                <div class="kz-checkbox-list"></div>
            </div>
        `;
    }

    function generateRadioHTML(uniqueId) {
        return `
            <div class="kz-radio-options">
                <span class="kz-add-radio kz-add-btn">+ Add Radio Button</span>
                <div class="kz-radio-list"></div>
            </div>
        `;
    }

    function attachDynamicEvents(dynamicOptions, type, uniqueId) {
        if (type === "text_input" || type==="email") {
            dynamicOptions.find(".kz-placeholder-input").on("input", function () {
                updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
            });
        }
        
        if (type === "number_input") {
            dynamicOptions.find(".kz-min-value, .kz-max-value, input[placeholder]").on("input", function () {
                updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
            });
        }
    
        if (type === "textarea") {
            dynamicOptions.find(".kz-min-length, .kz-max-length, textarea[placeholder]").on("input", function () {
                updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
            });
        }
    
        if (type === "checkbox_simple") {
            dynamicOptions.find(".kz-add-checkbox").on("click", function () {
                const checkboxList = dynamicOptions.find(".kz-checkbox-list");
                const newCheckbox = $(`
                    <div class="editable-checkbox-container kz-checkbox-item">
                        <label class="editable-checkbox">
                            <input type="checkbox" name="checkbox-group-${uniqueId}">
                            <span class="checkbox-label">
                                <input type="text" placeholder="Type here..." class="editable-input" />
                            </span>
                        </label>
                        <span class="kz-remove-checkbox kz-remove-btn">✖</span>
                    </div>
                `);
                checkboxList.append(newCheckbox);
    
                newCheckbox.find(".editable-input").on("input", function () {
                    updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                });
    
                newCheckbox.find(".kz-remove-checkbox").on("click", function () {
                    $(this).closest(".kz-checkbox-item").remove();
                    updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                });
    
                updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
            });
        }
    
        if (type === "radio") {
            dynamicOptions.find(".kz-add-radio").on("click", function () {
                const radioList = dynamicOptions.find(".kz-radio-list");
                const newRadio = $(`
                    <div class="editable-radio-container kz-radio-item">
                        <label class="editable-radio">
                            <input type="radio" name="radio-group-${uniqueId}">
                            <span class="radio-label">
                                <input type="text" placeholder="Type here..." class="editable-input" />
                            </span>
                        </label>
                        <span class="kz-remove-radio kz-remove-btn">✖</span>
                    </div>
                `);
                radioList.append(newRadio);
    
                newRadio.find(".editable-input").on("input", function () {
                    updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                });
    
                newRadio.find(".kz-remove-radio").on("click", function () {
                    $(this).closest(".kz-radio-item").remove();
                    updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                });
    
                updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
            });
        }

        if (type === "checkbox_with_image") {
            const list = dynamicOptions.find(".kz-checkbox-with-image-list");
            
            // Add new option
            dynamicOptions.find(".kz-add-checkbox-with-image").on("click", function () {
                const newItem = $(`
                    <div class="kz-checkbox-with-image-item">
                        <div class="image-upload-preview" style="margin-bottom: 10px;">
                            <button type="button" class="upload-image-button">Upload Image</button>
                        </div>
                        <input type="text" class="editable-input" placeholder="Type here...">
                        <button type="button" class="kz-remove-checkbox-with-image kz-remove-btn">Remove</button>
                    </div>
                `);
        
                list.append(newItem);
        
                // Handle media library for image upload
                newItem.find(".upload-image-button").on("click", function () {
                    const button = $(this);
                
                    const mediaUploader = wp.media({
                        title: "Select Image",
                        button: { text: "Use Image" },
                        multiple: false, // Single image selection
                    });
                
                    mediaUploader.on("select", function () {
                        const attachment = mediaUploader.state().get("selection").first().toJSON();
                
                        // Update the preview with the uploaded image
                        button.parent().html(`<img src="${attachment.url}" alt="Preview" style="max-width: 100px;">`);
                
                        // Set the image ID as a data attribute
                        newItem.attr("data-image-id", attachment.id);
                
                        // Trigger the update of the JSON
                        updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                    });
                
                    mediaUploader.open();
                });
                
        
                // Remove option
                newItem.find(".kz-remove-checkbox-with-image").on("click", function () {
                    newItem.remove();
                    updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                });
        
                // Update options JSON on input
                newItem.find(".editable-input").on("input", function () {
                    updateFieldOptions(dynamicOptions.closest(".kz-field-container"), uniqueId);
                });
            });
        }
        
    }
    

    function attachEditableEvents(item) {
        item.find(".editable-input").on("input", function () {
            // Optionally save dynamically if needed
        });

        item.find(".kz-remove-checkbox, .kz-remove-radio").on("click", function () {
            $(this).closest("div").remove();
        });
    }

    function attachFieldEventListeners(fieldContainer) {
        fieldContainer.find(".kz-field-header").on("click", function (e) {
            if ($(e.target).hasClass("kz-remove-field") || $(e.target).hasClass("kz-drag-handle")) return;
            $(this).next(".kz-field-body").slideToggle();
        });

        fieldContainer.find(".kz-remove-field").on("click", function (e) {
            e.stopPropagation();
            Swal.fire({
                title: "Are you sure?",
                text: "Do you really want to delete this field?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    fieldContainer.remove();
                    updateQuestionNumbers();
                }
            });
        });

            // Update "New Question" text on label input
        fieldContainer.find(".kz-field-label-input").on("input", function () {
            const newLabel = $(this).val() || "New Question";
            fieldContainer.find(".kz-toggle-collapse").text(`Q${fieldContainer.index() + 1}: ${newLabel}`);
        });

        fieldContainer.find(".kz-field-type-selector").on("change", function () {
            handleFieldTypeChange($(this), fieldContainer);
        });
    }

    function initializeGlobalEventListeners() {
        addFieldButton.on("click", addNewField);
    }

    function updateQuestionNumbers() {
        fieldsContainer.find(".kz-field-container").each(function (index) {
            const order = index + 1; // Calculate the field order
            const questionLabel = $(this).find(".kz-toggle-collapse");
            const currentLabel = questionLabel.text().split(": ")[1] || "New Question";
    
            // Update the question label
            questionLabel.text(`Q${order}: ${currentLabel}`);
    
            // Update the field order hidden input
            $(this).find(".kz-field-order-input").val(order);
        });
    }
    

    init();
});
