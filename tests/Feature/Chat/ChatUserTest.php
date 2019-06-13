<?php

namespace Tests\Feature\Chat;

use Tests\ChatCase as TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatUserTest extends TestCase
{
    public function testPing()
    {
        $response = $this->post('api/lib/ping', ['ping' => 1], $this->requestHeader);
        $this->baseVerification($response);
    }

    public function testGetFriendList()
    {
        $response = $this->get('api/chat/getFriendsList', $this->requestHeader);
        $this->baseVerification($response);
        return $response->getOriginalContent();
    }

    public function testGetGroupList()
    {
        $response = $this->get('api/chat/getGroupList', $this->requestHeader);
        $this->baseVerification($response);
    }

    public function testIsFriend()
    {
        $response = $this->get('api/chat/isFriends/2', $this->requestHeader);
        $this->baseVerification($response);
    }

    public function testGetUserChatMessage()
    {
        $response = $this->get('api/chat/getChatMessage/u/2', $this->requestHeader);
        $this->baseVerification($response);
    }
    public function testGetUserInfo()
    {
        $response = $this->get('api/chat/getUserInfo/2', $this->requestHeader);
        $this->baseVerification($response);
    }
    public function testAddFriends()
    {
        $response = $this->post('api/chat/addFriends', ['friend_id' => 2, 'remarks'=> $this->faker()->name()], $this->requestHeader);
        $this->baseVerification($response);
    }
}
