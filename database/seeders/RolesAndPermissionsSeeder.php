<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'enrollment.view', 'enrollment.create', 'enrollment.edit', 'enrollment.delete', 'enrollment.export',
            'challan.generate', 'challan.view', 'challan.download',
            'invoice.view', 'invoice.verify', 'invoice.cancel', 'invoice.export',
            'allotment.run', 'allotment.view', 'allotment.history',
            'examination.view', 'examination.create', 'examination.edit', 'examination.delete', 'examination.export',
            'examslip.view', 'examslip.download', 'examslip.bulk_download',
            'school.view', 'school.create', 'school.edit', 'school.delete',
            'school.toggle_enrollment', 'school.toggle_exam', 'school.import_bulk',
            'user.view', 'user.create', 'user.edit', 'user.delete', 'user.reset_password', 'user.toggle_active',
            'feerate.view', 'feerate.create', 'feerate.edit', 'feerate.delete',
            'academicyear.view', 'academicyear.create_next', 'academicyear.manage_windows',
            'report.enrollment', 'report.fee_collection', 'report.invoice_status',
            'report.allotment_history', 'report.missing_examforms', 'report.export',
            'settings.view', 'settings.edit', 'settings.system_settings', 'settings.notifications',
            'district.view', 'district.manage',
            'activitylog.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $districtAdmin = Role::firstOrCreate(['name' => 'district_admin', 'guard_name' => 'web']);
        $schoolAdmin = Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
        $assistantController = Role::firstOrCreate(['name' => 'assistant_controller', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permission::all());

        $districtAdmin->syncPermissions([
            'enrollment.view', 'invoice.view', 'examination.view', 'examslip.view', 'school.view',
            'report.enrollment', 'report.fee_collection', 'report.invoice_status',
            'report.allotment_history', 'report.missing_examforms', 'report.export',
            'district.view',
        ]);

        $schoolAdmin->syncPermissions([
            'enrollment.view', 'enrollment.create', 'enrollment.edit', 'enrollment.delete', 'enrollment.export',
            'challan.generate', 'challan.view', 'challan.download',
            'invoice.view',
            'examination.view', 'examination.create', 'examination.edit', 'examination.delete', 'examination.export',
            'examslip.view', 'examslip.download', 'examslip.bulk_download',
            'report.enrollment', 'report.missing_examforms',
        ]);

        $assistantController->syncPermissions(['invoice.view']);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
