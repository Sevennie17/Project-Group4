<?php
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

/* =====================================================
   SESSION
===================================================== */

/* =====================================================
   DEFAULT BRANDS
===================================================== */

if (!isset($_SESSION['brands'])) {

    $_SESSION['brands'] = [

        [
            'name'     => 'Tiffany',
            'country'  => 'USA',
            'website'  => 'tiffany.com',
            'products' => 15
        ],

        [
            'name'     => 'Cartier',
            'country'  => 'France',
            'website'  => 'cartier.com',
            'products' => 2
        ],

        [
            'name'     => 'Rolex',
            'country'  => 'Switzerland',
            'website'  => 'rolex.com',
            'products' => 3
        ]

    ];
}


/* =====================================================
   ADD / EDIT
   MUST BE BEFORE HEADER
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    $name = trim($_POST['brand_name'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $website = trim($_POST['website'] ?? '');


    /* =================================================
       ADD BRAND
    ================================================= */

    if ($action === 'add') {

        if (
            $name !== '' &&
            $country !== '' &&
            $website !== ''
        ) {

            $_SESSION['brands'][] = [

                'name'     => $name,
                'country'  => $country,
                'website'  => $website,
                'products' => 0

            ];
        }
    }


    /* =================================================
       EDIT BRAND
    ================================================= */

    elseif ($action === 'edit') {

        $index = isset($_POST['index'])
            ? (int) $_POST['index']
            : -1;


        if (
            isset($_SESSION['brands'][$index]) &&
            $name !== '' &&
            $country !== '' &&
            $website !== ''
        ) {

            $_SESSION['brands'][$index]['name'] =
                $name;

            $_SESSION['brands'][$index]['country'] =
                $country;

            $_SESSION['brands'][$index]['website'] =
                $website;
        }
    }


    /* =================================================
       REDIRECT
    ================================================= */

    header(
        'Location: ' .
        strtok($_SERVER['REQUEST_URI'], '?')
    );

    exit;
}


/* =====================================================
   DELETE
===================================================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    isset($_GET['delete'])
) {

    $index = (int) $_GET['delete'];


    if (isset($_SESSION['brands'][$index])) {

        array_splice(
            $_SESSION['brands'],
            $index,
            1
        );
    }


    header(
        'Location: ' .
        strtok($_SERVER['REQUEST_URI'], '?')
    );

    exit;
}


/* =====================================================
   GET BRANDS
===================================================== */

$brands = $_SESSION['brands'];


/* =====================================================
   HEADER
===================================================== */

include __DIR__ . '/../includes/header.php';

?>


<link
    rel="stylesheet"  href="./css/brand.css"
>


<!-- =====================================================
     BRANDS PAGE
===================================================== -->

<div class="brands-page">


    <!-- PAGE TITLE -->

    <div class="page-title">

    </div>


    <!-- =================================================
         BRAND CONTAINER
    ================================================== -->

    <div class="brands-container">


        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="brands-header">

            <div class="brands-header-text">

                <h2>
                    Brands <span>Management</span>
                </h2>

                <p>
                    Manage luxury brand partners
                </p>

            </div>


            <button
                type="button"
                class="add-brand-btn"
                id="openBrandModal"
            >

                + Add Brand

            </button>

        </div>


        <!-- =================================================
             TABLE CARD
        ================================================== -->

        <div class="table-card">


            <!-- TABLE TITLE -->

            <div class="table-title">

                Jewelry Brands

            </div>


            <!-- TABLE -->

            <div class="brands-table-wrapper">

                <table class="brands-table">


                    <!-- HEADER -->

                    <thead>

                        <tr>

                            <th>
                                Brand
                            </th>

                            <th>
                                Country
                            </th>

                            <th>
                                Website
                            </th>

                            <th>
                                Products
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- BODY -->

                    <tbody>


                        <?php if (empty($brands)): ?>


                            <tr>

                                <td
                                    colspan="5"
                                    class="no-data"
                                >

                                    No brands found.

                                </td>

                            </tr>


                        <?php else: ?>


                            <?php foreach (
                                $brands
                                as $index => $brand
                            ): ?>


                                <tr>


                                    <!-- BRAND -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $brand['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <!-- COUNTRY -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $brand['country'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <!-- WEBSITE -->

                                    <td>

                                        <?php

                                        $website =
                                            trim(
                                                $brand['website']
                                            );


                                        $websiteUrl =
                                            preg_match(
                                                '/^https?:\/\//i',
                                                $website
                                            )
                                            ? $website
                                            : 'https://' . $website;

                                        ?>


                                        <a
                                            href="<?= htmlspecialchars(
                                                $websiteUrl,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            class="website-link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >

                                            <?= htmlspecialchars(
                                                $website,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </a>

                                    </td>


                                    <!-- PRODUCTS -->

                                    <td>

                                        <?= (int)
                                            $brand['products']
                                        ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td>

                                        <div
                                            class="action-buttons"
                                        >


                                            <!-- EDIT -->

                                            <button
                                                type="button"
                                                class="btn-edit edit-brand"
                                                data-index="<?= $index ?>"
                                                data-name="<?= htmlspecialchars(
                                                    $brand['name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                data-country="<?= htmlspecialchars(
                                                    $brand['country'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                data-website="<?= htmlspecialchars(
                                                    $brand['website'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >

                                                Edit

                                            </button>


                                            <!-- DELETE -->

                                            <a
                                                href="?delete=<?= $index ?>"
                                                class="btn-delete"
                                                onclick="
                                                    return confirm(
                                                        'Delete this brand?'
                                                    );
                                                "
                                            >

                                                Delete

                                            </a>


                                        </div>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     ADD / EDIT MODAL
===================================================== -->

<div
    class="brand-modal-overlay hidden"
    id="brandModal"
>


    <div class="brand-modal">


        <!-- CLOSE -->

        <button
            type="button"
            class="modal-close"
            id="closeBrandModal"
        >

            &times;

        </button>


        <!-- FORM -->

        <form
            method="POST"
            action=""
            class="brand-form"
            id="brandForm"
        >


            <!-- ACTION -->

            <input
                type="hidden"
                name="action"
                id="brandAction"
                value="add"
            >


            <!-- INDEX -->

            <input
                type="hidden"
                name="index"
                id="brandIndex"
                value=""
            >


            <!-- NAME -->

            <input
                type="text"
                name="brand_name"
                id="brandName"
                placeholder="Brand name"
                autocomplete="off"
                required
            >


            <!-- COUNTRY -->

            <input
                type="text"
                name="country"
                id="brandCountry"
                placeholder="Country name"
                autocomplete="off"
                required
            >


            <!-- WEBSITE -->

            <input
                type="text"
                name="website"
                id="brandWebsite"
                placeholder="Website (e.g. brand.com)"
                autocomplete="off"
                required
            >


            <!-- BUTTONS -->

            <div class="modal-actions">


                <button
                    type="button"
                    class="cancel-btn"
                    id="cancelBrandModal"
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


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script src="brand.js"></script>


<?php

include __DIR__ . '/../includes/footer.php';

?>
