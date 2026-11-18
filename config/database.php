
<?php

$host = 'localhost';
$dbname = 'elck_kisumu';
$username = 'root';
$password = '';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    error_log($e->getMessage());

    die("Database connection failed. Please check your configuration.");

}


/*
INSERT INTO gallery (title, image, description)
VALUES
(
    'Sunday Worship',
    'sunday-worship.jpg',
    'Moments of worship and praise during a Sunday service.'
),
(
    'Church Fellowship',
    'church-fellowship.jpg',
    'Members sharing fellowship and encouraging one another.'
),
(
    'Church Celebration',
    'church-celebration.jpg',
    'Celebrating together as one church family.'
);
*/
/*
UPDATE church_info
SET address = 'Official Church Address, Kisumu',
    phone = 'OFFICIAL_PHONE_NUMBER',
    email = 'OFFICIAL_EMAIL_ADDRESS'
WHERE id = 1;
*/