<?php

namespace App\Providers;

use App\Channel;
use App\Media;
use App\Policies\ChannelPolicy;
use App\Policies\MediaPolicy;
use App\Policies\StoryPolicy;
use App\Story;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Story::class => StoryPolicy::class,
        Media::class => MediaPolicy::class,
        Channel::class => ChannelPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('is-superuser', function($user) {
            /**
             * @var $user User
             */
            return $user->isSuper()
                ? Response::allow()
                : Response::deny('You must be a super administrator.');
        });

        Gate::define('can-modify', function($user, string $route) {
            /**
             * @var $user User
             */
            if ($user->isSuper()) {
                return true;
            }

            if ($user->isEditor() && !in_array($route, ['users'])) {
                return true;
            }

            if ($user->isContributor() && !in_array($route, ['channels', 'tags', 'users'])) {
                return true;
            }

            return false;
        });
    }
}
