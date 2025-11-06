<?php

namespace Modules\Profile\Repositories;

use App\Models\User;
use App\Shared\Repositories\BaseRepository;
use Modules\Profile\Contracts\Repositories\ProfileRepositoryInterface;

class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    /**
     * Get the model instance
     */
    protected function getModel(): User
    {
        return new User();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id)
    {
        return $this->query()->where('id', $id)->first();
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data): User
    {
        $user = $this->findById($userId);
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Update allowed profile fields only
        $allowedFields = ['name', 'email', 'phone', 'address', 'bio'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        foreach ($updateData as $field => $value) {
            $user->$field = $value;
        }
        
        $user->save();
        
        return $user;
    }

    /**
     * Delete user profile
     */
    public function deleteProfile(int $userId): bool
    {
        return $this->query()->where('id', $userId)->delete() > 0;
    }

    /**
     * Create user profile (admin only)
     */
    public function createProfile(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'] ?? 'default_password'),
            'role' => $data['role'] ?? 'user',
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);
    }

    /**
     * Get user profile with relationships
     */
    public function getProfileWithRelations(int $userId, array $relations = []): ?User
    {
        $query = User::query()->where('id', $userId);
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->first();
    }
}