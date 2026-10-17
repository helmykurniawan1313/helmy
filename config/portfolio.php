<?php

// Language-neutral portfolio data. Translatable text lives in
// lang/{id,en}/portfolio.php.

return [
    'name' => 'Helmy Kurniawan',
    'phone' => '+62 899 3704 720',
    'whatsapp' => '628993704720',
    'email' => 'helmykurniawan1313@gmail.com',
    'website' => 'https://helmy.my.id',
    'photo' => 'images/helmy.jpeg',
    'career_start' => 2017,
    'gpa' => '3.72',

    // locale => [route name, label, og:locale]
    'locales' => [
        'en' => ['route' => 'home', 'label' => 'EN', 'name' => 'English', 'og' => 'en_US'],
        'id' => ['route' => 'home.id', 'label' => 'ID', 'name' => 'Bahasa Indonesia', 'og' => 'id_ID'],
    ],
];
