<?php

return [
    'fallbacks' => [
        'product' => 'images/admin-product-placeholder.svg',
        'category' => 'images/admin-product-placeholder.svg',
        'avatar' => 'images/admin-product-placeholder.svg',
        'seller_logo' => 'images/admin-product-placeholder.svg',
        'seller_banner' => 'images/admin-product-placeholder.svg',
    ],

    'types' => [
        'product' => [
            'disk' => 'public',
            'directory' => 'images/products/{product_id}',
            'allowed_mime_types' => [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            'allowed_extensions' => [
                'jpg',
                'jpeg',
                'png',
                'webp',
            ],
            'max_kb' => 8192,
            'max_width' => 6000,
            'max_height' => 6000,
            'reject_animated' => true,
            'keep_original' => true,
            'output_format' => 'webp',
            'variants' => [
                'thumb' => [
                    'width' => 160,
                    'height' => 160,
                    'mode' => 'cover',
                    'quality' => 78,
                ],
                'small' => [
                    'width' => 320,
                    'height' => 240,
                    'mode' => 'cover',
                    'quality' => 80,
                ],
                'medium' => [
                    'width' => 640,
                    'height' => 480,
                    'mode' => 'cover',
                    'quality' => 82,
                ],
                'large' => [
                    'width' => 1200,
                    'height' => 900,
                    'mode' => 'contain',
                    'quality' => 84,
                ],
            ],
        ],

        'category' => [
            'disk' => 'public',
            'directory' => 'images/categories/{category_id}',
            'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_kb' => 4096,
            'max_width' => 4000,
            'max_height' => 4000,
            'reject_animated' => true,
            'keep_original' => false,
            'output_format' => 'webp',
            'variants' => [
                'thumb' => ['width' => 160, 'height' => 160, 'mode' => 'cover', 'quality' => 78],
                'medium' => ['width' => 640, 'height' => 480, 'mode' => 'cover', 'quality' => 82],
            ],
        ],

        'avatar' => [
            'disk' => 'public',
            'directory' => 'images/avatars/{user_id}',
            'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_kb' => 2048,
            'max_width' => 3000,
            'max_height' => 3000,
            'reject_animated' => true,
            'keep_original' => false,
            'output_format' => 'webp',
            'variants' => [
                'thumb' => ['width' => 96, 'height' => 96, 'mode' => 'cover', 'quality' => 80],
                'medium' => ['width' => 320, 'height' => 320, 'mode' => 'cover', 'quality' => 82],
            ],
        ],

        'seller_logo' => [
            'disk' => 'public',
            'directory' => 'images/sellers/{seller_id}/logo',
            'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_kb' => 4096,
            'max_width' => 4000,
            'max_height' => 4000,
            'reject_animated' => true,
            'keep_original' => false,
            'output_format' => 'webp',
            'variants' => [
                'thumb' => ['width' => 120, 'height' => 120, 'mode' => 'contain', 'quality' => 82],
                'medium' => ['width' => 480, 'height' => 320, 'mode' => 'contain', 'quality' => 84],
            ],
        ],

        'seller_banner' => [
            'disk' => 'public',
            'directory' => 'images/sellers/{seller_id}/banner',
            'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_kb' => 6144,
            'max_width' => 6000,
            'max_height' => 4000,
            'reject_animated' => true,
            'keep_original' => false,
            'output_format' => 'webp',
            'variants' => [
                'thumb' => ['width' => 320, 'height' => 120, 'mode' => 'cover', 'quality' => 80],
                'large' => ['width' => 1600, 'height' => 600, 'mode' => 'cover', 'quality' => 84],
            ],
        ],
    ],
];
