jQuery(document).ready(function ($) {
    const addFieldButton = $("#kz-add-new-field");
    const fieldsContainer = $("#kz-fields-container");

    function init() {
        enableSortableFields();
        initializeEventListeners(); // Centralized event listeners
    }

    function enableSortableFields() {
        fieldsContainer.sortable({
            handle: ".kz-drag-handle",
            placeholder: "kz-sortable-placeholder",
            stop: updateQuestionNumbers,
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
                    <input type="text" placeholder="Enter field label here" class="kz-field-label-input">
                    
                    <label>Field Type:</label>
                    <select class="kz-field-type-selector">
                        <option value="text_input">Text Input</option>
                        <option value="number_input">Number Input</option>
                        <option value="radio">Radio Button</option>
                        <option value="checkbox_simple">Simple Checkbox</option>
                        <option value="textarea">Text Area</option>
                    </select>

                    <label>Is Required:</label>
                    <div class="kz-radio-group">
                        <label><input type="radio" name="required-${uniqueId}" value="yes"> Yes</label>
                        <label><input type="radio" name="required-${uniqueId}" value="no" checked> No</label>
                    </div>
                    
                    <div class="kz-dynamic-options"></div>
                </div>
            </div>`
        );
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
            <label>Placeholder:</label>
            <input type="text" placeholder="Enter placeholder text">
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
        if (type === "number_input") {
            dynamicOptions.find(".kz-min-value, .kz-max-value").on("input", function () {
                const min = dynamicOptions.find(".kz-min-value").val() || "N/A";
                const max = dynamicOptions.find(".kz-max-value").val() || "N/A";
                dynamicOptions.find(".kz-range-message").text(`Selected range: ${min} to ${max}`);
            });
        }

        if (type === "checkbox_simple") {
            dynamicOptions.find(".kz-add-checkbox").on("click", function () {
                dynamicOptions.find(".kz-checkbox-list").append(`
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
                attachEditableEvents(dynamicOptions.find(".kz-checkbox-item").last());
            });
        }

        if (type === "radio") {
            dynamicOptions.find(".kz-add-radio").on("click", function () {
                dynamicOptions.find(".kz-radio-list").append(`
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
                attachEditableEvents(dynamicOptions.find(".kz-radio-item").last());
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

        fieldContainer.find(".kz-field-label-input").on("input", function () {
            const newLabel = $(this).val() || "New Question";
            fieldContainer.find(".kz-toggle-collapse").text(`Q${fieldContainer.index() + 1}: ${newLabel}`);
        });

        fieldContainer.find(".kz-field-type-selector").on("change", function () {
            handleFieldTypeChange($(this), fieldContainer);
        });
    }

    function initializeEventListeners() {
        addFieldButton.on("click", addNewField);
    }

    function updateQuestionNumbers() {
        fieldsContainer.find(".kz-field-container").each(function (index) {
            const questionLabel = $(this).find(".kz-toggle-collapse");
            const currentLabel = questionLabel.text().split(": ")[1] || "New Question";
            questionLabel.text(`Q${index + 1}: ${currentLabel}`);
        });
    }

    init();
});
