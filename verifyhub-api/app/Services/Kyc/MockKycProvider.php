<?php

namespace App\Services\Kyc;

use App\Services\Providers\ProviderResult;
use Illuminate\Support\Str;

/**
 * Fake provider for development. No internet and no real data needed.
 *
 * Aadhaar : number ends with 0 -> not found | ends with 9 -> provider down | otherwise OTP is sent. Valid OTP: 123456
 * PAN     : ends with Z -> invalid | E -> provider down | M -> different name | L -> not linked with Aadhaar | otherwise valid
 * Bank    : account ends with 0 -> invalid | 9 -> provider down | 7 -> different name | otherwise valid
 * e-Sign  : always starts. Use the "simulate" button in the app to finish it (development only).
 *
 * The matching name is always TEST USER.
 */
class MockKycProvider implements KycProvider
{
    public const VALID_OTP = '123456';

    public function aadhaarSendOtp(string $aadhaarNumber): ProviderResult
    {
        $this->pause();
        $ref = 'MOCK-TXN-' . strtoupper(Str::random(10));

        return match (substr($aadhaarNumber, -1)) {
            '0'     => ProviderResult::failed('Aadhaar number not found.', $ref),
            '9'     => throw new \RuntimeException('Mock provider timeout.'),
            default => ProviderResult::success(['otp_sent' => true], $ref),
        };
    }

    public function aadhaarVerifyOtp(string $transactionId, string $otp): ProviderResult
    {
        $this->pause();

        return hash_equals(self::VALID_OTP, $otp)
            ? ProviderResult::success(['name' => 'TEST USER'], $transactionId)
            : ProviderResult::failed('Incorrect OTP.', $transactionId);
    }

    public function pan(string $pan): ProviderResult
    {
        $this->pause();
        $ref = 'MOCK-' . strtoupper(Str::random(10));

        return match (substr($pan, -1)) {
            'Z'     => ProviderResult::failed('PAN not found or invalid.', $ref),
            'E'     => throw new \RuntimeException('Mock provider timeout.'),
            'M'     => ProviderResult::success(['name' => 'RAMESH KUMAR', 'pan_status' => 'VALID', 'aadhaar_linked' => true], $ref),
            'L'     => ProviderResult::success(['name' => 'TEST USER', 'pan_status' => 'VALID', 'aadhaar_linked' => false], $ref),
            default => ProviderResult::success(['name' => 'TEST USER', 'pan_status' => 'VALID', 'aadhaar_linked' => true], $ref),
        };
    }

    public function bank(string $accountNumber, string $ifsc): ProviderResult
    {
        $this->pause();
        $ref = 'MOCK-' . strtoupper(Str::random(10));

        return match (substr($accountNumber, -1)) {
            '0'     => ProviderResult::failed('Bank account not found or invalid.', $ref),
            '9'     => throw new \RuntimeException('Mock provider timeout.'),
            '7'     => ProviderResult::success(['account_holder' => 'SOME OTHER PERSON', 'account_status' => 'ACTIVE'], $ref),
            default => ProviderResult::success(['account_holder' => 'TEST USER', 'account_status' => 'ACTIVE'], $ref),
        };
    }

    public function esignStart(array $document): ProviderResult
    {
        $this->pause();

        // No real signing page in the mock, so there is no link
        return ProviderResult::success(['sign_url' => null], 'MOCK-ESIGN-' . strtoupper(Str::random(10)));
    }

    private function pause(): void
    {
        usleep(300000); // pretend the network call takes a moment
    }
}
