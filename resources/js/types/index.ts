// ─── Core Domain Types ──────────────────────────────────────────────────────

export interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  role: 'super_admin' | 'district_admin' | 'school_admin';
  permissions: string[];
  is_active?: boolean;
  school?: School | null;
  district?: District | null;
}

export interface School {
  id: number;
  name: string;
  urdu_name?: string | null;
  code: string;
  type: 'government' | 'private' | 'semi-government';
  district_id: number;
  district?: District;
  tehsil_id?: number | null;
  tehsil?: Tehsil | null;
  address?: string | null;
  principal_name?: string | null;
  phone?: string | null;
  email?: string | null;
  affiliation_number?: string | null;
  gender_category?: 'boys' | 'girls' | 'mixed';
  allowed_levels?: ('matric' | 'intermediate')[];
  is_active: boolean;
}

export interface District {
  id: number;
  name: string;
  code?: string;
  school_count?: number;
  student_count?: number;
  verified_amount?: number;
  pending_invoices?: number;
  missing_exam_forms?: number;
}

export interface Tehsil {
  id: number;
  name: string;
  district_id: number;
}

export interface AcademicYear {
  id: number;
  label: string;
  year: number;
  is_active: boolean;
  is_enrollment_open: boolean;
  is_examination_open: boolean;
  enrollment_start?: string | null;
  enrollment_end?: string | null;
  exam_start?: string | null;
  exam_end?: string | null;
  enrollment_open_date?: string | null;
  enrollment_close_date?: string | null;
  enrollment_grace_end?: string | null;
  examination_open_date?: string | null;
  examination_close_date?: string | null;
  examination_grace_end?: string | null;
  enrollment_phase?: WindowPhaseResult | null;
  examination_phase?: WindowPhaseResult | null;
}

// ─── Three-Phase Window System ────────────────────────────────────────────────

export type WindowPhase = 'normal' | 'grace' | 'closed';

export interface WindowPhaseResult {
  phase: WindowPhase;
  is_access_allowed: boolean;
  normal_start: string | null;
  normal_end: string | null;
  grace_end: string | null;
  next_transition: string | null;
  next_transition_label: string;
  override_level: 'global' | 'district' | 'school';
  is_exception_based: boolean;
}

export interface WindowOverride {
  id: number;
  scope_type: 'district' | 'school';
  scope_id: number;
  scope_name: string;
  window_type: 'enrollment' | 'examination';
  normal_start: string;
  normal_end: string;
  grace_end: string | null;
  has_grace: boolean;
  is_active: boolean;
  created_at?: string;
}

// ─── Student & Enrollment ────────────────────────────────────────────────────

export type StudentStatus = 'pending_challan' | 'challan_submitted' | 'enrolled';
export type AcademicRecordStatus = 'draft' | 'final' | 'pending_challan' | 'challan_submitted' | 'enrolled';
export type ExamFormStatus = 'draft' | 'final' | 'submitted' | 'confirmed' | 'rejected';
export type ClassLevel = 'matric' | 'intermediate';
export type SubjectGroup = 'science' | 'arts' | 'commerce';
export type Gender = 'male' | 'female';
export type StudentType = 'fresh' | 'repeater' | 'private';

