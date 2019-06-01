<?php

namespace Tests\Feature\Chat;

use Tests\ChatCase as TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupTest extends TestCase
{
    public function testGetGroupMessage()
    {
        $res = $this->get('api/chat/getGroupMessage/1', $this->requestHeader);
        $this->baseVerification($res);
    }

    public function testGetGroupMember()
    {
        $res = $this->get('api/chat/getGroupMember/1', $this->requestHeader);
        $this->baseVerification($res);
    }

    public function testCreateGroup()
    {
        $res = $this->post('api/chat/createGroup', [
            'group_name' => $this->faker()->name(),
        ], $this->requestHeader);
        $this->baseVerification($res);
    }
}
