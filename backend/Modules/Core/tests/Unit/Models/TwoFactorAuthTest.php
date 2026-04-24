<?php

namespace Modules\Core\Tests\Unit\Models;

use Illuminate\Support\Facades\Crypt;
use Modules\Core\Models\TwoFactorAuth;
use Modules\Core\Models\User;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    public function test_user_relationship()
    {
        $user = User::factory()->create();
        $tfa = TwoFactorAuth::create([
            'user_id' => $user->id,
            'secret' => 'encrypted_secret',
            'enabled' => true,
        ]);

        $this->assertInstanceOf(User::class, $tfa->user);
        $this->assertEquals($user->id, $tfa->user->id);
    }

    public function test_get_decrypted_secret_exception()
    {
        $user = User::factory()->create();
        $tfa = TwoFactorAuth::create([
            'user_id' => $user->id,
            'secret' => 'invalid-encrypted-payload', // will throw exception in Crypt::decryptString
            'enabled' => true,
        ]);

        $this->assertNull($tfa->getDecryptedSecret());

        $emptyTfa = new TwoFactorAuth;
        $this->assertNull($emptyTfa->getDecryptedSecret());
    }

    public function test_verify_backup_code()
    {
        $user = User::factory()->create();
        $tfa = TwoFactorAuth::create([
            'user_id' => $user->id,
            'enabled' => true,
        ]);

        // Test empty backup codes
        $this->assertFalse($tfa->verifyBackupCode('123456'));
        $this->assertEquals(0, $tfa->getRemainingBackupCodesCount());

        // Generate codes
        $tfa->setBackupCodes(['code1', 'code2', 'code3']);
        $tfa->save();

        $this->assertEquals(3, $tfa->getRemainingBackupCodesCount());

        // Verify code
        $this->assertTrue($tfa->verifyBackupCode('code2'));

        // Try to verify again, should fail (consumed)
        $this->assertFalse($tfa->verifyBackupCode('code2'));

        // Count should decrease
        $this->assertEquals(2, $tfa->getRemainingBackupCodesCount());

        // Verify invalid code
        $this->assertFalse($tfa->verifyBackupCode('invalid-code'));
        $this->assertEquals(2, $tfa->getRemainingBackupCodesCount());
    }
}
