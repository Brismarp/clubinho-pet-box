<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Domain;

enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';
}
