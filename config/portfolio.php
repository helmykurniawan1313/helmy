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
        'id' => ['route' => 'home', 'label' => 'ID', 'name' => 'Bahasa Indonesia', 'og' => 'id_ID'],
        'en' => ['route' => 'home.en', 'label' => 'EN', 'name' => 'English', 'og' => 'en_US'],
    ],
];
