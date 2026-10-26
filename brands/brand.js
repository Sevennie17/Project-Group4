/* =====================================================
   ELEMENTS
===================================================== */

const brandModal =
    document.getElementById('brandModal');

const openBrandModal =
    document.getElementById('openBrandModal');

const closeBrandModal =
    document.getElementById('closeBrandModal');

const cancelBrandModal =
    document.getElementById('cancelBrandModal');

const brandAction =
    document.getElementById('brandAction');

const brandIndex =
    document.getElementById('brandIndex');

const brandName =
    document.getElementById('brandName');

const brandCountry =
    document.getElementById('brandCountry');

const brandWebsite =
    document.getElementById('brandWebsite');


/* =====================================================
   OPEN ADD MODAL
===================================================== */

openBrandModal.addEventListener(
    'click',
    function () {

        brandAction.value = 'add';

        brandIndex.value = '';

        brandName.value = '';

        brandCountry.value = '';

        brandWebsite.value = '';

        brandModal.classList.remove('hidden');

        brandName.focus();
    }
);


/* =====================================================
   CLOSE MODAL
===================================================== */

function closeBrandModalFunction() {

    brandModal.classList.add('hidden');
}


closeBrandModal.addEventListener(
    'click',
    closeBrandModalFunction
);


cancelBrandModal.addEventListener(
    'click',
    closeBrandModalFunction
);


/* =====================================================
   EDIT BRAND
===================================================== */

document
    .querySelectorAll('.edit-brand')
    .forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                brandAction.value =
                    'edit';

                brandIndex.value =
                    button.dataset.index;

                brandName.value =
                    button.dataset.name;

                brandCountry.value =
                    button.dataset.country;

                brandWebsite.value =
                    button.dataset.website;

                brandModal.classList.remove(
                    'hidden'
                );

                brandName.focus();
            }
        );

    });


/* =====================================================
   CLICK OUTSIDE MODAL
===================================================== */

brandModal.addEventListener(
    'click',
    function (event) {

        if (
            event.target === brandModal
        ) {

            closeBrandModalFunction();
        }

    }
);


/* =====================================================
   ESCAPE
===================================================== */

document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key === 'Escape' &&
            !brandModal.classList.contains('hidden')
        ) {

            closeBrandModalFunction();
        }

    }
);