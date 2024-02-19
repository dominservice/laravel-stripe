<?php

namespace Dominservice\LaraStripe;

use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\WebhookSignature;
use RuntimeException;

class WebhookVerifier
{
    const SIGNATURE_HEADER = 'Stripe-Signature';

    /**
     * @throws SignatureVerificationException
     */
    public function verify(Request $request, $name)
    {
        if (!$secret = config("stripe.webhooks.signing_secrets.{$name}")) {
            throw new RuntimeException("Webhook signing secret does not exist: {$name}");
        }

        if (!$header = $request->header(self::SIGNATURE_HEADER)) {
            throw SignatureVerificationException::factory(
                'Expecting ' . self::SIGNATURE_HEADER . ' header.',
                $request->getContent(),
                $header
            );
        }

        WebhookSignature::verifyHeader(
            $request->getContent(),
            $header,
            $secret,
            config('webhooks.signature_tolerance', Webhook::DEFAULT_TOLERANCE)
        );
    }
}