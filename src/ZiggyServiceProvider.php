<?php

namespace Tightenco\Ziggy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ZiggyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::macro('blacklist', function ($group = null) {
            return Macro::blacklist($this, $group);
        });

        Route::macro('whitelist', function ($group = null) {
            return Macro::whitelist($this, $group);
        });

        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('routes', function ($expression) {
                return "<?php echo app('" . BladeRouteGenerator::class . "')->generate({$expression}); ?>";
            });
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CommandRouteGenerator::class,
            ]);
        }
    }
}
