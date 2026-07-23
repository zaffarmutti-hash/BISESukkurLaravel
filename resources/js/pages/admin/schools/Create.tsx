import { Link, useForm as useInertiaForm } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppButton } from '@/components/shared/AppButton';
import { AppAlert } from '@/components/shared/AppAlert';
import type { District } from '@/types';

// ─── Zod Schema ───────────────────────────────────────────────────────────────

const schoolSchema = z.object({
  code:           z.string().min(1, 'School code is required').max(20),
  type:           z.enum(['school', 'college']),
  name:           z.string().min(2, 'School name is required').max(200),
  district_id:    z.string().min(1, 'District is required'),
  address:        z.string().max(500).optional().or(z.literal('')),
  principal_name: z.string().max(150).optional().or(z.literal('')),
  phone:          z.string().max(30).optional().or(z.literal('')),
  email:          z.string().email('Invalid email').optional().or(z.literal('')),
  gender:         z.enum(['male', 'female', 'mixed']),
  admin_name:     z.string().min(2, 'Admin name is required').max(150),
  admin_email:    z.string().email('Valid email required'),
  admin_password: z.string().min(8, 'Minimum 8 characters'),
});

type SchoolFormValues = z.infer<typeof schoolSchema>;

// ─── Props ────────────────────────────────────────────────────────────────────

interface CreateProps {
  districts?: District[];
}

// ─── Component ────────────────────────────────────────────────────────────────

export default function Create({ districts = [] }: CreateProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<SchoolFormValues>({
    resolver: zodResolver(schoolSchema),
    defaultValues: {
      code: '',
      type: 'school',
      name: '',
      district_id: '',
      address: '',
      principal_name: '',
      phone: '',
      email: '',
      gender: 'mixed',
      admin_name: '',
      admin_email: '',
      admin_password: '',
    },
  });

  const inertiaForm = useInertiaForm<SchoolFormValues>({
    code: '',
    type: 'school',
    name: '',
    district_id: '',
    address: '',
    principal_name: '',
    phone: '',
    email: '',
    gender: 'mixed',
    admin_name: '',
    admin_email: '',
    admin_password: '',
  });

  const districtOptions = districts.map((d) => ({ value: String(d.id), label: d.name }));

  function onSubmit(data: SchoolFormValues) {
    inertiaForm.setData(data);
    inertiaForm.post(route('superadmin.schools.store'));
  }

  function fieldError(field: keyof SchoolFormValues): string | undefined {
    return errors[field]?.message ?? inertiaForm.errors[field];
  }

  return (
    <SuperAdminLayout
      breadcrumb={
        <nav className="flex items-center gap-2 text-sm text-gray-500">
          <Link href={route('superadmin.schools')} className="hover:text-gray-700">Schools</Link>
          <span>/</span>
          <span className="text-gray-800 font-medium">Register New School</span>
        </nav>
      }
    >
      <div className="page-header">
        <h1 className="page-title">Register New School</h1>
        <p className="page-subtitle">
          Add a school/college to the BISE Sukkur system. A school admin account will be created automatically.
        </p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

          {/* Left Column: School Details */}
          <div className="lg:col-span-2 space-y-4">
            <div className="card">
              <div className="card-header"><h2 className="card-title">School Information</h2></div>
              <div className="card-body space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <AppInput
                    label="School/College Code"
                    placeholder="e.g. SUK-001"
                    hint="Unique identifier. Cannot be changed later."
                    error={fieldError('code')}
                    required
                    {...register('code')}
                  />
                  <AppSelect
                    label="Type"
                    options={[
                      { value: 'school',   label: 'School' },
                      { value: 'college',  label: 'College' },
                    ]}
                    error={fieldError('type')}
                    required
                    {...register('type')}
                  />
                </div>

                <AppInput
                  label="School Name (English)"
                  placeholder="Government Boys High School"
                  error={fieldError('name')}
                  required
                  {...register('name')}
                />

                <div className="grid grid-cols-2 gap-4">
                  <AppSelect
                    label="District"
                    options={districtOptions}
                    error={fieldError('district_id')}
                    required
                    {...register('district_id')}
                  />
                  <AppSelect
                    label="Gender Category"
                    options={[
                      { value: 'male',  label: 'Boys' },
                      { value: 'female', label: 'Girls' },
                      { value: 'mixed', label: 'Co-education / Mixed' },
                    ]}
                    error={fieldError('gender')}
                    required
                    {...register('gender')}
                  />
                </div>

                <div className="form-group">
                  <label className="form-label">Full Address</label>
                  <textarea
                    rows={2}
                    className="form-textarea w-full"
                    placeholder="Complete postal address"
                    {...register('address')}
                  />
                </div>
              </div>
            </div>

            {/* Contact */}
            <div className="card">
              <div className="card-header"><h2 className="card-title">Contact Details</h2></div>
              <div className="card-body space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <AppInput
                    label="Principal Name"
                    placeholder="Mr. / Ms. Full Name"
                    error={fieldError('principal_name')}
                    {...register('principal_name')}
                  />
                  <AppInput
                    label="Phone Number"
                    type="tel"
                    placeholder="03xx-xxxxxxx"
                    error={fieldError('phone')}
                    {...register('phone')}
                  />
                </div>
                <AppInput
                  label="School Email"
                  type="email"
                  placeholder="school@example.edu.pk"
                  error={fieldError('email')}
                  {...register('email')}
                />
              </div>
            </div>
          </div>

          {/* Right Column */}
          <div className="space-y-4">
            {/* Admin Account */}
            <div className="card border-amber-200">
              <div className="card-header bg-amber-50">
                <h2 className="card-title text-amber-800">School Admin Account</h2>
              </div>
              <div className="card-body space-y-4">
                <AppAlert
                  type="info"
                  message="A login account will be created for this school's administrator using the email and password below."
                />
                <AppInput
                  label="Admin Full Name"
                  placeholder="Administrator Name"
                  error={fieldError('admin_name')}
                  required
                  {...register('admin_name')}
                />
                <AppInput
                  label="Admin Email"
                  type="email"
                  placeholder="admin@school.edu.pk"
                  error={fieldError('admin_email')}
                  required
                  {...register('admin_email')}
                />
                <AppInput
                  label="Initial Password"
                  type="password"
                  placeholder="Minimum 8 characters"
                  hint="Admin must change this on first login."
                  error={fieldError('admin_password')}
                  required
                  {...register('admin_password')}
                />
              </div>
            </div>

            {/* Submit */}
            <AppButton
              type="submit"
              variant="primary"
              size="lg"
              loading={inertiaForm.processing}
              className="w-full justify-center"
            >
              Register School &amp; Create Account
            </AppButton>
            <Link href={route('superadmin.schools')} className="w-full block">
              <AppButton variant="secondary" className="w-full justify-center">
                Cancel
              </AppButton>
            </Link>
          </div>
        </div>
      </form>
    </SuperAdminLayout>
  );
}
