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
 * Demo data: 2 shops (active yearly plan, full profile, logo and photos), 4 customers and
 * 4 repair requests in different stages.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Safe to run again: accounts are updated in place, and images and requests are only
 * created if they don't exist yet. All demo accounts share DEMO_PASSWORD (default below).
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
            'services' => "Screen & display replacement (iPhone, Samsung, Xiaomi, Realme)\n"
                ."Battery replacement with health check\n"
                ."Charging port & speaker repair\n"
                ."Water damage cleaning and board recovery\n"
                ."Back glass & camera lens replacement\n"
                ."Software flashing, FRP & data backup\n"
                ."Open Sat–Thu, 10 AM – 9 PM",
        ],
        'fixpoint-mobile' => [
            'name' => 'FixPoint Mobile',
            'email' => 'fixpoint@example.com',
            'phone' => '01819-876543',
            'location' => 'Shop 214, Level 2, Mirpur Shopping Complex, Mirpur 10, Dhaka 1216',
            'motto' => 'Honest diagnosis first. You only pay when it is fixed.',
            'services' => "Free diagnosis for every device\n"
                ."Motherboard & IC-level repair (power, network, audio)\n"
                ."Screen, touch & display replacement\n"
                ."Charging port, mic & button repair\n"
                ."Camera module & face/fingerprint sensor repair\n"
                ."Feature phones and older models welcome\n"
                ."Open every day, 11 AM – 10 PM",
        ],
    ];

    private const CUSTOMERS = [
        'rahim' => ['name' => 'Rahim Ahmed', 'email' => 'rahim@example.com', 'phone' => '01712-345678', 'location' => 'Dhanmondi, Dhaka', 'device' => 'iPhone 13'],
        'nusrat' => ['name' => 'Nusrat Jahan', 'email' => 'nusrat@example.com', 'phone' => '01913-456789', 'location' => 'Mohammadpur, Dhaka', 'device' => 'Samsung Galaxy A54'],
        'tanvir' => ['name' => 'Tanvir Hasan', 'email' => 'tanvir@example.com', 'phone' => '01614-567890', 'location' => 'Mirpur 2, Dhaka', 'device' => 'Xiaomi Redmi Note 12'],
        'farhana' => ['name' => 'Farhana Akter', 'email' => 'farhana@example.com', 'phone' => '01515-678901', 'location' => 'Uttara, Dhaka', 'device' => 'Google Pixel 7'],
    ];

    /** One request per stage, so every screen has something to show. */
    private const REQUESTS = [
        [
            'customer' => 'rahim', 'shop' => 'gadget-doctor', 'days_ago' => 12,
            'model' => 'iPhone 13', 'imei' => '356789104523871',
            'problem' => 'Dropped the phone and the screen is cracked across the top. Touch stops working in the top-left corner.',
            'diagnosis' => 'Broken OLED panel. Replaced with a genuine-grade OLED screen and a new adhesive seal.',
            'cost' => 9500, 'rating' => 5,
            'steps' => ['accepted', 'in progress', 'completed', 'sent from shop'], 'status' => 'delivered',
        ],
        [
            'customer' => 'nusrat', 'shop' => 'gadget-doctor', 'days_ago' => 1,
            'model' => 'Samsung Galaxy A54', 'imei' => '352614118790234',
            'problem' => 'Battery drains from 100% to 20% in about 4 hours and the back of the phone gets hot while charging.',
            'diagnosis' => null, 'cost' => null, 'rating' => null,
            'steps' => [], 'status' => 'pending',
        ],
        [
            'customer' => 'tanvir', 'shop' => 'fixpoint-mobile', 'days_ago' => 3,
            'model' => 'Xiaomi Redmi Note 12', 'imei' => '864201059983112',
            'problem' => 'Charging only works if I hold the cable at an angle. Sometimes it does not charge at all.',
            'diagnosis' => 'Worn charging port and a loose sub-board connector. Replacing the charging sub-board.',
            'cost' => 1800, 'rating' => null,
            'steps' => ['accepted', 'in progress'], 'status' => 'in progress',
        ],
        [
            'customer' => 'farhana', 'shop' => 'fixpoint-mobile', 'days_ago' => 9,
            'model' => 'Google Pixel 7', 'imei' => '358240307716459',
            'problem' => 'Rear camera photos are blurry and the camera app shows a black screen when switching to zoom.',
            'diagnosis' => 'Faulty rear camera module (autofocus stuck). Replaced the main camera module and cleaned the lens.',
            'cost' => 6200, 'rating' => 4,
            'steps' => ['accepted', 'in progress', 'completed', 'sent from shop'], 'status' => 'delivered',
        ],
    ];

    public function run(ShopImageStorage $images): void
    {
        $password = env('DEMO_PASSWORD') ?: self::DEFAULT_PASSWORD;

        $shops = [];
        foreach (self::SHOPS as $key => $data) {
            $shops[$key] = $this->seedShop($key, $data, $password, $images);
        }

        $customers = [];
        foreach (self::CUSTOMERS as $key => $data) {
            $customers[$key] = $this->seedCustomer($data, $password);
        }

        foreach (self::REQUESTS as $request) {
            $this->seedRequest($request, $customers[$request['customer']], $shops[$request['shop']]);
        }

        // Shop rating = average of its rated repairs (same rule as the app).
        foreach ($shops as $shop) {
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
        ]);

        // Upload the logo and photos once (to Cloudinary when it is the upload disk).
        $dir = database_path("seeders/demo-images/{$key}");
        if (! $shop->images()->where('type', 'logo')->exists()) {
            $images->store($shop, $this->file("{$dir}/logo.png"), 'logo');
        }
        if (! $shop->images()->where('type', 'gallery')->exists()) {
            foreach (glob("{$dir}/photo-*.jpg") as $photo) {
                $images->store($shop, $this->file($photo), 'gallery');
            }
        }

        return $shop;
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
        $record = ServiceRecord::firstOrNew([
            'customer_id' => $customer->customer_id,
            'shop_id' => $shop->shop_id,
            'phone_imei_number' => $r['imei'],
        ]);

        if ($record->exists) {
            return;
        }

        $createdAt = now()->subDays($r['days_ago'])->setTime(11, 30);
        $record->fill([
            'phone_number' => $customer->user->phone,
            'phone_model' => $r['model'],
            'customer_problem' => $r['problem'],
            'shop_problem' => $r['diagnosis'],
            'status' => $r['status'],
            'repair_cost' => $r['cost'],
            'rating' => $r['rating'],
        ]);
        $record->created_at = $createdAt;
        $record->updated_at = $createdAt->copy()->addDays(count($r['steps']));
        // Mark status emails as sent so later status changes don't email these demo addresses again.
        if (in_array('in progress', $r['steps'], true)) {
            $record->in_progress_emailed_at = $createdAt->copy()->addDays(1);
        }
        if (in_array('completed', $r['steps'], true)) {
            $record->completed_emailed_at = $createdAt->copy()->addDays(2);
        }
        $record->save();

        // The notification history the customer would have received, one per shop action.
        foreach ($r['steps'] as $i => $status) {
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
            $notification->created_at = $notification->updated_at = $createdAt->copy()->addHours(3 + $i * 20);
            $notification->save();
        }
    }

    private function file(string $path): UploadedFile
    {
        return new UploadedFile($path, basename($path), mime_content_type($path), null, true);
    }
}
