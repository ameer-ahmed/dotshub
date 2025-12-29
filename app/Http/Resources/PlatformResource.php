<?php

namespace App\Http\Resources;

use App\Enums\Platform;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class PlatformResource extends JsonResource
{
    /**
     * Get the platform this resource belongs to.
     */
    abstract public static function platform(): Platform;

    /**
     * Override newCollection to resolve concrete class from container.
     *
     * This allows abstract resources to automatically resolve to the correct
     * platform-specific implementation (Web/Mobile) based on container bindings.
     */
    protected static function newCollection($resource)
    {
        // Resolve the concrete resource class from container based on platform binding
        // Pass empty array as resource parameter to get the concrete class
        $instance = app(static::class, ['resource' => []]);

        // Get the actual concrete class name (Web\Resource or Mobile\Resource)
        $concreteClass = get_class($instance);

        // Pass the concrete class to AnonymousResourceCollection
        return new AnonymousResourceCollection($resource, $concreteClass);
    }
}