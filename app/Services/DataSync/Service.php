<?php

namespace App\Services\DataSync;

use App\Data\Repositories\Parties;
use App\Data\Repositories\Congressmen;
use App\Data\Repositories\Departments;
use Silber\Bouncer\BouncerFacade as Bouncer;
use PragmaRX\Coollection\Package\Coollection;
use Silber\Bouncer\Database\Ability as BouncerAbility;
use App\Services\HttpClient\Service as HttpClientService;

class Service
{
    const CONGRESSMEN_ENDPOINT = 'http://apiportal.alerj.rj.gov.br/api/v1.0/proderj/api/deputadoservice';

    const PARTIES_ENDPOINT = 'https://dadosabertos.camara.leg.br/api/v2/partidos?dataInicio=1970-01-01&itens=500&ordem=ASC&ordenarPor=sigla&pagina=%s';

    public function congressmen()
    {
        $result = app(HttpClientService::class)->readJson(
            static::CONGRESSMEN_ENDPOINT
        );

        if ($result instanceof Coollection) {
            app(Congressmen::class)->sync($result['data']);
        }
    }

    public function parties()
    {
        $page = 1;

        while (true) {
            $result = app(HttpClientService::class)->readJson(
                sprintf(static::PARTIES_ENDPOINT, $page++)
            );

            if (
                $result instanceof Coollection &&
                count($result['data']['dados']) > 0
            ) {
                app(Parties::class)->sync($result['data']['dados']);
            } else {
                break;
            }
        }
    }

    public function departments()
    {
        collect(config('departments.list'))->each(function ($department) {
            app(Departments::class)->firstOrCreate($department);
        });
    }

    public function roles()
    {
        collect(config('roles.roles'))->each(function ($role) {
            Bouncer::role()->updateOrCreate(
                [
                    'name' => $role['name'],
                ],
                [
                    'title' => $role['title'],
                ]
            );
        });

        $this->rolesAbilities();
    }

    protected function cleanNonAssignedAbilities($assignedAbilities, $roleName)
    {
        BouncerAbility::all()
            ->whereNotIn('name', $assignedAbilities)
            ->each(function ($item) use ($roleName) {
                Bouncer::disallow($roleName)->to($item->name);
            });
    }

    protected function normalizeAbilities($abilitiesArray)
    {
        foreach ($abilitiesArray as $key => $item) {
            $newArray[] = $this->guessAbilityName($item, $key);
        }

        return $newArray;
    }

    public function guessAbilityName($title, $ability)
    {
        // Se a $ability for numérico, não temos o title da ability.
        // Temos o índice numérico no $ability e o nome da ability em $title
        return is_numeric($ability) ? $title : $ability;
    }

    public function findOrCreateAbility($title, $ability)
    {
        $model = BouncerAbility::where(
            'name',
            $this->guessAbilityName($title, $ability)
        )->first();

        return $model ? [$model->title, $model->name] : [$title, $ability];
    }

    public function rolesAbilities()
    {
        collect(config('roles.grants'))->each(function ($grant) {
            collect($grant['abilities'])->each(function ($title, $ability) use (
                $grant
            ) {
                list($title, $ability) = $this->findOrCreateAbility(
                    $title,
                    $ability
                );

                if (in($ability, 'everything', '*')) {
                    //If it is administrator
                    Bouncer::allow($grant['group'])->everything();
                } else {
                    Bouncer::ability()->updateOrCreate(
                        [
                            'name' => $ability,
                        ],
                        [
                            'title' => $title,
                        ]
                    );
                    Bouncer::allow($grant['group'])->to($ability);
                }
            });

            $this->cleanNonAssignedAbilities(
                $this->normalizeAbilities($grant['abilities']),
                $grant['group']
            );
        });
    }
}
