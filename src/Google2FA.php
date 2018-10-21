<?php

namespace PragmaRX\Google2FALaravel;

use Carbon\Carbon;
use Illuminate\Http\Request as IlluminateRequest;
use PragmaRX\Google2FA\Google2FA as Google2FAService;
use PragmaRX\Google2FA\Support\Constants as Google2FAConstants;
use PragmaRX\Google2FALaravel\Events\LoggedOut;
use PragmaRX\Google2FALaravel\Events\OneTimePasswordExpired;
use PragmaRX\Google2FALaravel\Support\Auth;
use PragmaRX\Google2FALaravel\Support\Config;
use PragmaRX\Google2FALaravel\Support\Constants;
use PragmaRX\Google2FALaravel\Support\Request;
use PragmaRX\Google2FALaravel\Support\Store;

class Google2FA extends Google2FAService
{
    use Auth, Config, Request, Store;

    /**
     * Authenticator constructor.
     *
     * @param IlluminateRequest $request
     */
    public function __construct(IlluminateRequest $request)
    {
        $this->boot($request);
    }

    /**
     * Authenticator boot.
     *
     * @param $request
     *
     * @return Google2FA
     */
    public function boot($request)
    {
        $this->setRequest($request);
        $this->setStore($request);

        return $this;
    }

    /**
     * Check if the 2FA is activated for the user.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isActivated()
    {
        $secret = $this->getGoogle2FASecretKey();

        return !is_null($secret) && !empty($secret);
    }

    /**
     * Get the user Google2FA secret.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function getGoogle2FASecretKey()
    {
        return $this->getUser()->{$this->config('otp_secret_column')};
    }

    /**
     * Set current auth as valid.
     */
    public function login()
    {
        $this->store->put(Constants::AUTH_PASSED, true);

        $this->updateCurrentAuthTime();
    }

    /**
     * Check if no user is authenticated using OTP.
     *
     * @return bool
     */
    protected function noUserIsAuthenticated()
    {
        return is_null($this->getUser());
    }

    /**
     * Verifies in the current cache if a 2fa check has already passed.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function twoFactorAuthStillValid()
    {
        return
            (bool)$this->store->get(Constants::AUTH_PASSED, false) &&
            !$this->passwordExpired();
    }

    /**
     * Check if OTP has expired.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function passwordExpired()
    {
        if (($minutes = $this->config('lifetime')) !== 0 && $this->minutesSinceLastActivity() > $minutes) {
            event(new OneTimePasswordExpired($this->getUser()));

            $this->logout();

            return true;
        }

        $this->keepAlive();

        return false;
    }

    /**
     * Get minutes since last activity.
     *
     * @return int
     */
    protected function minutesSinceLastActivity()
    {
        return Carbon::now()->diffInMinutes(
            $this->store->get(Constants::AUTH_TIME)
        );
    }

    /**
     * OTP logout.
     */
    public function logout()
    {
        $user = $this->getUser();

        $this->store->forget();

        event(new LoggedOut($user));
    }

    /**
     * Keep this OTP session alive.
     *
     * @throws \Exception
     */
    protected function keepAlive()
    {
        if ($this->config('keep_alive')) {
            $this->updateCurrentAuthTime();
        }
    }

    /**
     * Update the current auth time.
     */
    protected function updateCurrentAuthTime()
    {
        $this->store->put(Constants::AUTH_TIME, Carbon::now());
    }

    /**
     * Check if the module is enabled.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function isEnabled()
    {
        return $this->config('enabled');
    }

    /**
     * Verify the OTP and store the timestamp.
     *
     * @param $one_time_password
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function verifyAndStoreOneTimePassword($one_time_password)
    {
        return $this->storeOldTimeStamp(
            $this->verifyGoogle2FA(
                $this->getGoogle2FASecretKey(),
                $one_time_password
            )
        );
    }

    /**
     * Store the old OTP timestamp.
     *
     * @param $key
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function storeOldTimestamp($key)
    {
        return $this->config('forbid_old_passwords') === true
            ? $this->store->put(Constants::OTP_TIMESTAMP, $key)
            : $key;
    }

    /**
     * Verify the OTP.
     *
     * @param $secret
     * @param $one_time_password
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function verifyGoogle2FA($secret, $one_time_password)
    {
        return $this->verifyKey(
            $secret,
            $one_time_password,
            $this->config('window'),
            null, // $timestamp
            $this->getOldTimestamp() ?: Google2FAConstants::ARGUMENT_NOT_SET
        );
    }

    /**
     * Get the previous OTP timestamp.
     *
     * @return null|mixed
     *
     * @throws \Exception
     */
    protected function getOldTimestamp()
    {
        return $this->config('forbid_old_passwords') === true
            ? $this->store->get(Constants::OTP_TIMESTAMP)
            : null;
    }
}
