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

        // Handle dynamic options for checkboxes and radio buttons
        const fieldType = fieldContainer.find(".kz-field-type-selector").val();
        const dynamicOptions = fieldContainer.find(".kz-dynamic-options");
        const uniqueId = fieldContainer.attr("id");

        if (fieldType === "checkbox_simple" || fieldType === "radio") {
            attachDynamicEvents(dynamicOptions, fieldType, uniqueId);
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
                        <option value="number_input">Number Input</option>
                        <option value="radio">Radio Button</option>
                        <option value="checkbox_simple">Simple Checkbox</option>
                        <option value="textarea">Text Area</option>
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
    
        // Text Input
        if (type === "text_input") {
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
            number_input: generateNumberInputHTML,
            textarea: generateTextareaHTML,
            checkbox_simple: generateCheckboxHTML,
            radio: generateRadioHTML,
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
        if (type === "text_input") {
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
