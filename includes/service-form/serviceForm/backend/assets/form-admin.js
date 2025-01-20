jQuery(document).ready(function ($) {
    const addFieldButton = $("#kz-add-new-field");
    const fieldsContainer = $("#kz-fields-container");

    // Initialize the script
    function init() {
        enableSortableFields();
        attachEventListeners();
    }

    // Enable sortable functionality
    function enableSortableFields() {
        fieldsContainer.sortable({
            handle: ".kz-drag-handle",
            placeholder: "kz-sortable-placeholder",
            stop: function () {
                updateQuestionNumbers();
            }
        });
    }

    // Add a new field
    function addNewField() {
        const uniqueId = FormMetaData.formId + "-" + new Date().getTime(); // Unique ID based on form ID and timestamp

        const fieldContainer = createFieldContainer(uniqueId);
        fieldsContainer.append(fieldContainer);

        attachFieldEventListeners(fieldContainer);
        updateQuestionNumbers();
    }

    // Create a new field container
    function createFieldContainer(uniqueId) {
        return $(`
            <div class="kz-field-container" id="field-${uniqueId}">
                <div class="kz-field-header">
                    <span class="kz-drag-handle">☰</span>
                    <span class="kz-toggle-collapse">Q${$(".kz-field-container").length + 1}: New Question</span>
                    <button class="kz-remove-field">✖</button>
                </div>
                <div class="kz-field-body">
                    <label>Input Field Label:</label>
                    <input type="text" placeholder="Enter field label here" class="kz-field-label-input">
                    
                    <label>Field Type:</label>
                    <select>
                        <option value="text_input">Text Input</option>
                        <option value="number_input">Number Input</option>
                        <option value="radio">Radio Button</option>
                        <option value="checkbox_simple">Simple Checkbox</option>
                        <option value="checkbox_with_image">Checkbox with Image</option>
                        <option value="textarea">Text Area</option>
                    </select>

                    <label>Is Required:</label>
                    <div class="kz-radio-group">
                        <label><input type="radio" name="required-${uniqueId}" value="yes"> Yes</label>
                        <label><input type="radio" name="required-${uniqueId}" value="no" checked> No</label>
                    </div>
                </div>
            </div>
        `);
    }

    // Update question numbers based on the field order
    function updateQuestionNumbers() {
        fieldsContainer.find(".kz-field-container").each(function (index) {
            const questionLabel = $(this).find(".kz-toggle-collapse");
            const currentLabel = questionLabel.text().split(": ")[1] || "New Question";
            questionLabel.text(`Q${index + 1}: ${currentLabel}`);
        });
    }

    // Attach event listeners for the field container
    function attachFieldEventListeners(fieldContainer) {
        // Toggle collapse
        fieldContainer.find(".kz-field-header").on("click", function (e) {
            if ($(e.target).hasClass("kz-remove-field") || $(e.target).hasClass("kz-drag-handle")) return;
            $(this).next(".kz-field-body").slideToggle();
        });

        // Remove field
        fieldContainer.find(".kz-remove-field").on("click", function (e) {
            e.stopPropagation(); // Prevent the collapse toggle
            Swal.fire({
                title: "Are you sure?",
                text: "Do you really want to delete this field?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    fieldContainer.remove();
                    updateQuestionNumbers();
                }
            });
        });

        // Update label dynamically
        fieldContainer.find(".kz-field-label-input").on("input", function () {
            const newLabel = $(this).val() || "New Question";
            fieldContainer.find(".kz-toggle-collapse").text(`Q${fieldContainer.index() + 1}: ${newLabel}`);
        });
    }

    // Attach global event listeners
    function attachEventListeners() {
        addFieldButton.on("click", addNewField);
    }

    // Initialize
    init();
});
