<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Basmanak',
            'identity_id' => '081479283843',
            'gender' => 0,
            'address' => 'jalan merdeka barat no 45',
            //'photo' => 'hello.jpg',
            'email' => 'mochgunturpratama@gmail.com',
            'password' => app('hash')->make('helloworld'),
            'phone_number' => '081337708831',
            'api_token' => Str::random(40),
            'role' => 0,
            'status' => 1,
        ]);
    }
}
