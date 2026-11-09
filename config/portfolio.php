<?php

// Language-neutral portfolio data. Translatable text lives in
// lang/{id,en}/portfolio.php.

return [
    'name' => 'Helmy Kurniawan',
    'phone' => '+62 899 3704 720',
    'whatsapp' => '628993704720',
    'email' => 'helmykurniawan1313@gmail.com',
    'website' => 'https://helmy.my.id',
    'linkedin' => null,
    'instagram' => null,
    'instagram_business' => null,
    'instagram_business_name' => null,
    'photo' => 'images/helmy.jpeg',
    'career_start' => 2017,
    'gpa' => '3.72',

    // Publications: journal papers, conference papers, articles, thesis. Leave empty to hide the section.
    'publications' => [],

    // Digital badges; set 'url' to the verification link to show a "Show credential" link
    'badges' => [
        ['title' => 'Gen AI Agents: Transform Your Organization', 'issuer' => 'Google', 'date' => '08/2026', 'credential_id' => '27278920', 'url' => null],
        ['title' => 'Belajar Dasar AI', 'issuer' => 'Dicoding Indonesia', 'date' => '12/2025', 'credential_id' => 'GRX5J17DKX0M', 'url' => null],
    ],

    // locale => [route name, label, og:locale]
    'locales' => [
        'en' => ['route' => 'home', 'label' => 'EN', 'name' => 'English', 'og' => 'en_US'],
        'id' => ['route' => 'home.id', 'label' => 'ID', 'name' => 'Bahasa Indonesia', 'og' => 'id_ID'],
    ],
];
