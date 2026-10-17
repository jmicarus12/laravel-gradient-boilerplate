<?php

namespace App\Services\Concerns;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes one Activity Log entry (spatie/laravel-activitylog, log name "admin").
 *
 *   $this->record($actor, 'deactivated', 'Deactivated admin Maria Santos', $maria);
 *
 * The subject's name is copied into the properties so the entry still reads
 * well after the subject is deleted.
 */
trait RecordsAdminActivity
{
    /**
     * @param  array<string, mixed>  $properties
     */
    protected function record(?Admin $actor, string $event, string $description, ?Model $subject = null, array $properties = []): void
    {
        $logger = activity('admin')
            ->event($event)
            ->withProperties(array_filter([
                'subject_name' => $subject?->getAttribute('name'),
                ...$properties,
            ], fn ($value) => $value !== null && $value !== []));

        if ($subject) {
            $logger->performedOn($subject);
        }

        $actor ? $logger->causedBy($actor) : $logger->causedByAnonymous();

        $logger->log($description);
    }
}
