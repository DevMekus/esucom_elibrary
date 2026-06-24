<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Dynamic Title -->
    <title><?php echo isset($metaTitle) ? $metaTitle : BRAND_NAME; ?></title>
    <meta name="theme-color" content="rgb(213,29,40)" />
    <meta name="msapplication-navbutton-color" content="rgb(213,29,40)">
    <!-- Dynamic Meta Description -->
    <meta name="description" content="<?php echo isset($metaDescription) ? $metaDescription : TAG; ?>">

    <!-- Dynamic Meta Keywords -->
    <meta name="keywords" content="<?php echo isset($metaKeywords) ? $metaKeywords : 'Pizza, Shawarma, Drinks, Desserts'; ?>">

    <!-- Dynamic Open Graph Meta Tags (Optional for Social Media Sharing) -->
    <meta property="og:title" content="<?php echo isset($metaTitle) ? $metaTitle : BRAND_NAME; ?>" />
    <meta property="og:description" content="<?php echo isset($metaDescription) ? $metaDescription : TAG ?>" />
    <meta property="og:image" content="<?php echo BASE_URL; ?>assets/images/logo_white.png" />

    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" />

    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="<?php echo BRAND_NAME; ?>" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo isset($metaTitle) ? $metaTitle : BRAND_NAME; ?>" />
    <meta name="twitter:description" content="<?php echo isset($metaDescription) ? $metaDescription : TAG; ?>" />
    <meta name="twitter:image" content="<?php echo BASE_URL; ?>assets/images/logo_white.png" />


    <link rel="icon" href="<?php echo BASE_URL; ?>assets/images/favicon.jpg" type="image/png">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>assets/images/favicon.jpg" type="image/x-icon">


    <!-- Other meta tags you may need -->
    <meta name="robots" content="index, follow">

    <!-- Links to stylesheets and fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/styles/main.css" />

   

</head>