export interface StudentAcademicRecord {
  id: number;
  student_id: number;
  academic_year_id: number;
  class_level: string;
  subject_group: SubjectGroup;
  student_type: StudentType;
  status: AcademicRecordStatus;
  is_locked: boolean;
  locked_at?: string | null;
  previous_record_id?: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface Student {
  id: number;
  full_name: string;
  urdu_name?: string | null;
  father_name: string;
  mother_name?: string | null;
  date_of_birth?: string | null;
  gender: Gender;
  cnic_or_bform?: string | null;
  cnic?: string | null;
  b_form?: string | null;
  enrollment_number?: string | null;
  class_level: ClassLevel;
  subject_group?: SubjectGroup | null;
  status: StudentStatus;
  school_id: number;
  academic_year_id: number;
  photo?: string | null;
  photo_path?: string | null;
  domicile_district?: string | null;
  nationality?: string | null;
  religion?: string | null;
  address?: string | null;
  phone?: string | null;
  guardian_phone?: string | null;
  academic_record?: StudentAcademicRecord | null;
  student_type?: StudentType | null;

  // New fields from HTML template
  gr_number?: string | null;
  admission_date?: string | null;
  father_cnic?: string | null;
  guardian_name?: string | null;
  guardian_cnic?: string | null;
  surname?: string | null;
  marks_of_identification?: string | null;
  medium_of_instruction?: string | null;
  postal_code?: string | null;
  remarks?: string | null;
}

export interface Enrollment {
  id: number;
  student_id: number;
  student?: Student;
  school_id: number;
  academic_year_id: number;
  class_level: ClassLevel;
  subject_group: SubjectGroup;
  status: StudentStatus;
  challan_id?: number | null;
  enrollment_number?: string | null;
  created_at: string;
  updated_at: string;
}

// ─── Challan & Invoice ───────────────────────────────────────────────────────

export type ChallanStatus =
  | 'draft'
  | 'generated'
  | 'submitted'
  | 'verified'
  | 'rejected';

export interface Challan {
  id: number;
  challan_number: string;
  school_id: number;
  academic_year_id: number;
  type: 'enrollment' | 'examination';
  challan_type: string;
  status: ChallanStatus;
  total_students: number;
  student_count: number;
  amount: number;
  total_amount_paisas: number;
  late_fee_surcharge_paisas?: number;
  fee_phase?: 'normal' | 'grace';
  payment_date?: string | null;
  generated_at?: string | null;
  submitted_at?: string | null;
  verified_at?: string | null;
  bank_name?: string | null;
  bank_branch?: string | null;
  bank_reference?: string | null;
  rejection_reason?: string | null;
  deposit_slip_path?: string | null;
  created_at: string;
  school?: School;
}

export interface Invoice {
  id: number;
  invoice_number: string;
  challan_id: number;
  challan?: Challan;
  school_id: number;
  amount: number;
  total_amount_paisas?: number;
  late_fee_surcharge_paisas?: number;
  fee_phase?: 'normal' | 'grace';
  status: ChallanStatus;
  payment_date?: string | null;
  bank_name?: string | null;
  transaction_id?: string | null;
  created_at: string;
}

// ─── Fee ────────────────────────────────────────────────────────────────────

export interface FeeRate {
  id: number;
  academic_year_id: number;
  class_level: ClassLevel;
  subject_group?: SubjectGroup | null;
  type: 'enrollment' | 'examination';
  amount: number;
  is_active: boolean;
}

export interface FeeStructure {
  id: number;
  academic_year_id: number;
  class_level: string;
  student_type: string;
  fee_type: string;
  amount_paisas: number;
  late_fee_surcharge_paisas: number;
  is_active: boolean;
}

// ─── Examination ─────────────────────────────────────────────────────────────

export interface ExamForm {
  id: number;
  student_id: number;
  student?: Student;
  school_id: number;
  academic_year_id: number;
  class_level: ClassLevel;
  subjects: ExamSubject[];
  status: ExamFormStatus;
  challan_id?: number | null;
  exam_center_id?: number | string | null;
  student_academic_record?: StudentAcademicRecord | null;
  seat_number?: string | null;
}

export interface ExamSubject {
  id: number;
  subject_name: string;
  subject_code: string;
  is_elective: boolean;
}

export interface ExamCenter {
  id: number;
  name: string;
  code: string;
  district_id: number;
  address?: string | null;
  capacity?: number | null;
  invigilator_name?: string | null;
  contact_phone?: string | null;
  is_active: boolean;
}

export interface ExamTimetable {
  id: number;
  academic_year_id: number;
  exam_center_id?: number | null;
  subject_code: string;
  subject_name: string;
  exam_date: string;
  start_time: string;
  end_time: string;
  class_level: ClassLevel;
  total_marks: number;
}

export interface Certificate {
  id: number;
  student_id: number;
  student?: Student;
  academic_year_id: number;
  level: 'ssc' | 'hsc';
  verification_token: string;
  verification_url?: string;
  total_percentage: number;
  overall_grade: string;
  division: string;
  is_issued: boolean;
  issued_at?: string | null;
}

// ─── Activity Log ────────────────────────────────────────────────────────────

export interface ActivityLog {
  id: number;
  description: string;
  user: string;
  subject_type?: string | null;
  subject_id?: number | null;
  created_at: string;
  type?: string;
  actor?: string;
  time?: string;
}

// ─── System Health ────────────────────────────────────────────────────────────

export interface SystemHealth {
  healthy: boolean;
  database?: boolean;
  queue?: boolean;
  disk?: boolean;
  disk_free?: number | null;
  issues?: string[];
  checks?: HealthCheck[];
}

export interface HealthCheck {
  name: string;
  status: 'ok' | 'warning' | 'error';
  message?: string;
}

// ─── Pagination ───────────────────────────────────────────────────────────────

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: PaginationLink[];
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface Paginated<T> {
  data: T[];
  meta: PaginationMeta;
  links?: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

// ─── Form Errors ──────────────────────────────────────────────────────────────

export type FormErrors<T extends object> = Partial<Record<keyof T, string>>;

// ─── Select Options ───────────────────────────────────────────────────────────

export interface SelectOption {
  value: string | number;
  label: string;
}

// ─── Announcement ─────────────────────────────────────────────────────────────

export interface Announcement {
  id: number;
  title: string;
  body: string;
  type: 'info' | 'warning' | 'success';
  target_audience?: string | null;
  is_active: boolean;
  created_at: string;
}

// ─── School Exception ─────────────────────────────────────────────────────────

export interface SchoolException {
  id: number;
  school_id: number;
  school?: School;
  exception_type: string;
  description?: string | null;
  granted_by?: number | null;
  is_active: boolean;
  created_at: string;
}
