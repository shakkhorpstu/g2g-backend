<?php

namespace Modules\Profile\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allowedMimes = json_encode([
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/svg+xml',
            'application/pdf'
        ]);

        $documentTypes = [
            // Required Documents
            [
                'title' => 'Proof of Identity',
                'key' => 'proof_of_id',
                'description' => 'Government-issued ID for identity verification',
                'both_sided' => true,
                'is_required' => true,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240, // 10MB
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'title' => 'Vulnerable Sector Check',
                'key' => 'vulnerable_sector_check',
                'description' => 'Police clearance for vulnerable sector work',
                'both_sided' => false,
                'is_required' => true,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'title' => 'Personal Support Worker Certificate',
                'key' => 'psw_certificate',
                'description' => 'Valid PSW certification',
                'both_sided' => false,
                'is_required' => true,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'title' => 'First Aid & CPR Certificate',
                'key' => 'first_aid_cpr',
                'description' => 'Current First Aid and CPR certification',
                'both_sided' => false,
                'is_required' => true,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 4,
                'active' => true,
            ],
            
            // Optional Documents
            [
                'title' => 'ACLS Certificate',
                'key' => 'acls_certificate',
                'description' => 'Advanced Cardiovascular Life Support certification',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 5,
                'active' => true,
            ],
            [
                'title' => 'Mask Fit Certificate',
                'key' => 'mask_fit_certificate',
                'description' => 'N95 mask fit testing certificate',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 6,
                'active' => true,
            ],
            [
                'title' => 'TB Test',
                'key' => 'tb_test',
                'description' => 'Tuberculosis test results',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 7,
                'active' => true,
            ],
            [
                'title' => 'Tetanus and Diphtheria',
                'key' => 'tetanus_diphtheria',
                'description' => 'Tetanus and Diphtheria vaccination record',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 8,
                'active' => true,
            ],
            [
                'title' => 'Measles, Mumps, Rubella',
                'key' => 'mmr',
                'description' => 'MMR vaccination record',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 9,
                'active' => true,
            ],
            [
                'title' => 'Varicella (Chickenpox)',
                'key' => 'varicella',
                'description' => 'Varicella (Chickenpox) vaccination record',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 10,
                'active' => true,
            ],
            [
                'title' => 'Covid-19 Vaccination',
                'key' => 'covid_vaccination',
                'description' => 'COVID-19 vaccination record',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 11,
                'active' => true,
            ],
            [
                'title' => 'Critical Care Certificate',
                'key' => 'critical_care_certificate',
                'description' => 'Critical care certification',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 12,
                'active' => true,
            ],
            [
                'title' => 'Articles of Incorporation',
                'key' => 'articles_of_incorporation',
                'description' => 'Business incorporation documents',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 13,
                'active' => true,
            ],
            [
                'title' => 'Proof of RNAO Membership',
                'key' => 'rnao_membership',
                'description' => 'Registered Nurses Association of Ontario membership proof',
                'both_sided' => false,
                'is_required' => false,
                'allowed_mime' => $allowedMimes,
                'max_size_kb' => 10240,
                'sort_order' => 14,
                'active' => true,
            ],
        ];

        $now = now();

        foreach ($documentTypes as &$type) {
            $type['created_at'] = $now;
            $type['updated_at'] = $now;
        }

        // Check if data already exists to prevent duplicates
        $existingCount = DB::table('document_types')->count();
        
        if ($existingCount === 0) {
            DB::table('document_types')->insert($documentTypes);
            $this->command->info('Document types seeded successfully!');
        } else {
            $this->command->warn('Document types already exist. Skipping seeding.');
        }
    }
}
