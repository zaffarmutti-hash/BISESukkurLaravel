import React, { useState, useEffect } from 'react';
import { useForm, Link, usePage } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import type { Student, StudentAcademicRecord, AcademicYear } from '@/types';
import type { PageProps } from '@inertiajs/core';

interface EnrollmentCreateProps {
  student?:        Student | null;
  academicRecord?: StudentAcademicRecord | null;
  isLocked?:       boolean;
  viewOnly?:       boolean;
}

export default function EnrollmentCreate({
  student: existingStudent,
  academicRecord,
  isLocked = false,
  viewOnly = false,
}: EnrollmentCreateProps) {
  const { props: rawProps } = usePage<PageProps>();
  const props = rawProps as { activeYear?: AcademicYear | null };

  const isEditing = !!existingStudent;
  const readOnly  = viewOnly || isLocked;

  const { data, setData, post, processing, errors } = useForm({
    gr_number:               existingStudent?.gr_number               ?? '',
    admission_date:          existingStudent?.admission_date          ?? '',
    full_name:               existingStudent?.full_name               ?? '',
    surname:                 existingStudent?.surname                 ?? '',
    father_name:             existingStudent?.father_name             ?? '',
    father_cnic:             existingStudent?.father_cnic             ?? '',
    cnic:                    existingStudent?.cnic                    ?? '',
    b_form:                  existingStudent?.b_form                  ?? '',
    date_of_birth:           existingStudent?.date_of_birth           ?? '',
    marks_of_identification: existingStudent?.marks_of_identification ?? '',
    gender:                  existingStudent?.gender                  ?? '',
    nationality:             existingStudent?.nationality             ?? 'Pakistani',
    religion:                existingStudent?.religion                ?? 'Islam',
    medium_of_instruction:   existingStudent?.medium_of_instruction   ?? 'Urdu',
    phone:                   existingStudent?.phone                   ?? '',
    guardian_name:           existingStudent?.guardian_name           ?? '',
    guardian_cnic:           existingStudent?.guardian_cnic           ?? '',
    guardian_phone:          existingStudent?.guardian_phone          ?? '',
    address:                 existingStudent?.address                 ?? '',
    postal_code:             existingStudent?.postal_code             ?? '',
    remarks:                 existingStudent?.remarks                 ?? '',
    class_level:             academicRecord?.class_level              ?? 'ssc_part1',
    subject_group:           academicRecord?.subject_group            ?? 'science',
    student_type:            academicRecord?.student_type             ?? 'fresh',
    save_as:                 'final',
    photo:                   null as File | null,
    _method:                 isEditing ? 'PUT' : 'POST',
  });

  const [photoPreview, setPhotoPreview] = useState<string | null>(
    existingStudent?.photo_path ? `/storage/${existingStudent.photo_path}` : null
  );

  const [dobInWords, setDobInWords] = useState('');

  // Date of birth to words converter helper
  useEffect(() => {
    if (data.date_of_birth) {
      const date = new Date(data.date_of_birth);
      if (!isNaN(date.getTime())) {
        const days = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth',
                      'Eleventh', 'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth', 'Seventeenth',
                      'Eighteenth', 'Nineteenth', 'Twentieth', 'Twenty-First', 'Twenty-Second', 'Twenty-Third',
                      'Twenty-Fourth', 'Twenty-Fifth', 'Twenty-Sixth', 'Twenty-Seventh', 'Twenty-Eighth',
                      'Twenty-Ninth', 'Thirtieth', 'Thirty-First'];
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        const dayWord = days[date.getDate() - 1];
        const monthWord = months[date.getMonth()];
        const year = date.getFullYear();
        
        setDobInWords(`${dayWord} ${monthWord} ${year}`);
      } else {
        setDobInWords('');
      }
    } else {
      setDobInWords('');
    }
  }, [data.date_of_birth]);

  function handlePhoto(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (file) {
      setData('photo', file);
      setPhotoPreview(URL.createObjectURL(file));
    }
  }

  function submit(saveAs: 'draft' | 'final') {
    data.save_as = saveAs;
    data._method = isEditing ? 'PUT' : 'POST';

    if (isEditing && existingStudent) {
      post(route('school.students.update', existingStudent.id), {
        forceFormData: true,
        preserveScroll: true,
      });
    } else {
      post(route('school.students.store'), {
        forceFormData: true,
        preserveScroll: true,
      });
    }
  }

  function formatCnic(val: string): string {
    const clean = val.replace(/\D/g, '');
    if (clean.length <= 5) return clean;
    if (clean.length <= 12) return `${clean.slice(0, 5)}-${clean.slice(5)}`;
    return `${clean.slice(0, 5)}-${clean.slice(5, 12)}-${clean.slice(12, 13)}`;
  }

  function formatPhone(val: string): string {
    const clean = val.replace(/\D/g, '');
    if (clean.length <= 4) return clean;
    return `${clean.slice(0, 4)}-${clean.slice(4, 11)}`;
  }

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <Link href={route('school.students.index')} className="text-gray-500 hover:text-gray-700">Enrollment Forms</Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">{isEditing ? 'Edit Enrollment' : 'New Enrollment'}</span>
        </nav>
      }
    >
      <div className="ec-container">
        
        {/* Page Header */}
        <div className="ec-page-header">
          <Link href={route('school.students.index')} className="ec-back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to list
          </Link>
          <h1 className="ec-page-title">{isEditing ? 'Edit Enrollment' : 'New Enrollment'}</h1>
        </div>

        {/* Grace Phase Surcharge Warning Banner */}
        {props.activeYear?.enrollment_phase?.phase === 'grace' && (
          <div className="ec-grace-warning-banner">
            <div className="ec-grace-warning-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 className="ec-grace-warning-title">Late Fee Phase Active</h3>
              <p className="ec-grace-warning-text">
                The enrollment window is currently in the grace period. New student records registered during this phase will be subject to the late fee surcharge during challan generation.
              </p>
            </div>
          </div>
        )}

        {/* Locked Banner */}
        {isLocked && (
          <div className="ec-locked-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            This record is locked. It is part of a confirmed/paid invoice and cannot be modified.
          </div>
        )}

        <form onSubmit={(e) => e.preventDefault()}>

          {/* Section 1: Academic Information */}
          <div className="ec-card">
            <div className="ec-card-header">
              <div className="ec-section-number">1</div>
              <div className="ec-section-title">ACADEMIC INFORMATION</div>
            </div>
            <div className="ec-card-body">
              <div className="ec-form-grid">
                
                {/* Academic Session */}
                <div className="ec-form-group">
                  <label className="ec-required">Academic Session</label>
                  <select value={props.activeYear?.label ?? '2026'} disabled>
                    <option>{props.activeYear?.label ?? '2026'}</option>
                  </select>
                </div>

                {/* Enrollment For */}
                <div className="ec-form-group">
                  <label className="ec-required">Enrollment For</label>
                  <select 
                    value={data.class_level} 
                    onChange={(e) => setData('class_level', e.target.value)}
                    disabled={readOnly}
                    className={errors.class_level ? 'ec-input-error' : ''}
                  >
                    <option value="ssc_part1">SSC - I</option>
                    <option value="ssc_part2">SSC - II</option>
                    <option value="hsc_part1">HSC - I</option>
                    <option value="hsc_part2">HSC - II</option>
                  </select>
                  {errors.class_level && <p className="ec-error">{errors.class_level}</p>}
                </div>

                {/* Group */}
                <div className="ec-form-group">
                  <label className="ec-required">Group</label>
                  <select 
                    value={data.subject_group} 
                    onChange={(e) => setData('subject_group', e.target.value as 'science' | 'arts' | 'commerce')}
                    disabled={readOnly}
                    className={errors.subject_group ? 'ec-input-error' : ''}
                  >
                    <option value="science">Science</option>
                    <option value="arts">Arts</option>
                    <option value="commerce">Commerce</option>
                    <option value="general">General Science</option>
                  </select>
                  {errors.subject_group && <p className="ec-error">{errors.subject_group}</p>}
                </div>

                {/* Student Type */}
                <div className="ec-form-group">
                  <label>Student Type</label>
                  <select 
                    value={data.student_type} 
                    onChange={(e) => setData('student_type', e.target.value as 'fresh' | 'repeater' | 'private')}
                    disabled={readOnly}
                    className={errors.student_type ? 'ec-input-error' : ''}
                  >
                    <option value="fresh">Fresh</option>
                    <option value="repeater">Repeater</option>
                    <option value="private">Private</option>
                  </select>
                  {errors.student_type && <p className="ec-error">{errors.student_type}</p>}
                </div>

                {/* G.R / Roll No */}
                <div className="ec-form-group">
                  <label className="ec-required">G.R / Roll No</label>
                  <input 
                    type="text" 
                    placeholder="Enter roll number" 
                    value={data.gr_number} 
                    onChange={(e) => setData('gr_number', e.target.value)}
                    disabled={readOnly}
                    className={errors.gr_number ? 'ec-input-error' : ''}
                  />
                  {errors.gr_number && <p className="ec-error">{errors.gr_number}</p>}
                </div>

                {/* Date of Admission */}
                <div className="ec-form-group">
                  <label className="ec-required">Date of Admission</label>
                  <div className="ec-date-wrapper">
                    <input 
                      type="date" 
                      value={data.admission_date} 
                      onChange={(e) => setData('admission_date', e.target.value)}
                      disabled={readOnly}
                      className={errors.admission_date ? 'ec-input-error' : ''}
                    />
                  </div>
                  {errors.admission_date && <p className="ec-error">{errors.admission_date}</p>}
                </div>

                {/* Enrollment Reg No */}
                <div className="ec-form-group">
                  <label>Enrollment Reg No</label>
                  <input 
                    type="text" 
                    placeholder="Issued by board" 
                    value={existingStudent?.enrollment_number ?? ''} 
                    disabled 
                  />
                </div>

              </div>
            </div>
          </div>

          {/* Section 2: Bio Data */}
          <div className="ec-card">
            <div className="ec-card-header">
              <div className="ec-section-number">2</div>
              <div className="ec-section-title">BIO DATA (PERSONAL INFORMATION)</div>
            </div>
            <div className="ec-card-body">
              <div className="ec-form-grid">
                
                {/* Candidate Name */}
                <div className="ec-form-group ec-full-width">
                  <label className="ec-required">Name of Candidate (CAPITAL LETTERS)</label>
                  <input 
                    type="text" 
                    placeholder="FULL NAME AS PER OFFICIAL RECORD" 
                    value={data.full_name} 
                    onChange={(e) => setData('full_name', e.target.value.toUpperCase())}
                    disabled={readOnly}
                    className={errors.full_name ? 'ec-input-error' : ''}
                  />
                  {errors.full_name && <p className="ec-error">{errors.full_name}</p>}
                </div>

                {/* B.Form / CNIC */}
                <div className="ec-form-group">
                  <label className="ec-required">B.Form / CNIC No</label>
                  <input 
                    type="text" 
                    placeholder="00000-0000000-0" 
                    value={data.cnic} 
                    onChange={(e) => {
                      const formatted = formatCnic(e.target.value);
                      setData('cnic', formatted);
                      // Mirror to b_form for compatibility
                      setData('b_form', formatted);
                    }}
                    disabled={readOnly}
                    className={errors.cnic ? 'ec-input-error' : ''}
                  />
                  {errors.cnic && <p className="ec-error">{errors.cnic}</p>}
                </div>

                {/* Father Name */}
                <div className="ec-form-group">
                  <label className="ec-required">Father Name (CAPITAL)</label>
                  <input 
                    type="text" 
                    placeholder="FATHER'S FULL NAME" 
                    value={data.father_name} 
                    onChange={(e) => setData('father_name', e.target.value.toUpperCase())}
                    disabled={readOnly}
                    className={errors.father_name ? 'ec-input-error' : ''}
                  />
                  {errors.father_name && <p className="ec-error">{errors.father_name}</p>}
                </div>

                {/* Photo Upload Box */}
                <div className="ec-form-group ec-row-span3">
                  <div className="ec-photo-section">
                    <div className={`ec-photo-preview ${photoPreview ? 'ec-has-image' : ''} ${errors.photo ? 'ec-input-error' : ''}`}>
                      {photoPreview ? (
                        <img src={photoPreview} alt="Preview" />
                      ) : (
                        <div className="ec-photo-placeholder">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                          <span>Photo</span>
                        </div>
                      )}
                    </div>
                    {!readOnly && (
                      <>
                        <button 
                          type="button" 
                          className={`ec-btn-upload ${errors.photo ? 'ec-input-error' : ''}`}
                          onClick={() => document.getElementById('photoInput')?.click()}
                        >
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                          Choose photo
                        </button>
                        <input 
                          type="file" 
                          id="photoInput" 
                          accept="image/jpeg,image/png" 
                          style={{ display: 'none' }} 
                          onChange={handlePhoto} 
                        />
                      </>
                    )}
                    <span className="ec-photo-hint">JPG/PNG, max 2MB</span>
                    {errors.photo && <p className="ec-error">{errors.photo}</p>}
                  </div>
                </div>

                {/* Father CNIC */}
                <div className="ec-form-group">
                  <label>Father's CNIC</label>
                  <input 
                    type="text" 
                    placeholder="00000-0000000-0" 
                    value={data.father_cnic} 
                    onChange={(e) => setData('father_cnic', formatCnic(e.target.value))}
                    disabled={readOnly}
                    className={errors.father_cnic ? 'ec-input-error' : ''}
                  />
                  {errors.father_cnic && <p className="ec-error">{errors.father_cnic}</p>}
                </div>

                {/* Guardian Name */}
                <div className="ec-form-group">
                  <label>Guardian Name</label>
                  <input 
                    type="text" 
                    placeholder="GUARDIAN NAME" 
                    value={data.guardian_name} 
                    onChange={(e) => setData('guardian_name', e.target.value)}
                    disabled={readOnly}
                    className={errors.guardian_name ? 'ec-input-error' : ''}
                  />
                  {errors.guardian_name && <p className="ec-error">{errors.guardian_name}</p>}
                </div>

                {/* Guardian CNIC */}
                <div className="ec-form-group">
                  <label>Guardian CNIC</label>
                  <input 
                    type="text" 
                    placeholder="00000-0000000-0" 
                    value={data.guardian_cnic} 
                    onChange={(e) => setData('guardian_cnic', formatCnic(e.target.value))}
                    disabled={readOnly}
                    className={errors.guardian_cnic ? 'ec-input-error' : ''}
                  />
                  {errors.guardian_cnic && <p className="ec-error">{errors.guardian_cnic}</p>}
                </div>

                {/* Surname / Cast */}
                <div className="ec-form-group">
                  <label className="ec-required">Surname / Cast (CAPITAL)</label>
                  <input 
                    type="text" 
                    placeholder="SURNAME" 
                    value={data.surname} 
                    onChange={(e) => setData('surname', e.target.value.toUpperCase())}
                    disabled={readOnly}
                    className={errors.surname ? 'ec-input-error' : ''}
                  />
                  {errors.surname && <p className="ec-error">{errors.surname}</p>}
                </div>

                {/* Gender */}
                <div className="ec-form-group">
                  <label className="ec-required">Gender</label>
                  <select 
                    value={data.gender} 
                    onChange={(e) => setData('gender', e.target.value)}
                    disabled={readOnly}
                    className={errors.gender ? 'ec-input-error' : ''}
                  >
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                  {errors.gender && <p className="ec-error">{errors.gender}</p>}
                </div>

                {/* Date of Birth */}
                <div className="ec-form-group">
                  <label className="ec-required">Date of Birth</label>
                  <input 
                    type="date" 
                    value={data.date_of_birth} 
                    onChange={(e) => setData('date_of_birth', e.target.value)}
                    disabled={readOnly}
                    className={errors.date_of_birth ? 'ec-input-error' : ''}
                  />
                  {errors.date_of_birth && <p className="ec-error">{errors.date_of_birth}</p>}
                </div>

                {/* Date of Birth (In Words) */}
                <div className="ec-form-group">
                  <label>Date of Birth (In Words)</label>
                  <input 
                    type="text" 
                    value={dobInWords} 
                    placeholder="Will appear after selecting date" 
                    disabled 
                  />
                </div>

                {/* Marks of Identification */}
                <div className="ec-form-group">
                  <label className="ec-required">Marks of Identification</label>
                  <input 
                    type="text" 
                    placeholder="E.G. MOLE ON LEFT CHEEK" 
                    value={data.marks_of_identification} 
                    onChange={(e) => setData('marks_of_identification', e.target.value.toUpperCase())}
                    disabled={readOnly}
                    className={errors.marks_of_identification ? 'ec-input-error' : ''}
                  />
                  {errors.marks_of_identification && <p className="ec-error">{errors.marks_of_identification}</p>}
                </div>

                {/* Religion */}
                <div className="ec-form-group">
                  <label className="ec-required">Religion</label>
                  <select 
                    value={data.religion} 
                    onChange={(e) => setData('religion', e.target.value)}
                    disabled={readOnly}
                    className={errors.religion ? 'ec-input-error' : ''}
                  >
                    <option value="Islam">Islam</option>
                    <option value="Christianity">Christianity</option>
                    <option value="Hinduism">Hinduism</option>
                    <option value="Other">Other</option>
                  </select>
                  {errors.religion && <p className="ec-error">{errors.religion}</p>}
                </div>

                {/* Medium of Instruction */}
                <div className="ec-form-group ec-full-width">
                  <label>Medium of Instruction</label>
                  <div className="ec-medium-options">
                    {['Urdu', 'English', 'Sindhi'].map((medium) => (
                      <button
                        key={medium}
                        type="button"
                        className={`ec-medium-btn ${data.medium_of_instruction === medium ? 'ec-active' : ''}`}
                        onClick={() => !readOnly && setData('medium_of_instruction', medium)}
                      >
                        {medium}
                      </button>
                    ))}
                  </div>
                </div>

              </div>
            </div>
          </div>

          {/* Section 3: Contact Information */}
          <div className="ec-card">
            <div className="ec-card-header">
              <div className="ec-section-number">3</div>
              <div className="ec-section-title">CONTACT INFORMATION</div>
            </div>
            <div className="ec-card-body">
              <div className="ec-form-grid">
                
                {/* Mobile Number */}
                <div className="ec-form-group">
                  <label>Mobile Number (optional)</label>
                  <input 
                    type="tel" 
                    placeholder="0300-0000000" 
                    value={data.phone} 
                    onChange={(e) => setData('phone', formatPhone(e.target.value))}
                    disabled={readOnly}
                    className={errors.phone ? 'ec-input-error' : ''}
                  />
                  {errors.phone && <p className="ec-error">{errors.phone}</p>}
                </div>

                {/* Guardian Phone */}
                <div className="ec-form-group">
                  <label>Guardian Mobile</label>
                  <input 
                    type="tel" 
                    placeholder="0300-0000000" 
                    value={data.guardian_phone} 
                    onChange={(e) => setData('guardian_phone', formatPhone(e.target.value))}
                    disabled={readOnly}
                    className={errors.guardian_phone ? 'ec-input-error' : ''}
                  />
                  {errors.guardian_phone && <p className="ec-error">{errors.guardian_phone}</p>}
                </div>

                {/* Postal Code */}
                <div className="ec-form-group">
                  <label>Postal Code</label>
                  <input 
                    type="text" 
                    placeholder="e.g. 65000" 
                    value={data.postal_code} 
                    onChange={(e) => setData('postal_code', e.target.value)}
                    disabled={readOnly}
                    className={errors.postal_code ? 'ec-input-error' : ''}
                  />
                  {errors.postal_code && <p className="ec-error">{errors.postal_code}</p>}
                </div>

                {/* Home Address */}
                <div className="ec-form-group ec-full-width">
                  <label>Home Address</label>
                  <textarea 
                    placeholder="COMPLETE HOME ADDRESS..." 
                    value={data.address} 
                    onChange={(e) => setData('address', e.target.value)}
                    disabled={readOnly}
                    className={errors.address ? 'ec-input-error' : ''}
                  />
                  {errors.address && <p className="ec-error">{errors.address}</p>}
                </div>

                {/* Remarks */}
                <div className="ec-form-group ec-full-width">
                  <label>Remarks</label>
                  <textarea 
                    placeholder="ANY ADDITIONAL NOTES..." 
                    value={data.remarks} 
                    onChange={(e) => setData('remarks', e.target.value)}
                    disabled={readOnly}
                    className={errors.remarks ? 'ec-input-error' : ''}
                  />
                  {errors.remarks && <p className="ec-error">{errors.remarks}</p>}
                </div>

              </div>
            </div>

            {/* Footer Buttons */}
            {!readOnly && (
              <div className="ec-form-footer">
                <Link
                  href={route('school.students.index')}
                  className="ec-btn ec-btn-draft"
                  style={{ textDecoration: 'none', textAlign: 'center', display: 'inline-flex', justifyContent: 'center', alignItems: 'center' }}
                >
                  Cancel
                </Link>
                <button 
                  type="submit" 
                  className="ec-btn ec-btn-submit" 
                  disabled={processing}
                  onClick={() => submit('final')}
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                  Confirm &amp; Save
                </button>
              </div>
            )}
          </div>
        </form>

      </div>

      {/* Styled exactly matching the user's provided template layout */}
      <style>{`
        .ec-container { max-width: 900px; margin: 0 auto; }
        .ec-page-header { display: flex; align-items: center; margin-bottom: 24px; gap: 12px; }
        .ec-back-btn { display: inline-flex; align-items: center; gap: 6px; color: #2563eb; text-decoration: none; font-size: 14px; font-weight: 500; padding: 8px 12px; border-radius: 8px; transition: all 0.2s; }
        .ec-back-btn:hover { background: #dbeafe; }
        .ec-page-title { font-size: 24px; font-weight: 700; color: #1f2937; }
        .ec-grace-warning-banner{display:flex;align-items:center;gap:12px;padding:16px 20px;background:linear-gradient(135deg,#FFFBEB 0%,#FEF3C7 100%);border:1px solid #FCD34D;border-radius:12px;color:#92400E;font-size:14px;font-weight:500;margin-bottom:20px;box-shadow:0 8px 20px rgba(245,158,11,.06)}
        .ec-grace-warning-icon{width:36px;height:36px;background:#F59E0B;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .ec-grace-warning-icon svg{width:20px;height:20px;stroke:#fff}
        .ec-grace-warning-title{font-size:14px;font-weight:800;color:#783F0F;margin:0}
        .ec-grace-warning-text{font-size:12px;color:#92400E;margin-top:2px;line-height:1.4}
        .ec-locked-banner{display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;background:#FEFCE8;color:#92400E;border:1px solid #FDE68A;border-radius:10px;font-size:.875rem;font-weight:500;margin-bottom:1rem}
        .ec-locked-banner svg{width:18px;height:18px;flex-shrink:0}
        
        .ec-input-error { border-color: #ef4444 !important; background-color: #fef2f2 !important; }
        .ec-input-error:focus { box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important; }

        .ec-card { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.6); overflow: hidden; margin-bottom: 24px; animation: ecFadeIn 0.4s ease-out; }
        .ec-card-header { display: flex; align-items: center; gap: 12px; padding: 20px 28px; background: linear-gradient(90deg, #dbeafe, transparent); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .ec-section-number { width: 32px; height: 32px; background: #2563eb; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
        .ec-section-title { font-size: 15px; font-weight: 600; color: #1f2937; letter-spacing: 0.3px; }
        .ec-card-body { padding: 24px 28px; }
        
        .ec-form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 16px; }
        .ec-form-group { display: flex; flex-direction: column; gap: 6px; }
        .ec-full-width { grid-column: 1 / -1; }
        .ec-row-span3 { grid-row: span 3; }
        
        .ec-required::after { content: ' *'; color: #ef4444; font-weight: 600; }
        .ec-error { font-size: 11px; color: #ef4444; margin-top: 2px; font-weight: 500; }
        
        .ec-form-group label { font-size: 13px; font-weight: 500; color: #6b7280; }
        .ec-form-group input[type="text"],
        .ec-form-group input[type="date"],
        .ec-form-group input[type="tel"],
        .ec-form-group select,
        .ec-form-group textarea { padding: 10px 14px; border: 1.5px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #1f2937; background: white; transition: all 0.2s; outline: none; width: 100%; }
        
        .ec-form-group input:focus, .ec-form-group select:focus, .ec-form-group textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .ec-form-group input:disabled, .ec-form-group select:disabled, .ec-form-group textarea:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
        
        .ec-mono { font-family: 'Courier New', monospace; letter-spacing: 0.05em; }
        
        .ec-photo-section { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .ec-photo-preview { width: 120px; height: 140px; border: 2px dashed #d1d5db; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: #eff6ff; transition: all 0.2s; overflow: hidden; position: relative; }
        .ec-photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .ec-photo-placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: #9ca3af; }
        .ec-photo-placeholder svg { width: 32px; height: 32px; opacity: 0.5; }
        .ec-photo-placeholder span { font-size: 12px; }
        
        .ec-btn-upload { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: white; border: 1.5px solid #2563eb; border-radius: 8px; color: #2563eb; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .ec-btn-upload:hover { background: #2563eb; color: white; }
        .ec-photo-hint { font-size: 11px; color: #9ca3af; }
        
        .ec-medium-options { display: flex; gap: 8px; flex-wrap: wrap; }
        .ec-medium-btn { padding: 10px 24px; border: 1.5px solid #d1d5db; border-radius: 8px; background: white; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.2s; flex: 1; min-width: 80px; }
        .ec-medium-btn:hover { border-color: #2563eb; color: #2563eb; }
        .ec-medium-btn.ec-active { background: #1f2937; border-color: #1f2937; color: white; }
        
        .ec-form-footer { display: flex; justify-content: center; gap: 16px; padding: 24px 28px; background: rgba(255,255,255,0.6); border-top: 1px solid rgba(0,0,0,0.05); }
        .ec-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; outline: none; }
        .ec-btn-draft { background: white; color: #2563eb; border: 1.5px solid #2563eb; }
        .ec-btn-draft:hover { background: #eff6ff; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .ec-btn-submit { background: linear-gradient(135deg, #2563eb, #1e40af); color: white; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); }
        .ec-btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45); }
        
        @keyframes ecFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 768px) {
          .ec-form-grid { grid-template-columns: 1fr !important; }
          .ec-card-body { padding: 20px; }
          .ec-form-footer { flex-direction: column; }
          .ec-btn { width: 100%; justify-content: center; }
        }
      `}</style>
    </SchoolLayout>
  );
}
