<?php

namespace Modules\Core\Tests\Unit\Rules;

use Modules\Core\Models\Setting;
use Modules\Core\Rules\StrongPassword;
use Tests\TestCase;

class StrongPasswordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear settings for clean slate
        Setting::truncate();
    }

    public function test_default_requirements()
    {
        $rule = new StrongPassword;

        // Defaults: 8 chars, upper, lower, number, no symbol
        $this->assertFalse($rule->passes('password', 'short')); // length
        $this->assertFalse($rule->passes('password', 'alllowercase123')); // uppercase
        $this->assertFalse($rule->passes('password', 'ALLUPPERCASE123')); // lowercase
        $this->assertFalse($rule->passes('password', 'AllLettersNoNumber')); // number

        $this->assertTrue($rule->passes('password', 'Valid123456'));
    }

    public function test_custom_requirements()
    {
        Setting::set('password_min_length', 12);
        Setting::set('password_require_symbol', true);

        $rule = new StrongPassword;

        $this->assertFalse($rule->passes('password', 'Valid123456')); // too short
        $this->assertFalse($rule->passes('password', 'Valid123456789')); // no symbol

        $this->assertTrue($rule->passes('password', 'Valid123456789!'));
    }

    public function test_non_string_value()
    {
        $rule = new StrongPassword;
        $this->assertFalse($rule->passes('password', ['not', 'a', 'string']));
    }

    public function test_non_numeric_min_length_setting()
    {
        Setting::set('password_min_length', 'abc');
        $rule = new StrongPassword;

        // Should fallback to 8
        $this->assertFalse($rule->passes('password', 'sh12A')); // Only 5 chars
        $this->assertTrue($rule->passes('password', 'Valid12345')); // 10 chars
    }

    public function test_all_requirements_disabled()
    {
        Setting::set('password_min_length', 1);
        Setting::set('password_require_uppercase', false);
        Setting::set('password_require_lowercase', false);
        Setting::set('password_require_number', false);
        Setting::set('password_require_symbol', false);

        $rule = new StrongPassword;
        $this->assertTrue($rule->passes('password', 'a'));
    }

    public function test_error_message()
    {
        $rule = new StrongPassword;
        $rule->passes('password', '1'); // fails multiple

        $message = $rule->message();
        $this->assertStringContainsString('at least 8 characters', $message);
        $this->assertStringContainsString('one uppercase letter', $message);
        $this->assertStringContainsString('one lowercase letter', $message);

        // Test empty failed checks message (unreachable normally via passes(), but good for 100%)
        $prop = new \ReflectionProperty($rule, 'failedChecks');
        $prop->setAccessible(true);
        $prop->setValue($rule, []);

        $this->assertEquals('The :attribute does not meet the password requirements.', $rule->message());
    }
}
