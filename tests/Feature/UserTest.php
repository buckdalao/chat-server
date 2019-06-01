<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{

    public function testRegister()
    {
        $user = [
            'name'      => $this->faker()->name(),
            'email'     => $this->faker()->email,
            'password'  => Hash::make('123123'),
            'mb_prefix' => '+86',
            'phone'     => $this->faker()->phoneNumber,
            'photo'     => 'storage/photos/photo.jpg' // default photo
        ];
        $response = $this->post('api/auth/register', $user, $this->requestHeader);
        $this->baseVerification($response);
    }

    /*public function testRefresh()
    {
        $response = $this->post('api/auth/refresh', [], $this->requestHeader);
        $this->baseVerification($response);
    }

    public function testGetMe()
    {
        $response = $this->post('api/auth/me', [], $this->requestHeader);
        $this->baseVerification($response);
    }*/
}
