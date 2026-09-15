<?php

namespace App\ApiFilter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\State\ParameterNotFound;
use App\Entity\Specialite;
use Doctrine\ORM\QueryBuilder;

/** Masque la spécialité technique dans les collections qui l'exigent. */
final class MasquerSpecialiteParDefautFilter implements FilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /** @param array<string, mixed> $context */
    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $parameterMetadata = $context['parameter'] ?? null;
        if (!$parameterMetadata instanceof Parameter) {
            return;
        }

        $value = $parameterMetadata->getValue();
        if (Specialite::class !== $resourceClass || $value instanceof ParameterNotFound || true !== $value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameter = $queryNameGenerator->generateParameterName('specialite_par_defaut');
        $queryBuilder
            ->andWhere(sprintf('%s.intitule != :%s', $alias, $parameter))
            ->setParameter($parameter, Specialite::SANS_SPECIALITE);
    }
}
