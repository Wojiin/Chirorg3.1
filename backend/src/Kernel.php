<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    // Limite les environnements Symfony acceptés par ChirOrg à la production, au développement et aux tests. Dépendances utilisées : Kernel.
    /**
     * @return list<string> An array of allowed values for APP_ENV
     */
    protected function getAllowedEnvs(): array
    {
        return ['prod', 'dev', 'test'];
    }
}
