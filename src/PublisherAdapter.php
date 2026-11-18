<?php
declare(strict_types=1);

namespace Vox\SmartEngine;

interface PublisherAdapter
{
    /**
     * Publish user-approved content through an official, authorized platform API.
     * Return the platform's post identifier and canonical URL.
     */
    public function publish(array $account, array $content): array;
}
