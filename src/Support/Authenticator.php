<?php

namespace PragmaRX\Google2FALaravel\Support;

use PragmaRX\Google2FALaravel\Events\EmptyOneTimePasswordReceived;
use PragmaRX\Google2FALaravel\Events\LoginFailed;
use PragmaRX\Google2FALaravel\Events\LoginSucceeded;
use PragmaRX\Google2FALaravel\Exceptions\InvalidOneTimePassword;
use PragmaRX\Google2FALaravel\Google2FA;

class Authenticator extends Google2FA
{
    use ErrorBag, Input, Response;

    /**
     * The current password.
     *
     * @var
     */
    protected $password;

    /**
     * Authenticator boot.
     *
     * @param $request
     *
     * @return Google2FA
     */
    public function boot($request)
    {
        parent::boot($request);

        return $this;
    }

    /**
     * Check if the current use is authenticated via OTP.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isAuthenticated()
    {
        return $this->canPassWithoutCheckingOTP() || $this->checkOTP();
    }

    /**
     * Check if it is already logged in or passable without checking for an OTP.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function canPassWithoutCheckingOTP()
    {
        return
            !$this->isEnabled() ||
            $this->noUserIsAuthenticated() ||
            !$this->isActivated() ||
            $this->twoFactorAuthStillValid();
    }

    /**
     * Check if the input OTP is valid.
     *
     * @return bool
     *
     * @throws InvalidOneTimePassword
     */
    protected function checkOTP()
    {
        if (!$this->inputHasOneTimePassword()) {
            return false;
        }

        if ($isValid = $this->verifyOneTimePassword()) {
            $this->login();
        }

        return $this->fireLoginEvent($isValid);
    }

    /**
     * Verify the OTP.
     *
     * @return mixed
     *
     * @throws InvalidOneTimePassword
     *
     * @throws \Exception
     */
    protected function verifyOneTimePassword()
    {
        return $this->verifyAndStoreOneTimePassword($this->getOneTimePassword());
    }

    /**
     * Get the OTP from user input.
     *
     * @throws InvalidOneTimePassword
     *
     * @return mixed
     */
    protected function getOneTimePassword()
    {
        if (is_null($password = $this->getInputOneTimePassword()) || empty($password)) {
            event(new EmptyOneTimePasswordReceived());

            if ($this->config('throw_exceptions', true)) {
                throw new InvalidOneTimePassword('One Time Password cannot be empty.');
            }
        }

        return $password;
    }

    /**
     * Fire login (success or failed).
     *
     * @param $succeeded
     *
     * @return bool
     */
    private function fireLoginEvent($succeeded)
    {
        event(
            $succeeded
                ? new LoginSucceeded($this->getUser())
                : new LoginFailed($this->getUser())
        );

        return $succeeded;
    }
}
