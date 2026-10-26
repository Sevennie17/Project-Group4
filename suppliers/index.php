<?php include __DIR__ . '/../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier</title>
    <link rel="stylesheet" href="./css/supplier.css">
</head>
<body>
    <div class="supplier-page">



    <!-- Supplier card -->
    <div class="supplier-card">
        <!-- Header -->
        <div class="supplier-header">

            <div>
                <h2>
                    Suppliers <span>Management</span>
                </h2>

                <p>Track your jewelry suppliers</p>
            </div>

            <button
                type="button"
                class="add-btn"
                onclick="openAddSupplierModal()"
            >
                + Add supplier
            </button>

        </div>


        <!-- Supplier Directory -->
        <div class="supplier-table-wrapper">

            <div class="table-title">
                Supplier Directory
            </div>


            <table class="supplier-table" id="supplierTable">

                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Purchases</th>
                        <th>Actions</th>
                    </tr>
                </thead>


                <tbody>

                    <tr>
                        <td>Goldcraft Suppliers</td>
                        <td>John Smith</td>
                        <td>+1 234 567 8901</td>
                        <td>john@goldcraft.com</td>
                        <td>123 Jewel St, New York</td>
                        <td>45</td>

                        <td class="actions">
                            <button
                                type="button"
                                class="edit-btn"
                                onclick="editSupplier(this)"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="delete-btn"
                                onclick="deleteSupplier(this)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>


                    <tr>
                        <td>Diamond Direct</td>
                        <td>Sarah Johnson</td>
                        <td>+1 234 567 8901</td>
                        <td>sarah@diamonddirect.com</td>
                        <td>458 Gem Ave, Los Angeles</td>
                        <td>30</td>

                        <td class="actions">
                            <button
                                type="button"
                                class="edit-btn"
                                onclick="editSupplier(this)"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="delete-btn"
                                onclick="deleteSupplier(this)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>


                    <tr>
                        <td>Precious Metals Inc</td>
                        <td>Mike Brown</td>
                        <td>+1 234 567 8901</td>
                        <td>mike@preciousmetals.com</td>
                        <td>789 Gold Rd, Chicago</td>
                        <td>55</td>

                        <td class="actions">
                            <button
                                type="button"
                                class="edit-btn"
                                onclick="editSupplier(this)"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="delete-btn"
                                onclick="deleteSupplier(this)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>


                    <tr>
                        <td>Gemstone World</td>
                        <td>Lisa Davis</td>
                        <td>+1 234 567 8901</td>
                        <td>lisa@gemstoneworld.com</td>
                        <td>321 Crystal Ln, Miami</td>
                        <td>23</td>

                        <td class="actions">
                            <button
                                type="button"
                                class="edit-btn"
                                onclick="editSupplier(this)"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="delete-btn"
                                onclick="deleteSupplier(this)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>



<!-- ==========================================
     ADD / EDIT SUPPLIER MODAL
========================================== -->

<div
    class="modal-overlay"
    id="supplierModal"
>

    <div class="supplier-modal">

        <!-- Close -->
        <button
            type="button"
            class="close-modal"
            onclick="closeSupplierModal()"
        >
            &times;
        </button>


        <!-- Form -->
        <form id="supplierForm">

            <!-- Hidden field to know edit/add -->
            <input
                type="hidden"
                id="editRowIndex"
                value=""
            >


            <input
                type="text"
                id="supplierName"
                name="supplier_name"
                placeholder="Supplier Name"
                required
            >

            <input
                type="text"
                id="contactPerson"
                name="contact_person"
                placeholder="Contact Person"
                required
            >

            <input
                type="text"
                id="phone"
                name="phone"
                placeholder="Phone"
                required
            >

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Email"
                required
            >

            <input
                type="text"
                id="address"
                name="address"
                placeholder="Address"
                required
            >


            <!-- Buttons -->
            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeSupplierModal()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="save-btn"
                >
                    Save
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>



<script src="supplier.js"></script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
