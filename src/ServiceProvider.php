<?php

namespace Dominservice\LaraStripe;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    private $lpMigration = 0;

    /**
 * Bootstrap the application services.
 *
 * @return void
 */
    public function boot(Router $router, Filesystem $filesystem)
    {
        /** Middleware */
        $router->aliasMiddleware('stripe.verify', Http\Middleware\VerifySignature::class);

        /** Migrations */
        $this->publishes([
            __DIR__.'/../database/migrations/create_stripe_tables.php.stub' => $this->getMigrationFileName($filesystem, 'create_stripe_tables'),

        ], 'stripe-migrations');


        /** Config */
        $this->publishes([
            __DIR__ . '/../config/stripe.php' => config_path('stripe.php'),
        ], 'stripe');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/stripe.php',
            'stripe'
        );

        /** Service */
        $this->app->singleton(LaraStripeService::class);
        $this->app->alias(LaraStripeService::class, 'lara_stripe');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem, $name): string
    {
        $this->lpMigration++;
        $timestamp = date('Y_m_d_Hi'.str_pad($this->lpMigration, 2, "0", STR_PAD_LEFT));

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.'*'.$name.'.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_{$name}.php")
            ->first();
    }
}