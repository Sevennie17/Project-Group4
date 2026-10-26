
<?php include __DIR__ . '/../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Categories Management</title>

    <!-- CSS -->
    <link rel="stylesheet" href="./css/category.css">
</head>

<body>

    <!-- =========================
         MAIN PAGE
    ========================== -->

    <div class="category-page">

        <!-- =========================
             CATEGORY MAIN CARD
        ========================== -->

        <div class="category-card">

            <!-- Header -->
            <div class="category-header">

                <div class="category-header-text">
                    <h2>Categories Management</h2>
                    <p>Organize your jewelry collections</p>
                </div>

                <button
                    type="button"
                    class="add-btn"
                    onclick="openModal()">
                    + Add Category
                </button>

            </div>


            <!-- =========================
                 TABLE SECTION
            ========================== -->

            <div class="category-content">

                <h3>Category</h3>

                <div class="table-wrapper">

                    <table class="category-table">

                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>DESCRIPTION</th>
                                <th>PRODUCT</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>

                        <tbody>

                            <!-- Row 1 -->
                            <tr>
                                <td>Rings</td>

                                <td>
                                    Engagement, wedding, and fashion rings
                                </td>

                                <td>15</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Rings', 'Engagement, wedding, and fashion rings')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Row 2 -->
                            <tr>
                                <td>Rings</td>

                                <td>
                                    Engagement, wedding, and fashion rings
                                </td>

                                <td>15</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Rings', 'Engagement, wedding, and fashion rings')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Row 3 -->
                            <tr>
                                <td>Necklaces</td>

                                <td>
                                    Chains, pendants, and statement pieces
                                </td>

                                <td>2</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Necklaces', 'Chains, pendants, and statement pieces')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Row 4 -->
                            <tr>
                                <td>Bracelets</td>

                                <td>
                                    Tennis, bangles, and charm bracelets
                                </td>

                                <td>2</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Bracelets', 'Tennis, bangles, and charm bracelets')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Row 5 -->
                            <tr>
                                <td>Earrings</td>

                                <td>
                                    Studs, drops, and hoops
                                </td>

                                <td>1</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Earrings', 'Studs, drops, and hoops')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Row 6 -->
                            <tr>
                                <td>Pendants</td>

                                <td>
                                    Gemstone and designer pendants
                                </td>

                                <td>1</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Pendants', 'Gemstone and designer pendants')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Row 7 -->
                            <tr>
                                <td>Bangles</td>

                                <td>
                                    Traditional and modern bangles
                                </td>

                                <td>1</td>

                                <td class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-btn"
                                        onclick="editCategory('Bangles', 'Traditional and modern bangles')">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="deleteCategory(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================
         ADD / EDIT MODAL
    ========================== -->

    <div
        class="modal-overlay"
        id="categoryModal">

        <div class="modal">

            <!-- Close -->
            <button
                type="button"
                class="close-btn"
                onclick="closeModal()">
                &times;
            </button>


            <!-- Modal form -->
            <form
                id="categoryForm"
                onsubmit="saveCategory(event)">

                <input
                    type="text"
                    id="categoryName"
                    name="category_name"
                    placeholder="Category name"
                    autocomplete="off"
                    required>


                <input
                    type="text"
                    id="categoryDescription"
                    name="description"
                    placeholder="Description"
                    autocomplete="off"
                    required>


                <div class="modal-buttons">

                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeModal()">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="save-btn">
                        Save
                    </button>

                </div>

            </form>

        </div>

    </div>
<script src="category.js"></script>

   

</body>

</html>
<?php include __DIR__ . '/../includes/footer.php'; ?>
