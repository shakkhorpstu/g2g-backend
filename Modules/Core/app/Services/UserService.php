<?php

namespace Modules\Core\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Contracts\Repositories\UserRepositoryInterface;

class UserService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get paginated list of users
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return \Modules\Core\Models\User|null
     */
    public function findById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return \Modules\Core\Models\User|null
     */
    public function findByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }
}
