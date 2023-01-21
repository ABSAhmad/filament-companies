<?php

namespace Wallo\FilamentCompanies;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Laravel\Fortify\Fortify;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Wallo\FilamentCompanies\Http\Livewire\ApiTokenManager;
use Wallo\FilamentCompanies\Http\Livewire\CompanyEmployeeManager;
use Wallo\FilamentCompanies\Http\Livewire\CreateCompanyForm;
use Wallo\FilamentCompanies\Http\Livewire\DeleteCompanyForm;
use Wallo\FilamentCompanies\Http\Livewire\DeleteUserForm;
use Wallo\FilamentCompanies\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Wallo\FilamentCompanies\Http\Livewire\TwoFactorAuthenticationForm;
use Wallo\FilamentCompanies\Http\Livewire\UpdateCompanyNameForm;
use Wallo\FilamentCompanies\Http\Livewire\UpdatePasswordForm;
use Wallo\FilamentCompanies\Http\Livewire\UpdateProfileInformationForm;
use Wallo\FilamentCompanies\Pages\Companies\CompanySettings;
use Wallo\FilamentCompanies\Pages\Companies\CreateCompany;
use Wallo\FilamentCompanies\Pages\User\APITokens;
use Wallo\FilamentCompanies\Pages\User\Profile;
use Wallo\FilamentCompanies\Http\Livewire\ConnectedAccountsForm;
use Wallo\FilamentCompanies\Http\Livewire\SetPasswordForm;

class FilamentCompaniesServiceProvider extends ServiceProvider
{
    protected static string $name;

    protected array $pages = [];

    protected array $views = [];


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../config/filament-companies.php', 'filament-companies');

        $this->app->afterResolving(BladeCompiler::class, function () {
            if (config('filament-companies.stack') === 'filament' && class_exists(Livewire::class)) {
                Livewire::component(UpdateProfileInformationForm::getName(), UpdateProfileInformationForm::class);
                Livewire::component(UpdatePasswordForm::getName(), UpdatePasswordForm::class);
                Livewire::component(TwoFactorAuthenticationForm::getName(), TwoFactorAuthenticationForm::class);
                Livewire::component(LogoutOtherBrowserSessionsForm::getName(), LogoutOtherBrowserSessionsForm::class);
                Livewire::component(DeleteUserForm::getName(), DeleteUserForm::class);
                Livewire::component(SetPasswordForm::getName(), SetPasswordForm::class);
                Livewire::component(ConnectedAccountsForm::getName(), ConnectedAccountsForm::class);

                if (Features::hasApiFeatures()) {
                    Livewire::component(ApiTokenManager::getName(), ApiTokenManager::class);
                }

                if (Features::hasCompanyFeatures()) {
                    Livewire::component(CreateCompanyForm::getName(), CreateCompanyForm::class);
                    Livewire::component(UpdateCompanyNameForm::getName(), UpdateCompanyNameForm::class);
                    Livewire::component(CompanyEmployeeManager::getName(), CompanyEmployeeManager::class);
                    Livewire::component(DeleteCompanyForm::getName(), DeleteCompanyForm::class);
                }
            }
        });

        $this->app->resolving('filament', function () {
            Filament::registerPages($this->getPages());
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-companies');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-companies');

        foreach ($this->getPages() as $page) {
            Livewire::component($page::getName(), $page);
        }

        Fortify::viewPrefix('filament-companies::auth.');

        // $this->configureComponents();
        $this->configurePublishing();
        $this->configureRoutes();
        $this->configureCommands();
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../stubs/config/filament-companies.php' => config_path('filament-companies.php'),
        ], 'filament-companies-config');

        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
        ], 'filament-companies-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/2020_05_21_100000_create_companies_table.php' => database_path('migrations/2020_05_21_100000_create_companies_table.php'),
            __DIR__.'/../database/migrations/2020_05_21_200000_create_company_user_table.php' => database_path('migrations/2020_05_21_200000_create_company_user_table.php'),
            __DIR__.'/../database/migrations/2020_05_21_300000_create_company_invitations_table.php' => database_path('migrations/2020_05_21_300000_create_company_invitations_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'filament-companies-company-migrations');
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        if (FilamentCompanies::$registersRoutes) {
            Route::group([
                'domain' => config('filament.domain'),
                'middleware' => config('filament.middleware.base'),
                'name' => config('filament.'),
                'name' => config('filament-companies.terms_and_privacy_route_group_prefix'),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }
    }

    /**
     * Configure the commands offered by the application.
     *
     * @return void
     */
    protected function configureCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }

    protected function getPages(): array
    {
        return [
            Profile::class,
            APITokens::class,
            CompanySettings::class,
            CreateCompany::class,
        ];
    }
}
