<?php
$layout = file_get_contents('frontend/src/components/Layout.jsx');
$layout = str_replace(
    "['super_admin', 'finance_admin', 'inventory_admin', 'catalog_admin', 'orders_admin', 'staff']",
    "['admin', 'super_admin', 'finance_admin', 'inventory_admin', 'catalog_admin', 'orders_admin', 'staff']",
    $layout
);
$layout = str_replace(
    "['super_admin', 'inventory_admin', 'catalog_admin']",
    "['admin', 'super_admin', 'inventory_admin', 'catalog_admin']",
    $layout
);
$layout = str_replace(
    "['super_admin', 'orders_admin']",
    "['admin', 'super_admin', 'orders_admin']",
    $layout
);
$layout = str_replace(
    "['super_admin', 'finance_admin']",
    "['admin', 'super_admin', 'finance_admin']",
    $layout
);
$layout = str_replace(
    "['super_admin', 'inventory_admin']",
    "['admin', 'super_admin', 'inventory_admin']",
    $layout
);
file_put_contents('frontend/src/components/Layout.jsx', $layout);
echo "Updated Layout.jsx\n";

$app = file_get_contents('frontend/src/App.jsx');
$app = str_replace(
    "['super_admin', 'inventory_admin', 'catalog_admin']",
    "['admin', 'super_admin', 'inventory_admin', 'catalog_admin']",
    $app
);
$app = str_replace(
    "['super_admin', 'orders_admin']",
    "['admin', 'super_admin', 'orders_admin']",
    $app
);
$app = str_replace(
    "['super_admin', 'finance_admin']",
    "['admin', 'super_admin', 'finance_admin']",
    $app
);
$app = str_replace(
    "['super_admin', 'inventory_admin']",
    "['admin', 'super_admin', 'inventory_admin']",
    $app
);
file_put_contents('frontend/src/App.jsx', $app);
echo "Updated App.jsx\n";
