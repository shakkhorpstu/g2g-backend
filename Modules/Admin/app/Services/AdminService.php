<?php

namespace Modules\Admin\Services;

use App\Shared\Services\BaseService;
use Modules\Admin\Contracts\Repositories\AdminRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AdminService extends BaseService
{
    protected AdminRepositoryInterface $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * Get paginated list of admins
     *
     * @param int $perPage
     * @param string|null $search
     * @return array
     */
    public function index(int $perPage = 15, ?string $search = null): array
    {
        $result = $this->adminRepository->paginate($perPage, $search);

        return $this->success(
            $result,
            'Admins retrieved successfully.'
        );
    }

    /**
     * Get single admin by ID
     *
     * @param int $id
     * @return array
     */
    public function show(int $id): array
    {
        $admin = $this->adminRepository->find($id);

        if (!$admin) {
            $this->fail('Admin not found.', 404);
        }

        return $this->success(
            $admin,
            'Admin retrieved successfully.'
        );
    }

    /**
     * Create new admin
     *
     * @param array $data
     * @return array
     */
    public function store(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Check if email already exists
            if ($this->adminRepository->findByEmail($data['email'])) {
                $this->fail('Email already exists.', 422);
            }

            // Hash password
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $admin = $this->adminRepository->create($data);

            return $this->success(
                $admin,
                'Admin created successfully.',
                201
            );
        }, 'Failed to create admin');
    }

    /**
     * Update admin
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        return $this->executeWithTransaction(function () use ($id, $data) {
            $admin = $this->adminRepository->find($id);

            if (!$admin) {
                $this->fail('Admin not found.', 404);
            }

            // Check if email already exists for different admin
            if (isset($data['email']) && $data['email'] !== $admin['email']) {
                $existingAdmin = $this->adminRepository->findByEmail($data['email']);
                if ($existingAdmin && $existingAdmin['id'] !== $id) {
                    $this->fail('Email already exists.', 422);
                }
            }

            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $updatedAdmin = $this->adminRepository->update($id, $data);

            return $this->success(
                $updatedAdmin,
                'Admin updated successfully.'
            );
        }, 'Failed to update admin');
    }

    /**
     * Delete admin
     *
     * @param int $id
     * @return array
     */
    public function destroy(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $admin = $this->adminRepository->find($id);

            if (!$admin) {
                $this->fail('Admin not found.', 404);
            }

            $deleted = $this->adminRepository->delete($id);

            return $this->success(
                ['deleted' => $deleted],
                'Admin deleted successfully.'
            );
        }, 'Failed to delete admin');
    }
}
