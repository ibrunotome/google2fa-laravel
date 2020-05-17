<?php

namespace PragmaRX\Google2FALaravel\Support;

use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait ErrorBag
{
    /**
     * Create an error bag and store a message on int.
     *
     * @param $message
     *
     * @return MessageBag
     */
    protected function createErrorBagForMessage($message)
    {
        return new MessageBag([
            'message' => $message,
        ]);
    }

    /**
     * Get a message bag with a message for a particular status code.
     *
     * @param $statusCode
     *
     * @return MessageBag
     */
    protected function getErrorBagForStatusCode($statusCode)
    {
        return $this->createErrorBagForMessage(
            trans(
                config(
                    $statusCode == SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
                        ? 'google2fa.error_messages.wrong_otp'
                        : 'google2fa.error_messages.unknown_error'
                )
            )
        );
    }

    /**
     * Get a message bag with a message for a particular status code.
     *
     * @param $statusCode
     *
     * @return MessageBag
     */
    protected function getJsonErrorBagForStatusCode($statusCode)
    {
        return $this->getCustomFields($this->createErrorBagForMessage(trans(
            config(($statusCode == SymfonyResponse::HTTP_SEE_OTHER ?
                'google2fa.error_messages.one_time_password_requested' :
                $statusCode == SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY)
                    ? 'google2fa.error_messages.wrong_otp'
                    : 'google2fa.error_messages.unknown_error')
        )));
    }


    /**
     * Merge MessageBag with custom fields
     * @package MessageBag $messageBag
     *
     * @return MessageBag
     */
    protected function getCustomFields($messageBag)
    {
        return $messageBag->merge(config('google2fa.custom_json_fields'));
    }
}
