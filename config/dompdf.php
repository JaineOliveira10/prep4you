<?php

return [
    'default_font' => 'Courier',
    'default_font_size' => 10,
    'margin_top' => 0,
    'margin_right' => 0,
    'margin_bottom' => 0,
    'margin_left' => 0,
    'margin_header' => 0,
    'margin_footer' => 0,
    'orientation' => 'portrait',
    'enable_php' => false,
    'enable_javascript' => false,
    'enable_remote' => true,
    'font_dir' => base_path('resources/fonts/'),
    'font_cache' => storage_path('framework/fonts/'),
    'temp_dir' => storage_path('framework/dompdf/'),
    'chroot' => base_path(),
    'logOutputFile' => storage_path('logs/dompdf.log'),
    'defaultMediaType' => 'screen',
    'isPhpEnabled' => false,
];