<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Policies\ExamPolicy;
use App\Policies\ExamQuestionPolicy;
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
        Exam::class => ExamPolicy::class,
        ExamQuestion::class => ExamQuestionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
