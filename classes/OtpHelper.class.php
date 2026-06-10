<?php
require_once __DIR__ . '/../vendor/autoload.php';

use OTPHP\TOTP;

class OtpHelper
{
    private TOTP $totp;

    public function __construct(string $secret = null, int $digits = 6, int $period = 45200)
    {
       if ($secret === null) {
            // New secret
            $this->totp = TOTP::create(null, $period, 'sha1', $digits);
        } else {
            // Existing secret
            $this->totp = TOTP::create($secret, $period, 'sha1', $digits);
        }
       //  Set digits and period regardless
       // $this->totp->setDigits($digits);
       // $this->totp->setPeriod($period);
    }

    // Returns the base32 secret for storing with user
    public function getSecret(): string
    {
        return $this->totp->getSecret();
    }

    // Returns current OTP
    public function generateOtp(): string
    {
        return $this->totp->now();
    }

    // Verifies user input OTP (with time drift tolerance)
    public function verifyOtp(string $userInput): bool
    {
        return $this->totp->verify($userInput, null, 1);
    }

    // Optional: Get a provisioning URI for use with Google Authenticator
    public function getProvisioningUri(string $label, string $issuer): string
    {
        $this->totp->setLabel($label);
        $this->totp->setIssuer($issuer);
        return $this->totp->getProvisioningUri();
    }

    // Optional: Generate QR code URL via Google Charts
    public function getQrCodeUrl(string $label, string $issuer): string
    {
        $uri = $this->getProvisioningUri($label, $issuer);
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($uri);
    }
}
