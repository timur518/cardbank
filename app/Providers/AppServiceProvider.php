<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use App\Services\Mail\MailConfigurator;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\StubPaymentGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayContract::class, StubPaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);

        // Применяет SMTP-настройки из админки (App\Filament\Admin\Pages\NotificationSettings,
        // вкладка Email) вместо жёстко прописанных MAIL_* в .env; если хост ещё не
        // задан — остаётся дефолтный мейлер из config/mail.php.
        MailConfigurator::applyFromDatabase();
    }
}
