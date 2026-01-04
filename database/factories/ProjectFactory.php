<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Symfony\Component\Uid\Ulid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'public_id' => (string) new Ulid(),
            'name' => $this->faker->words(3, true) . ' Newsletter',
            'description' => $this->faker->sentence(),
            'allowed_origins' => json_encode([
                'https://' . $this->faker->domainName(),
                'https://www.' . $this->faker->domainName()
            ]),
            'api_key' => 'nlc_' . bin2hex(random_bytes(28)),
            'double_opt_in' => $this->faker->boolean(70),
            'welcome_email' => $this->faker->boolean(60),
            'admin_notifications' => $this->faker->boolean(50),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the project is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the project has double opt-in enabled.
     */
    public function withDoubleOptIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'double_opt_in' => true,
        ]);
    }

    /**
     * Indicate that the project has welcome emails enabled.
     */
    public function withWelcomeEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'welcome_email' => true,
        ]);
    }

    /**
     * Configure allowed origins.
     */
    public function withOrigins(array $origins): static
    {
        return $this->state(fn (array $attributes) => [
            'allowed_origins' => json_encode($origins),
        ]);
    }
}
