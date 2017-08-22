<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 21.08.17
 * Time: 17:31
 */

namespace frontend\tests\unit\entities;


use Codeception\Test\Unit;
use common\entities\User;

class SignupTest extends Unit
{
    public function testSuccess()
    {
        $user = User::signup(
            $name = 'username',
            $email = 'email@gmail.com',
            $password = 'password2207'
        );
        
        $this->assertEquals($name, $user->username);
        $this->assertEquals($email, $user->email);
        $this->assertNotEquals($password, $user->password_hash);
        $this->assertNotEmpty($user->password_hash);
        //$this->assertNotEmpty($user->created_at);
        $this->assertNotEmpty($user->auth_key);
        //$this->assertTrue($user->isActive());
    }
}