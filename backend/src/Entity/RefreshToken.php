<?php

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
#[ORM\Index(name: 'IDX_REFRESH_TOKEN_USERNAME', fields: ['username'])]
#[ORM\Index(name: 'IDX_REFRESH_TOKEN_VALID', fields: ['valid'])]
#[ORM\Index(name: 'IDX_REFRESH_TOKEN_FAMILY', fields: ['family'])]
class RefreshToken extends BaseRefreshToken
{
}
