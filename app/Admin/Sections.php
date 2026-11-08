<?php

namespace App\Admin;

/**
 * Every section the admin can edit.
 *
 * A section has `fields` (single values, keyed by their data path) and/or a
 * `list` (a repeatable group of items stored at one data path). `translatable`
 * sections are edited once per language; the others are shared.
 *
 * Field types: text, textarea, number, email, url, select, bool, image,
 * lines (one entry per line), paragraphs (blank line between entries),
 * tags (comma separated).
 */
class Sections
{
    public static function all(): array
    {
        $levels = ['advanced' => 'Advanced', 'intermediate' => 'Intermediate', 'basic' => 'Basic'];
        $groups = ['infra' => 'Infrastructure & IT', 'dev' => 'Development', 'creative' => 'Creative & marketing'];

        return [
            'profile' => [
                'group' => 'Profile',
                'title' => 'Personal info',
                'icon' => 'person-badge',
                'translatable' => false,
                'description' => 'Name, contact details and the numbers shown on the site.',
                'fields' => [
                    ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['key' => 'phone', 'label' => 'Phone (display)', 'type' => 'text'],
                    ['key' => 'whatsapp', 'label' => 'WhatsApp number', 'type' => 'text', 'help' => 'Digits only with country code, e.g. 628993704720.'],
                    ['key' => 'website', 'label' => 'Website', 'type' => 'url'],
                    ['key' => 'career_start', 'label' => 'Career start year', 'type' => 'number', 'help' => 'Used for the "years of experience" figure.'],
                    ['key' => 'gpa', 'label' => 'GPA', 'type' => 'text'],
                    ['key' => 'photo', 'label' => 'Photo (sharing preview)', 'type' => 'image'],
                ],
            ],
            'seo' => [
                'group' => 'Profile',
                'title' => 'SEO',
                'icon' => 'search',
                'translatable' => true,
                'description' => 'Browser tab title and the description search engines show.',
                'fields' => [
                    ['key' => 'meta.title', 'label' => 'Page title', 'type' => 'text', 'required' => true],
                    ['key' => 'meta.description', 'label' => 'Meta description', 'type' => 'textarea', 'help' => 'Aim for 150–160 characters.'],
                ],
            ],

            'hero' => [
                'group' => 'Homepage',
                'title' => 'Hero',
                'icon' => 'stars',
                'translatable' => true,
                'description' => 'The first screen visitors see.',
                'fields' => [
                    ['key' => 'banner.hero_title', 'label' => 'Headline', 'type' => 'text', 'required' => true],
                    ['key' => 'banner.hero_title_accent', 'label' => 'Headline accent (blue line)', 'type' => 'text'],
                    ['key' => 'banner.hero_sub', 'label' => 'Intro sentence', 'type' => 'textarea'],
                    ['key' => 'role', 'label' => 'Job title', 'type' => 'text'],
                    ['key' => 'location', 'label' => 'Location', 'type' => 'text'],
                ],
            ],
            'feature' => [
                'group' => 'Homepage',
                'title' => 'Photo banner',
                'icon' => 'image',
                'translatable' => true,
                'description' => 'The full-width photo with a big sentence, right after the hero.',
                'fields' => [
                    ['key' => 'banner.feature_title', 'label' => 'Sentence on the photo', 'type' => 'textarea', 'required' => true],
                    ['key' => 'banner.feature_image', 'label' => 'Photo', 'type' => 'image', 'help' => 'Wide landscape photo, at least 2000 px wide. Darker photos keep the white text readable. Empty = the server-room photo.'],
                    ['key' => 'banner.feature_alt', 'label' => 'Photo description (for screen readers and Google)', 'type' => 'text'],
                ],
            ],
            'about' => [
                'group' => 'Homepage',
                'title' => 'About',
                'icon' => 'person-lines-fill',
                'translatable' => true,
                'fields' => [
                    ['key' => 'banner.about_section.title', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'banner.about_section.lead', 'label' => 'Lead sentence', 'type' => 'textarea'],
                    ['key' => 'banner.about_section.body', 'label' => 'Body', 'type' => 'paragraphs', 'help' => 'Leave an empty line between paragraphs.'],
                    ['key' => 'ui.principle_quote', 'label' => 'Quote', 'type' => 'text'],
                    ['key' => 'about', 'label' => 'Short summary', 'type' => 'textarea', 'help' => 'Also given to the AI assistant.'],
                ],
            ],
            'services' => [
                'group' => 'Homepage',
                'title' => 'Services',
                'icon' => 'grid',
                'translatable' => true,
                'fields' => [
                    ['key' => 'banner.services_title', 'label' => 'Section heading', 'type' => 'text'],
                    ['key' => 'banner.services_sub', 'label' => 'Section intro', 'type' => 'textarea'],
                ],
                'list' => [
                    'key' => 'services',
                    'item' => 'service',
                    'title' => 'title',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'text', 'help' => 'Phosphor icon class, e.g. ph-network (phosphoricons.com).'],
                        ['key' => 'desc', 'label' => 'Description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'contact' => [
                'group' => 'Homepage',
                'title' => 'Contact',
                'icon' => 'envelope',
                'translatable' => true,
                'fields' => [
                    ['key' => 'banner.cta_title', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'banner.cta_title_accent', 'label' => 'Heading accent', 'type' => 'text'],
                    ['key' => 'banner.cta_sub', 'label' => 'Intro sentence', 'type' => 'textarea'],
                ],
            ],

            'experiences' => [
                'group' => 'Career',
                'title' => 'Experience',
                'icon' => 'briefcase',
                'translatable' => true,
                'description' => 'Newest first. Roles at the same company are grouped automatically.',
                'list' => [
                    'key' => 'experiences',
                    'item' => 'role',
                    'title' => 'title',
                    'subtitle' => 'org',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Job title', 'type' => 'text', 'required' => true, 'col' => 6],
                        ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'col' => 3, 'help' => 'Full-time, Freelance…'],
                        ['key' => 'period', 'label' => 'Period', 'type' => 'text', 'col' => 3, 'help' => 'MM/YYYY - MM/YYYY'],
                        ['key' => 'org', 'label' => 'Company', 'type' => 'text', 'required' => true, 'col' => 9, 'help' => 'Use exactly the same name for every role at one company.'],
                        ['key' => 'current', 'label' => 'Current role', 'type' => 'bool', 'col' => 3],
                        ['key' => 'logo', 'label' => 'Company logo', 'type' => 'image', 'help' => 'Square PNG/WebP (about 128×128). Only needed on one role per company. Empty = initials.'],
                        ['key' => 'points', 'label' => 'Responsibilities', 'type' => 'lines', 'help' => 'One bullet per line.'],
                        ['key' => 'tags', 'label' => 'Tools', 'type' => 'tags', 'help' => 'Comma separated.'],
                    ],
                ],
            ],
            'skills' => [
                'group' => 'Career',
                'title' => 'Skills',
                'icon' => 'tools',
                'translatable' => true,
                'fields' => [
                    ['key' => 'skill_groups.infra', 'label' => 'Group 1 name', 'type' => 'text', 'col' => 4],
                    ['key' => 'skill_groups.dev', 'label' => 'Group 2 name', 'type' => 'text', 'col' => 4],
                    ['key' => 'skill_groups.creative', 'label' => 'Group 3 name', 'type' => 'text', 'col' => 4],
                    ['key' => 'skill_group_desc.infra', 'label' => 'Group 1 description', 'type' => 'textarea', 'col' => 4],
                    ['key' => 'skill_group_desc.dev', 'label' => 'Group 2 description', 'type' => 'textarea', 'col' => 4],
                    ['key' => 'skill_group_desc.creative', 'label' => 'Group 3 description', 'type' => 'textarea', 'col' => 4],
                ],
                'list' => [
                    'key' => 'skills',
                    'item' => 'skill',
                    'title' => 'name',
                    'fields' => [
                        ['key' => 'name', 'label' => 'Skill', 'type' => 'text', 'required' => true, 'col' => 6],
                        ['key' => 'level', 'label' => 'Level', 'type' => 'select', 'options' => $levels, 'col' => 3],
                        ['key' => 'group', 'label' => 'Group', 'type' => 'select', 'options' => $groups, 'col' => 3],
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'text', 'help' => 'Phosphor icon class, e.g. ph-code.'],
                    ],
                ],
            ],
            'projects' => [
                'group' => 'Career',
                'title' => 'Portfolio',
                'icon' => 'collection-play',
                'translatable' => true,
                'fields' => [
                    ['key' => 'banner.projects_title', 'label' => 'Section heading', 'type' => 'text'],
                    ['key' => 'banner.projects_sub', 'label' => 'Section intro', 'type' => 'textarea'],
                ],
                'list' => [
                    'key' => 'projects',
                    'item' => 'work',
                    'title' => 'title',
                    'subtitle' => 'client',
                    'fields' => [
                        ['key' => 'type', 'label' => 'Type', 'type' => 'select', 'col' => 4, 'options' => ['project' => 'Dev project', 'image' => 'Photo / design', 'video' => 'Video']],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'col' => 8],
                        ['key' => 'client', 'label' => 'Client / context', 'type' => 'text', 'col' => 6],
                        ['key' => 'category', 'label' => 'Category badge', 'type' => 'text', 'col' => 6, 'help' => 'e.g. Company Profile, Wedding, Product shoot.'],
                        ['key' => 'description', 'label' => 'Short description', 'type' => 'textarea', 'help' => 'One or two sentences. Shown when the item is opened.'],
                        ['key' => 'image', 'label' => 'Image / cover', 'type' => 'image', 'help' => 'Photo, screenshot or video thumbnail. Landscape, about 1200×800.'],
                        ['key' => 'video', 'label' => 'Video link', 'type' => 'url', 'col' => 6, 'help' => 'Videos only: YouTube, Vimeo or a direct .mp4 link.'],
                        ['key' => 'link', 'label' => 'Live site / repo link', 'type' => 'url', 'col' => 6, 'help' => 'Dev projects: https://… (optional).'],
                        ['key' => 'tech', 'label' => 'Tech stack / tools', 'type' => 'tags', 'help' => 'Comma separated, e.g. Laravel, MySQL, Bootstrap.'],
                    ],
                ],
            ],

            'education' => [
                'group' => 'Credentials',
                'title' => 'Education',
                'icon' => 'mortarboard',
                'translatable' => true,
                'list' => [
                    'key' => 'education',
                    'item' => 'school',
                    'title' => 'degree',
                    'subtitle' => 'school',
                    'fields' => [
                        ['key' => 'degree', 'label' => 'Degree / major', 'type' => 'text', 'required' => true, 'col' => 6],
                        ['key' => 'period', 'label' => 'Period', 'type' => 'text', 'col' => 6],
                        ['key' => 'school', 'label' => 'School', 'type' => 'text'],
                        ['key' => 'points', 'label' => 'Highlights', 'type' => 'lines', 'help' => 'One per line.'],
                    ],
                ],
            ],
            'certificates' => [
                'group' => 'Credentials',
                'title' => 'Certifications',
                'icon' => 'award',
                'translatable' => true,
                'list' => [
                    'key' => 'certificates',
                    'item' => 'certification',
                    'title' => 'title',
                    'subtitle' => 'issuer',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Certification', 'type' => 'text', 'required' => true],
                        ['key' => 'issuer', 'label' => 'Issuer (full name)', 'type' => 'text', 'col' => 6],
                        ['key' => 'short', 'label' => 'Issuer (short)', 'type' => 'text', 'col' => 3],
                        ['key' => 'date', 'label' => 'Date', 'type' => 'text', 'col' => 3, 'help' => 'MM/YYYY'],
                        ['key' => 'credential_id', 'label' => 'Credential ID', 'type' => 'text', 'col' => 4],
                        ['key' => 'url', 'label' => 'Certificate link', 'type' => 'url', 'col' => 8, 'help' => 'Verification page or PDF. Adds an arrow button on the card.'],
                        ['key' => 'logo', 'label' => 'Issuer logo', 'type' => 'image', 'help' => 'Square PNG/WebP, transparent background works best (about 128×128). Empty = initials.'],
                    ],
                ],
            ],
            'badges' => [
                'group' => 'Credentials',
                'title' => 'Badges',
                'icon' => 'patch-check',
                'translatable' => false,
                'list' => [
                    'key' => 'badges',
                    'item' => 'badge',
                    'title' => 'title',
                    'subtitle' => 'issuer',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Badge', 'type' => 'text', 'required' => true],
                        ['key' => 'issuer', 'label' => 'Issuer', 'type' => 'text', 'col' => 6],
                        ['key' => 'date', 'label' => 'Date', 'type' => 'text', 'col' => 3, 'help' => 'MM/YYYY'],
                        ['key' => 'credential_id', 'label' => 'Credential ID', 'type' => 'text', 'col' => 3],
                        ['key' => 'url', 'label' => 'Verification link', 'type' => 'url', 'help' => 'Adds an arrow button on the card.'],
                        ['key' => 'logo', 'label' => 'Issuer logo', 'type' => 'image', 'help' => 'Square PNG/WebP, transparent background works best (about 128×128). Empty = initials.'],
                    ],
                ],
            ],

            'publications' => [
                'group' => 'Credentials',
                'title' => 'Publications',
                'icon' => 'journal-text',
                'translatable' => false,
                'list' => [
                    'key' => 'publications',
                    'item' => 'publication',
                    'title' => 'title',
                    'subtitle' => 'venue',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                        ['key' => 'type', 'label' => 'Type', 'type' => 'select', 'col' => 4, 'options' => ['journal' => 'Journal paper', 'conference' => 'Conference paper', 'article' => 'Article', 'thesis' => 'Thesis', 'book' => 'Book / chapter']],
                        ['key' => 'year', 'label' => 'Year', 'type' => 'text', 'col' => 2, 'help' => 'e.g. 2022'],
                        ['key' => 'venue', 'label' => 'Published in', 'type' => 'text', 'col' => 6, 'help' => 'Journal, conference or website name, with volume/issue if any.'],
                        ['key' => 'authors', 'label' => 'Authors', 'type' => 'text', 'help' => 'e.g. Helmy Kurniawan, Co-author Name'],
                        ['key' => 'abstract', 'label' => 'Short summary', 'type' => 'textarea', 'help' => 'One or two sentences (optional).'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'help' => 'DOI, journal page or PDF. Shows a "Read" button.'],
                    ],
                ],
            ],

            'assistant' => [
                'group' => 'Assistant',
                'title' => 'AI assistant',
                'icon' => 'robot',
                'translatable' => true,
                'description' => 'Texts in the chat window. Answers always come from the content above.',
                'fields' => [
                    ['key' => 'assistant.title', 'label' => 'Chat title', 'type' => 'text'],
                    ['key' => 'assistant.greeting', 'label' => 'Greeting', 'type' => 'textarea'],
                    ['key' => 'assistant.suggestions', 'label' => 'Suggested questions', 'type' => 'lines', 'help' => 'One per line; 3–4 work best.'],
                ],
            ],
        ];
    }

    public const GROUP_ICONS = [
        'Profile' => 'person-circle',
        'Homepage' => 'house-door',
        'Career' => 'briefcase',
        'Credentials' => 'award',
        'Assistant' => 'chat-dots',
    ];

    public static function find(string $key): ?array
    {
        $section = static::all()[$key] ?? null;

        return $section ? $section + ['key' => $key] : null;
    }

    /** @return array<string, array<string, array>> sections keyed by group */
    public static function grouped(): array
    {
        $out = [];
        foreach (static::all() as $key => $section) {
            $out[$section['group']][$key] = $section;
        }

        return $out;
    }

    /** Data paths this section owns (used for saving and resetting). */
    public static function paths(array $section): array
    {
        $paths = array_column($section['fields'] ?? [], 'key');
        if (isset($section['list'])) {
            $paths[] = $section['list']['key'];
        }

        return $paths;
    }
}
