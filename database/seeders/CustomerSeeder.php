<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create(); // Dùng en_US mặc định
        $genders = ['male', 'female', 'other', 'unknown'];
        $statuses = ['active', 'inactive', 'blacklist'];
        $types = ['individual', 'company'];
        $sources = ['Facebook', 'Zalo', 'Website', 'Referral', 'Ads'];

        for ($i = 0; $i < 1000; $i++) {
            $gender = $faker->randomElement($genders);
            $type = $faker->randomElement($types);
            $total_spent = $faker->randomFloat(2, 0, 500000000);

            Customer::create([
                'name' => $gender === 'male' ? $faker->name('male') : $faker->name('female'),
                'phone' => $faker->unique()->phoneNumber(),
                'email' => $faker->email(),
                'birthday' => $faker->date('Y-m-d', '-18 years'),
                'gender' => $gender,
                'address' => $faker->address(),
                'province_id' => $faker->numberBetween(1, 63),
                'district_id' => $faker->numberBetween(1, 999),
                'code' => 'CUST' . $faker->unique()->numberBetween(1000, 9999),
                'tags' => json_encode($faker->randomElement([
                    ['dễ chịu'],
                    ['khó tính', 'hay trả giá'],
                    ['thường xuyên gọi lại']
                ])),
                'assigned_user_id' => $faker->numberBetween(1, 10),
                'total_orders' => $faker->numberBetween(0, 50),
                'total_spent' => $total_spent,
                'last_order_date' => $faker->dateTimeBetween('-2 years', 'now'),
                'vip_at' => $total_spent > 100000000 ? $faker->dateTimeBetween('-1 years', 'now') : null,
                'type' => $type,
                'source' => $faker->randomElement($sources),
                'status' => $faker->randomElement($statuses),
                'facebook_url' => $faker->url(),
                'zalo_phone' => $faker->phoneNumber(),
                'tax_code' => $type === 'company' ? $faker->bothify('######-###') : null,
                'debt_amount' => $faker->randomFloat(2, 0, 50000000),
                'credit_limit' => $type === 'company' ? 100000000 : 0,
                'note' => $faker->sentence(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
