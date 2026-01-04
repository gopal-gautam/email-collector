<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');
        
        return [
            'project_id' => Project::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => $this->faker->randomElement(['subscribed', 'pending', 'unsubscribed']),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'source_url' => $this->faker->url(),
            'referrer' => $this->faker->optional(0.7)->url(),
            'meta' => $this->faker->optional(0.5)->passthrough([
                'utm_source' => $this->faker->randomElement(['google', 'facebook', 'twitter', 'direct']),
                'utm_campaign' => $this->faker->word(),
                'utm_medium' => $this->faker->randomElement(['email', 'social', 'cpc', 'organic'])
            ]),
            'confirmed_at' => function (array $attributes) {
                return $attributes['status'] === 'subscribed' 
                    ? $this->faker->dateTimeBetween($attributes['created_at'] ?? '-30 days', 'now')
                    : null;
            },
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * Indicate that the subscription is active/subscribed.
     */
    public function subscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'subscribed',
            'confirmed_at' => $this->faker->dateTimeBetween($attributes['created_at'] ?? '-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the subscription is pending confirmation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the subscription is unsubscribed.
     */
    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unsubscribed',
        ]);
    }

    /**
     * Set a specific email for the subscription.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Set metadata for the subscription.
     */
    public function withMeta(array $meta): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => $meta,
        ]);
    }

    /**
     * Set the source URL for the subscription.
     */
    public function fromSource(string $sourceUrl): static
    {
        return $this->state(fn (array $attributes) => [
            'source_url' => $sourceUrl,
        ]);
    }

    /**
     * Create a recent subscription (within last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }
}
