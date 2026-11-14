<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\ServiceRecord;
use App\Models\Shop;
use App\Models\User;
use App\Services\Images\ShopImageStorage;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Demo data: 2 device repair shops (active yearly plan, full profile, logo and photos),
 * 4 customers and 8 repair requests across phones, laptops, tablets and accessories.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Shops you created yourself can join the demo too: list their login emails in
 * DEMO_EXTRA_SHOPS (comma separated). Each one that has an active plan gets demo photos
 * (if it has none), a motto and services (if missing) and 4 demo requests:
 *
 *   DEMO_EXTRA_SHOPS=myshop@example.com php artisan db:seed --class=DemoSeeder
 *
 * Safe to run again: accounts are updated in place, images are uploaded only once, and the
 * demo customers' requests are reset to their starting state (handy before each demo).
 * All demo accounts share DEMO_PASSWORD (default below).
 */
class DemoSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Demo@1234';

    private const SHOPS = [
        'gadget-doctor' => [
            'name' => 'Gadget Doctor',
            'email' => 'gadgetdoctor@example.com',
            'phone' => '01711-234567',
            'location' => 'House 12, Road 7, Dhanmondi, Dhaka 1205',
            'motto' => 'Same-day repairs with genuine parts and a 6-month warranty.',
            'devices' => ['phone', 'laptop', 'tablet', 'accessory'],
            'services' => "Phone, tablet & laptop screen replacement\n"
                ."Battery replacement with health check for every device\n"
                ."Laptop keyboard, trackpad, hinge & fan repair\n"
                ."Charging port & speaker repair\n"
                ."Water damage cleaning and board recovery\n"
                ."Windows/macOS install, upgrades & data backup\n"
                ."Earbud, smartwatch & charger repair\n"
                ."Open Sat–Thu, 10 AM – 9 PM",
        ],
        'fixpoint-mobile' => [
            'name' => 'FixPoint Mobile',
            'email' => 'fixpoint@example.com',
            'phone' => '01819-876543',
            'location' => 'Shop 214, Level 2, Mirpur Shopping Complex, Mirpur 10, Dhaka 1216',
            'motto' => 'Honest diagnosis first. You only pay when it is fixed.',
            'devices' => ['phone', 'tablet', 'accessory'],
            'services' => "Free diagnosis for every device\n"
                ."Motherboard & IC-level repair for phones and tablets\n"
                ."Screen, touch & display replacement\n"
                ."Battery and charging port replacement\n"
                ."Camera module & face/fingerprint sensor repair\n"
                ."Earbuds, smartwatches, power banks & chargers\n"
                ."Open every day, 11 AM – 10 PM",
        ],
    ];

    /** Profile filled in for DEMO_EXTRA_SHOPS where it is missing. */
    private const EXTRA_SHOP_PROFILE = [
        'motto' => 'Laptop and phone specialists. Fixed right the first time.',
        'services' => "Laptop motherboard & chip-level repair\n"
            ."Laptop screen, keyboard, hinge & battery replacement\n"
            ."SSD/RAM upgrades, Windows install & data recovery\n"
            ."Phone and tablet screen & battery replacement\n"
            ."Charging port and USB-C port repair\n"
            ."Game controller, earbud & smartwatch repair\n"
            ."Free pickup inside Motijheel. Open Sat–Thu, 10 AM – 8 PM",
    ];

    private const CUSTOMERS = [
        'rahim' => ['name' => 'Rahim Ahmed', 'email' => 'rahim@example.com', 'phone' => '01712-345678', 'location' => 'Dhanmondi, Dhaka', 'device' => 'iPhone 13'],
        'nusrat' => ['name' => 'Nusrat Jahan', 'email' => 'nusrat@example.com', 'phone' => '01913-456789', 'location' => 'Mohammadpur, Dhaka', 'device' => 'Dell Inspiron 15'],
        'tanvir' => ['name' => 'Tanvir Hasan', 'email' => 'tanvir@example.com', 'phone' => '01614-567890', 'location' => 'Mirpur 2, Dhaka', 'device' => 'Xiaomi Redmi Note 12'],
        'farhana' => ['name' => 'Farhana Akter', 'email' => 'farhana@example.com', 'phone' => '01515-678901', 'location' => 'Uttara, Dhaka', 'device' => 'iPad Air (5th gen)'],
    ];

    /** Requests for the demo shops, spread over every stage and device type. */
    private const REQUESTS = [
        // Gadget Doctor
        [
            'customer' => 'rahim', 'shop' => 'gadget-doctor', 'days_ago' => 12, 'status' => 'delivered',
            'type' => 'phone', 'model' => 'iPhone 13', 'imei' => '356789104523871',
            'problem' => 'Dropped the phone and the screen is cracked across the top. Touch stops working in the top-left corner.',
            'diagnosis' => 'Broken OLED panel. Replaced with a genuine-grade OLED screen and a new adhesive seal.',
            'cost' => 9500, 'rating' => 5,
        ],
        [
            'customer' => 'nusrat', 'shop' => 'gadget-doctor', 'days_ago' => 1, 'status' => 'pending',
            'type' => 'laptop', 'model' => 'Dell Inspiron 15 3520', 'imei' => '7XK2PQ3',
            'problem' => 'The laptop shuts down by itself after 20–30 minutes. It gets very hot near the left vent and the fan is loud.',
        ],
        [
            'customer' => 'tanvir', 'shop' => 'gadget-doctor', 'days_ago' => 4, 'status' => 'in progress',
            'type' => 'laptop', 'model' => 'MacBook Pro 13" (2020)', 'imei' => 'C02DK1ABP3XY',
            'problem' => 'Several keys (E, R and space) are sticky or do not register, and the battery only lasts about an hour.',
            'diagnosis' => 'Liquid residue under the keyboard and a worn battery (412 cycles, service recommended). Cleaning the keyboard and replacing the battery.',
            'cost' => 14500,
        ],
        [
            'customer' => 'farhana', 'shop' => 'gadget-doctor', 'days_ago' => 2, 'status' => 'accepted',
            'type' => 'accessory', 'model' => 'Apple Watch Series 7 (45 mm)', 'imei' => null,
            'problem' => 'Screen cracked after hitting a door frame. Touch still works but the glass is lifting at one corner.',
            'diagnosis' => 'Cracked front glass, display underneath is fine. Will replace the glass and reseal.',
            'cost' => 6800,
        ],
        // FixPoint Mobile
        [
            'customer' => 'tanvir', 'shop' => 'fixpoint-mobile', 'days_ago' => 3, 'status' => 'in progress',
            'type' => 'phone', 'model' => 'Xiaomi Redmi Note 12', 'imei' => '864201059983112',
            'problem' => 'Charging only works if I hold the cable at an angle. Sometimes it does not charge at all.',
            'diagnosis' => 'Worn charging port and a loose sub-board connector. Replacing the charging sub-board.',
            'cost' => 1800,
        ],
        [
            'customer' => 'farhana', 'shop' => 'fixpoint-mobile', 'days_ago' => 9, 'status' => 'delivered',
            'type' => 'tablet', 'model' => 'iPad Air (5th gen)', 'imei' => 'DMPXK2LQ1F',
            'problem' => 'Battery goes from 100% to 0 overnight, and it only charges if the cable is held at an angle.',
            'diagnosis' => 'Worn battery (68% health) and a loose USB-C port. Replaced the battery and the charging port.',
            'cost' => 7800, 'rating' => 4,
        ],
        [
            'customer' => 'nusrat', 'shop' => 'fixpoint-mobile', 'days_ago' => 5, 'status' => 'in progress',
            'type' => 'tablet', 'model' => 'Samsung Galaxy Tab S7', 'imei' => 'R52N80ABCDE',
            'problem' => 'The screen flickers and shows green lines, worse when the tablet is warm.',
            'diagnosis' => 'Damaged display flex cable. Replacing the display assembly with an original panel.',
            'cost' => 11200,
        ],
        [
            'customer' => 'rahim', 'shop' => 'fixpoint-mobile', 'days_ago' => 6, 'status' => 'completed',
            'type' => 'accessory', 'model' => 'AirPods Pro (2nd gen)', 'imei' => null,
            'problem' => 'The left earbud does not charge in the case and dies after 10 minutes.',
            'diagnosis' => 'Corroded charging contacts on the left earbud and in the case. Cleaned and replaced the contact pins.',
            'cost' => 2500,
        ],
    ];

    /** Requests added to each shop listed in DEMO_EXTRA_SHOPS. */
    private const EXTRA_SHOP_REQUESTS = [
        [
            'customer' => 'nusrat', 'days_ago' => 3, 'status' => 'in progress',
            'type' => 'laptop', 'model' => 'HP Pavilion 15', 'imei' => '5CD1234XYZ',
            'problem' => 'Stuck on the HP logo after a Windows update. Restarting does not help and I need my files.',
            'diagnosis' => 'Failing SSD with a corrupted boot partition. Recovering the files, fitting a new 512 GB SSD and reinstalling Windows.',
            'cost' => 6500,
        ],
        [
            'customer' => 'rahim', 'days_ago' => 7, 'status' => 'sent from shop',
            'type' => 'phone', 'model' => 'Samsung Galaxy S21', 'imei' => '351234567890123',
            'problem' => 'Charging is very slow and the phone gets hot near the port.',
            'diagnosis' => 'Damaged USB-C port. Replaced the charging port board.',
            'cost' => 1500,
        ],
        [
            'customer' => 'tanvir', 'days_ago' => 15, 'status' => 'delivered',
            'type' => 'accessory', 'model' => 'PS5 DualSense controller', 'imei' => null,
            'problem' => 'The left stick drifts upwards on its own, games keep moving without touching it.',
            'diagnosis' => 'Worn left analog stick module. Replaced it with a new Hall-effect stick.',
            'cost' => 1800, 'rating' => 5,
        ],
        [
            'customer' => 'farhana', 'days_ago' => 0, 'status' => 'pending',
            'type' => 'tablet', 'model' => 'iPad (9th gen)', 'imei' => 'GG7XK29PQ1',
            'problem' => 'Glass cracked in the corner after a fall. Touch does not respond along the bottom edge.',
        ],
    ];

    /** The shop actions each status implies, used for the notification history. */
    private const STEPS = [
        'pending' => [],
        'accepted' => ['accepted'],
        'in progress' => ['accepted', 'in progress'],
        'completed' => ['accepted', 'in progress', 'completed'],
        'sent from shop' => ['accepted', 'in progress', 'completed', 'sent from shop'],
        'delivered' => ['accepted', 'in progress', 'completed', 'sent from shop'],
    ];

    public function run(ShopImageStorage $images): void
    {
        $password = env('DEMO_PASSWORD') ?: self::DEFAULT_PASSWORD;

        $shops = [];
        foreach (self::SHOPS as $key => $data) {
            $shops[$key] = $this->seedShop($key, $data, $password, $images);
        }
        $extraShops = $this->extraShops($images);

        $customers = [];
        foreach (self::CUSTOMERS as $key => $data) {
            $customers[$key] = $this->seedCustomer($data, $password);
        }

        // Reset the demo customers' requests (and their notifications) to the starting state.
        $customerIds = array_map(fn (Customer $c) => $c->customer_id, $customers);
        CustomerNotification::whereIn('customer_id', $customerIds)->delete();
        ServiceRecord::whereIn('customer_id', $customerIds)->delete();

        foreach (self::REQUESTS as $request) {
            $this->seedRequest($request, $customers[$request['customer']], $shops[$request['shop']]);
        }
        foreach ($extraShops as $shop) {
            foreach (self::EXTRA_SHOP_REQUESTS as $request) {
                $this->seedRequest($request, $customers[$request['customer']], $shop);
            }
        }

        // Shop rating = average of its rated repairs (same rule as the app).
        foreach ([...array_values($shops), ...$extraShops] as $shop) {
            $avg = ServiceRecord::where('shop_id', $shop->shop_id)->whereNotNull('rating')->avg('rating');
            $shop->update(['rating' => $avg ? round((float) $avg, 2) : 0]);
        }

        $this->command?->info('Demo data ready. Password for every demo account: '.$password);
        $this->command?->table(
            ['Role', 'Name', 'Email'],
            array_merge(
                array_map(fn ($s) => ['shop', $s['name'], $s['email']], array_values(self::SHOPS)),
                array_map(fn ($c) => ['customer', $c['name'], $c['email']], array_values(self::CUSTOMERS)),
            )
        );
        foreach ($extraShops as $shop) {
            $this->command?->info("Added demo photos/details/requests to {$shop->user->name} (password unchanged).");
        }
    }

    private function seedShop(string $key, array $data, string $password, ShopImageStorage $images): Shop
    {
        $user = User::updateOrCreate(['email' => $data['email']], [
            'name' => $data['name'],
            'password' => $password,
            'role' => 'shop',
            'phone' => $data['phone'],
            'location' => $data['location'],
        ]);

        $oneYear = now()->addYear();
        $shop = Shop::updateOrCreate(['user_id' => $user->id], [
            'subscription_status' => 'activated',
            'subscription_plan' => 'yearly',
            'expiry_date' => $oneYear,
            'current_period_end' => $oneYear,
            'description' => $data['location'],
            'motto' => $data['motto'],
            'services_provided' => $data['services'],
            'device_types' => $data['devices'],
        ]);

        // Upload the logo and photos once (to Cloudinary when it is the upload disk).
        $dir = database_path("seeders/demo-images/{$key}");
        if (! $shop->images()->where('type', 'logo')->exists()) {
            $images->store($shop, $this->file("{$dir}/logo.png"), 'logo');
        }
        $this->uploadGalleryOnce($shop, $dir, $images);

        return $shop;
    }

    /**
     * Shops listed in DEMO_EXTRA_SHOPS that have an active plan. Their account, plan and logo
     * are left alone; only missing photos, motto and services are filled in.
     *
     * @return list<Shop>
     */
    private function extraShops(ShopImageStorage $images): array
    {
        $emails = array_filter(array_map(
            fn ($e) => strtolower(trim($e)),
            explode(',', (string) env('DEMO_EXTRA_SHOPS', ''))
        ));
        $demoEmails = array_column(self::SHOPS, 'email');

        $shops = [];
        foreach ($emails as $email) {
            $user = User::where('email', $email)->where('role', 'shop')->first();
            $shop = $user?->shop;
            if (in_array($email, $demoEmails, true) || ! $shop) {
                $this->command?->warn("DEMO_EXTRA_SHOPS: no other shop account found for {$email}, skipped.");
                continue;
            }
            if ($shop->subscription_status !== 'activated' || ! $shop->expiry_date?->isFuture()) {
                $this->command?->warn("DEMO_EXTRA_SHOPS: {$user->name} has no active plan, skipped.");
                continue;
            }

            $updates = [];
            if (blank($shop->motto)) {
                $updates['motto'] = self::EXTRA_SHOP_PROFILE['motto'];
            }
            if (count(array_filter(preg_split('/\R/', (string) $shop->services_provided))) < 3) {
                $updates['services_provided'] = self::EXTRA_SHOP_PROFILE['services'];
            }
            if ($updates) {
                $shop->update($updates);
            }

            $this->uploadGalleryOnce($shop, database_path('seeders/demo-images/extra-shop'), $images);
            $shops[] = $shop->load('user');
        }

        return $shops;
    }

    private function uploadGalleryOnce(Shop $shop, string $dir, ShopImageStorage $images): void
    {
        if ($shop->images()->where('type', 'gallery')->exists()) {
            return;
        }
        foreach (glob("{$dir}/photo-*.jpg") as $photo) {
            $images->store($shop, $this->file($photo), 'gallery');
        }
    }

    private function seedCustomer(array $data, string $password): Customer
    {
        $user = User::updateOrCreate(['email' => $data['email']], [
            'name' => $data['name'],
            'password' => $password,
            'role' => 'customer',
            'phone' => $data['phone'],
            'location' => $data['location'],
        ]);

        return Customer::updateOrCreate(['user_id' => $user->id], ['primary_device' => $data['device']]);
    }

    private function seedRequest(array $r, Customer $customer, Shop $shop): void
    {
        // Never give a shop a request for a device it doesn't repair.
        if (! $shop->repairs($r['type'])) {
            return;
        }

        $steps = self::STEPS[$r['status']];
        $createdAt = min(now()->subDays($r['days_ago'])->setTime(11, 30), now()->subHour());

        $record = new ServiceRecord([
            'customer_id' => $customer->customer_id,
            'shop_id' => $shop->shop_id,
            'device_type' => $r['type'],
            'phone_number' => $customer->user->phone,
            'phone_model' => $r['model'],
            'phone_imei_number' => $r['imei'],
            'customer_problem' => $r['problem'],
            'shop_problem' => $r['diagnosis'] ?? null,
            'status' => $r['status'],
            'repair_cost' => $r['cost'] ?? null,
            'rating' => $r['rating'] ?? null,
        ]);
        $record->created_at = $createdAt;
        $record->updated_at = min(now(), $createdAt->copy()->addHours(3 + max(count($steps) - 1, 0) * 20));
        // Mark status emails as sent so later status changes don't email these demo addresses again.
        if (in_array('in progress', $steps, true)) {
            $record->in_progress_emailed_at = $createdAt->copy()->addHours(23);
        }
        if (in_array('completed', $steps, true)) {
            $record->completed_emailed_at = $createdAt->copy()->addHours(43);
        }
        $record->save();

        // The notification history the customer would have received, one per shop action.
        foreach ($steps as $i => $status) {
            $notification = new CustomerNotification([
                'customer_id' => $customer->customer_id,
                'service_id' => $record->service_id,
                'message' => match ($status) {
                    'accepted' => "Your {$r['model']} request has been accepted.",
                    'in progress' => "Your {$r['model']} is now in progress.",
                    'completed' => "Your {$r['model']} repair is completed.",
                    'sent from shop' => "Your {$r['model']} has been sent from shop.",
                },
            ]);
            $notification->created_at = $notification->updated_at = min(now(), $createdAt->copy()->addHours(3 + $i * 20));
            $notification->save();
        }
    }

    private function file(string $path): UploadedFile
    {
        return new UploadedFile($path, basename($path), mime_content_type($path), null, true);
    }
}
