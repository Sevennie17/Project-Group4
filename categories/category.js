
        let editingRow = null;


        // Open modal for adding
        function openModal() {

            editingRow = null;

            document.getElementById("categoryForm").reset();

            document.getElementById("categoryModal").style.display = "flex";

            document.getElementById("categoryName").focus();
        }


        // Close modal
        function closeModal() {

            document.getElementById("categoryModal").style.display = "none";

            document.getElementById("categoryForm").reset();

            editingRow = null;
        }


        // Edit category
        function editCategory(category, description) {

            const rows = document.querySelectorAll(
                ".category-table tbody tr"
            );

            rows.forEach(function(row) {

                const name = row.children[0].textContent.trim();

                const desc = row.children[1].textContent.trim();

                if (
                    name === category &&
                    desc === description
                ) {
                    editingRow = row;
                }

            });


            document.getElementById("categoryName").value = category;

            document.getElementById("categoryDescription").value = description;

            document.getElementById("categoryModal").style.display = "flex";

            document.getElementById("categoryName").focus();
        }


        // Save category
        function saveCategory(event) {

            event.preventDefault();

            const name =
                document.getElementById("categoryName").value.trim();

            const description =
                document.getElementById("categoryDescription").value.trim();


            if (name === "" || description === "") {
                return;
            }


            // Editing existing row
            if (editingRow !== null) {

                editingRow.children[0].textContent = name;

                editingRow.children[1].textContent = description;

            }

            // Adding new row
            else {

                const tableBody =
                    document.querySelector(".category-table tbody");


                const row =
                    document.createElement("tr");


                row.innerHTML = `

                    <td>${escapeHTML(name)}</td>

                    <td>${escapeHTML(description)}</td>

                    <td>0</td>

                    <td class="action-buttons">

                        <button
                            type="button"
                            class="edit-btn"
                            onclick="editCategory(
                                '${escapeJS(name)}',
                                '${escapeJS(description)}'
                            )">
                            Edit
                        </button>

                        <button
                            type="button"
                            class="delete-btn"
                            onclick="deleteCategory(this)">
                            Delete
                        </button>

                    </td>

                `;


                tableBody.appendChild(row);
            }


            closeModal();
        }


        // Delete category
        function deleteCategory(button) {

            const row = button.closest("tr");

            const category =
                row.children[0].textContent.trim();


            const confirmed =
                confirm(
                    "Are you sure you want to delete " +
                    category +
                    "?"
                );


            if (confirmed) {

                row.remove();

            }
        }


        // Prevent HTML injection
        function escapeHTML(value) {

            return value
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }


        // Escape JavaScript strings
        function escapeJS(value) {

            return value
                .replace(/\\/g, "\\\\")
                .replace(/'/g, "\\'")
                .replace(/"/g, '\\"');
        }


        // Close when clicking outside modal
        window.addEventListener("click", function(event) {

            const modal =
                document.getElementById("categoryModal");

            if (event.target === modal) {

                closeModal();

            }

        });


        // Close with ESC key
        document.addEventListener("keydown", function(event) {

            if (event.key === "Escape") {

                closeModal();

            }

        });

    