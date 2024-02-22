<?php

namespace Dominservice\LaraStripe\Http\Middleware;

use Dominservice\LaraStripe\Events\SignatureVerificationFailed;
use Dominservice\LaraStripe\Log\Logger;
use Dominservice\LaraStripe\WebhookVerifier;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class VerifySignature
{

    /**
     * @var WebhookVerifier
     */
    private $verifier;

    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * @var Logger
     */
    private $log;

    /**
     * VerifySignature constructor.
     *
     * @param WebhookVerifier $verifier
     * @param Dispatcher $events
     * @param Logger $log
     */
    public function __construct(WebhookVerifier $verifier, Dispatcher $events, Logger $log)
    {
        $this->verifier = $verifier;
        $this->events = $events;
        $this->log = $log;
    }

    /**
     * @param $request
     * @param \Closure $next
     * @param string $signingSecret
     * @return mixed
     */
    public function handle($request, \Closure $next, $signingSecret = 'checkout')
    {
        $this->log->log("Verifying Stripe webhook using signing secret: {$signingSecret}");
        Log::info("Verifying Stripe webhook using signing secret: {$signingSecret}");

        try {
            $this->verifier->verify($request, $signingSecret);
        } catch (SignatureVerificationException $ex) {
            Log::error('stripe_webhook_verification_error: ' . $ex->getMessage());
            $event = new SignatureVerificationFailed($ex->getMessage(), $ex->getSigHeader(), $signingSecret);

            $this->log->log("Stripe webhook signature verification failed.", $event->toArray());
            $this->events->dispatch($event);

            return response()->json(['error' => 'Invalid signature.'], Response::HTTP_BAD_REQUEST);
        }

        $this->log->log("Verified Stripe webhook with signing secret: {$signingSecret}");

        return $next($request);
    }

}
