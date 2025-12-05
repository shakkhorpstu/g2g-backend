<?php

namespace Modules\Core\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PswService extends BaseService
{
    protected PswRepositoryInterface $pswRepository;

    public function __construct(PswRepositoryInterface $pswRepository)
    {
        $this->pswRepository = $pswRepository;
    }

    /**
     * Get paginated list of PSWs with filters
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = DB::table('psws')->orderBy('created_at', 'desc');

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find PSW by ID
     *
     * @param int $id
     * @return \Modules\Core\Models\Psw|null
     */
    public function findById(int $id)
    {
        return $this->pswRepository->findById($id);
    }

    /**
     * Find PSW by email
     *
     * @param string $email
     * @return \Modules\Core\Models\Psw|null
     */
    public function findByEmail(string $email)
    {
        return $this->pswRepository->findByEmail($email);
    }
}
