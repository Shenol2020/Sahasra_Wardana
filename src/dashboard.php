<?php
require_once 'config.php';
requireLogin();


if (isUserType('buyer')) {
    include 'buyer_dashboard.php';
} else {
    include 'farmer_dashboard.php';
}
?>