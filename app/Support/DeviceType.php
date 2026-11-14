<?php

namespace App\Support;

/**
 * The kinds of devices RepairiX shops repair. Shops choose which ones they take;
 * every repair request is for one of them. Keep in sync with resources/js/lib/devices.js.
 */
final class DeviceType
{
    public const PHONE = 'phone';
    public const LAPTOP = 'laptop';
    public const TABLET = 'tablet';
    public const ACCESSORY = 'accessory';

    public const ALL = [self::PHONE, self::LAPTOP, self::TABLET, self::ACCESSORY];

    public const PLURAL_LABELS = [
        self::PHONE => 'phones',
        self::LAPTOP => 'laptops',
        self::TABLET => 'tablets',
        self::ACCESSORY => 'accessories',
    ];
}
