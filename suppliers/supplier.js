
/* ==========================================
   GET MODAL
========================================== */

const supplierModal = document.getElementById('supplierModal');

const supplierForm = document.getElementById('supplierForm');

const supplierTable = document.getElementById('supplierTable');



/* ==========================================
   OPEN ADD MODAL
========================================== */

function openAddSupplierModal() {

    supplierForm.reset();

    document.getElementById('editRowIndex').value = '';

    supplierModal.classList.add('active');

}



/* ==========================================
   CLOSE MODAL
========================================== */

function closeSupplierModal() {

    supplierModal.classList.remove('active');

}



/* ==========================================
   EDIT SUPPLIER
========================================== */

function editSupplier(button) {

    const row = button.closest('tr');

    const cells = row.querySelectorAll('td');

    const rowIndex = row.rowIndex;

    /*
        Store row index.
        rowIndex includes the table header,
        so we subtract 1 when accessing tbody.
    */

    document.getElementById('editRowIndex').value = rowIndex;


    /*
        Fill modal with current values
    */

    document.getElementById('supplierName').value =
        cells[0].textContent.trim();

    document.getElementById('contactPerson').value =
        cells[1].textContent.trim();

    document.getElementById('phone').value =
        cells[2].textContent.trim();

    document.getElementById('email').value =
        cells[3].textContent.trim();

    document.getElementById('address').value =
        cells[4].textContent.trim();


    /*
        Open modal
    */

    supplierModal.classList.add('active');

}



/* ==========================================
   DELETE SUPPLIER
========================================== */

function deleteSupplier(button) {

    const row = button.closest('tr');

    const supplierName =
        row.cells[0].textContent.trim();


    const confirmed = confirm(
        'Are you sure you want to delete "' +
        supplierName +
        '"?'
    );


    if (!confirmed) {
        return;
    }


    /*
        Remove row
    */

    row.remove();

}



/* ==========================================
   SAVE SUPPLIER
========================================== */

supplierForm.addEventListener('submit', function(event) {

    event.preventDefault();


    /* Get form values */

    const supplierName =
        document.getElementById('supplierName').value.trim();

    const contactPerson =
        document.getElementById('contactPerson').value.trim();

    const phone =
        document.getElementById('phone').value.trim();

    const email =
        document.getElementById('email').value.trim();

    const address =
        document.getElementById('address').value.trim();


    const editRowIndex =
        document.getElementById('editRowIndex').value;


    /*
        ==========================================
        EDIT EXISTING SUPPLIER
        ==========================================
    */

    if (editRowIndex !== '') {

        const tbody =
            supplierTable.querySelector('tbody');

        const row =
            tbody.rows[parseInt(editRowIndex) - 1];


        if (row) {

            row.cells[0].textContent = supplierName;

            row.cells[1].textContent = contactPerson;

            row.cells[2].textContent = phone;

            row.cells[3].textContent = email;

            row.cells[4].textContent = address;

        }

    }


    /*
        ==========================================
        ADD NEW SUPPLIER
        ==========================================
    */

    else {

        const tbody =
            supplierTable.querySelector('tbody');


        const newRow =
            tbody.insertRow();


        newRow.innerHTML = `

            <td>${escapeHtml(supplierName)}</td>

            <td>${escapeHtml(contactPerson)}</td>

            <td>${escapeHtml(phone)}</td>

            <td>${escapeHtml(email)}</td>

            <td>${escapeHtml(address)}</td>

            <td>0</td>

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

        `;

    }


    /*
        Close modal
    */

    closeSupplierModal();


    /*
        Reset form
    */

    supplierForm.reset();

    document.getElementById('editRowIndex').value = '';

});



/* ==========================================
   ESCAPE HTML
   Prevent HTML from being inserted into
   the table when adding a supplier.
========================================== */

function escapeHtml(value) {

    const div = document.createElement('div');

    div.textContent = value;

    return div.innerHTML;

}



/* ==========================================
   CLICK OUTSIDE MODAL
========================================== */

supplierModal.addEventListener(
    'click',
    function(event) {

        if (event.target === supplierModal) {

            closeSupplierModal();

        }

    }
);



/* ==========================================
   ESC KEY CLOSE MODAL
========================================== */

document.addEventListener(
    'keydown',
    function(event) {

        if (event.key === 'Escape') {

            closeSupplierModal();

        }

    }
);